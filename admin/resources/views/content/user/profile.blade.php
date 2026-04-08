@extends('layouts/layoutMaster')

@section('title', 'User Profile - Profile')

<!-- Vendor Styles -->
@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-profile.scss'])
@endsection


<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/app-user-view-account.js'])
@endsection

@section('content')
<!-- Header -->
@include('content.user.profile-header')
<!--/ Header -->

<div class="row">
  <div class="col-12 col-xl-8 col-lg-10">
    <div class="card mb-6">
      <h5 class="card-header">Edit profile</h5>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}" class="row g-4">
          @csrf
          <div class="col-12 col-md-6">
            <label class="form-label" for="profileName">Name</label>
            <input type="text" id="profileName" name="name" class="form-control @error('name') is-invalid @enderror"
              value="{{ old('name', auth()->user()->name) }}" placeholder="Your name" autocomplete="name" required />
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="profileEmail">Email</label>
            <input type="email" id="profileEmail" name="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email', auth()->user()->email) }}" placeholder="you@example.com" autocomplete="email" required />
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="profilePhone">Phone number</label>
            <input type="text" id="profilePhone" name="phone" class="form-control @error('phone') is-invalid @enderror"
              value="{{ old('phone', auth()->user()->phone) }}" placeholder="10–20 letters or numbers" autocomplete="tel"
              onkeypress="if (event.key.length !== 1) return true; return /^[a-zA-Z0-9]$/i.test(event.key)" />
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection