@extends('layouts/layoutMaster')

@section('title', 'Customer - Overview')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('page-script')
  @vite([
    'resources/assets/js/customer-detail.js',
    'resources/assets/js/customer-overview.js',
    'resources/assets/js/modal-edit-customer.js'
  ])
  <script>
    @if ($errors->editCustomer->any())
      document.addEventListener("DOMContentLoaded", function () {
        // let offcanvasCustomerEdit = new bootstrap.Offcanvas(document.getElementById('offcanvasCustomerEdit'));
        // offcanvasCustomerEdit.show();
        let editCustomerModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        editCustomerModal.show();
      });
    @endif
  </script>
@endsection

@section('content')
  @php
    $customerName = $customer->company_name ?: ($customer->email ?: 'Customer');
    $statusLabel = ($customer->is_active ?? false) ? 'Active' : 'Inactive';
    $statusClass = ($customer->is_active ?? false) ? 'bg-label-success' : 'bg-label-secondary';

    $branchesCollection = collect($branches ?? []);
    $defaultDelivery = $branchesCollection->firstWhere('is_default_delivery', true) ?: $branchesCollection->first();
    $defaultBilling = $branchesCollection->firstWhere('is_default_billing', true) ?: $branchesCollection->first();

    $shipping = [
      'Contact' => $customer->email ?? '-',
      'Company' => $customer->company_name ?? '-',
      'Address line 1' => $defaultDelivery->address_line1 ?? ($customer->company_address_line1 ?? '-'),
      'Address line 2' => $defaultDelivery->address_line2 ?? ($customer->company_address_line2 ?? '-'),
      'City' => $defaultDelivery->city ?? ($customer->company_city ?? '-'),
      'ZIP' => $defaultDelivery->zip_code ?? ($customer->company_zip_code ?? '-'),
      'Country' => $defaultDelivery->country ?? ($customer->company_country ?? '-'),
    ];

    $billing = [
      'Contact' => $customer->email ?? '-',
      'Company' => $customer->company_name ?? '-',
      'Address line 1' => $defaultBilling->address_line1 ?? ($customer->company_address_line1 ?? '-'),
      'Address line 2' => $defaultBilling->address_line2 ?? ($customer->company_address_line2 ?? '-'),
      'City' => $defaultBilling->city ?? ($customer->company_city ?? '-'),
      'ZIP' => $defaultBilling->zip_code ?? ($customer->company_zip_code ?? '-'),
      'Country' => $defaultBilling->country ?? ($customer->company_country ?? '-'),
    ];
  @endphp

  <style>
    .customer-topbar {
      background: #fff;
      border: 1px solid #eceef1;
      border-radius: .375rem;
      padding: 1rem 1.25rem;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .customer-breadcrumb {
      display: flex;
      align-items: center;
      gap: .5rem;
      font-size: 1rem;
      font-weight: 600;
      color: #566a7f;
    }
    .customer-breadcrumb a {
      color: #696cff;
      text-decoration: none;
      font-weight: 600;
    }
    .customer-breadcrumb a:hover {
      color: #5f61e6;
      text-decoration: underline;
    }
    .customer-breadcrumb .muted { color: #a1acb8; font-weight: 500; }
    .customer-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: .75rem;
    }
    .customer-actions a {
      color: #696cff;
      text-decoration: none;
      font-weight: 600;
      white-space: nowrap;
    }
    .customer-actions a:hover {
      color: #5f61e6;
      text-decoration: underline;
    }
    .customer-details-grid .row-item {
      display: grid;
      grid-template-columns: 170px 1fr;
      gap: .75rem;
      padding: .55rem 0;
      border-bottom: 1px solid #eceef1;
    }
    .customer-details-grid .row-item:last-child { border-bottom: none; }
    .customer-details-grid .k { color: #a1acb8; font-weight: 600; }
    .customer-details-grid .v { color: #566a7f; font-weight: 600; }
    .customer-details-grid .v a {
      color: #696cff;
      text-decoration: none;
      font-weight: 600;
    }
    .customer-details-grid .v a:hover {
      color: #5f61e6;
      text-decoration: underline;
    }
    .customer-note {
      background: #fff9db;
      border: 1px solid #ffe69c;
      border-radius: .375rem;
      padding: .85rem 1rem;
      min-height: 64px;
      color: #566a7f;
    }
    .orders-table thead th {
      background: #f5f5f9;
      color: #566a7f;
      font-weight: 600;
    }
    .orders-table tbody tr {
      cursor: pointer;
    }
    .orders-table tbody tr:hover {
      background-color: #f5f5f9;
    }
    .orders-table tbody td {
      padding-top: 0.9rem;
      padding-bottom: 0.9rem;
      vertical-align: middle;
    }
  </style>

  <div class="customer-topbar">
    <div class="customer-breadcrumb">
      <a href="{{ route('customer.list') }}">Customers</a>
      <span class="muted">/</span>
      <a href="{{ route('customer.overview', $customer->id) }}">{{ $customerName }}</a>
      <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>
    <div class="customer-actions gap-5">
      <a href="javascript:void(0);" data-bs-target="#editCustomerModal" data-bs-toggle="modal">Edit Customer</a>
      <a href="{{ route('customer.branches', $customer->id) }}">Addresses</a>
      <a href="{{ route('customer.security', $customer->id) }}">Reset Password</a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-lg-7">
              <div class="customer-details-grid">
                <div class="row-item">
                  <div class="k">Reference</div>
                  <div class="v">{{ $customer->id }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Group</div>
                  <div class="v">
                    @if($customer->customerGroup)
                      <a href="{{ route('settings.customerGroup.edit', $customer->customerGroup->id) }}">
                        {{ $customer->customerGroup->name }}
                      </a>
                    @else
                      -
                    @endif
                  </div>
                </div>
                <div class="row-item">
                  <div class="k">Contact</div>
                  <div class="v">{{ $customer->email ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Email</div>
                  <div class="v">{{ $customer->email ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Created On</div>
                  <div class="v">{{ optional($customer->created_at)->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Last Seen</div>
                  <div class="v">{{ optional($customer->last_login)->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Phone</div>
                  <div class="v">{{ $customer->phone ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Price List</div>
                  <div class="v">{{ optional($customer->priceList)->name ?? '-' }}</div>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="row g-4">
                <div class="col-6">
                  <div class="text-uppercase small fw-bold text-muted mb-2">Shipping</div>
                  @foreach($shipping as $k => $v)
                    <div class="small">{{ $v }}</div>
                  @endforeach
                </div>
                <div class="col-6">
                  <div class="text-uppercase small fw-bold text-muted mb-2">Billing</div>
                  @foreach($billing as $k => $v)
                    <div class="small">{{ $v }}</div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
          @if(($ordersCount ?? 0) > 0)
            <hr class="my-5" />
            <h5 class="mb-3">Orders</h5>
            <div class="table-responsive">
              <table class="table orders-table datatables-customer-order" data-customer-id="{{ $customer->id ?? '' }}">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Order Date</th>
                    <th>Total</th>
                    <th>Invoice</th>
                    <th>Status</th>
                  </tr>
                </thead>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  @include('_partials._modals.modal-edit-customer')
@endsection