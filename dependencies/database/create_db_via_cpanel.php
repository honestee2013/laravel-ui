<?php

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    die('Access denied.');
}

// Use Laravel-style storage/logs (already git-ignored and writable)
$logFile = __DIR__ . '/../storage/logs/cpanel_db_creation.log';

// Ensure log directory exists
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Helper: silent logging (no echo, no output)
function logMessage(string $message, string $logFile): void
{
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

// Load credentials securely from .env.cpanel (NOT hardcoded)
$envFile = __DIR__ . '/.env.cpanel';
if (!file_exists($envFile)) {
    logMessage('FATAL: Missing .env.cpanel file', $logFile);
    exit(1);
}

$env = parse_ini_file($envFile);
if (!$env || !isset($env['CPANEL_USERNAME'], $env['CPANEL_API_TOKEN'], $env['CPANEL_HOST'])) {
    logMessage('FATAL: Invalid or incomplete .env.cpanel', $logFile);
    exit(1);
}

$cpanelUser = $env['CPANEL_USERNAME'];
$cpanelToken = $env['CPANEL_API_TOKEN'];
$cpanelHost = $env['CPANEL_HOST'];

// Generate DB name
$dbName = '_qf_pool_' . date('ymdHis') . '_' . substr(md5(uniqid()), 0, 6);

logMessage("START: Attempting to create database: $dbName", $logFile);

// cPanel API call
$url = "https://{$cpanelHost}:2083/execute/Mysql/create_database";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => "$cpanelUser:$cpanelToken",
    CURLOPT_POSTFIELDS => http_build_query(['name' => $dbName]),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true, // Security best practice
    CURLOPT_FAILONERROR => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle result
if ($error) {
    logMessage("CURL ERROR: $error (DB: $dbName)", $logFile);
    exit(1);
} elseif ($httpCode !== 200) {
    logMessage("HTTP ERROR $httpCode (DB: $dbName). Response: $response", $logFile);
    exit(1);
} else {
    $result = json_decode($response, true);
    if ($result && ($result['status'] ?? false)) {
        logMessage("SUCCESS: Database $dbName created", $logFile);
        exit(0);
    } else {
        $errorMsg = $result['errors'][0] ?? 'Unknown API error';
        logMessage("API ERROR: $errorMsg (DB: $dbName)", $logFile);
        exit(1);
    }
}