@extends('invoices.layout')

@php
    $docTitle = $invoice->type === 'quotation' ? 'Quotation' : 'Invoice';
    $internal = $internal ?? false;
@endphp

@section('title', ($invoice->title ?: $docTitle) . ' — ' . $invoice->reference . ($internal ? ' (Internal)' : ''))

@if ($invoice->title)
    @section('doc-heading', $invoice->title . ' — ' . $invoice->reference . ($internal ? ' — INTERNAL COPY' : ''))
    @section('doc-custom-title', $invoice->title)
@else
    @section('doc-heading', $docTitle . ' — ' . $invoice->reference . ($internal ? ' — INTERNAL COPY' : ''))
@endif

@section('doc-title', $docTitle . ($internal ? ' (Internal)' : ''))
@section('doc-meta')
    Ref: {{ $invoice->reference }}<br>
    Date: {{ $invoice->created_at->format('d M Y') }}
@endsection

@section('content')
    @include('invoices._body', [
        'billTo' => [
            'name' => $invoice->bill_to_name,
            'email' => $invoice->bill_to_email,
            'phone' => $invoice->bill_to_phone,
            'address' => $invoice->bill_to_address,
        ],
        'from' => config('app.name'),
        'lineItems' => $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'qty' => rtrim(rtrim(number_format($item->quantity, 2), '0'), '.'),
            'unitPrice' => (float) $item->unit_price,
            'actualPrice' => $item->actual_price !== null ? (float) $item->actual_price : null,
            'total' => (float) $item->total,
        ])->all(),
        'total' => (float) $invoice->total,
        'discount' => (float) $invoice->discount,
        'paymentInfo' => null,
        'notes' => $invoice->notes,
        'inspectionNotes' => $invoice->inspection_notes,
        'internal' => $internal,
    ])
@endsection
