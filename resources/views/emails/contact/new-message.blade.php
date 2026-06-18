<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #eee;">

                    <tr>
                        <td style="background:#263592;padding:20px 28px;">
                            <span style="color:#fff;font-size:16px;font-weight:bold;letter-spacing:.5px;">Equator Group — New Contact Message</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 18px;font-size:14px;color:#555;">You received a new message from the website contact form.</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr><td style="padding:6px 0;color:#888;width:130px;">Name</td><td style="padding:6px 0;font-weight:bold;">{{ $contactMessage->name }}</td></tr>
                                <tr><td style="padding:6px 0;color:#888;">Email</td><td style="padding:6px 0;"><a href="mailto:{{ $contactMessage->email }}" style="color:#006CCD;">{{ $contactMessage->email }}</a></td></tr>
                                @if($contactMessage->phone)
                                    <tr><td style="padding:6px 0;color:#888;">Phone</td><td style="padding:6px 0;">{{ $contactMessage->phone }}</td></tr>
                                @endif
                                @if($contactMessage->company)
                                    <tr><td style="padding:6px 0;color:#888;">Company</td><td style="padding:6px 0;">{{ $contactMessage->company }}</td></tr>
                                @endif
                                <tr><td style="padding:6px 0;color:#888;">Subject</td><td style="padding:6px 0;font-weight:bold;">{{ $contactMessage->subject }}</td></tr>
                            </table>

                            <div style="margin-top:18px;padding:16px;background:#f7f8fa;border-left:4px solid #263592;border-radius:0 8px 8px 0;">
                                <p style="margin:0;font-size:14px;line-height:1.7;color:#444;white-space:pre-line;">{{ $contactMessage->message }}</p>
                            </div>

                            <p style="margin:24px 0 0;font-size:12px;color:#999;">Received at {{ $contactMessage->created_at?->format('d M Y, H:i') }}. Reply to this email to respond directly to the sender.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;">
                            <span style="font-size:11px;color:#aaa;">This is an automated notification from the Equator Group CMS.</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
