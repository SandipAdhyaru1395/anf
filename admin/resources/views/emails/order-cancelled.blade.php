@extends('emails.layouts.email')

@section('title', 'Order Update - #' . $order->order_number . ' | ' . ($company_title ?? config('app.name')))

@section('content')

    <h1>Update on Your Order #{{ $order->order_number }}</h1>
    <p>Hi {{ $user->name }}, we wanted to give you a quick update on your order.</p>

    <p>
        <span class="status-badge" style="background:#fee2e2; color:#991b1b;">
            {{ $statusLabel }}
        </span>
    </p>

    <hr class="divider">

    <table class="summary-table">
        <tr><th colspan="2">Order Status</th></tr>
        <tr>
            <td><strong>Order Number</strong></td>
            <td>#{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td><strong>Current Status</strong></td>
            <td>{{ $statusLabel }}</td>
        </tr>
        <tr>
            <td><strong>Updated On</strong></td>
            <td>{{ optional($order->updated_at)->format('d M Y') }}</td>
        </tr>
    </table>

    <div class="info-box">
        {{ $statusMessage }}
    </div>

    <p>
        If you'd like to place a new order or need help with this one, please contact us at
        <a href="mailto:{{ $company_email ?? '' }}">
            {{ $company_email ?? '' }}
        </a>.
    </p>

    <div class="btn-wrapper">
        <a href="{{ url('/orders/' . $order->order_number) }}" class="btn">View My Order</a>
    </div>

    <p style="text-align:center; margin-top:20px;">
        Thank you,<br>
        <strong>The {{ $company_title ?? config('app.name') }} Team</strong>
    </p>

@endsection
