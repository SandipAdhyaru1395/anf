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

    if (is_null($customer->is_approved)) {
      $approvalMeta = ['label' => 'Pending approval', 'class' => 'bg-label-warning'];
    } elseif ((int) ($customer->is_approved ?? 0) === 0) {
      $approvalMeta = ['label' => 'Rejected', 'class' => 'bg-label-danger'];
    } else {
      $approvalMeta = ['label' => 'Approved', 'class' => 'bg-label-success'];
    }

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

    $groups = collect($customer_groups ?? []);
    $lists = collect($price_lists ?? []);
    $overviewCustomerGroup = $customer->customerGroup
      ?? ($customer->customer_group_id ? $groups->firstWhere('id', (int) $customer->customer_group_id) : null);
    $overviewPriceList = $customer->priceList
      ?? ($customer->price_list_id ? $lists->firstWhere('id', (int) $customer->price_list_id) : null);
  @endphp

  <style>
    .customer-topbar {
      background: #fff;
      border: 1px solid #eceef1;
      border-radius: .5rem;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      margin-bottom: 1rem;
      box-shadow: 0 1px 2px rgba(67, 89, 113, 0.06);
    }
    .customer-pending-banner {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1rem 1.25rem;
      padding: 1rem 1.25rem;
      background: linear-gradient(110deg, #fffbeb 0%, #fef9c3 45%, #fef3c7 100%);
      border-bottom: 1px solid rgba(245, 158, 11, 0.35);
      border-left: 4px solid #f59e0b;
    }
    .customer-pending-banner__icon {
      flex-shrink: 0;
      width: 2.75rem;
      height: 2.75rem;
      border-radius: .5rem;
      background: rgba(245, 158, 11, 0.15);
      color: #b45309;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .customer-pending-banner__icon i {
      font-size: 1.35rem;
    }
    .customer-pending-banner__text {
      flex: 1;
      min-width: 200px;
    }
    .customer-pending-banner__title {
      font-size: .9375rem;
      font-weight: 700;
      color: #78350f;
      letter-spacing: .01em;
      margin: 0 0 .2rem;
    }
    .customer-pending-banner__hint {
      font-size: .8125rem;
      color: #92400e;
      opacity: .9;
      margin: 0;
      line-height: 1.45;
      max-width: 42rem;
    }
    .customer-pending-banner__actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: .5rem;
    }
    .customer-pending-banner__actions .btn {
      font-weight: 600;
      padding: .45rem 1rem;
      border-radius: .375rem;
    }
    .customer-topbar-main {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 1rem 1.25rem;
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
      grid-template-columns: minmax(220px, 42%) 1fr;
      gap: .75rem;
      padding: .55rem 0;
      border-bottom: 1px solid #eceef1;
      align-items: start;
    }
    .customer-details-grid .row-item:last-child { border-bottom: none; }
    .customer-details-grid .k { color: #a1acb8; font-weight: 600; }
    .customer-details-grid .k.k-long {
      font-size: 0.8125rem;
      line-height: 1.45;
      font-weight: 600;
      padding-right: .35rem;
    }
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
    .customer-details-extra {
      margin-top: 1.75rem;
      padding-top: 1.5rem;
      border-top: 1px solid #eceef1;
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
    @if(is_null($customer->is_approved))
      <div class="customer-pending-banner" role="region" aria-label="Registration pending approval">
        <div class="customer-pending-banner__icon" aria-hidden="true">
          <i class="icon-base ti tabler-clock"></i>
        </div>
        <div class="customer-pending-banner__text">
          <p class="customer-pending-banner__title">Pending approval</p>
          <p class="customer-pending-banner__hint">This customer signed up online and cannot sign in until you approve or reject their registration.</p>
        </div>
        <div class="customer-pending-banner__actions">
          <form action="{{ route('customer.approve', $customer->id) }}" method="post" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm shadow-sm">Approve</button>
          </form>
          <form action="{{ route('customer.reject', $customer->id) }}" method="post" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
          </form>
        </div>
      </div>
    @endif
    <div class="customer-topbar-main">
      <div class="customer-breadcrumb">
        <a href="{{ route('customer.list') }}">Customers</a>
        <span class="muted">/</span>
        <a href="{{ route('customer.overview', $customer->id) }}">{{ $customerName }}</a>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        @if(!is_null($customer->is_approved))
          <span class="badge {{ $approvalMeta['class'] }}">{{ $approvalMeta['label'] }}</span>
        @endif
      </div>
      <div class="customer-actions gap-5">
        <a href="javascript:void(0);" data-bs-target="#editCustomerModal" data-bs-toggle="modal">Edit Customer</a>
        <a href="{{ route('customer.branches', $customer->id) }}">Addresses</a>
        <a href="{{ route('customer.security', $customer->id) }}">Reset Password</a>
      </div>
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
                    @if($overviewCustomerGroup)
                      <a href="{{ route('settings.customerGroup.edit', $overviewCustomerGroup->id) }}">
                        {{ $overviewCustomerGroup->name }}
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
                  <div class="v">{{ \App\Helpers\Helpers::displayDateTime($customer->created_at, '-') }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Last Seen</div>
                  <div class="v">{{ \App\Helpers\Helpers::displayDateTime($customer->last_login, '-') }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Phone</div>
                  <div class="v">{{ $customer->phone ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Price List</div>
                  <div class="v">{{ optional($overviewPriceList)->name ?? '-' }}</div>
                </div>
                <div class="row-item">
                  <div class="k">Pay later</div>
                  <div class="v">
                    @if($customer->pay_later)
                      <span class="badge bg-label-success">Allowed</span>
                    @else
                      <span class="badge bg-danger">Not allowed</span>
                    @endif
                  </div>
                </div>
              </div>

              <div class="customer-details-extra">
                <div class="text-uppercase small fw-bold text-muted mb-3">Registration &amp; business</div>
                <div class="customer-details-grid">
                  <div class="row-item">
                    <div class="k">VAT number</div>
                    <div class="v">{{ $customer->vat_number ?: '-' }}</div>
                  </div>
                  <div class="row-item">
                    <div class="k">EORI number</div>
                    <div class="v">{{ $customer->eori_number ?: '-' }}</div>
                  </div>
                  <div class="row-item">
                    <div class="k k-long">Are you part of a group? i.e. A symbol group or Industry Body, The FED etc.</div>
                    <div class="v">
                      @if($customer->is_part_of_group === null || $customer->is_part_of_group === '')
                        -
                      @else
                        {{ (int) $customer->is_part_of_group === 1 ? 'Yes' : 'No' }}
                      @endif
                    </div>
                  </div>
                  <div class="row-item">
                    <div class="k">Type of business</div>
                    <div class="v">{{ $customer->business_type ?: '-' }}</div>
                  </div>
                  <div class="row-item">
                    <div class="k k-long">What is your average monthly spend excluding VAT?</div>
                    <div class="v">{{ $customer->average_monthly_spend_ex_vat ?: '-' }}</div>
                  </div>
                  <div class="row-item">
                    <div class="k k-long">How many stores do you have or how many stores do you service?</div>
                    <div class="v">
                      @if($customer->stores_serviced_count === null || $customer->stores_serviced_count === '')
                        -
                      @else
                        {{ $customer->stores_serviced_count }}
                      @endif
                    </div>
                  </div>
                  <div class="row-item">
                    <div class="k">Name</div>
                    <div class="v">{{ $customer->contact_person_name ?: '-' }}</div>
                  </div>
                  <div class="row-item">
                    <div class="k">Rep code</div>
                    <div class="v">{{ $customer->rep_code ?: '-' }}</div>
                  </div>
                  <div class="row-item">
                    <div class="k k-long">Sales rep name (if known)</div>
                    <div class="v">{{ $customer->sales_rep_name ?: '-' }}</div>
                  </div>
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