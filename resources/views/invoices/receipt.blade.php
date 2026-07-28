@extends('invoices.layout')

@section('title', $title . ' — ' . $reference)

@section('doc-title', $title)
@section('doc-meta')
    Ref: {{ $reference }}<br>
    Date: {{ ($date instanceof \Illuminate\Support\Carbon ? $date : \Illuminate\Support\Carbon::parse($date))->format('d M Y') }}
@endsection

@section('content')
    @include('invoices._body', [
        'billTo' => $billTo,
        'from' => $from,
        'lineItems' => $lineItems,
        'total' => $total,
        'paymentInfo' => $paymentInfo ?? null,
        'notes' => $notes ?? null,
    ])
@endsection
