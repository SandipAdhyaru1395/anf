@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@extends('layouts/commonMaster')

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar @if(!isset($sidebarMenuData)) layout-without-menu @endif">
    <div class="layout-container">
     
      @if(isset($sidebarMenuData))
        @include('layouts/sections/menu/verticalMenu')
      @endif
      <!-- Layout page -->
      <div class="layout-page">

        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        {{-- <x-banner /> --}}

        <!-- BEGIN: Navbar-->
         @if(isset($sidebarMenuData))
          @include('layouts/sections/navbar/navbar')
        @endif
        <!-- END: Navbar-->

        <!-- Content wrapper -->
        <div class="content-wrapper">

          <!-- Content -->
         
          <div class="container-xxl flex-grow-1 container-p-y">

          @yield('content')

        </div>
        <!-- / Content -->
        <!-- / Footer -->
        <div class="content-backdrop fade"></div>
      </div>
      <!--/ Content wrapper -->
    </div>
    <!-- / Layout page -->
  </div>

  <!-- Overlay -->
  <div class="layout-overlay layout-menu-toggle"></div>
  <!-- Drag Target Area To SlideIn Menu On Small Screens -->
  <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
@endsection
