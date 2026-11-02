<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>DEPTH registration confirmed</title>
</head>
@php
    $participantName = trim(preg_replace('/^dr\.?\s*/i', '', $doctor->name));
    $artworkPath = public_path('web/depth/assets/email');
    $headerImage = $message->embed($artworkPath.'/thank-you-header.png');
    $salutationImage = $message->embedData(
        \App\Support\RegistrationEmailArtwork::salutation($participantName),
        'thank-you-salutation.png',
        'image/png',
    );
    $contentImage = $message->embed($artworkPath.'/thank-you-content.png');
    $buttonLeftImage = $message->embed($artworkPath.'/thank-you-button-left.png');
    $buttonRightImage = $message->embed($artworkPath.'/thank-you-button-right.png');
    $resourcesImage = $message->embed($artworkPath.'/thank-you-resources.png');
    $footerImage = $message->embed($artworkPath.'/thank-you-footer-bottom.png');
@endphp
<body style="margin:0;padding:0;background:#ececec;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Your registration for the Hetero DEPTH Series is confirmed.
</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#ececec;">
    <tr>
        <td align="center" style="padding:18px 0;">
            <table role="presentation" width="700" cellspacing="0" cellpadding="0" border="0" style="width:700px;background:#ffffff;border-collapse:collapse;">
                <tr>
                    <td style="font-size:0;line-height:0;">
                        <img src="{{ $headerImage }}" width="700" height="141" alt="DEPTH - Hetero" style="display:block;width:700px;height:141px;border:0;">
                    </td>
                </tr>
                <tr>
                    <td style="font-size:0;line-height:0;">
                        <img src="{{ $salutationImage }}" width="700" height="39" alt="Dear Dr. {{ $participantName }}" style="display:block;width:700px;height:39px;border:0;">
                    </td>
                </tr>
                <tr>
                    <td style="font-size:0;line-height:0;">
                        <img src="{{ $contentImage }}" width="700" height="433" alt="DEPTH programme schedule and scientific session" style="display:block;width:700px;height:433px;border:0;">
                    </td>
                </tr>
                <tr>
                    <td style="font-size:0;line-height:0;">
                        <table role="presentation" width="700" height="42" cellspacing="0" cellpadding="0" border="0" style="width:700px;height:42px;border-collapse:collapse;">
                            <tr>
                                <td width="156" height="42" style="width:156px;height:42px;font-size:0;line-height:0;"><a href="{{ $accessUrl }}" target="_blank" aria-label="Access your programme and resources" style="display:block;text-decoration:none;"><img src="{{ $buttonLeftImage }}" width="156" height="42" alt="" style="display:block;width:156px;height:42px;border:0;"></a></td>
                                <td width="388" height="42" align="center" valign="middle" style="width:388px;height:42px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;">
                                    <a href="{{ $accessUrl }}" target="_blank" aria-label="Access your programme and resources" style="display:block;width:384px;height:36px;border:2px solid #ffffff;border-radius:22px;background:#ed1c24;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;line-height:36px;box-shadow:0 0 0 2px #b6b6b6;white-space:nowrap;">ACCESS YOUR PROGRAMME &amp; RESOURCES</a>
                                </td>
                                <td width="156" height="42" style="width:156px;height:42px;font-size:0;line-height:0;"><a href="{{ $accessUrl }}" target="_blank" aria-label="Access your programme and resources" style="display:block;text-decoration:none;"><img src="{{ $buttonRightImage }}" width="156" height="42" alt="" style="display:block;width:156px;height:42px;border:0;"></a></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:0;line-height:0;">
                        <a href="{{ $accessUrl }}" target="_blank" aria-label="Access your programme and resources" style="display:block;text-decoration:none;">
                            <img src="{{ $resourcesImage }}" width="700" height="168" alt="Clinical case booklets, educational materials, and diagnostic tools" style="display:block;width:700px;height:168px;border:0;">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:0;line-height:0;">
                        <img src="{{ $footerImage }}" width="700" height="221" alt="Team Hetero" style="display:block;width:700px;height:221px;border:0;">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
