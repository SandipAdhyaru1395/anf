@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@extends('layouts/commonMaster')

@section('layoutContent')
  <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
    <div class="layout-container">

      <!-- BEGIN: Navbar-->
      @include('layouts/sections/navbar/navbar')
      <!-- END: Navbar-->

      <!-- Layout page -->
      <div class="layout-page">
        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        {{-- <x-banner /> --}}
        <!-- Content wrapper -->
        <div class="content-wrapper">
          @include('layouts/sections/menu/horizontalMenu')

          <div class="container-xxl flex-grow-1 container-p-y">

          @yield('content')

        </div>
        <!-- / Content -->
        <div class="content-backdrop fade"></div>
      </div>
      <!--/ Content wrapper -->
    </div>
    <!-- / Layout page -->
  </div>
  <!-- / Layout Container -->

  <!-- Overlay -->
  <div class="layout-overlay layout-menu-toggle"></div>
  <!-- Drag Target Area To SlideIn Menu On Small Screens -->
  <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
@endsection
