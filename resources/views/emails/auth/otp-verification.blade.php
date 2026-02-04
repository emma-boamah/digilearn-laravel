<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #3b82f6; padding: 20px; text-align: center; color: #ffffff; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .otp-box { background-color: #f0f9ff; border: 2px dashed #3b82f6; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0; }
        .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #3b82f6; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Verify Your Email</h1>
        </div>
        <div class="content">
            <p>Hello {{ $name }},</p>
            <p>Thank you for signing up with ShoutOutGh. To complete your registration, please use the verification code below:</p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <p>This code will expire in 10 minutes.</p>
            <p>If you didn't request this code, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ShoutOutGh. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
