<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ShoutOutGH Receipt</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 40px; padding-top: 40px; }
        .main { background-color: #ffffff; width: 100%; max-width: 600px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 40px 20px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; }
        .greeting { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
        .message { font-size: 16px; line-height: 1.6; color: #475569; margin-bottom: 32px; }
        .receipt-card { background-color: #f1f5f9; border-radius: 12px; padding: 24px; margin-bottom: 32px; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; }
        .receipt-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { color: #64748b; font-size: 14px; font-weight: 500; }
        .value { color: #1e293b; font-size: 14px; font-weight: 600; text-align: right; }
        .amount-big { font-size: 28px; font-weight: 800; color: #3b82f6; text-align: center; margin: 24px 0; }
        .btn { display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; text-align: center; width: 100%; box-sizing: border-box; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
        .social-links { margin-top: 16px; }
        .social-links a { margin: 0 8px; color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Payment Successful</h1>
            </div>
            <div class="content">
                <div class="greeting">Hello {{ $userName }},</div>
                <p class="message">Your payment has been successfully processed. Thank you for choosing ShoutOutGH! Your learning journey continues.</p>
                
                <div class="receipt-card">
                    <div class="amount-big">
                        {{ $paymentData['currency'] ?? 'GHS' }} {{ number_format($paymentData['amount'] ?? 0, 2) }}
                    </div>
                    
                    <div class="receipt-row" style="display: table; width: 100%;">
                        <span class="label" style="display: table-cell; text-align: left;">Plan Name</span>
                        <span class="value" style="display: table-cell; text-align: right;">{{ $paymentData['plan_name'] ?? 'Premium Plan' }}</span>
                    </div>
                    
                    <div class="receipt-row" style="display: table; width: 100%; margin-top: 12px;">
                        <span class="label" style="display: table-cell; text-align: left;">Transaction ID</span>
                        <span class="value" style="display: table-cell; text-align: right;">{{ $paymentData['transaction_id'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="receipt-row" style="display: table; width: 100%; margin-top: 12px;">
                        <span class="label" style="display: table-cell; text-align: left;">Reference</span>
                        <span class="value" style="display: table-cell; text-align: right;">{{ $paymentData['reference'] ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <a href="{{ route('dashboard.main') }}" class="btn">Go to Dashboard</a>
                
                <p style="text-align: center; margin-top: 32px; color: #94a3b8; font-size: 14px;">
                    Having trouble? Contact us at <a href="mailto:support@shoutoutgh.com" style="color: #3b82f6; text-decoration: none;">support@shoutoutgh.com</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} ShoutOutGH Online Learning Platform. All rights reserved.</p>
                <div class="social-links">
                    <a href="#">Facebook</a> | <a href="#">Twitter</a> | <a href="#">Instagram</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
