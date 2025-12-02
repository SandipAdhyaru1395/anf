<style>
    /* Scoped to the Order view modal content */
    #order-view-modal-content {
        font-family: Arial, sans-serif;
        background: #fff;
    }
    
    #order-view-modal-content .company-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #0d6efd;
        padding-top: 0.5rem;
    }
    
    #order-view-modal-content .company-logo-section {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    #order-view-modal-content .company-logo-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ff6b9d;
        color: #fff;
        border-radius: 50%;
        font-weight: bold;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    #order-view-modal-content .company-logo-text {
        font-size: 1.75rem;
        font-weight: 700;
        color: #000;
        line-height: 1.2;
    }
    
    #order-view-modal-content .company-logo-text .ltd {
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    #order-view-modal-content .company-details {
        text-align: right;
        font-size: 0.8125rem;
        color: #333;
        line-height: 1.8;
        font-weight: 400;
    }
    
    #order-view-modal-content .invoice-title-bar {
        background-color: var(--logo-color);
        color: #fff;
        padding: 0.75rem;
        text-align: center;
        font-weight: 600;
        font-size: 1.125rem;
        margin: 1.5rem 0;
    }
    
    #order-view-modal-content .invoice-info-section {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }
    
    #order-view-modal-content .customer-address {
        font-size: 0.875rem;
        line-height: 1.8;
    }
    
    #order-view-modal-content .customer-address strong {
        display: block;
        margin-bottom: 0.5rem;
    }
    
    #order-view-modal-content .invoice-details {
        font-size: 0.875rem;
    }
    
    #order-view-modal-content .invoice-details-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    #order-view-modal-content .invoice-details-table td {
        padding: 0.25rem 0;
        font-size: 0.875rem;
        line-height: 1.8;
    }
    
    #order-view-modal-content .invoice-details-table td:first-child {
        text-align: right;
        font-weight: 600;
        padding-right: 0.5rem;
        min-width: 120px;
    }
    
    #order-view-modal-content .invoice-details-table td:last-child {
        text-align: left;
    }
    
    #order-view-modal-content .items-table-wrapper {
        overflow-x: auto;
        margin-bottom: 1.5rem;
        -webkit-overflow-scrolling: touch;
    }
    
    #order-view-modal-content .items-table {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }
    
    @media (max-width: 992px) {
        #order-view-modal-content .items-table {
            min-width: 700px;
            font-size: 0.75rem;
        }
        
        #order-view-modal-content .items-table th,
        #order-view-modal-content .items-table td {
            padding: 0.5rem 0.4rem;
        }
    }
    
    @media (max-width: 768px) {
        #order-view-modal-content .items-table {
            min-width: 600px;
            font-size: 0.7rem;
        }
        
        #order-view-modal-content .items-table th,
        #order-view-modal-content .items-table td {
            padding: 0.4rem 0.3rem;
        }
    }
    
    #order-view-modal-content .items-table thead {
        background-color: var(--logo-color);
        color: #fff;
    }
    
    #order-view-modal-content .items-table th {
        padding: 0.75rem 0.5rem;
        text-align: center;
        font-weight: 600;
        border: 1px solid #fff;
        white-space: nowrap;
    }
    
    #order-view-modal-content .items-table td:last-child {
        text-align: right;
    }
    
    #order-view-modal-content .items-table td {
        padding: 0.75rem 0.5rem;
        border: 1px solid #dee2e6;
        white-space: nowrap;
    }
    
    #order-view-modal-content .items-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    #order-view-modal-content .summary-section {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1.5rem;
    }
    
    #order-view-modal-content .summary-table {
        width: 300px;
        border-collapse: collapse;
    }
    
    #order-view-modal-content .summary-table td {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border: 1px solid #dee2e6;
    }
    
    #order-view-modal-content .summary-table td:first-child {
        text-align: right;
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    #order-view-modal-content .summary-table td:last-child {
        text-align: right;
    }
    
    #order-view-modal-content .payment-mode-section {
        margin-bottom: 1.5rem;
    }
    
    #order-view-modal-content .payment-mode-label {
        display: inline-block;
        margin-right: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    #order-view-modal-content .payment-mode-box {
        display: inline-block;
        padding: 0.5rem 1rem;
        font-weight: 600;
        border-radius: 4px;
        color: #fff;
    }
    
    #order-view-modal-content .payment-mode-box.paid {
        background-color: #28a745;
    }
    
    #order-view-modal-content .payment-mode-box.outstanding {
        background-color: #dc3545;
    }
    
    #order-view-modal-content .payment-mode-box.partial {
        background-color: #ffc107;
        color: #000;
    }
    
    #order-view-modal-content .payment-details-table {
        width: 100%;
        max-width: 600px;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
        font-size: 0.8125rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        #order-view-modal-content .company-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        #order-view-modal-content .company-details {
            text-align: left;
            width: 100%;
        }
        
        #order-view-modal-content .invoice-info-section {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        #order-view-modal-content .footer-section {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        #order-view-modal-content .footer-left {
            order: 1;
        }
        
        #order-view-modal-content .footer-right {
            order: 2;
        }
        
        #order-view-modal-content .summary-section {
            justify-content: flex-start;
        }
        
        #order-view-modal-content .summary-table {
            width: 100%;
            max-width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        #order-view-modal-content .company-logo-text {
            font-size: 1.5rem;
        }
        
        #order-view-modal-content .company-logo-text .ltd {
            font-size: 1.1rem;
        }
        
        #order-view-modal-content .company-details {
            font-size: 0.75rem;
        }
        
        #order-view-modal-content .invoice-title-bar {
            font-size: 1rem;
            padding: 0.5rem;
        }
        
        #order-view-modal-content .customer-address,
        #order-view-modal-content .invoice-details {
            font-size: 0.8125rem;
        }
        
        #order-view-modal-content .payment-details-table {
            max-width: 100%;
            font-size: 0.75rem;
        }
        
        #order-view-modal-content .payment-details-table th,
        #order-view-modal-content .payment-details-table td {
            padding: 0.5rem 0.25rem;
        }
        
        #order-view-modal-content .footer-left,
        #order-view-modal-content .footer-right {
            padding: 1rem;
        }
        
        #order-view-modal-content .footer-left h6 {
            margin: -1rem -1rem 1rem -1rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        #order-view-modal-content .footer-left .detail-label,
        #order-view-modal-content .footer-right .contact-label {
            min-width: 90px;
            font-size: 0.8125rem;
        }
        
        #order-view-modal-content .footer-right .signature-label {
            font-size: 0.8125rem;
        }
        
        #order-view-modal-content .highlight-notice {
            font-size: 0.8125rem;
            padding: 0.5rem 0.75rem;
        }
    }
    
    @media (max-width: 576px) {
        #order-view-modal-content {
            padding: 1rem !important;
        }
        
        #order-view-modal-content .company-logo-icon {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }
        
        #order-view-modal-content .company-logo-text {
            font-size: 1.25rem;
        }
        
        #order-view-modal-content .company-logo-text .ltd {
            font-size: 1rem;
        }
        
        #order-view-modal-content .invoice-title-bar {
            font-size: 0.875rem;
            margin: 1rem 0;
        }
        
        #order-view-modal-content .invoice-info-section {
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        #order-view-modal-content .footer-left,
        #order-view-modal-content .footer-right {
            padding: 0.75rem;
        }
        
        #order-view-modal-content .footer-left h6 {
            margin: -0.75rem -0.75rem 0.75rem -0.75rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.8125rem;
        }
        
        #order-view-modal-content .footer-left .detail-row,
        #order-view-modal-content .footer-right .contact-row {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        #order-view-modal-content .footer-left .detail-label,
        #order-view-modal-content .footer-right .contact-label {
            min-width: auto;
            width: 100%;
        }
        
        #order-view-modal-content .summary-table {
            font-size: 0.8125rem;
        }
        
        #order-view-modal-content .summary-table td {
            padding: 0.4rem 0.75rem;
        }
    }
    
    #order-view-modal-content .payment-details-table thead {
        background-color: var(--logo-color);
        color: #fff;
    }
    
    #order-view-modal-content .payment-details-table th {
        padding: 0.75rem 0.5rem;
        text-align: left;
        font-weight: 600;
        border: 1px solid #0a58ca;
    }
    
    #order-view-modal-content .payment-details-table td {
        padding: 0.75rem 0.5rem;
        border: 1px solid #dee2e6;
    }
    
    #order-view-modal-content .footer-section {
        margin-top: 2rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        font-size: 0.75rem;
        line-height: 1.8;
    }
    
    /* Ensure footer sections stack in correct order on small screens */
    @media (max-width: 992px) {
        #order-view-modal-content .footer-section {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        #order-view-modal-content .footer-left {
            order: 1;
        }
        
        #order-view-modal-content .footer-right {
            order: 2;
        }
    }
    
    #order-view-modal-content .footer-left {
        border: 1px solid #dee2e6;
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    #order-view-modal-content .footer-right {
        border: 1px solid #dee2e6;
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    #order-view-modal-content .footer-right .contact-info {
        margin-bottom: 1.5rem;
    }
    
    #order-view-modal-content .footer-right .contact-row {
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    #order-view-modal-content .footer-right .contact-row:last-child {
        border-bottom: none;
    }
    
    #order-view-modal-content .footer-right .contact-label {
        font-weight: 700;
        color: #495057;
        display: inline-block;
        min-width: 120px;
        margin-right: 0.5rem;
    }
    
    #order-view-modal-content .footer-right .contact-value {
        color: #212529;
        font-weight: 500;
    }
    
    #order-view-modal-content .footer-right .signature-section {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid #dee2e6;
    }
    
    #order-view-modal-content .footer-right .signature-item {
        margin-bottom: 1.25rem;
        padding: 0.5rem 0;
    }
    
    #order-view-modal-content .footer-right .signature-item:last-child {
        margin-bottom: 0;
    }
    
    #order-view-modal-content .footer-right .signature-label {
        font-weight: 700;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
        display: block;
        letter-spacing: 0.5px;
    }
    
    #order-view-modal-content .footer-right .signature-line {
        border-bottom: 2px solid #212529;
        width: 100%;
        height: 2px;
        margin-top: 0.5rem;
        background-color: transparent;
    }
    
    #order-view-modal-content .footer-left h6 {
        background-color: var(--logo-color);
        color: #fff;
        padding: 0.5rem 1rem;
        font-weight: 700;
        font-size: 1rem;
        margin: -1.5rem -1.5rem 1rem -1.5rem;
        border-radius: 8px 8px 0 0;
    }
    
    #order-view-modal-content .footer-left .bank-details {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
    }
    
    #order-view-modal-content .footer-left .detail-row {
        display: flex;
        margin-bottom: 0.5rem;
        align-items: flex-start;
    }
    
    #order-view-modal-content .footer-left .detail-label {
        font-weight: 600;
        color: #495057;
        min-width: 100px;
        margin-right: 0.5rem;
    }
    
    #order-view-modal-content .footer-left .detail-value {
        color: #212529;
        flex: 1;
    }
    
    #order-view-modal-content .footer-left .highlight-notice {
        background-color: #fff3cd;
        border-left: 3px solid #ffc107;
        padding: 0.75rem 1rem;
        margin-top: 1rem;
        border-radius: 4px;
        color: #856404;
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    #order-view-modal-content .footer-left strong {
        display: block;
        margin-bottom: 0.5rem;
        color: #212529;
    }
    
    #order-view-modal-content .footer-right strong {
        display: block;
        margin-bottom: 0.5rem;
    }
    
    #order-view-modal-content .picker-sign {
        margin-top: 2rem;
        padding-top: 1rem;
    }
    
    #order-view-modal-content .picker-sign-line {
        border-bottom: 1px solid #000;
        width: 200px;
        margin-top: 1rem;
    }
    
    #order-view-modal-content .final-summary {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #dee2e6;
    }
    
    #order-view-modal-content .final-summary-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    #order-view-modal-content .final-summary-table td {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border: 1px solid #dee2e6;
    }
    
    #order-view-modal-content .final-summary-table td:first-child {
        text-align: right;
        font-weight: 600;
        background-color: #f8f9fa;
        width: 50%;
    }
    
    #order-view-modal-content .final-summary-table td:last-child {
        text-align: right;
        width: 50%;
    }
</style>

<div class="modal-body p-4">
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    
    <!-- Company Header -->
    <div class="company-header">
        <div class="company-logo-section">
                <img src="{{ asset('storage/'.$settings['company_logo']) }}" alt="Logo" style="max-width: 150px;
    max-height: 80px;
    width: auto;
    height: auto;
    object-fit: contain;">
        </div>
        <div class="company-details">
            <b>{{ $settings['company_name'] ?? '' }}</b><br>
            @if($settings['vat_registration_number'] ?? null)
                VAT Reg No.: {{ $settings['vat_registration_number'] }}<br>
            @endif
            @if($settings['company_phone'] ?? null)
                Mobile No.: {{ $settings['company_phone'] }}<br>
            @endif
            @if($settings['company_email'] ?? null)
                Email-Id: {{ $settings['company_email'] }}
            @endif
        </div>
    </div>
    
    <!-- Invoice/Credit Note Title Bar -->
    <div class="invoice-title-bar">
        @if($order->type === 'CN')
            CREDIT NOTE
        @elseif($order->type === 'EST')
            ESTIMATE
        @else
            INVOICE
        @endif
    </div>
    
    <!-- Invoice Info Section -->
    <div class="invoice-info-section">
        <div class="customer-address">
            <strong>Customer Name & Address:</strong>
            {{ optional($order->customer)->company_name ?? '' }}<br>
            @if($order->address_line1)
                {{ $order->address_line1 }}<br>
            @endif
            @if($order->address_line2)
                {{ $order->address_line2 }}<br>
            @endif
            @if($order->city)
                {{ $order->city }}
            @endif
            @if($order->zip_code)
                {{ $order->zip_code }}<br>
            @endif
            @if($order->country)
                {{ $order->country }}
            @endif
        </div>
        <div class="invoice-details">
            <table class="invoice-details-table">
                @if($order->type === 'CN')
                    <tr>
                        <td>CN DATE:</td>
                        <td>{{ optional($order->order_date)->format('d/m/Y H:i') ?? optional($order->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>CN NO.:</td>
                        <td>{{ '#CN' . $order->order_number }}</td>
                    </tr>
                @elseif($order->type === 'EST')
                    <tr>
                        <td>ORDER DATE:</td>
                        <td>{{ optional($order->order_date)->format('d/m/Y H:i') ?? optional($order->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>ORDER NO.:</td>
                        <td>{{ '#EST' . $order->order_number }}</td>
                    </tr>
                @else
                    <tr>
                        <td>INVOICE DATE:</td>
                        <td>{{ optional($order->order_date)->format('d/m/Y H:i') ?? optional($order->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    
                    <tr>
                        <td>ORDER NO.:</td>
                        <td>{{ '#SO'. $order->order_number }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
    
    <!-- Items Table -->
    <div class="items-table-wrapper">
    <table class="items-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 60px;">Sr No.</th>
                <th rowspan="2" style="width: 100px;">Box Qty</th>
                <th rowspan="2">Product</th>
                <th rowspan="2" style="width: 80px; text-align: center;">Qty</th>
                <th colspan="2" style="text-align: center;">Rate</th>
                <th colspan="3" style="text-align: center;">VAT</th>
                <th rowspan="2" style="width: 120px; text-align: center;">Amount</th>
            </tr>
            <tr>
                <th style="width: 100px; text-align: center;">Unit</th>
                <th style="width: 100px; text-align: center;">Total</th>
                <th style="width: 80px; text-align: center;">Unit</th>
                <th style="width: 80px; text-align: center;">Total</th>
                <th style="width: 80px; text-align: center;">%</th>
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
                    <td style="text-align: right;">{{ number_format($quantity, 2) }}</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($unitPrice, 2) }}</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($totalPrice, 2) }}</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($unitVat, 2) }}</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($totalVat, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($vatPercentage, 2) }}</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format($total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    
    <!-- Summary Section -->
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td style="background-color: #e9ecef;">Sub Total</td>
                <td>{{ $currencySymbol }}{{ number_format($orderSubtotal, 2) }}</td>
            </tr>
            <tr>
                <td>VAT </td>
                <td>{{ $currencySymbol }}{{ number_format($orderTotalVat, 2) }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700; background-color: #e9ecef;">Grand Total</td>
                <td style="font-weight: 700; background-color: #e9ecef;">{{ $currencySymbol }}{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Payment Details Table -->
    @if($order->payments->count() > 0)
    <h6 class="fw-bold" style="margin-top: 3.5rem;">Payment Details :</h6>
    <table class="payment-details-table mx-auto">
        <thead>
            <tr>
                <th>Date</th>
                <th>Payment Reference</th>
                <th>Paid by</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->payments as $payment)
                <tr>
                    <td>{{ optional($payment->date)->format('d/m/Y H:i') ?? '' }}</td>
                    <td>{{ $payment->reference_no ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ $currencySymbol }}{{ number_format((float) ($payment->amount ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <!-- Footer Section -->
    <div class="footer-section">
        <div class="footer-left">
            <h6>Please make cheques payable to:</h6>
            <div class="company-info">
                <strong>{{ $settings['company_name'] ?? '' }}</strong>
                @if(isset($settings['company_address']) && $settings['company_address'])
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">{{ $settings['company_address'] }}</span>
                    </div>
                @endif
            </div>
            
           
            <div class="bank-details">
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
                <div class="highlight-notice">
                    Your order will not ship until we receive payment.
                </div>
            </div>
        </div>
        
        <div class="footer-right">
            <div class="contact-info">
                <div class="contact-row">
                    <span class="contact-label">TO ORDER:</span>
                    <span class="contact-value"></span>
                </div>
                <div class="contact-row">
                    <span class="contact-label">OFFICE:</span>
                    <span class="contact-value"></span>
                </div>
                <div class="contact-row">
                    <span class="contact-label">MOBILE NO.:</span>
                    <span class="contact-value">{{ $settings['company_phone'] ?? '' }}</span>
                </div>
                <div class="contact-row">
                    <span class="contact-label">EMAIL:</span>
                    <span class="contact-value">{{ $settings['company_email'] ?? '' }}</span>
                </div>
            </div>
            <div class="signature-section">
                <div class="signature-item">
                    <span class="signature-label">PICKER SIGN:</span>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-item">
                    <span class="signature-label">PACKER SIGN:</span>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-item">
                    <span class="signature-label">RECIPIENT SIGN:</span>
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
    </div>
</div>
