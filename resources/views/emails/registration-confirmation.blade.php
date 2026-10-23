<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DEPTH registration confirmed</title>
</head>
<body style="margin:0;background:#f5f6f8;color:#20242a;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f6f8;padding:28px 12px;">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(24,32,44,.08);">
            <tr><td style="background:#9d1118;padding:30px 36px;color:#ffffff;">
                <div style="font-size:13px;letter-spacing:2px;font-weight:700;opacity:.82;">DEPTH</div>
                <div style="font-size:27px;line-height:1.25;font-weight:700;margin-top:8px;">Registration confirmed</div>
            </td></tr>
            <tr><td style="padding:34px 36px 18px;">
                <p style="font-size:17px;line-height:1.6;margin:0 0 18px;">Dear {{ $doctor->name }},</p>
                <p style="font-size:15px;line-height:1.7;margin:0 0 18px;color:#4e5661;">Thank you for registering for the DEPTH learning programme. Your registration has been confirmed successfully.</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8f1f1;border-left:4px solid #9d1118;border-radius:6px;margin:22px 0;">
                    <tr><td style="padding:18px 20px;font-size:14px;line-height:1.7;color:#3c424a;">
                        <strong>Name:</strong> {{ $doctor->name }}<br>
                        <strong>Email:</strong> {{ $doctor->email }}
                        @if($doctor->hospital)<br><strong>Hospital / Institution:</strong> {{ $doctor->hospital }}@endif
                        @if($doctor->country)<br><strong>Country:</strong> {{ $doctor->country }}@endif
                    </td></tr>
                </table>
                @if(!\App\Models\Course::participantAccessEnabled())
                    <p style="font-size:15px;line-height:1.7;margin:0 0 24px;color:#4e5661;">Your registration has been received. Access to the DEPTH programme will be enabled later.</p>
                @else
                    <p style="font-size:15px;line-height:1.7;margin:0 0 24px;color:#4e5661;">You can use your registered email address to access the programme dashboard. No password is required.</p>
                    <table role="presentation" cellspacing="0" cellpadding="0"><tr><td style="border-radius:7px;background:#9d1118;">
                        <a href="{{ route('home', ['auth' => 'login']) }}#register" style="display:inline-block;padding:13px 22px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">Open DEPTH</a>
                    </td></tr></table>
                @endif
                <p style="font-size:13px;line-height:1.6;margin:28px 0 0;color:#7a828c;">If you did not make this registration, please ignore this email.</p>
            </td></tr>
            <tr><td style="padding:20px 36px 28px;color:#8b929b;font-size:12px;line-height:1.6;border-top:1px solid #eceef1;">This is an automated confirmation from DEPTH. Please do not reply to this email.</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
