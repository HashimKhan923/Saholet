@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 16px; font-size:20px; color:#111827;">Reset your password</h2>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#374151;">
        We received a request to reset the password for your {{ config('app.name') }} account.
        Click the button below to choose a new one. This link expires in {{ $expireMinutes }} minutes.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-radius:8px; background:#1a7a35;">
                <a href="{{ $url }}" style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">Reset password</a>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0; font-size:13px; color:#6b7280;">If you didn't request a password reset, you can safely ignore this email.</p>
@endsection
