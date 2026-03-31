@extends('emails.layouts.email')

@section('title', 'Reset Your Password - ' . ($company_title ?? config('app.name')))

@section('content')

    <h1>Reset Your Password 🔐</h1>
    <p>Hi {{ $user->name }},</p>
    <p>
        We received a request to reset the password for your
        <strong>{{ $company_title ?? config('app.name') }}</strong> account linked to this email address.
    </p>

    <div class="btn-wrapper">
        <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
    </div>

    <div class="info-box">
        ⏱ <strong>This link will expire in {{ $expiryMinutes }} minutes</strong>
        for your security.
    </div>

    <hr class="divider">

    <p style="font-size:13px; color:#888;">
        If you didn't request a password reset, you can safely ignore this email —
        your account remains secure and no changes have been made.
    </p>

    <p style="font-size:13px; color:#888;">
        🔒 For your protection, <strong>never share your password</strong> with anyone,
        including our support team. We will never ask for it.
    </p>

    <hr class="divider">

    <p style="font-size:12px; color:#aaa; text-align:center;">
        Having trouble with the button? Copy and paste this link into your browser:
    </p>
    <p class="fallback-link">
        {{ $resetUrl }}
    </p>

    <hr class="divider">

    <p style="font-size:13px; color:#888; text-align:center;">
        Need help?
        <a href="mailto:{{ $company_email ?? '' }}">
            {{ $company_email ?? '' }}
        </a>
    </p>

    <p style="text-align:center; margin-top:20px;">
        Stay safe,<br>
        <strong>The {{ $company_title ?? config('app.name') }} Team</strong>
    </p>

    <p style="font-size:11px; color:#ccc; text-align:center; margin-top:12px;">
        This is an automated message. Please do not reply directly to this email.
    </p>

@endsection
