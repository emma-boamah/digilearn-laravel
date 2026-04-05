<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Renewal Reminder</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; padding-top: 40px; }
        .main { background-color: #ffffff; width: 100%; max-width: 600px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 40px 20px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; }
        .greeting { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
        .message { font-size: 16px; line-height: 1.6; color: #475569; margin-bottom: 32px; }
        .expiring-card { border: 2px dashed #f59e0b; background-color: #fffbeb; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 32px; }
        .expiring-text { color: #92400e; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .plan-details { color: #78350f; font-size: 14px; font-weight: 500; }
        .btn { display: inline-block; background-color: #f59e0b; color: #ffffff !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; text-align: center; width: 100%; box-sizing: border-box; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
        .warning-bullets { margin: 24px 0; padding: 0; list-style: none; text-align: left; }
        .warning-bullets li { position: relative; padding-left: 28px; margin-bottom: 12px; color: #475569; font-size: 14px; }
        .warning-bullets li::before { content: '⚠️'; position: absolute; left: 0; top: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Action Required: Renew Your Plan</h1>
            </div>
            <div class="content">
                <div class="greeting">Hello {{ $userName }},</div>
                <p class="message">Your current premium access is coming to an end. To ensure you don't lose your learning momentum, please renew your plan soon.</p>
                
                <div class="expiring-card">
                    <div class="expiring-text">Expires in {{ $daysRemaining }} Day{{ $daysRemaining === 1 ? '' : 's' }}</div>
                    <div class="plan-details">Your **{{ $planName }}** plan ends on {{ now()->addDays($daysRemaining)->format('F d, Y') }}</div>
                </div>
                
                <p class="message">What happens when your plan expires?</p>
                <ul class="warning-bullets">
                    <li>Access to all video lessons will be suspended.</li>
                    <li>You will not be able to take quizzes or view quiz results.</li>
                    <li>Learning tracks and progress tracking will be paused.</li>
                    <li>Downloads and offline study materials will become unavailable.</li>
                </ul>
                
                <a href="{{ route('pricing') }}" class="btn">Renew Access Now</a>
                
                <p style="text-align: center; margin-top: 32px; color: #94a3b8; font-size: 14px;">
                    Questions? Reach out to us at <a href="mailto:support@shoutoutgh.com" style="color: #3b82f6; text-decoration: none;">support@shoutoutgh.com</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} ShoutOutGH Online Learning Platform. All rights reserved.</p>
                <p>Stay ahead of the curve with Ghana's best learning materials.</p>
            </div>
        </div>
    </div>
</body>
</html>
