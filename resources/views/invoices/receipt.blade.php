<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — {{ $reference }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1e293b; }
        .header { width: 100%; margin-bottom: 24px; }
        .header td { vertical-align: top; }
        .brand { font-size: 22px; font-weight: bold; color: #0d9488; }
        .brand-sub { font-size: 10px; color: #64748b; margin-top: 2px; }
        .doc-title { font-size: 16px; font-weight: bold; text-align: right; color: #1e293b; }
        .doc-meta { text-align: right; font-size: 11px; color: #64748b; margin-top: 4px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; }
        .box-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #0f766e; color: #ffffff; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        table.totals { width: 100%; margin-top: 8px; }
        table.totals td { padding: 4px 10px; font-size: 12px; }
        table.totals .label { text-align: right; color: #64748b; }
        table.totals .value { text-align: right; width: 120px; font-weight: bold; }
        table.totals .grand td { border-top: 2px solid #0f766e; font-size: 15px; font-weight: bold; color: #0f766e; padding-top: 8px; }
        .payment-status { margin-top: 24px; padding: 10px 16px; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 4px; font-size: 11px; color: #0f766e; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="50%">
                <div class="brand">{{ config('app.name') }}</div>
                <div class="brand-sub">On-demand home services across Pakistan</div>
            </td>
            <td width="50%">
                <div class="doc-title">{{ $title }}</div>
                <div class="doc-meta">
                    Ref: {{ $reference }}<br>
                    Date: {{ ($date instanceof \Illuminate\Support\Carbon ? $date : \Illuminate\Support\Carbon::parse($date))->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td width="50%" style="vertical-align: top; padding-right: 10px;">
                <div class="box">
                    <div class="box-label">Billed to</div>
                    <strong>{{ $billTo['name'] }}</strong><br>
                    {{ $billTo['email'] }}<br>
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
                    <strong>{{ $from }}</strong>
                </div>
            </td>
        </tr>
    </table>

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
        <tr class="grand">
            <td class="label">Total</td>
            <td class="value">Rs. {{ number_format($total, 0) }}</td>
        </tr>
    </table>

    <div class="payment-status">
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

    <div class="footer">
        This is a system-generated receipt from {{ config('app.name') }}. For questions, contact support through the app.
    </div>
</body>
</html>
