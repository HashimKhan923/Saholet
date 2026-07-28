<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f7f5; font-family: Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7f5; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0;">
                    {{-- Brand accent bar --}}
                    <tr>
                        <td style="padding:0; line-height:0; font-size:0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                                <td width="50%" style="background:#c4341f; height:5px; line-height:5px; font-size:0;">&nbsp;</td>
                                <td width="50%" style="background:#1a7a35; height:5px; line-height:5px; font-size:0;">&nbsp;</td>
                            </tr></table>
                        </td>
                    </tr>

                    {{-- Header / logo --}}
                    <tr>
                        <td style="background:#1a7a35; padding:24px 32px; text-align:center;">
                            <img src="{{ asset('images/WhiteLogo.png') }}?v={{ filemtime(public_path('images/WhiteLogo.png')) }}" alt="{{ config('app.name') }}" style="height:48px;">
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#14532d;">
                                <tr>
                                    <td style="padding:12px 32px 4px 32px; text-align:center;">
                                        <span style="display:inline-block; padding:4px 14px; border:1px solid #4ade80; border-radius:999px; font-size:10px; font-weight:bold; letter-spacing:2px; color:#bbf7d0; text-transform:uppercase;">Facility Management Services</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 32px 24px 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-bottom:10px; font-size:12px; line-height:1.6; color:#e2f5e8;" colspan="2">
                                                    <strong style="color:#ffffff;">Address</strong><br>
                                                    Office no 502, plot no 107B, Munawar Tower, Midway B, Bahria Town, Karachi
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" style="vertical-align:top; font-size:12px; line-height:1.7; color:#e2f5e8;">
                                                    <strong style="color:#ffffff;">Phone</strong><br>
                                                    <a href="https://wa.me/923313578446" style="color:#e2f5e8; text-decoration:none;">0331 3578446 (WhatsApp)</a><br>
                                                    <a href="tel:+923313578446" style="color:#e2f5e8; text-decoration:none;">+92 331 3578446</a>
                                                </td>
                                                <td width="50%" style="vertical-align:top; font-size:12px; line-height:1.7; color:#e2f5e8;">
                                                    <strong style="color:#ffffff;">Online</strong><br>
                                                    <a href="mailto:info@sahoulat.com" style="color:#e2f5e8; text-decoration:none;">info@sahoulat.com</a><br>
                                                    <a href="{{ url('/') }}" style="color:#e2f5e8; text-decoration:none;">www.sahoulat.com</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 32px; border-top:1px solid #1a7a35; text-align:center;">
                                        <p style="margin:0; font-size:11px; color:#86c99a;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
