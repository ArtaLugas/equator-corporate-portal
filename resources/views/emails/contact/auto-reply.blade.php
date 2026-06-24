@php
    $company = app_setting('company_name', 'Equator Group');
    $office = primary_office();
    $officeEmail = $office?->email ?: app_setting('company_email');
    $officePhone = $office?->phone;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.auto_reply.subject', ['reference' => $contactMessage->reference]) }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    {{-- Hidden preheader (inbox preview text) --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        {{ __('emails.auto_reply.preheader', ['reference' => $contactMessage->reference]) }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #eee;">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#263592;padding:22px 28px;">
                            <span style="color:#fff;font-size:16px;font-weight:bold;letter-spacing:.5px;">{{ $company }}</span>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#333;">
                                {{ __('emails.auto_reply.greeting', ['name' => $contactMessage->name]) }}
                            </p>

                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#555;">
                                {{ __('emails.auto_reply.body') }}
                            </p>

                            {{-- Reference number --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="margin:0 0 18px;background:#f7f8fa;border:1px solid #eef0f4;border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <span style="display:block;font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:#888;">{{ __('emails.auto_reply.reference_label') }}</span>
                                        <span style="display:block;margin-top:4px;font-size:18px;font-weight:bold;letter-spacing:.04em;color:#263592;">{{ $contactMessage->reference }}</span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Response time --}}
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#555;">
                                {{ __('emails.auto_reply.response_time') }}
                            </p>

                            {{-- Their message (recap) --}}
                            <p style="margin:0 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:#999;">{{ __('emails.auto_reply.your_message_label') }}</p>
                            <p style="margin:0 0 4px;font-size:13px;color:#666;"><strong>{{ __('emails.auto_reply.subject_label') }}:</strong> {{ $contactMessage->subject }}</p>
                            <div style="margin:0 0 22px;padding:14px 16px;background:#f7f8fa;border-left:4px solid #80C7E3;border-radius:0 8px 8px 0;">
                                <p style="margin:0;font-size:13px;line-height:1.7;color:#555;white-space:pre-line;">{{ $contactMessage->message }}</p>
                            </div>

                            {{-- Contact info --}}
                            @if ($officeEmail || $officePhone)
                                <p style="margin:0 0 6px;font-size:14px;color:#555;">{{ __('emails.auto_reply.contact_intro') }}</p>
                                <p style="margin:0 0 22px;font-size:14px;color:#333;">
                                    @if ($officeEmail)
                                        <a href="mailto:{{ $officeEmail }}" style="color:#006CCD;text-decoration:none;">{{ $officeEmail }}</a>
                                    @endif
                                    @if ($officeEmail && $officePhone) &nbsp;·&nbsp; @endif
                                    @if ($officePhone)
                                        <a href="tel:{{ $officePhone }}" style="color:#006CCD;text-decoration:none;">{{ $officePhone }}</a>
                                    @endif
                                </p>
                            @endif

                            {{-- Signature --}}
                            <p style="margin:0;font-size:14px;color:#555;">{{ __('emails.auto_reply.signature') }}</p>
                            <p style="margin:2px 0 0;font-size:14px;font-weight:bold;color:#263592;">{{ __('emails.auto_reply.team', ['company' => $company]) }}</p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;">
                            <span style="font-size:11px;color:#aaa;">{{ __('emails.auto_reply.automated_note') }}</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
