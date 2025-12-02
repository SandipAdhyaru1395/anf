@extends('layouts/layoutMaster')

@section('title', 'User List')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/cleave-zen/cleave-zen.js','resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/modal-ajax-edit-user.js', 'resources/assets/js/app-user-list.js'])

  <script>
    @if ($errors->addModal->any())
          document.addEventListener("DOMContentLoaded", function () {
              let addCustomerOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasCustomerAdd'));
              addCustomerOffcanvas.show();
          });
    @endif
  

    $(document).ready(function () {
      @if ($errors->editModal->any())
            $('#editModal').modal('show');
      @endif

      $('#ajaxEditUserModal').on('show.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');

        if (id) {
          $.ajax({
            url: "{{ route('user-ajax.show') }}",
            type: 'GET',
            data: { id: id },
            success: function (response) {
              $('#ajaxEditUserForm').find('#id').val(response.id);
              $('#ajaxEditUserForm').find('#modalEditUserName').val(response.name);
              $('#ajaxEditUserForm').find('#modalEditUserEmail').val(response.email);
              $('#ajaxEditUserForm').find('#modalEditUserStatus').val(response.status).trigger('change');
              $('#ajaxEditUserForm').find('#modalEditUserPhone').val(response.phone);
              $('#ajaxEditUserForm').find('#modalEditUserRole').val(response.role_id).trigger('change');
            }
          });
        }
      });
    });
    function deleteRecord(id) {
      if (id) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
          customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {
          if(result.isConfirmed){
            window.location.href = baseUrl + 'user/delete/' + id;
          }
        });
      }
    }
  </script>
@endsection

@section('content')
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">All Users</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $all_users_count }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-users icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">InActive Users</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $inactive_users_count }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="icon-base ti tabler-user-plus icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Active Users</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $active_users_count }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="icon-base ti tabler-user-check icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Users List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Filters</h5>
      <div class="d-flex align-items-center row pt-4 gap-4 gap-md-0">
        <div class="col-md-4 user_role"></div>
        <div class="col-md-4 user_status"></div>
      </div>
    </div>
    <div class="card-datatable">
      <table class="datatables-users table">
        <thead class="border-top">
          <tr>
            <th></th>
            <th></th>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
    <!-- Offcanvas to add new user -->
    @include('_partials/_offcanvas/offcanvas-add-user')
  </div>
  <!-- Edit User Modal -->
  @include('_partials/_modals/modal-ajax-edit-user')
  <!-- / Edit User Modal -->
@endsection