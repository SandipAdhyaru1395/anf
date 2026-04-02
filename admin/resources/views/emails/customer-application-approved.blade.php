@extends('emails.layouts.email')

@section('title', 'Your application has been approved - ' . ($company_title ?? config('app.name')))

@section('content')

    <p style="margin: 0 0 16px;">Dear {{ $customerName }},</p>

    <p style="margin: 0 0 16px;">Your application has been approved.</p>

    <p style="margin: 0 0 20px;">
        You can now view our product catalogue and place orders through our website at
        <a href="{{ $storefrontUrl }}" style="color: #1d4ed8; font-weight: 600;">{{ $storefrontUrl }}</a>.
    </p>

    <div class="btn-wrapper">
        <a href="{{ $storefrontUrl }}" class="btn">Go to website</a>
    </div>

    <p style="margin-top: 20px;">
        Kind regards,<br>
        <strong>The {{ $company_title ?? config('app.name') }} Team</strong>
    </p>

@endsection
