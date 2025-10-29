<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px 40px;
        }
        .content h2 {
            color: #667eea;
            margin-top: 0;
        }
        .content p {
            margin: 15px 0;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .reset-button:hover {
            transform: translateY(-2px);
        }
        .or-text {
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-size: 14px;
        }
        .link-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            word-break: break-all;
            font-size: 14px;
            color: #495057;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            margin: 5px 0;
            font-size: 14px;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>
        
        <div class="content">
            <h2>Hello, <?= esc($user['full_name'] ?? $user['username']) ?>!</h2>
            
            <p>We received a request to reset the password for your Manrealstore account.</p>
            
            <p>Click the button below to reset your password:</p>
            
            <div class="button-container">
                <a href="<?= esc($reset_link) ?>" class="reset-button">Reset Password</a>
            </div>
            
            <p class="or-text">Or copy and paste this link into your browser:</p>
            
            <div class="link-box">
                <?= esc($reset_link) ?>
            </div>
            
            <div class="warning">
                <p><strong>⚠️ Important Security Information:</strong></p>
                <p>• This password reset link will expire in <strong>1 hour</strong></p>
                <p>• If you didn't request this password reset, please ignore this email</p>
                <p>• Your password will remain unchanged until you create a new one</p>
            </div>
            
            <p>If you're having trouble with the button above, copy and paste the URL into your web browser.</p>
        </div>
        
        <div class="footer">
            <p><strong>Manrealstore</strong></p>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>If you need assistance, please contact our support team.</p>
        </div>
    </div>
</body>
</html>

