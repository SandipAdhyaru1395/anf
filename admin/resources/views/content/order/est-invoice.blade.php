<!-- EST Invoice without VAT columns -->
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>#EST{{ $order->order_number ?? '' }}</title>
    <style>
        /* A4 sizing and print-ready */
        @page {
            size: A4;
            margin: 12mm;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #eee;
            font-family: 'DejaVu Sans', 'Arial', sans-serif
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 10px auto;
            background: #fff;
            padding: 14mm;
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
            padding: 8px 6px
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
            padding: 8px;
            font-size: 12px
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
            font-size: 12.1px;
            margin-bottom: 6mm;
            border: 1px solid #333
        }

        table.items thead th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 700;
            background: transparent;
            border: 1px solid #333
        }

        table.items tbody td {
            padding: 8px 6px;
            border: 1px solid #333;
            vertical-align: top
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
            padding: 8px;
            font-size: 12px;
            border: 1px solid #333
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
            padding: 6px;
            text-align: left;
            font-weight: 700;
            border: 1px solid #333;
            font-size: 12px
        }

        table.history td {
            padding: 6px;
            font-size: 12px
            border: 1px solid #333
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
            padding: 8px;
            border: 1px solid #333
        }

        /* Print tweaks */
        @media print {
            body {
                background: white
            }

            .page {
                box-shadow: none;
                margin: 0;
                padding: 12mm
            }
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

        /* Action buttons */
        .invoice-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .invoice-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .invoice-actions .btn-print {
            background-color: #0d6efd;
            color: white;
        }

        .invoice-actions .btn-print:hover {
            background-color: #0b5ed7;
        }

        .invoice-actions .btn-email {
            background-color: #198754;
            color: white;
        }

        .invoice-actions .btn-email:hover {
            background-color: #157347;
        }

        .invoice-actions .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .invoice-actions .btn-back:hover {
            background-color: #5c636a;
            color: white;
        }

        @media print {
            .invoice-actions {
                display: none;
            }
        }

        /* SweetAlert2 form styling */
        .swal2-popup .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .swal2-popup .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            font-size: 1rem;
        }
        .swal2-popup .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="invoice-actions">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-email" onclick="sendEmail()">Email</button>
        <a href="{{ route('order.list') }}" class="btn-back">Back</a>
    </div>
    
    <div class="page" role="document">
        <table class="header" aria-label="Invoice header">
            <tr>
                <td colspan="2" class="center" style="font-size:20px;font-weight:700;padding:6px">
                    Order Details
                </td>
            </tr>
            <tr>
                <td class="addr" aria-label="Payment details">
                    <p><b>Order Date:</b> {{ optional($order->order_date)->format('d/m/Y H:i') ?? optional($order->created_at)->format('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
        <table class="items" aria-label="Items">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 60px;">Sr No.</th>
                    <th rowspan="2" style="width: 350px;">Box Qty</th>
                    <th rowspan="2" style="width: 40%;">Product</th>
                    <th rowspan="2" style="width: 60px; text-align: center;">Qty</th>
                    <th colspan="2" style="text-align: center;">Rate</th>
                    <th rowspan="2" style="width: 100px; text-align: center;">Amount</th>
                </tr>
                <tr>
                    <th style="width: 80px; text-align: center;">Unit</th>
                    <th style="width: 80px; text-align: center;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $itemNumber = 1;
                    $soldItems = $order->items->where('type', '!=', 'returned');
                    
                    // Use order subtotal and VAT from order
                    $orderSubtotal = (float)($order->subtotal ?? 0);
                    $orderTotal = (float)($order->total_amount ?? 0);
                    $paidAmount = (float)($order->paid_amount ?? 0);
                    $dueAmount = (float)($order->unpaid_amount ?? 0);
                @endphp
                
                @foreach($soldItems as $item)
                    @php
                        $totalPrice = (float)($item->total_price ?? 0);
                        $unitPrice = (float)($item->unit_price ?? 0);
                        $quantity = (float)($item->quantity ?? 0);
                        $total = (float)($item->total ?? 0);
                        // Get box qty from product_unit or default
                        $boxQty = $item->product_unit ?? '';
                    @endphp
                    <tr>
                        <td>{{ $itemNumber++ }}</td>
                        <td>{{ $boxQty ?: '-' }}</td>
                        <td>
                            @if($item->product)
                                {{ $item->product->name ?? 'N/A' }}
                            @else
                                Product #{{ $item->product_id }}
                            @endif
                        </td>
                        <td class="center">{{ number_format($quantity, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($unitPrice, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($totalPrice, 2) }}</td>
                        <td class="right">{{ $currencySymbol }}{{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="6" class="right" style="padding-top:10px">Sub Total</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format($orderSubtotal, 2) }}</td>
                </tr>
                @if(isset($order->shipping_cost) && $order->shipping_cost > 0)
                <tr>
                    <td colspan="6" class="right" style="padding-top:10px">Shipping</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format((float)$order->shipping_cost, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="6" class="right" style="padding-top:10px">Total</td>
                    <td class="right" style="padding-top:10px">{{ $currencySymbol }}{{ number_format($orderTotal, 2) }}</td>
                </tr>
                @if($paidAmount > 0)
                <tr>
                    <td colspan="6" class="right" style="padding-top:10px">- Paid</td>
                    <td class="right" style="padding-top:10px">- {{ $currencySymbol }}{{ number_format($paidAmount, 2) }}</td>
                </tr>
                @endif
                @if($dueAmount > 0)
                <tr>
                    <td colspan="6" class="right" style="font-weight:700;padding-top:10px">Due</td>
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
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function sendEmail() {
            const orderId = {{ $order->id }};
            const customerEmail = '{{ optional($order->customer)->email ?? '' }}';
            const btn = event.target;
            
            // Show prompt with customer email pre-filled
            Swal.fire({
                title: 'Send Invoice Email',
                html: `
                    <div class="mb-3">
                        <label for="swal-email-input" class="form-label">Email Addresses (comma-separated)</label>
                        <input type="text" id="swal-email-input" class="form-control" 
                               value="${customerEmail}" 
                               placeholder="email1@example.com, email2@example.com" autocomplete="off">
                        <small class="form-text text-muted">Enter email addresses separated by commas</small>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Send Email',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light',
                    cancelButton: 'btn btn-secondary waves-effect waves-light'
                },
                didOpen: () => {
                    // Focus on the input field
                    const input = document.getElementById('swal-email-input');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                },
                preConfirm: () => {
                    const emailInput = document.getElementById('swal-email-input');
                    const emails = emailInput ? emailInput.value.trim() : '';
                    
                    if (!emails) {
                        Swal.showValidationMessage('Please enter at least one email address');
                        return false;
                    }

                    // Validate email format (basic validation)
                    const emailList = emails.split(',').map(e => e.trim()).filter(e => e);
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const invalidEmails = emailList.filter(email => !emailRegex.test(email));
                    
                    if (invalidEmails.length > 0) {
                        Swal.showValidationMessage(`Invalid email format: ${invalidEmails.join(', ')}`);
                        return false;
                    }

                    return emailList;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const emailList = result.value;
                    const originalText = btn.textContent;
                    
                    // Disable button and show loading state
                    btn.disabled = true;
                    btn.textContent = 'Sending...';
                    
                    // Send AJAX request to email endpoint with email list
                    fetch(`/order/invoice/${orderId}/email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            emails: emailList
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || 'Invoice email sent successfully',
                                icon: 'success',
                                customClass: {
                                    confirmButton: 'btn btn-success waves-effect waves-light'
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to send email',
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-danger waves-effect waves-light'
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error sending email. Please try again.',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-danger waves-effect waves-light'
                            }
                        });
                    })
                    .finally(() => {
                        // Re-enable button
                        btn.disabled = false;
                        btn.textContent = originalText;
                    });
                }
            });
        }
    </script>
</body>

</html>

