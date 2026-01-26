<!DOCTYPE html>
<html>
<head>
    <title>Account Access Information</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>Account Access Information</h2>
    <p>Hello {{ $user->name }},</p>
    <p>We received a request to reset the password for your ShoutOutGh account associated with this email address.</p>
    <p>However, your account is linked to your Google account. You do not have a separate password for ShoutOutGh.</p>
    <p>To sign in, please use the <strong>"Continue with Google"</strong> button on the login page.</p>
    <p>If you need to reset your Google password, please visit Google's account recovery page.</p>
    <p>Regards,<br>ShoutOutGh</p>
</body>
</html>
