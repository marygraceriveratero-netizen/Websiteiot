<!DOCTYPE html>
<html>
<head>
     <meta charset="UTF-8">
    <title>Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f2f2f2; padding:30px;">

    <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);"></div>
      <!-- Header -->
        <div style="background:#b71c1c; color:#fff; padding:20px; text-align:center;">
            <h1 style="margin:0; font-size:22px;">🔥 Fire Monitoring System</h1>
            <p style="margin:5px 0 0; font-size:14px;">Real-time Fire Alert Notification</p>
        </div>

     <div style="padding:25px; color:#333;">
    <p style="font-size:16px; margin-bottom:10px;">Hello!</p>
    <p style="font-size:16px; margin-bottom:10px;">Your verification code is: <strong>{{ $code }}</strong></p>
    <p style="font-size:16px; margin-bottom:10px;">Please use this code to verify your account.</p>

    <p style="font-size:16px; margin-bottom:10px;">
        Click the link below to go to the verification form:
        <a href="{{ route('verification.form') }}">Verify Your Account</a>
    </p>

    </div>
</body>
</html>