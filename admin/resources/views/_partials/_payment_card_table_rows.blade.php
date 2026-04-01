{{-- Expects $payment (\App\Models\Payment). Separate lines: "Card brand: …", no "Payment details" wrapper. Optional $labelWeight for first column in 2-col layouts (unused when $singleColumn). --}}
@php
    $singleColumn = $singleColumn ?? false;
    $lw = $labelWeight ?? 600;
@endphp
@if($singleColumn)
<tr>
    <td colspan="2">Card brand: {{ $payment->card_brand ?: '—' }}</td>
</tr>
<tr>
    <td colspan="2">Card number: @if(!empty($payment->card_last4))•••• {{ $payment->card_last4 }}@else—@endif</td>
</tr>
<tr>
    <td colspan="2">Expires: {{ $payment->card_expiry ?: '—' }}</td>
</tr>
@else
<tr>
    <td style="width:22%; font-weight: {{ $lw }};">Card brand:</td>
    <td>{{ $payment->card_brand ?: '—' }}</td>
</tr>
<tr>
    <td style="font-weight: {{ $lw }};">Card number:</td>
    <td>@if(!empty($payment->card_last4))•••• {{ $payment->card_last4 }}@else—@endif</td>
</tr>
<tr>
    <td style="font-weight: {{ $lw }};">Expires:</td>
    <td>{{ $payment->card_expiry ?: '—' }}</td>
</tr>
@endif
