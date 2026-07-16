@php $company = app_setting('company_name', 'Equator Group'); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial,Helvetica,sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="height:4px; background:#263592;"></td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <p style="margin:0 0 4px; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#9ca3af;">{{ $company }}</p>
                            <h1 style="margin:0; font-size:20px; color:#111827;">Reset your password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 32px 0;">
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6; color:#374151;">
                                Hi {{ $admin->name }}, we received a request to reset the password for your admin account
                                ({{ $admin->email }}). Click the button below to choose a new password.
                            </p>
                            <p style="margin:0 0 24px; text-align:center;">
                                <a href="{{ $resetUrl }}" style="display:inline-block; background:#263592; color:#ffffff; text-decoration:none; font-size:14px; font-weight:bold; padding:12px 28px; border-radius:10px;">Reset Password</a>
                            </p>
                            <p style="margin:0 0 16px; font-size:13px; line-height:1.6; color:#6b7280;">
                                This link expires in {{ $expiresMinutes }} minutes. If you did not request a password reset,
                                you can safely ignore this email — your password will not change.
                            </p>
                            <p style="margin:0 0 4px; font-size:12px; color:#9ca3af;">If the button does not work, copy this URL into your browser:</p>
                            <p style="margin:0 0 8px; font-size:12px; word-break:break-all; color:#263592;">{{ $resetUrl }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px 32px;">
                            <hr style="border:none; border-top:1px solid #f3f4f6; margin:0 0 16px;">
                            <p style="margin:0; font-size:11px; color:#9ca3af;">&copy; {{ date('Y') }} {{ $company }}. Enterprise System.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
