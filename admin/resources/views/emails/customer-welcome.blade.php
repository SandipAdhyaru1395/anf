@extends('emails.layouts.email')

@section('title', 'Welcome to ' . ($company_title ?? config('app.name')))

@section('content')

    <h1>Welcome, {{ $user->name }}! 🎉</h1>
    <p>We're really glad you're here. Your account is all set up and ready to go.</p>

    <div class="info-box">
        <strong>Here's what you can do from your dashboard:</strong><br>
        ✔ Browse vapes, e-liquids, pods &amp; accessories<br>
        ✔ Save your favourite products to your wishlist<br>
        ✔ Track your orders in real time<br>
        ✔ Manage your delivery addresses and payment details
    </div>

    <div class="btn-wrapper">
        <a href="{{ url('/shop') }}" class="btn">Start Shopping</a>
    </div>

    <hr class="divider">

    <p style="text-align:center; font-weight:700; color:#111; font-size:16px;">🎁 Your Welcome Gift</p>
    <p style="text-align:center; color:#555;">Use code below at checkout for <strong>{{ $discountPercent }}% off</strong> your first order:</p>

    <span class="promo-code">{{ $welcomeCode }}</span>

    <hr class="divider">

    <div class="age-box">
        ⚠️ <strong>Age Verification Reminder</strong><br>
        By creating an account you have confirmed you are aged <strong>18 or over</strong>.
        We comply fully with UK vaping regulations and reserve the right to request
        proof of age at any time.
    </div>

    <p style="font-size:13px; color:#888; text-align:center;">
        Questions? Email us at
        <a href="mailto:{{ $company_email ?? '' }}">
            {{ $company_email ?? '' }}
        </a>
        or use the live chat on our website.
    </p>

    <p style="text-align:center; margin-top:20px;">
        Happy vaping,<br>
        <strong>The {{ $company_title ?? config('app.name') }} Team</strong>
    </p>

@endsection
