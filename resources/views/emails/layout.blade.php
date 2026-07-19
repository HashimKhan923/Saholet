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
                    <tr>
                        <td style="background:#1a7a35; padding:24px 32px; text-align:center;">
                            <img src="{{ asset('images/WhiteLogo.png') }}" alt="{{ config('app.name') }}" style="height:48px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#f8fafc; border-top:1px solid #e2e8f0; text-align:center;">
                            <p style="margin:0 0 6px; font-size:12px; color:#64748b;">{{ config('app.name') }} &middot; Bahria Town Karachi, Pakistan</p>
                            <p style="margin:0 0 6px; font-size:12px; color:#64748b;">
                                <a href="mailto:info@sahoulat.com" style="color:#1a7a35; text-decoration:none;">info@sahoulat.com</a>
                                &nbsp;&middot;&nbsp;
                                <a href="https://wa.me/923313578446" style="color:#1a7a35; text-decoration:none;">+92 331 3578446</a>
                            </p>
                            <p style="margin:0; font-size:12px; color:#94a3b8;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
