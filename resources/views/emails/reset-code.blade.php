<!DOCTYPE html>
<html>
<body>
      <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);"></div>
      <!-- Header -->
        <div style="background:#b71c1c; color:#fff; padding:20px; text-align:center;">
            <h1 style="margin:0; font-size:22px;">🔥 Fire Monitoring System</h1>
            <p style="margin:5px 0 0; font-size:14px;">Real-time Fire Alert Notification</p>
        </div>


   <div style="padding:25px; color:#333;">
   <p style="font-size:16px; margin-bottom:10px;">Hello!</p>
   <p style="font-size:16px; margin-bottom:10px;">Your password reset code is:</p>
   <h3 style="color:#1d4ed8;">{{ $code }}</h3>
   <p style="font-size:16px; margin-bottom:10px;">Use this code on the reset page to change your password.</p>

   <p style="margin-top: 20px;">
    Click the link below to open the reset page:
    <a href="{{ url('/reset-password') }}" style="color:#2563eb;">Reset Password</a>
  </p>

  <p>Thank you!</p>
</body>
</html>