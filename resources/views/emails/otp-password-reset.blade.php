@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 16px; font-size:20px; color:#111827;">Reset your password</h2>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#374151;">
        We received a request to reset the password for your {{ config('app.name') }} account.
        Enter the code below to choose a new password. This code expires in {{ $expireMinutes }} minutes.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
        <tr>
            <td style="border-radius:10px; background:#f0fdf4; border:1px solid #bbf7d0; padding:18px 32px; text-align:center;">
                <span style="font-family:'Courier New',monospace; font-size:32px; font-weight:bold; letter-spacing:0.35em; color:#1a7a35;">{{ $code }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0; font-size:13px; color:#6b7280;">If you didn't request a password reset, you can safely ignore this email.</p>
@endsection
