@extends('emails.layout')

@section('content')
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
        <tr>
            <td style="display:inline-block; padding:5px 12px; border-radius:999px; background:#f0fdf4; border:1px solid #bbf7d0; font-size:11px; font-weight:bold; letter-spacing:1px; color:#166534; text-transform:uppercase;">New Website Inquiry</td>
        </tr>
    </table>

    <h2 style="margin:0 0 8px; font-size:21px; line-height:1.3; color:#111827;">{{ $data['subject'] ?: 'New message from your website' }}</h2>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#6b7280;">Someone just submitted the contact form on {{ config('app.name') }}. Details are below.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:24px;">
        <tr>
            <td style="padding:14px 18px; @if($data['phone']) border-bottom:1px solid #f1f5f9; @endif">
                <p style="margin:0; font-size:10px; font-weight:bold; letter-spacing:0.06em; text-transform:uppercase; color:#9ca3af;">Name</p>
                <p style="margin:2px 0 0; font-size:14px; color:#111827;">{{ $data['name'] }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 18px; @if($data['phone']) border-bottom:1px solid #f1f5f9; @endif">
                <p style="margin:0; font-size:10px; font-weight:bold; letter-spacing:0.06em; text-transform:uppercase; color:#9ca3af;">Email</p>
                <p style="margin:2px 0 0; font-size:14px;"><a href="mailto:{{ $data['email'] }}" style="color:#1a7a35; text-decoration:none;">{{ $data['email'] }}</a></p>
            </td>
        </tr>
        @if ($data['phone'])
        <tr>
            <td style="padding:14px 18px;">
                <p style="margin:0; font-size:10px; font-weight:bold; letter-spacing:0.06em; text-transform:uppercase; color:#9ca3af;">Phone</p>
                <p style="margin:2px 0 0; font-size:14px; color:#111827;">{{ $data['phone'] }}</p>
            </td>
        </tr>
        @endif
    </table>

    <p style="margin:0 0 8px; font-size:10px; font-weight:bold; letter-spacing:0.06em; text-transform:uppercase; color:#9ca3af;">Message</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border-left:3px solid #1a7a35; border-radius:0 8px 8px 0; margin-bottom:28px;">
        <tr>
            <td style="padding:16px 18px; font-size:14px; line-height:1.7; color:#374151;">{{ $data['message'] }}</td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-radius:8px; background:#1a7a35;">
                <a href="mailto:{{ $data['email'] }}" style="display:inline-block; padding:12px 26px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">Reply to {{ $data['name'] }} &rarr;</a>
            </td>
        </tr>
    </table>

    <p style="margin:22px 0 0; font-size:13px; color:#9ca3af; border-top:1px solid #f1f5f9; padding-top:16px;">You can also reply directly to this email &mdash; it will go straight to {{ $data['email'] }}.</p>
@endsection
