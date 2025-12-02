<!-- PDF version of invoice without buttons -->
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>@if($order->type === 'CN')Credit Note @else Invoice @endif {{ $order->order_number ?? '' }}</title>
    <style>
        /* A4 sizing and print-ready */
        @page {
            size: A4;
            margin: 10mm;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            width: 100%;
            overflow-x: hidden;
        }

        .page {
            width: 100%;
            max-width: 190mm;
            min-height: 277mm;
            margin: 0 auto;
            background: #fff;
            padding: 10mm;
            box-sizing: border-box;
            color: #111;
        }

        /* All tables with borders */
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #333;
            margin-bottom: 6mm
        }

        table td,
        table th {
            padding: 8px 6px;
            border: 1px solid #333;
            vertical-align: top
        }

        /* Nested tables should not have margins */
        table table {
            margin-bottom: 0
        }

        /* Top area - header table */
        table.header {
            width: 100%;
            margin-bottom: 6mm;
            border: 1px solid #333
        }

        table.header td {
            vertical-align: top;
            border: none;
            padding: 6px 4px;
            word-wrap: break-word;
        }

        table.header td.meta {
            vertical-align: middle
        }

        table.header tr:first-child td {
            border-bottom: 1px solid #333
        }

        table.header tr:nth-child(2) td {
            border-bottom: 1px solid #333
        }

        table.header tr:nth-child(3) td {
            font-size: 13px
        }

        table.header tr:nth-child(3) td:first-child {
            border-right: 1px solid #333
        }

        .left {
            width: 60%
        }

        .logo {
            max-width: 150px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .company {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 3px 0
        }

        .meta {
            width: 38%;
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
            line-height: 1.25
        }

        .meta .invoice-no {
            font-size: 18px;
            font-weight: 700;
            display: block;
            margin-top: 6px
        }

        /* Title table */
        table.title {
            width: 100%;
            margin-bottom: 6mm
        }

        table.title td {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            padding: 6px
        }

        /* Addresses table */
        table.addresses {
            width: 100%;
            margin-bottom: 8mm
        }

        table.addresses td {
            padding: 6px 4px;
            font-size: 11px;
            word-wrap: break-word;
        }

        .addr h4 {
            margin: 0 0 6px 0;
            font-size: 13px
        }

        .muted {
            color: #222;
            font-size: 12px
        }

        /* Items table */
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 6mm;
            border: 1px solid #333;
            table-layout: fixed;
        }

        table.items thead th {
            padding: 6px 4px;
            text-align: left;
            font-weight: 700;
            background: transparent;
            border: 1px solid #333;
            word-wrap: break-word;
        }

        table.items tbody td {
            padding: 6px 4px;
            border: 1px solid #333;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .center {
            text-align: center
        }

        .right {
            text-align: right
        }

        /* Subtotal and totals area */
        .totals {
            width: 340px;
            margin-left: auto;
            font-size: 12.1px;
            border-collapse: collapse;
            border: 1px solid #333
        }

        .totals td {
            padding: 6px 8px;
            border: 1px solid #333
        }

        .totals .total {
            font-weight: 700;
            padding-top: 10px
        }

        /* Payment & history area */
        table.payment {
            width: 100%;
            margin-top: 6mm;
            border: 1px solid #333
        }

        table.payment td {
            padding: 6px 4px;
            font-size: 11px;
            border: 1px solid #333;
            word-wrap: break-word;
        }

        .payment-left {
            width: 60%
        }

        .payment-right {
            width: 40%
        }

        table.history {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #333;
            font-size: 12px
        }

        table.history thead th {
            padding: 6px 4px;
            text-align: left;
            font-weight: 700;
            border: 1px solid #333;
            font-size: 11px;
            word-wrap: break-word;
        }

        table.history td {
            padding: 6px 4px;
            font-size: 11px;
            border: 1px solid #333;
            word-wrap: break-word;
        }

        /* signatures */
        .signs {
            display: flex;
            gap: 18px;
            margin-top: 14px
        }

        .sign {
            flex: 1
        }

        .sign .line {
            margin-top: 18px;
            border-top: 1px solid #ddd;
            padding-top: 6px;
            color: #666;
            font-size: 11px;
            width: 80%
        }

        /* Footer table */
        table.footer {
            margin-top: 14mm;
            font-size: 11px;
            color: #666;
            width: 100%;
            border: 1px solid #333
        }

        table.footer td {
            padding: 6px 4px;
            border: 1px solid #333;
            word-wrap: break-word;
        }

        /* Small helpers to match PDF spacing */
        .small {
            font-size: 11px;
            color: #222
        }

        .bold {
            font-weight: 700
        }
        .detail-row {
            margin-top: 18px;
            display: flex;
            margin-bottom: 0.5rem;
            align-items: flex-start;
            flex-wrap: wrap;
            word-wrap: break-word;
        }
        .detail-label {
            margin-right: 0.5rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .detail-value {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>

<body>
    <div class="page" role="document">
        <table class="header" aria-label="Invoice header">
            <tr>
                <td class="left" aria-label="Seller">
                    @if(isset($logoBase64) && $logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
                    @endif
                </td>
                <td class="meta" aria-label="Invoice meta">
                    <div class="company">{{ $settings['company_name'] ?? 'A & F Supplies LTD' }}</div>
                    @if(isset($settings['company_phone']) && $settings['company_phone'])
                        <div class="small">Mobile No.: {{ $settings['company_phone'] }}</div>
                    @endif
                    @if(isset($settings['company_email']) && $settings['company_email'])
                        <div class="small">Email-Id: {{ $settings['company_email'] }}</div>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" class="center" style="font-size:20px;font-weight:700;padding:6px">
                    @if($order->type === 'CN')
                        Credit Note
                    @else
                        Invoice
                    @endif
                </td>
            </tr>
            <tr>
                <td class="addr" aria-label="Customer address">
                    <h4>Customer Name & Address</h4>
                    <div class="bold">{{ optional($order->customer)->company_name ?? '' }}</div>
                    @if($order->address_line1)
                        <div>{{ $order->address_line1 }}</div>
                    @endif
                    @if($order->address_line2)
                        <div>{{ $order->address_line2 }}</div>
                    @endif
                    @if($order->city)
                        <div>{{ $order->city }}@if($order->state) {{ $order->state }}@endif @if($order->country){{ $order->country }}@endif</div>
                    @endif
                    @if($order->zip_code)
                        <div>{{ $order->zip_code }}</div>
                    @endif
                </td>
                <td class="addr" aria-label="Payment details">
                    @if($order->type === 'CN')
                        <p><b>CN Date:</b> {{ optional($order->order_date)->format('d/m/Y H:i') ?? optional($order->created_at)->format('d/m/Y H:i') }}</p>
                        <p><b>CN No.:</b> #CN{{ $order->order_number }}</p>
                    @else
                        <p><b>Invoice Date:</b> {{ optional($order->order_date)->format('d/m/Y H:i') ?? optional($order->created_at)->format('d/m/Y H:i') }}</p>
                        <p><b>Invoice No.:</b> #SO{{ $order->order_number }}</p>
                    @endif
                    @if($order->invoice_ref)
                        <p><b>Invoice Ref:</b> {{ $order->invoice_ref }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items" aria-label="Items">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">Sr No.</th>
                    <th rowspan="2" style="width: 12%;">Box Qty</th>
                    <th rowspan="2" style="width: 25%;">Product</th>
                    <th rowspan="2" style="width: 6%; text-align: center;">Qty</th>
                    <th colspan="2" style="text-align: center;">Rate</th>
                    <th colspan="3" style="text-align: center;">VAT</th>
                    <th rowspan="2" style="width: 10%; text-align: center;">Amount</th>
                </tr>
                <tr>
                    <th style="width: 8%; text-align: center;">Unit</th>
                    <th style="width: 8%; text-align: center;">Total</th>
                    <th style="width: 8%; text-align: center;">Unit</th>
                    <th style="width: 8%; text-align: center;">Total</th>
                    <th style="width: 6%; text-align: center;">%</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itemNumber = 1;
                    $soldItems = $order->items->where('type', '!=', 'returned');
                    
                    // If this is a credit note, show credit note items
                    if ($order->type === 'CN') {
                        $soldItems = $order->items;
                    }
                    
                    // Use order subtotal and VAT from order
                    $orderSubtotal = (float)($order->subtotal ?? 0);
                    $orderTotalVat = (float)($order->vat_amount ?? 0);
                    $orderTotal = (float)($order->total_amount ?? 0);
                    $paidAmount = (float)($order->paid_amount ?? 0);
                    $dueAmount = (float)($order->unpaid_amount ?? 0);
                @endphp
                
                @foreach($soldItems as $item)
                    @php
                        $totalPrice = (float)($item->total_price ?? 0);
                        $unitPrice = (float)($item->unit_price ?? 0);
                        $quantity = (float)($item->quantity ?? 0);
                        $unitVat = (float)($item->unit_vat ?? 0);
                        $totalVat = (float)($item->total_vat ?? 0);
                        $total = (float)($item->total ?? 0);
                        // Get box qty from product_unit or default
                        $boxQty = $item->product_unit ?? '';
                        // Calculate VAT percentage
                        $vatPercentage = 0;
                        if ($unitPrice > 0) {
                            $vatPercentage = ($unitVat / $unitPrice) * 100;
                        }
                    @endphp
                    <tr>
                        <td>{{ $itemNumber++ }}</td>
                        <td>{{ $boxQty ?: '-' }}</td>
                        <td>
                            @if($item->product)
                                {{ $item->product->name ?? 'N/A' }}
                                @if($order->type === 'CN')
                                    [RETURN ITEM]
                                @endif
                            @else
                                Product #{{ $item->product_id }}
                            @endif
                        </td>
                        <td class="center">{{ number_format($quantity, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($unitPrice, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($totalPrice, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($unitVat, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($totalVat, 2) }}</td>
                        <td class="right">{{ number_format($vatPercentage, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="9" class="right" style="padding-top:10px">Sub Total</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format($orderSubtotal, 2) }}</td>
                </tr>
                @if(isset($order->vat_amount) && $order->vat_amount > 0)
                <tr>
                    <td colspan="9" class="right" style="padding-top:10px">VAT</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format($order->vat_amount, 2) }}</td>
                </tr>
                @endif
                @if(isset($order->shipping_cost) && $order->shipping_cost > 0)
                <tr>
                    <td colspan="9" class="right" style="padding-top:10px">Shipping</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format((float)$order->shipping_cost, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="9" class="right" style="padding-top:10px">Total</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format($orderTotal, 2) }}</td>
                </tr>
                @if($paidAmount > 0)
                <tr>
                    <td colspan="9" class="right" style="padding-top:10px">- Paid</td>
                    <td class="right" style="padding-top:10px">- {{ $currencySymbol }}{{ number_format($paidAmount, 2) }}</td>
                </tr>
                @endif
                @if($dueAmount > 0)
                <tr>
                    <td colspan="9" class="right" style="font-weight:700;padding-top:10px">Due</td>
                    <td class="right" style="font-weight:700;padding-top:10px">{{ $currencySymbol }}{{ number_format($dueAmount, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        @if($order->payments && $order->payments->count() > 0)
        <h4 style="margin-top:35px; margin-bottom:10px;">Payments:</h4>
        <table class="history">
            <thead>
                <tr>
                    <th style="width:35%">Date</th>
                    <th style="width:45%">Reference</th>
                    <th style="width:45%">Paid By</th>
                    <th style="width:20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->payments as $payment)
                <tr>
                    <td>{{ optional($payment->date)->format('d/m/Y') ?? optional($payment->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $payment->reference_no ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                    <td>{{ $currencySymbol }}{{ number_format((float)($payment->amount ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <table class="payment-info" style="width:100%;margin-top:6mm;border:1px solid #333;border-collapse:collapse;table-layout:fixed;font-size:11px">
            <tr>
                <td style="width:50%;padding:6px 4px;vertical-align:top;border:1px solid #333;word-wrap:break-word;overflow-wrap:break-word;font-size:11px">
                    <div style="margin-bottom:16px">Please make cheques payable to:</div>
                    <div style="font-weight:700;">{{ $settings['company_name'] ?? 'A & F Supplies LTD' }}</div>
                    @if(isset($settings['company_address']) && $settings['company_address'])
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">{{ $settings['company_address'] }}</span>
                    </div>
                    @endif
                    @if(isset($settings['account_name']) && $settings['account_name'])
                    <div class="detail-row">
                        <span class="detail-label">Account:</span>
                        <span class="detail-value">{{ $settings['account_name'] }}</span>
                    </div>
                    @endif
                    @if(isset($settings['bank']) && $settings['bank'])
                    <div class="detail-row">
                        <span class="detail-label">Bank:</span>
                        <span class="detail-value">{{ $settings['bank'] }}</span>
                    </div>
                    @endif
                    @if(isset($settings['sort_code']) && $settings['sort_code'])
                    <div class="detail-row">
                        <span class="detail-label">Sort Code:</span>
                        <span class="detail-value">{{ $settings['sort_code'] }}</span>
                    </div>
                    @endif
                    @if(isset($settings['account_no']) && $settings['account_no'])
                    <div class="detail-row">
                        <span class="detail-label">Account No:</span>
                        <span class="detail-value">{{ $settings['account_no'] }}</span>
                    </div>
                    @endif
                    <div style="margin-top:25px;font-size:11px;color:#666">Your order will not ship until we receive payment.</div>
                </td>
                <td style="width:50%;padding:6px 4px;vertical-align:top;border:1px solid #333;word-wrap:break-word;overflow-wrap:break-word;font-size:11px">
                    <div class="detail-row">
                        <span class="detail-label">TO ORDER:</span>
                        <span class="detail-value"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">OFFICE:</span>
                        <span class="detail-value"></span>
                    </div>
                    @if(isset($settings['company_phone']) && $settings['company_phone'])
                    <div class="detail-row">
                        <span class="detail-label">MOBILE NO.:</span>
                        <span class="detail-value">{{ $settings['company_phone'] }}</span>
                    </div>
                    @endif
                    @if(isset($settings['company_email']) && $settings['company_email'])
                    <div class="detail-row">
                        <span class="detail-label">EMAIL:</span>
                        <span class="detail-value">{{ $settings['company_email'] }}</span>
                    </div>
                    @endif
                    <div style="margin-top:20px">
                        <div style="margin-top:25px;border-bottom:1px solid #ddd;padding-top:6px;font-size:11px">PICKER SIGN:</div>
                        <div style="margin-top:25px;border-bottom:1px solid #ddd;padding-top:6px;font-size:11px">PACKER SIGN:</div>
                        <div style="margin-top:25px;border-bottom:1px solid #ddd;padding-top:6px;font-size:11px">RECIPIENT SIGN:</div>
                    </div>
                </td>
            </tr>
        </table>
        
    </div>
</body>

</html>

