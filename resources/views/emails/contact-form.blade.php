<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Feedback Submission</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px;
        }
        .user-info {
            background-color: #f8fafc;
            border-left: 4px solid #3182ce;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 0 8px 8px 0;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .label {
            color: #718096;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }
        .value {
            color: #2d3748;
            font-size: 16px;
            font-weight: 500;
        }
        .feedback-section {
            margin-top: 30px;
        }
        .feedback-section h3 {
            color: #2d3748;
            font-size: 18px;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #edf2f7;
        }
        .feedback-text {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.6;
            white-space: pre-wrap;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #edf2f7;
            border-radius: 8px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #a0aec0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Feedback Received</h1>
        </div>
        <div class="content">
            <div class="user-info">
                @if(isset($data['firstName']))
                    <div class="info-row">
                        <span class="label">Full Name</span>
                        <span class="value">{{ $data['firstName'] }} {{ $data['lastName'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email Address</span>
                        <span class="value">{{ $data['email'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value">{{ $data['phone'] ?? 'N/A' }}</span>
                    </div>
                @else
                    <div class="info-row">
                        <span class="label">User Name</span>
                        <span class="value">{{ $data['name'] ?? 'Authenticated User' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">User Email</span>
                        <span class="value">{{ $data['email'] }}</span>
                    </div>
                @endif
            </div>

            <div class="feedback-section">
                <h3>Submission Message</h3>
                <div class="feedback-text">
                    {{ $data['message'] ?? $data['feedback'] }}
                </div>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} ShoutOutGh. All rights reserved.<br>
            Sent on {{ now()->format('F j, Y \a\t g:i A') }} (UTC)
        </div>
    </div>
</body>
</html>
