@extends('layouts/layoutMaster')

@section('title', 'User View - Pages')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/cleave-zen/cleave-zen.js','resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js',
])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/page-user-view.scss')
@endsection


@section('page-script')
@vite(['resources/assets/js/modal-edit-user.js', 'resources/assets/js/app-user-view.js'])
@endsection

@section('content')
<div class="row">
  @include('content.user.account-sidebar')

  <!-- User Content -->
  <div class="col-xl-8 col-lg-7 order-0 order-md-1">
    <!-- User Pills -->
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6 flex-wrap row-gap-2">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/account/'.$user->id) }}"><i class="icon-base ti tabler-user-check me-1_5 icon-sm"></i>Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/security/'.$user->id) }}"><i class="icon-base ti tabler-lock me-1_5 icon-sm"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-bell me-1_5 icon-sm"></i>Notifications</a>
        </li>
      </ul>
    </div>
    <!--/ User Pills -->

    <!-- Project table -->
    <div class="card mb-6">
      <!-- Notifications -->
      <div class="card-header">
        <h5 class="mb-0">Notifications</h5>
        <span class="card-subtitle">Change to notification settings, the user will get the update</span>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead class="border-top">
            <tr>
              <th class="text-nowrap">Type</th>
              <th class="text-nowrap text-center">Email</th>
              <th class="text-nowrap text-center">Browser</th>
              <th class="text-nowrap text-center">App</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-nowrap text-heading">New for you</td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck1" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck2" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck3" checked />
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-nowrap text-heading">Account activity</td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck4" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck5" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck6" checked />
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-nowrap text-heading">A new browser used to sign in</td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck7" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck8" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck9" />
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-nowrap text-heading">A new device is linked</td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck10" checked />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck11" />
                </div>
              </td>
              <td>
                <div class="form-check d-flex justify-content-center mb-0">
                  <input class="form-check-input" type="checkbox" id="defaultCheck12" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-body">
        <button type="submit" class="btn btn-primary me-3">Save changes</button>
        <button type="reset" class="btn btn-label-secondary">Discard</button>
      </div>
      <!-- /Notifications -->
    </div>
    <!-- /Project table -->
  </div>
  <!--/ User Content -->
</div>

<!-- Modals -->
@include('_partials/_modals/modal-edit-user')
<!-- /Modals -->

@endsection
