<div
  class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-6 text-center text-sm-start gap-2">
  <div class="mb-2 mb-sm-0">
    <h4 class="mb-1">Customer ID #{{ $customer->id ?? '-' }}</h4>
    @if(!empty($customer) && !empty($customer->last_login))
    <p class="mb-0">Last login: {{ $customer->last_login->format('M d, Y, g:i A') }}</p>
    @endif
  </div>
  <button type="button" class="btn btn-label-danger delete-customer" data-id="{{ $customer->id }}">Delete Customer</button>
</div>