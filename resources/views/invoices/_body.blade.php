{{-- Shared invoice/receipt body. Expects: $billTo, $from, $lineItems, $total, $paymentInfo (nullable), $notes (nullable), $inspectionNotes (nullable), $discount (nullable, default 0) --}}
@php
    $discount = (float) ($discount ?? 0);
    $subtotal = $total + $discount;
@endphp
<table style="width: 100%; margin-bottom: 4px;">
    <tr>
        <td width="50%" style="vertical-align: top; padding-right: 10px;">
            <div class="box">
                <div class="box-label">Billed to</div>
                <strong>{{ $billTo['name'] }}</strong><br>
                @if (!empty($billTo['email']))
                    {{ $billTo['email'] }}<br>
                @endif
                @if (!empty($billTo['phone']))
                    {{ $billTo['phone'] }}<br>
                @endif
                @if (!empty($billTo['address']))
                    {{ $billTo['address'] }}
                @endif
            </div>
        </td>
        <td width="50%" style="vertical-align: top; padding-left: 10px;">
            <div class="box">
                <div class="box-label">Provided by</div>
                <strong>{{ $from }}</strong><br>
                <span class="provided-tagline">Facility Management Services</span>
            </div>
        </td>
    </tr>
</table>

@if (!empty($inspectionNotes))
    <div class="section-heading">Inspection / Review</div>
    <div class="section-text">{{ $inspectionNotes }}</div>
@endif

<table class="items">
    <thead>
        <tr>
            <th>Description</th>
            <th class="num">Qty</th>
            <th class="num">Unit price (Rs.)</th>
            <th class="num">Total (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lineItems as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td class="num">{{ $item['qty'] }}</td>
                <td class="num">{{ number_format($item['unitPrice'], 0) }}</td>
                <td class="num">{{ number_format($item['total'], 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    @if ($discount > 0)
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rs. {{ number_format($subtotal, 0) }}</td>
        </tr>
        <tr class="discount-row">
            <td class="label">Discount</td>
            <td class="value">-Rs. {{ number_format($discount, 0) }}</td>
        </tr>
    @endif
    <tr class="grand">
        <td class="label">Total</td>
        <td class="value">Rs. {{ number_format($total, 0) }}</td>
    </tr>
</table>

@if (!empty($paymentInfo))
    <div class="status-box">
        <strong>Payment status:</strong> {{ $paymentInfo['status'] }}
        @if (!empty($paymentInfo['gateway']))
            &nbsp;·&nbsp; Method: {{ $paymentInfo['gateway'] }}
        @endif
        @if (!empty($paymentInfo['reference']))
            &nbsp;·&nbsp; Gateway ref: {{ $paymentInfo['reference'] }}
        @endif
        @if (!empty($paymentInfo['paidAt']))
            &nbsp;·&nbsp; Paid: {{ ($paymentInfo['paidAt'] instanceof \Illuminate\Support\Carbon ? $paymentInfo['paidAt'] : \Illuminate\Support\Carbon::parse($paymentInfo['paidAt']))->format('d M Y, g:i A') }}
        @endif
    </div>
@endif

@if (!empty($notes))
    <div class="notes-box">
        <strong>Notes:</strong> {{ $notes }}
    </div>
@endif
