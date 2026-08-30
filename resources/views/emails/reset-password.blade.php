{{--
    The password reset email.

    Written as a table layout with inline styles rather than as a page: every
    mail client throws away external stylesheets, and most of them still lay out
    with tables. Nothing here depends on the app's Tailwind build.
--}}
@php
    $brand = config('app.name');
    $primary = '#001330';
    $accent = '#0041a3';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:0; background-color:#f7f9fb; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#191c1e;">

    {{-- Shown in the inbox preview line, then hidden in the message itself. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Choose a new password for your {{ $brand }} account. The link is good for {{ $minutes }} minutes.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f9fb; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:560px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e0e3e5;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:{{ $primary }}; padding:28px 32px;">
                            <span style="display:block; color:#ffffff; font-size:20px; font-weight:700; letter-spacing:-0.2px;">
                                {{ $brand }}
                            </span>
                            <span style="display:block; margin-top:4px; color:#c2daff; font-size:13px;">
                                Your Biology Exam Simplified
                            </span>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px; font-size:22px; font-weight:700; color:#191c1e;">
                                Reset your password
                            </h1>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#464554;">
                                Hi{{ $name ? ' ' . $name : '' }},
                            </p>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#464554;">
                                Someone asked to reset the password on your {{ $brand }} account. Choose a new
                                one with the button below.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background-color:{{ $accent }}; border-radius:10px;">
                                        <a href="{{ $url }}"
                                            style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none;">
                                            Choose a new password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#464554;">
                                This link stops working in <strong>{{ $minutes }} minutes</strong>. Your password
                                stays as it is until you set a new one, so if you did not ask for this you can
                                ignore this email.
                            </p>

                            {{-- What a new password has to be, so the reader knows before they get there. --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:#f2f4f6; border-radius:12px; margin:0 0 24px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0 0 8px; font-size:13px; font-weight:700; color:#191c1e; text-transform:uppercase; letter-spacing:0.04em;">
                                            Your new password needs
                                        </p>
                                        <ul style="margin:0; padding-left:18px; font-size:14px; line-height:1.7; color:#464554;">
                                            <li>At least 8 characters</li>
                                            <li>At least one number</li>
                                            <li>At least one special character</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:13px; line-height:1.6; color:#767586;">
                                If the button does not work, copy this address into your browser:
                                <br>
                                <a href="{{ $url }}" style="color:{{ $accent }}; word-break:break-all;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:20px 32px; background-color:#f2f4f6; border-top:1px solid #e0e3e5;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#767586;">
                                &copy; {{ date('Y') }} {{ $brand }}. This message was sent because a password
                                reset was requested for this address.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
