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
@vite(['resources/assets/js/modal-edit-user.js', 'resources/assets/js/modal-enable-otp.js', 'resources/assets/js/app-user-view.js', 'resources/assets/js/app-user-view-security.js'])
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
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-lock me-1_5 icon-sm"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('user/view/notifications/'.$user->id) }}"><i class="icon-base ti tabler-bell me-1_5 icon-sm"></i>Notifications</a>
        </li>
      </ul>
    </div>
    <!--/ User Pills -->

    <!-- Change Password -->
    <div class="card mb-6">
      <h5 class="card-header">Change Password</h5>
      <div class="card-body">
        <form id="formChangePassword" method="POST" onsubmit="return false" action="{{ route('user.update-password') }}">
          @csrf
          <input type="hidden" name="id" value="{{ $user->id }}">
          <!-- <div class="alert alert-warning alert-dismissible" role="alert">
            <h5 class="alert-heading mb-1">Ensure that these requirements are met</h5>
            <span>Minimum 8 characters long, uppercase & symbol</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div> -->
          <div class="row gx-6">
            <div class="mb-4 col-12 col-sm-6 form-password-toggle form-control-validation">
              <label class="form-label" for="newPassword">New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="newPassword" name="newPassword" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>

            <div class="mb-4 col-12 col-sm-6 form-password-toggle form-control-validation">
              <label class="form-label" for="confirmPassword">Confirm New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" name="confirmPassword" id="confirmPassword" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>
            <div>
              <button type="submit" class="btn btn-primary me-2">Change Password</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!--/ Change Password -->

   
    <!-- Recent Devices -->
    <div class="card mb-6">
      <h5 class="card-header">Recent Devices</h5>
      <div class="table-responsive table-border-bottom-0">
        <table class="table">
          <thead>
            <tr>
              <th class="text-truncate">Browser</th>
              <th class="text-truncate">Device</th>
              <th class="text-truncate">Location</th>
              <th class="text-truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-brand-windows icon-md text-info me-4"></i> <span class="text-heading">Chrome on Windows</span></td>
              <td class="text-truncate">HP Spectre 360</td>
              <td class="text-truncate">Switzerland</td>
              <td class="text-truncate">10, July 2021 20:07</td>
            </tr>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-device-mobile icon-md text-danger me-4"></i> <span class="text-heading">Chrome on iPhone</span></td>
              <td class="text-truncate">iPhone 12x</td>
              <td class="text-truncate">Australia</td>
              <td class="text-truncate">13, July 2021 10:10</td>
            </tr>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-brand-android icon-md text-success me-4"></i> <span class="text-heading">Chrome on Android</span></td>
              <td class="text-truncate">Oneplus 9 Pro</td>
              <td class="text-truncate">Dubai</td>
              <td class="text-truncate">14, July 2021 15:15</td>
            </tr>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-brand-apple icon-md me-4"></i> <span class="text-heading">Chrome on MacOS</span></td>
              <td class="text-truncate">Apple iMac</td>
              <td class="text-truncate">India</td>
              <td class="text-truncate">16, July 2021 16:17</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!--/ Recent Devices -->
  </div>
  <!--/ User Content -->
</div>

<!-- Modals -->
@include('_partials/_modals/modal-edit-user')
<!-- /Modals -->

@endsection
