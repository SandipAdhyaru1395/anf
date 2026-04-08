@extends('emails.layouts.email')

@section('title', 'New customer registration - ' . ($company_title ?? config('app.name')))

@php
  $partOfGroup = $customer->is_part_of_group === null || $customer->is_part_of_group === ''
    ? '—'
    : ((int) $customer->is_part_of_group === 1 ? 'Yes' : 'No');
@endphp

@section('content')

    <p style="margin: 0 0 16px;">A new customer has signed up on your website and is <strong>pending approval</strong>.</p>

    <div class="info-box" style="margin-bottom: 20px;">
        <table class="summary-table" style="margin: 0;">
            <tr>
                <th style="width: 200px;">Customer reference</th>
                <td>{{ $customer->id }}</td>
            </tr>
            <tr>
                <th>Registered at</th>
                <td>{{ \App\Helpers\Helpers::displayDateTime($customer->created_at) }}</td>
            </tr>
            <tr>
                <th>Company name</th>
                <td>{{ $customer->company_name ?: '—' }}</td>
            </tr>
            <tr>
                <th>Your name (contact)</th>
                <td>{{ $customer->contact_person_name ?: '—' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td><a href="mailto:{{ $customer->email }}">{{ $customer->email ?: '—' }}</a></td>
            </tr>
            <tr>
                <th>Mobile / phone</th>
                <td>{{ $customer->phone ?: '—' }}</td>
            </tr>
            <tr>
                <th>Address line 1</th>
                <td>{{ $customer->company_address_line1 ?: '—' }}</td>
            </tr>
            <tr>
                <th>Address line 2</th>
                <td>{{ $customer->company_address_line2 ?: '—' }}</td>
            </tr>
            <tr>
                <th>City</th>
                <td>{{ $customer->company_city ?: '—' }}</td>
            </tr>
            <tr>
                <th>Country</th>
                <td>{{ $customer->company_country ?: '—' }}</td>
            </tr>
            <tr>
                <th>Postcode</th>
                <td>{{ $customer->company_zip_code ?: '—' }}</td>
            </tr>
            <tr>
                <th>VAT number</th>
                <td>{{ $customer->vat_number ?: '—' }}</td>
            </tr>
            <tr>
                <th>EORI number</th>
                <td>{{ $customer->eori_number ?: '—' }}</td>
            </tr>
            <tr>
                <th>Part of a group?</th>
                <td>{{ $partOfGroup }}</td>
            </tr>
            <tr>
                <th>Type of business</th>
                <td>{{ $customer->business_type ?: '—' }}</td>
            </tr>
            <tr>
                <th>Average monthly spend (ex VAT)</th>
                <td>{{ $customer->average_monthly_spend_ex_vat ?: '—' }}</td>
            </tr>
            <tr>
                <th>Stores (have / service)</th>
                <td>
                    @if($customer->stores_serviced_count === null || $customer->stores_serviced_count === '')
                        —
                    @else
                        {{ $customer->stores_serviced_count }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Rep code</th>
                <td>{{ $customer->rep_code ?: '—' }}</td>
            </tr>
            <tr>
                <th>Sales rep name (if known)</th>
                <td>{{ $customer->sales_rep_name ?: '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="btn-wrapper">
        <a href="{{ $reviewUrl }}" class="btn">Review in admin</a>
    </div>

    <p class="fallback-link" style="margin-top: 16px;">If the button does not work, copy this link:<br>{{ $reviewUrl }}</p>

    <p style="font-size: 13px; color: #888; margin-top: 20px;">
        This is an automated message from {{ $company_title ?? config('app.name') }}.
    </p>

@endsection
