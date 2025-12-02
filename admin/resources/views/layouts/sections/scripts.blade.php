<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/js/bootstrap.js',
    ])
@yield('vendor-script')

@vite(['resources/assets/vendor/js/menu.js','resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js'])

<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
{{-- @vite(['resources/js/app.js']) --}}
<!-- END: app JS-->
