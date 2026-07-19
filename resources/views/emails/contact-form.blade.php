@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 16px; font-size:20px; color:#111827;">New message from your website</h2>
    <p style="margin:0 0 16px; font-size:14px; line-height:1.6; color:#374151;">Someone just submitted the contact form on {{ config('app.name') }}.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px; font-size:14px; color:#374151;">
        <tr>
            <td style="padding:4px 0; width:110px; color:#6b7280;">Name</td>
            <td style="padding:4px 0;">{{ $contactMessage->name }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0; color:#6b7280;">Email</td>
            <td style="padding:4px 0;">{{ $contactMessage->email }}</td>
        </tr>
        @if ($contactMessage->phone)
        <tr>
            <td style="padding:4px 0; color:#6b7280;">Phone</td>
            <td style="padding:4px 0;">{{ $contactMessage->phone }}</td>
        </tr>
        @endif
        @if ($contactMessage->subject)
        <tr>
            <td style="padding:4px 0; color:#6b7280;">Subject</td>
            <td style="padding:4px 0;">{{ $contactMessage->subject }}</td>
        </tr>
        @endif
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border-left:4px solid #1a7a35; margin-bottom:24px;">
        <tr>
            <td style="padding:16px; font-size:14px; line-height:1.6; color:#374151;">{{ $contactMessage->message }}</td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-radius:8px; background:#1a7a35;">
                <a href="{{ route('admin.contact-messages.show', $contactMessage) }}" style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">View in admin dashboard</a>
            </td>
        </tr>
    </table>

    <p style="margin:20px 0 0; font-size:13px; color:#6b7280;">You can also reply directly to this email to respond to {{ $contactMessage->name }}.</p>
@endsection
