<!DOCTYPE html>
<html>
<head>
    <title>Reset Password Request</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>Password Reset Request</h2>
    <p>Hello,</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p style="margin: 20px 0;">
        <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}" style="background-color: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
    </p>
    <p>This password reset link will expire in 60 minutes.</p>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>Regards,<br>ShoutOutGh</p>
</body>
</html>
