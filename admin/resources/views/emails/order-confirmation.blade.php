@extends('emails.layouts.email')

@section('title', 'Order Confirmed - #' . $order->order_number)

@section('content')

    <h1>Order Confirmed! 📦</h1>
    <p>Hi {{ $user->name }}, thank you for your order. We've received it and it's being prepared.</p>

    <p><span class="status-badge status-confirmed">✔ Confirmed</span></p>

    <hr class="divider">

    <table class="summary-table">
        <tr>
            <th colspan="2">Order Summary</th>
        </tr>
        <tr>
            <td><strong>Order Number</strong></td>
            <td>#{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td><strong>Order Date</strong></td>
            <td>{{ \App\Helpers\Helpers::displayDateTime($order->created_at) }}</td>
        </tr>
        <tr>
            <td><strong>Payment</strong></td>
            <td>{{ $payment_summary }}</td>
        </tr>
    </table>

    <hr class="divider">

    <table class="summary-table">
        <tr>
            <th>Product</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Price</th>
        </tr>
        @foreach ($order->items as $item)
        <tr>
            <td>{{ optional($item->product)->name ?? ('Product #' . $item->product_id) }}</td>
            <td style="text-align:center;">{{ $item->quantity }}</td>
            <td style="text-align:right;">£{{ number_format((float) $item->total_price, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="2">Subtotal</td>
            <td style="text-align:right;">£{{ number_format((float) $order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">VAT</td>
            <td style="text-align:right;">£{{ number_format((float) $order->vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">Shipping @if (!empty($order->delivery_method_name)) ({{ $order->delivery_method_name }})@endif</td>
            <td style="text-align:right;">
                @if ((float) $order->delivery_charge === 0.0) FREE
                @else £{{ number_format((float) $order->delivery_charge, 2) }}
                @endif
            </td>
        </tr>
        @if ((float) $order->wallet_credit_used > 0)
        <tr>
            <td colspan="2">Discount</td>
            <td style="text-align:right; color:#e63946;">-£{{ number_format((float) $order->wallet_credit_used, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td colspan="2"><strong>Total</strong></td>
            <td style="text-align:right;"><strong>£{{ number_format((float) $order->outstanding_amount, 2) }}</strong></td>
        </tr>
    </table>

    <hr class="divider">

    <div class="info-box">
        <strong>Delivering To:</strong>
        {{ $shipping_address->name ?? $user->name }} -
        {{ $shipping_address->address_line1 ?? '-' }}
        @if (!empty($shipping_address->address_line2)), {{ $shipping_address->address_line2 }}@endif,
        {{ $shipping_address->city ?? '-' }}, {{ $shipping_address->zip_code ?? '-' }}, {{ $shipping_address->country ?? 'United Kingdom' }}<br><br>
        <strong>Estimated Delivery:</strong> {{ $estimated_delivery ?: 'To be confirmed' }}
    </div>

    <p>You'll receive another email with your tracking details once your order has been dispatched.</p>

    <div class="btn-wrapper">
        <a href="{{ $view_order_url }}" class="btn">View My Order</a>
    </div>

    <p style="font-size:13px; color:#888; text-align:center;">
        Need to make a change? Contact us within <strong>1 hour</strong> at
        <a href="mailto:{{ $company_email ?? '' }}">
            {{ $company_email ?? '' }}
        </a>
        and we'll do our best to help.
    </p>

    <p style="text-align:center; margin-top:20px;">
        Thanks again,<br>
        <strong>The {{ $company_title ?? config('app.name') }} Team</strong>
    </p>

@endsection
