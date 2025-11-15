<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔥 Fire Alert Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f2f2f2; padding:30px;">

    <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background:#b71c1c; color:#fff; padding:20px; text-align:center;">
            <h1 style="margin:0; font-size:22px;">🔥 Fire Monitoring System</h1>
            <p style="margin:5px 0 0; font-size:14px;">Real-time Fire Alert Notification</p>
        </div>

        <!-- Body -->
        <div style="padding:25px; color:#333;">

            <p style="font-size:16px; margin-bottom:10px;">Hello,</p>

            <p style="font-size:15px; margin-bottom:20px;">
                A new fire event has been recorded by the monitoring system. Please see the details below:
            </p>

            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr>
                    <td style="padding:8px; font-weight:bold; width:150px; background:#f9f9f9;">Status</td>
                    <td style="padding:8px;">{{ $status }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold; background:#f9f9f9;">Fire Type</td>
                    <td style="padding:8px;">{{ $fireType }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold; background:#f9f9f9;">Extinguisher</td>
                    <td style="padding:8px;">{{ $extinguisher }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold; background:#f9f9f9;">Location</td>
                    <td style="padding:8px;">{{ $location_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold; background:#f9f9f9;">Temperature</td>
                    <td style="padding:8px;">{{ $temperature ?? '-' }} °C</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold; background:#f9f9f9;">Smoke Level</td>
                    <td style="padding:8px;">{{ $smoke ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:bold; background:#f9f9f9;">Time</td>
                    <td style="padding:8px;">{{ $time }}</td>
                </tr>
            </table>


              <p style="margin-top:30px; font-size:14px; line-height:1.5;">
                Please check the attached image for details.
            </p>
            <div style="margin-top:25px; text-align:center;">
                <p style="font-weight:bold; margin-bottom:10px;">📷 Snapshot:</p>
                <img src="{{ $imageUrl }}" alt="Fire Snapshot" style="max-width:100%; border-radius:6px; border:1px solid #ddd;">
            </div>

            <p style="margin-top:30px; font-size:14px; line-height:1.5;">
                Please take immediate action if this is a confirmed fire emergency.
            </p>
            <p style="font-size:14px; margin-top:20px;">Stay safe, <br><b>Fire Monitoring Team</b></p>
        </div>

        <!-- Footer -->
        <div style="background:#f2f2f2; text-align:center; padding:12px; font-size:12px; color:#777;">
            © {{ date('Y') }} Fire Monitoring System. All Rights Reserved.
        </div>
    </div>

</body>
</html>
