<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $replySubject }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #eee;">

                    <tr>
                        <td style="background:#263592;padding:20px 28px;">
                            <span style="color:#fff;font-size:16px;font-weight:bold;letter-spacing:.5px;">{{ app_setting('company_name', 'Equator Group') }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:14px;color:#444;">Dear {{ $contactMessage->name ?: 'Sir/Madam' }},</p>

                            <div style="font-size:14px;line-height:1.8;color:#444;white-space:pre-line;">{{ $replyBody }}</div>

                            <p style="margin:24px 0 0;font-size:14px;color:#444;">Best regards,<br><strong>{{ app_setting('company_name', 'Equator Group') }}</strong></p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;">
                            <p style="margin:0 0 6px;font-size:12px;color:#888;">In reply to your message: "{{ $contactMessage->subject }}"</p>
                            <span style="font-size:11px;color:#aaa;">You can simply reply to this email to continue the conversation.</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
