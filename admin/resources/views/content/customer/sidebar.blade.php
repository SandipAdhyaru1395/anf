<!-- Customer-detail Sidebar -->
<div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
    <!-- Customer-detail Card -->
    <div class="card mb-6">
      <div class="card-body pt-12">
        <div class="d-flex justify-content-around flex-wrap mb-10 gap-0 gap-md-3 gap-lg-4">
          <div class="d-flex align-items-center gap-4 me-5">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-shopping-cart icon-lg"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-0">{{ $ordersCount ?? 0 }}</h5>
              <span>Orders</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-4">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-0">{{ $currencySymbol }}{{ number_format($totalSpent ?? 0, 2) }}</h5>
              <span>Spent</span>
            </div>
          </div>
        </div>

        <div class="info-container">
          <h5 class="pb-4 border-bottom text-capitalize mb-4">Details</h5>
          <ul class="list-unstyled mb-6">
            @if(!empty($customer->company_name))
            <li class="mb-2">
              <span class="h6 me-1">Company:</span>
              <span>{{ $customer->company_name }}</span>
            </li>
            @endif
            @if(!empty($customer->email))
            <li class="mb-2">
              <span class="h6 me-1">Email:</span>
              <span>{{ $customer->email }}</span>
            </li>
            @endif
            @if(isset($customer->is_active))
            <li class="mb-2">
              <span class="h6 me-1">Status:</span>
              <span class="badge {{ $customer->is_active ? 'bg-label-success' : 'bg-label-danger' }}">{{ $customer->is_active ? 'Active' : 'Inactive' }}</span>
            </li>
            @endif
            @if(!empty($customer->phone))
            <li class="mb-2">
              <span class="h6 me-1">Contact:</span>
              <span>{{ $customer->phone }}</span>
            </li>
            @endif
            @if(!empty($customer->vat_number))
            <li class="mb-2">
              <span class="h6 me-1">VAT Number:</span>
              <span>{{ $customer->vat_number }}</span>
            </li>
            @endif
            @if(isset($customer->credit_balance))
            <li class="mb-2">
              <span class="h6 me-1">Credit Balance:</span>
              <span>{{ $currencySymbol }}{{ number_format($customer->credit_balance, 2) }}</span>
            </li>
            @endif
            @if(!empty($customer->approved_by))
              <li class="mb-2">
                <span class="h6 me-1">Approved By:</span>
                <span>{{ $customer->approvedBy->name }}</span>
              </li>
            @endif
            @if(!empty($customer->approved_at))
            <li class="mb-2">
              <span class="h6 me-1">Approved At:</span>
              <span>{{ $customer->approved_at->format('M d, Y, g:i A') }}</span>
            </li>
            @endif
          </ul>
          <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-primary w-100" data-bs-target="#editCustomerModal" data-bs-toggle="modal">Edit
              Details</a>
          </div>
        </div>
      </div>
    </div>
    <!-- /Customer-detail Card -->
  </div>
  <!--/ Customer Sidebar -->
  @include('_partials._modals.modal-edit-customer')