@extends('emails.layout')

@php
    $methodLabel = $payment->isCash() ? 'Cash on completion' : 'Bank transfer';
@endphp

@section('content')
    <h2 style="margin:0 0 16px; font-size:20px; color:#111827;">Payment confirmed — thank you!</h2>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#374151;">
        Hi {{ $booking->consumer->name }},<br><br>
        We've confirmed your payment for booking <strong>{{ $booking->reference }}</strong>. Your invoice is attached to this email as a PDF for your records.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
        <tr>
            <td style="padding:10px 16px; background:#f8fafc; font-size:12px; color:#64748b; border-bottom:1px solid #e2e8f0;">Service</td>
            <td style="padding:10px 16px; background:#f8fafc; font-size:13px; color:#1e293b; font-weight:600; text-align:right; border-bottom:1px solid #e2e8f0;">{{ $booking->service->name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px; font-size:12px; color:#64748b; border-bottom:1px solid #e2e8f0;">Invoice reference</td>
            <td style="padding:10px 16px; font-size:13px; color:#1e293b; font-weight:600; text-align:right; border-bottom:1px solid #e2e8f0;">{{ $invoice->reference }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px; font-size:12px; color:#64748b; border-bottom:1px solid #e2e8f0;">Payment method</td>
            <td style="padding:10px 16px; font-size:13px; color:#1e293b; font-weight:600; text-align:right; border-bottom:1px solid #e2e8f0;">{{ $methodLabel }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px; font-size:12px; color:#64748b;">Amount paid</td>
            <td style="padding:10px 16px; font-size:16px; color:#1a7a35; font-weight:800; text-align:right;">Rs. {{ number_format((float) $payment->amount, 0) }}</td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-radius:8px; background:#1a7a35;">
                <a href="{{ route('consumer.bookings.show', $booking) }}" style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">View booking</a>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0; font-size:12px; line-height:1.6; color:#94a3b8;">
        Questions about this invoice? Reply to this email or reach us at {{ config('mail.invoices_from') }}.
    </p>
@endsection
