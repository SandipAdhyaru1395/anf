@extends('layouts/layoutMaster')

@section('title', 'Monthly Sales Report')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/monthly-sales-report.js'])
@endsection

@section('page-style')
<style>
  #monthly-sales-calendar table {
    table-layout: fixed;
  }
  #monthly-sales-calendar td {
    width: 25%;
    vertical-align: top;
    min-height: 250px;
    padding: 0.5rem;
  }
  #monthly-sales-calendar .small {
    font-size: 0.8rem !important;
  }
  #monthly-sales-calendar .table-sm {
    font-size: 0.8rem !important;
  }
  #monthly-sales-calendar .table-sm td {
    padding: 0.1rem 0.2rem;
    border: none;
    font-size: 0.8rem !important;
  }
  #monthly-sales-calendar .table-sm td strong {
    font-size: 0.8rem !important;
  }
</style>
@endsection

@section('content')
<div style="background: var(--bs-body-bg);"
  class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-1">Monthly Sales Report</h4>
    <p class="text-muted mb-0 small">You can change the year by clicking the >> (next) or << (previous)</p>
  </div>
  <div class="d-flex align-content-center flex-wrap gap-4">
    <div class="d-flex gap-4">
      <a href="{{ route('report.index') }}" class="btn btn-secondary">Back To Reports</a>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <!-- Year Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <button type="button" id="prev-year" class="btn btn-label-secondary">
        <i class="icon-base ti tabler-chevron-left"></i> Previous
      </button>
      <h3 class="mb-0" id="current-year">Loading...</h3>
      <button type="button" id="next-year" class="btn btn-label-secondary">
        Next <i class="icon-base ti tabler-chevron-right"></i>
      </button>
    </div>

    <!-- Calendar -->
    <div id="monthly-sales-calendar" class="table-responsive">
      <table class="table table-bordered">
        <tbody id="calendar-body">
          <tr>
            <td colspan="4" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

