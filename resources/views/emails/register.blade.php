<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>Hello, {{ $user->first_name }} {{ $user->last_name }}!</p>
    <p>Thank you for registering. Please click the button below to verify your email address:</p>
    <a href="{{ url('/api/verify-email?token=' . $token) }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer;">
        Verify Email Address
    </a>
    <p>If you did not create an account, no further action is required.</p>
</body>
</html>
