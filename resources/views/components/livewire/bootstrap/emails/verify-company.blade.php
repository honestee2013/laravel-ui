<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Email Verification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .btn { display: inline-block; padding: 0.5rem 1rem; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 0.375rem; }
    </style>
</head>
<body>
    <h2>Hello!</h2>
    <p>Please verify your <strong>{{ $company_name }}</strong> workspace by clicking the button below:</p>
    <a href="{{ $verification_url }}" class="btn">Verify Email</a>
    <p>If you didn’t request this, please ignore this email.</p>
    <hr>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>