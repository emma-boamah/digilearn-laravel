<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; padding-top: 40px; }
        .main { background-color: #ffffff; width: 100%; max-width: 600px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 40px 20px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; }
        .greeting { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
        .message { font-size: 16px; line-height: 1.6; color: #475569; margin-bottom: 32px; }
        .status-card { border: 2px solid #ef4444; background-color: #fef2f2; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 32px; }
        .status-text { color: #991b1b; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .btn { display: inline-block; background-color: #ef4444; color: #ffffff !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; text-align: center; width: 100%; box-sizing: border-box; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Access Paused: Plan Expired</h1>
            </div>
            <div class="content">
                <div class="greeting">Hello {{ $userName }},</div>
                <p class="message">Your **{{ $planName }}** subscription has expired. Your access to premium lessons and quizzes has been temporarily paused.</p>
                
                <div class="status-card">
                    <div class="status-text">Subscription Expired</div>
                    <p style="margin: 0; color: #7f1d1d; font-size: 14px;">Don't worry! Your progress is saved for the next 90 days.</p>
                </div>
                
                <p class="message">Renew now to pick up exactly where you left off and continue your learning journey.</p>
                
                <a href="{{ route('pricing') }}" class="btn">Renew Access Now</a>
                
                <p style="text-align: center; margin-top: 32px; color: #94a3b8; font-size: 14px;">
                    Questions? Reach out to us at <a href="mailto:support@shoutoutgh.com" style="color: #3b82f6; text-decoration: none;">support@shoutoutgh.com</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} ShoutOutGH Online Learning Platform. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
