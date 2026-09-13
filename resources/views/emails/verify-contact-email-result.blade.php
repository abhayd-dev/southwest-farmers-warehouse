<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { max-width: 420px; margin: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 40px; text-align: center; }
        .icon { font-size: 48px; margin-bottom: 16px; }
        .icon.success { color: #28a745; }
        .icon.error { color: #dc3545; }
        h1 { font-size: 20px; margin-bottom: 12px; }
        p { font-size: 14px; color: #666; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        @if ($success)
            <div class="icon success">&#10003;</div>
            <h1>Email Verified</h1>
            <p>Thank you — this email address has been confirmed. You can close this page now.</p>
        @else
            <div class="icon error">&#10007;</div>
            <h1>Verification Link Invalid</h1>
            <p>This link has expired, was already used, or the email address on file has since changed. Please contact your warehouse administrator for a new link.</p>
        @endif
    </div>
</body>
</html>
