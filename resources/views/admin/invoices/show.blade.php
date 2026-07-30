@extends('invoices.layout')

@php
    $docTitle = $invoice->type === 'quotation' ? 'Quotation' : 'Invoice';
@endphp

@section('title', ($invoice->title ?: $docTitle) . ' — ' . $invoice->reference)

@if ($invoice->title)
    @section('doc-heading', $invoice->title . ' — ' . $invoice->reference)
    @section('doc-custom-title', $invoice->title)
@else
    @section('doc-heading', $docTitle . ' — ' . $invoice->reference)
@endif

@section('toolbar')
    <a href="{{ route('admin.invoices.index') }}">&larr; Back to invoices</a>
    <a href="{{ route('admin.invoices.edit', $invoice) }}">Edit</a>
    <a href="{{ route('admin.invoices.download', $invoice) }}" class="primary">Download PDF</a>
@endsection

@section('doc-title', $docTitle)
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
            'total' => (float) $item->total,
        ])->all(),
        'total' => (float) $invoice->total,
        'discount' => (float) $invoice->discount,
        'paymentInfo' => null,
        'notes' => $invoice->notes,
        'inspectionNotes' => $invoice->inspection_notes,
    ])
@endsection
