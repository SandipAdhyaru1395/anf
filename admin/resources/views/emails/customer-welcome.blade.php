@extends('emails.layouts.email')

@section('title', 'Thank you for your application - ' . ($company_title ?? config('app.name')))

@section('content')

    <p>Dear {{ $user->name }},</p>

    <p>Thank you for your recent application for an account.</p>

    <p>We will email you again as soon as your application has been reviewed.</p>

    <p>
        Regards,<br>
        <strong>{{ $company_title ?? config('app.name') }}</strong>
    </p>

@endsection
