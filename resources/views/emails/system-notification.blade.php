@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 16px; font-size:20px; color:#111827;">{{ $notifTitle }}</h2>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#374151;">{{ $notifBody }}</p>

    @if ($notifUrl)
    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-radius:8px; background:#1a7a35;">
                <a href="{{ $notifUrl }}" style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">View details</a>
            </td>
        </tr>
    </table>
    @endif
@endsection
