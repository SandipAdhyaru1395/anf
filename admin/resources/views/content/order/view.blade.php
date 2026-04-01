@extends('layouts/layoutMaster')

@section('title', 'Order View')

@section('content')
    @php
        $orderedOn =
            optional($order->order_date)->format('d M Y H:i') ??
            (optional($order->created_at)->format('d M Y H:i') ?? '—');
        $customerLabel = optional($order->customer)->company_name ?? (optional($order->customer)->email ?? '—');
        $contactName = optional($order->customer)->email
            ? \Illuminate\Support\Str::before($order->customer->email, '@')
            : null;

        $soldItems = $order->items->where('type', '!=', 'returned');
        if ($order->type === 'CN') {
            $soldItems = $order->items;
        }
        $totalQty = (float) $soldItems->sum('quantity');
        $dispatchedQty = $order->status === 'Completed' ? $totalQty : 0;
        $dispatchDate =
            $order->status === 'Completed'
                ? optional($order->order_date)->format('d M Y') ?? optional($order->updated_at)->format('d M Y')
                : null;
        $shippingBranch = $order->shippingBranch;
        $billingBranch = $order->billingBranch ?: $shippingBranch;
    @endphp

    <div class="card order-view-page">
        <div class="card-header py-7">
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('order.list') }}" class="ov-bc-link">Orders</a>
                    </li>
                    {{-- <li class="breadcrumb-item active" aria-current="page">
                        #{{ $order->type }}{{ $order->order_number }}
                    </li> --}}
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $order->order_number }}
                    </li>
                </ol>
            </nav>
        </div>
        <div class="card-body">

            <div class="row gx-5 gy-3 align-items-start">
                <div class="col-12 col-lg-7">
                    <div class="ov-info">
                        <div class="ov-row">
                            <div class="ov-label">Ordered On</div>
                            <div class="ov-value">{{ $orderedOn }}</div>
                        </div>
                        <div class="ov-row">
                            <div class="ov-label">Customer</div>
                            <div class="ov-value">
                                @if ($order->customer)
                                    <a class="ov-link"
                                        href="{{ route('customer.overview', $order->customer->id) }}">{{ $customerLabel }}</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="ov-row">
                            <div class="ov-label">Customer Ref</div>
                            <div class="ov-value">{{ $order->customer_ref ?? '—' }}</div>
                        </div>
                        <div class="ov-row ov-row-last">
                            <div class="ov-label">Created By</div>
                            <div class="ov-value">{{ $order->created_by ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="ov-addresses">
                        <div class="ov-address">
                            <div class="ov-address-title">DELIVER TO</div>
                            <div class="ov-address-line">{{ $contactName ?? '—' }}</div>
                            <div class="ov-address-line">{{ $customerLabel }}</div>
                            @if ($shippingBranch?->address_line1)
                                <div class="ov-address-line">{{ $shippingBranch->address_line1 }}</div>
                            @endif
                            @if ($shippingBranch?->address_line2)
                                <div class="ov-address-line">{{ $shippingBranch->address_line2 }}</div>
                            @endif
                            @if ($shippingBranch?->city)
                                <div class="ov-address-line text-uppercase">{{ $shippingBranch->city }}</div>
                            @endif
                            @if ($shippingBranch?->zip_code)
                                <div class="ov-address-line">{{ $shippingBranch->zip_code }}</div>
                            @endif
                            @if ($shippingBranch?->country)
                                <div class="ov-address-line text-uppercase">{{ $shippingBranch->country }}</div>
                            @endif
                            @if (optional($order->customer)->phone)
                                <div class="ov-address-line">Phone: {{ $order->customer->phone }}</div>
                            @endif
                            <a class="ov-link mt-1 d-inline-block"
                                href="{{ $order->customer ? route('customer.branches', $order->customer->id) : '#' }}">Edit</a>
                        </div>

                        <div class="ov-address">
                            <div class="ov-address-title">INVOICE TO</div>
                            <div class="ov-address-line">{{ $contactName ?? '—' }}</div>
                            <div class="ov-address-line">{{ $customerLabel }}</div>
                            @if ($billingBranch?->address_line1)
                                <div class="ov-address-line">{{ $billingBranch->address_line1 }}</div>
                            @endif
                            @if ($billingBranch?->address_line2)
                                <div class="ov-address-line">{{ $billingBranch->address_line2 }}</div>
                            @endif
                            @if ($billingBranch?->city)
                                <div class="ov-address-line text-uppercase">{{ $billingBranch->city }}</div>
                            @endif
                            @if ($billingBranch?->zip_code)
                                <div class="ov-address-line">{{ $billingBranch->zip_code }}</div>
                            @endif
                            @if ($billingBranch?->country)
                                <div class="ov-address-line text-uppercase">{{ $billingBranch->country }}</div>
                            @endif
                            <a class="ov-link mt-1 d-inline-block"
                                href="{{ $order->customer ? route('customer.branches', $order->customer->id) : '#' }}">Edit</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ov-divider"></div>

            <div class="row gx-4 gy-3 align-items-start">
                <div class="col-12">
                    <div class="table-responsive ov-table-wrap">
                        <table class="table ov-items-table">
                            <thead>
                                <tr>
                                    <th class="ov-th-code">Code</th>
                                    <th>Name</th>
                                    <th class="text-center ov-th-sm">Qty.</th>
                                    <th class="text-center ov-th-sm">Invoiced</th>
                                    <th class="text-center ov-th-sm">Paid</th>
                                    <th class="text-center ov-th-sm">Dispatched</th>
                                    <th class="text-end ov-th-money">Unit {{ $currencySymbol }}</th>
                                    <th class="text-end ov-th-money">Total {{ $currencySymbol }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach ($soldItems as $item)
                                    @php
                                        $qty = (float) ($item->quantity ?? 0);
                                        $unitPrice = (float) ($item->unit_price ?? 0);
                                        $total = (float) ($item->total ?? 0);
                                        $grandTotal += $total;
                                        $dispatched = $order->status === 'Completed' ? $qty : 0;
                                        $invoiced = $order->type === 'EST' ? 0 : $qty;
                                        $paid = 0;
                                        if (
                                            (float) ($order->paid_amount ?? 0) > 0 &&
                                            (float) ($order->total_amount ?? 0) > 0
                                        ) {
                                            $paid = round(
                                                $qty * ((float) $order->paid_amount / (float) $order->total_amount),
                                                2,
                                            );
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ optional($item->product)->sku ?? '—' }}</td>
                                        <td class="fw-semibold">
                                            {{ optional($item->product)->name ?? 'Product #' . $item->product_id }}
                                        </td>
                                        <td class="text-center">{{ number_format($qty, 0) }}</td>
                                        <td class="text-center">{{ number_format($invoiced, 0) }}</td>
                                        <td class="text-center">{{ number_format($paid, 0) }}</td>
                                        <td class="text-center">{{ number_format($dispatched, 0) }}</td>
                <td class="text-end">{{ $currencySymbol }}{{ number_format($unitPrice, 2) }}</td>
                <td class="text-end">{{ $currencySymbol }}{{ number_format($total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="ov-total-label">Total {{ $currencyCode ?? 'GBP' }} ({{ $soldItems->count() }}
                                        item{{ $soldItems->count() !== 1 ? 's' : '' }})</td>
                                    <td class="text-end ov-total-value">
                                        {{ $currencySymbol }}{{ number_format($grandTotal > 0 ? $grandTotal : (float) ($order->total_amount ?? 0), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="ov-divider"></div>
            <div class="row gx-4 gy-3 align-items-start">
                <div class="col-12 col-lg-6">
                    <div class="ov-title mb-2">Payment</div>
                    <div class="ov-info" style="border-top: 0">
                        <div class="ov-row">
                            <div class="ov-label">Status</div>
                            <div class="ov-value">{{ strtoupper($order->payment_status ?? '—') }}</div>
                        </div>
                        @php
                            $payment = $order->payments->first();
                        @endphp
                        @if ($payment)
                            <div class="ov-row">
                                <div class="ov-label">Paid on</div>
                                <div class="ov-value">{{ optional($payment->date)->format('d M Y H:i') ?? '—' }}</div>
                            </div>
                            <div class="ov-row">
                                <div class="ov-label">Payment reference</div>
                                <div class="ov-value">{{ $payment->reference_no ?? '—' }}</div>
                            </div>
                            <div class="ov-row">
                                <div class="ov-label">Card brand</div>
                                <div class="ov-value">{{ $payment->card_brand ?: '—' }}</div>
                            </div>
                            <div class="ov-row">
                                <div class="ov-label">Card number</div>
                                <div class="ov-value" dir="ltr">@if(!empty($payment->card_last4))•••• {{ $payment->card_last4 }}@else—@endif</div>
                            </div>
                            <div class="ov-row ov-row-last">
                                <div class="ov-label">Expires</div>
                                <div class="ov-value">{{ $payment->card_expiry ?: '—' }}</div>
                            </div>
                        @else
                            <div class="ov-row ov-row-last">
                                <div class="ov-label">Card</div>
                                <div class="ov-value text-muted">No payment on file</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('page-style')
    <style>
        .order-view-page {
            --ov-line: #e9ecef;
            --ov-text: #2f3a44;
            --ov-muted: #6c757d;
            color: var(--ov-text);
        }

        .order-view-page .ov-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .order-view-page .breadcrumb {
            --bs-breadcrumb-divider-color: var(--ov-muted);
            --bs-breadcrumb-item-active-color: var(--ov-text);
            font-size: 1.125rem;
        }

        .order-view-page .ov-bc-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .order-view-page .ov-bc-link:hover {
            text-decoration: underline;
        }

        .order-view-page .breadcrumb-item+.breadcrumb-item::before {
            color: var(--ov-muted);
        }

        .order-view-page .ov-info {
            border-top: 1px solid var(--ov-line);
        }

        .order-view-page .ov-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid var(--ov-line);
            align-items: center;
        }

        .order-view-page .ov-row-last {
            border-bottom: 0;
        }

        .order-view-page .ov-label {
            font-size: 0.8125rem;
            color: var(--ov-muted);
        }

        .order-view-page .ov-value {
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .order-view-page .ov-link {
            color: #0d6efd;
            font-weight: 600;
            font-size: 0.8125rem;
            text-decoration: none;
        }
        .order-view-page .ov-link:hover {
            text-decoration: underline;
        }

        .order-view-page .ov-addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
        }

        .order-view-page .ov-address-title {
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            color: var(--ov-muted);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .order-view-page .ov-address-line {
            font-size: 0.8125rem;
            line-height: 1.4;
            color: var(--ov-text);
        }

        .order-view-page .ov-divider {
            height: 1px;
            background: var(--ov-line);
            margin: 18px 0;
        }

        .order-view-page .ov-dispatch {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .order-view-page .ov-box-illu {
            width: 120px;
            height: 92px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .order-view-page .ov-dispatch-count {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ov-text);
        }

        .order-view-page .ov-dispatch-date {
            font-size: 0.75rem;
            color: var(--ov-muted);
        }

        .order-view-page .ov-table-wrap {
            border: 1px solid var(--ov-line);
        }

        .order-view-page .ov-items-table {
            margin: 0;
            font-size: 0.75rem;
        }

        .order-view-page .ov-items-table thead th {
            background: #f6f7f9;
            border-bottom: 1px solid var(--ov-line);
            color: var(--ov-muted);
            font-weight: 700;
            padding: 10px 10px;
            white-space: nowrap;
        }

        .order-view-page .ov-items-table tbody td {
            border-top: 0;
            border-bottom: 1px solid var(--ov-line);
            padding: 12px 10px;
            vertical-align: middle;
        }

        .order-view-page .ov-items-table tbody td+td,
        .order-view-page .ov-items-table thead th+th {
            border-left: 1px solid var(--ov-line);
        }

        .order-view-page .ov-items-table tfoot td {
            padding: 10px 10px;
            border-top: 0;
            background: #fff;
            font-weight: 600;
            color: var(--ov-muted);
        }

        .order-view-page .ov-total-label {
            text-align: center;
            border-right: 1px solid var(--ov-line);
        }

        .order-view-page .ov-total-value {
            font-weight: 600;
            color: var(--ov-text);
        }

        .order-view-page .ov-th-code {
            width: 120px;
        }

        .order-view-page .ov-th-sm {
            width: 70px;
        }

        .order-view-page .ov-th-money {
            width: 90px;
        }

        @media (max-width: 992px) {
            .order-view-page .ov-addresses {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .order-view-page .ov-row {
                grid-template-columns: 120px 1fr;
            }
        }
    </style>
@endsection
