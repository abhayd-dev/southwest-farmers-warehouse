<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Please confirm your email address</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background-color: #1a73e8; color: #fff; padding: 30px; text-align: center; }
        .header h1 { font-size: 20px; margin-bottom: 6px; }
        .body { padding: 30px; font-size: 14px; line-height: 1.6; }
        .btn-row { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 14px 36px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; background-color: #1a73e8; color: #fff; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
        .fallback-link { word-break: break-all; font-size: 12px; color: #1a73e8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Confirm Your Email Address</h1>
        </div>

        <div class="body">
            <p>Hello,</p>
            <p style="margin-top: 12px;">
                The email address <strong>{{ $recipient }}</strong> was entered as the contact for
                <strong>{{ $label }}</strong> in the Southwest Farmers Warehouse system.
            </p>
            <p style="margin-top: 12px;">
                Please confirm this is your correct email address by clicking the button below.
                This helps make sure important notifications actually reach you.
            </p>

            <div class="btn-row">
                <a href="{{ $verifyUrl }}" class="btn">Confirm Email Address</a>
            </div>

            <p style="margin-top: 20px;">If the button doesn't work, copy and paste this link into your browser:</p>
            <p class="fallback-link">{{ $verifyUrl }}</p>

            <p style="margin-top: 20px; color: #888; font-size: 12px;">
                This link is valid for 7 days. If you did not expect this email, you can safely ignore it.
            </p>
        </div>

        <div class="footer">
            This email was sent by the Warehouse Management System.<br>
            If you did not expect this email, please contact your warehouse administrator.
        </div>
    </div>
</body>
</html>
