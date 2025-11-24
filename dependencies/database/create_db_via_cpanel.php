<?php
// Standalone script - no Laravel dependencies
$cpanelUser = $_ENV['CPANEL_USERNAME'] ?? 'your_cpanel_user';
$cpanelToken = $_ENV['CPANEL_API_TOKEN'] ?? 'your_api_token';
$cpanelHost = $_ENV['CPANEL_HOST'] ?? 'yourdomain.com';
$domain = $_ENV['APP_DOMAIN'] ?? 'quickerfaster.com';

$dbName = $argv[1] ?? die("Usage: php create_db_via_cpanel.php <db_name>\n");

// cPanel API call
$url = "https://{$cpanelHost}:2083/execute/Mysql/create_database";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$cpanelUser:$cpanelToken");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['name' => $dbName]));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($result, true);
if ($result['status'] ?? false) {
    echo "SUCCESS: Database $dbName created\n";
    exit(0);
} else {
    fwrite(STDERR, "ERROR: " . ($result['errors'][0] ?? 'Unknown error') . "\n");
    exit(1);
}