<html lang="{{ session()->get('locale') ?? app()->getLocale() }}"

  class="@if(isset($sidebarMenuData)) layout-navbar-fixed layout-compact layout-menu-fixed @else customizer-hide layout-menu-fixed @endif"
  dir="ltr" data-skin="default" data-assets-path="{{ asset('/') }}"
  data-currency-symbol="{{ $setting['currency_symbol'] ?? '' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel" data-template="@if(isset($sidebarMenuData)) vertical-menu-template @else blank-menu-template @endif"
  data-bs-theme="light">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>
    @yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}
    {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
  </title>
  <meta name="description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords"
    content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}" />
  <meta property="og:title" content="{{ config('variables.ogTitle') ? config('variables.ogTitle') : '' }}" />
  <meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
  <meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
  <meta property="og:description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta property="og:site_name"
    content="{{ config('variables.creatorName') ? config('variables.creatorName') : '' }}" />
  <meta name="robots" content="noindex, nofollow" />
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ isset($setting['company_logo']) ? asset('storage/'.$setting['company_logo']) : '' }}" />

  <!-- Include Styles -->
  @include('layouts/sections/styles')

  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes')
  <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">

  <style>
    :root {
      --bs-primary: #134570;
      --logo-color: #134570;
      --sidebar-font-color: #444050;
      --table-odd-row-color: #44405021;
      --table-odd-row-font-color: #000;
      --table-even-row-color: #fff;
      --table-even-row-font-color: #000;
      --table-odd-row-button-color: #000;
      --table-even-row-button-color: #000;
      --table-odd-row-button-hover-color: #fff;
      --table-even-row-button-hover-color: #134570;
      --table-odd-row-button-hover-background-color: #134570;
      --table-even-row-button-hover-font-color: #fff;
    }

    .btn-primary,
    .menu-item.active a {
      background: var(--logo-color) !important;
    }

    /* Odd rows (excluding empty row) */
    table.dataTable tbody tr:nth-child(odd):not(:has(td.dt-empty)) {
      background-color: var(--table-odd-row-color) !important;
    }

    table.dataTable tbody tr:nth-child(odd):not(:has(td.dt-empty)) td {
      color: var(--table-odd-row-font-color) !important;
    }

    table.dataTable tbody tr:nth-child(odd):not(:has(td.dt-empty)) td.dt-select input {
      border-color: var(--table-odd-row-font-color) !important;
    }

    table.dataTable tbody tr:nth-child(even):not(:has(td.dt-empty)) td.dt-select input {
      border-color: var(--table-even-row-font-color) !important;
    }

    table.dataTable tbody tr:nth-child(odd):not(:has(td.dt-empty)) td:last-child button {
      color: var(--table-odd-row-button-color) !important;
    }

    table.dataTable tbody tr:nth-child(even):not(:has(td.dt-empty)) td:last-child button {
      color: var(--table-even-row-button-color) !important;
    }

    table.dataTable tbody tr:nth-child(odd):not(:has(td.dt-empty)) td:last-child button:hover {
      background-color: var(--table-odd-row-button-hover-background-color) !important;
      /* border : none !important; */
      color: var(--table-odd-row-button-hover-color) !important;
    }

    table.dataTable tbody tr:nth-child(even):not(:has(td.dt-empty)) td:last-child button:hover {
      background-color: var(--table-even-row-button-hover-color) !important;
      color: var(--table-even-row-button-hover-font-color) !important;
    }

    

    /* Even rows */
    table.dataTable tbody tr:nth-child(even) {
      background-color: var(--table-even-row-color) !important;
    }

    table.dataTable tbody tr:nth-child(even) td {
      color: var(--table-even-row-font-color) !important;
    }

    /* Prevent "selected" from changing colors (keep zebra striping) */
    table.dataTable tbody tr.selected,
    table.dataTable tbody tr.selected>td {
      background-color: inherit !important;
      /* color: inherit !important; */
    }


    .badge {
      font-size: 12px;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .badge.bg-label-success {
      background-color: #28a745 !important;
      color: #fff !important;
    }

    .badge.bg-label-danger {
      background-color: #dc3545 !important;
      /* Bootstrap danger red */
      color: #fff !important;
    }

    .table .avatar{
      max-height:30px !important;
      max-width:30px !important;
    }

    .table td{
      font-size:14px;
      padding: 5px 20px;
      max-height:10px;
    }

    .card-datatable .pagination .active button{
      background-color: var(--logo-color);
      color: #fff;
    }

    table.dataTable tbody tr:nth-child(even) a {
      color: var(--table-even-row-font-color);
    }

    table.dataTable tbody tr:nth-child(odd) a {
      color: var(--table-odd-row-font-color);
    }

    table.dataTable tbody tr:nth-child(odd):not(:has(td.dt-empty)) button:hover {
      background-color: var(--table-odd-row-button-hover-background-color) !important;
      /* border : none !important; */
      color: var(--table-odd-row-button-hover-color) !important;
    }

    table.dataTable tbody tr:nth-child(even):not(:has(td.dt-empty)) button:hover {
      /* background-color: var(--table-even-row-button-hover-color); */
      color: var(--table-even-row-button-hover-font-color);
    }

    table.dataTable tbody tr:nth-child(even) .dropdown-menu a {
      color: var(--table-even-row-font-color);
    }

    table.dataTable tbody tr:nth-child(odd) .dropdown-menu a {
      color: var(--table-even-row-font-color);
    }

    .select2-container--default .select2-results__option[aria-selected=true]{
      background-color: var(--logo-color);
      color: #fff;
    }

    .dt-select input[type="checkbox"]{
      border-color: #000;
    }

    .form-check-input{
      border: 1px solid var(--bs-secondary-color);
    }

    .switch .switch-toggle-slider .icon-base {
      inset-block-start: 1.1px;
    }
  </style>
</head>

<body>
  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->



  <script src="{{ asset('js/jquery.min.js') }}"></script>

  <!-- Include Scripts -->
  @include('layouts/sections/scripts')


  <script src="{{ asset('js/toastr.min.js') }}"></script>
  {!! Toastr::message() !!}

</body>

</html>