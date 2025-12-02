@extends('layouts/layoutMaster')

@section('title', 'Roles')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])

  <!-- @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss']) -->
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/cleave-zen/cleave-zen.js','resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-access-roles.js', 'resources/assets/js/modal-add-user.js', 'resources/assets/js/modal-ajax-edit-user.js', 'resources/assets/js/modal-edit-role.js', 'resources/assets/js/modal-add-role.js'])
  <script>
    $(document).ready(function () {

      $('#editRoleModal').on('show.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');

        $.ajax({
          url: "{{ route('role.show') }}",
          type: 'GET',
          data: { id: id },
          success: function (response) {
            $('#editRoleForm').find('input[type="checkbox"]').prop('checked', false);
            $('#editRoleForm').find('#role_id').val(response.id);
            $('#editRoleForm').find('#modalRoleName').val(response.role_name);

            if (response.menus) {

              Object.keys(response.menus).forEach(function (key) {
                Object.keys(response.menus[key]).forEach(function (action) {
                  $('#editRoleForm').find('#edit_' + key + '_' + action).prop('checked', true);
                });
              });
            }
          }
        });
      });

      $('#addRoleModal').on('show.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');

        if (id) {
          $.ajax({
            url: "{{ route('role.show') }}",
            type: 'GET',
            data: { id: id },
            success: function (response) {
              if (response.menus) {
                Object.keys(response.menus).forEach(function (key) {
                  Object.keys(response.menus[key]).forEach(function (action) {
                    $('#addRoleForm').find('#' + key + '_' + action).prop('checked', true);
                  });
                });
              }
            }
          });
        }
      });

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
  <h4 class="mb-1">Roles List</h4>

  <p class="mb-6">
    A role provided access to predefined menus and features so that depending on <br />
    assigned role an administrator can have access to what user needs.
  </p>
  <!-- Role cards -->
  <div class="row g-6">
    @forelse($roles as $role)
      @if($role->id != 1)
        <div class="col-xl-4 col-lg-6 col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-normal mb-0 text-body">Total {{ $role->users()->count() }} users</h6>
                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                  @if((isset($role->users[0])))
                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                      title="{{ $role->users[0]->name }}" class="avatar pull-up">
                      <img class="rounded-circle" src="{{ $role->users[0]->image ?? asset('assets/img/avatars/4.png') }}"
                        alt="Avatar" />
                    </li>
                  @endif
                  @if((isset($role->users[1])))
                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                      title="{{ $role->users[1]->name }}" class="avatar pull-up">
                      <img class="rounded-circle" src="{{ $role->users[1]->image ?? asset('assets/img/avatars/1.png') }}"
                        alt="Avatar" />
                    </li>
                  @endif
                  @if((isset($role->users[2])))
                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                      title="{{ $role->users[2]->name }}" class="avatar pull-up">
                      <img class="rounded-circle" src="{{ $role->users[2]->image ?? asset('assets/img/avatars/2.png') }}"
                        alt="Avatar" />
                    </li>
                  @endif
                  @if((isset($role->users[3])))
                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                      title="{{ $role->users[3]->name }}" class="avatar pull-up">
                      <img class="rounded-circle"
                        src="{{ asset('assets/img/avatars/3.png') ?? asset('assets/img/avatars/3.png') }}" alt="Avatar" />
                    </li>
                  @endif
                  @if($role->users()->count() > 4)
                    <li class="avatar">
                      <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="{{ $role->users()->count() - 4 }} more">+{{ $role->users()->count() - 4 }}</span>
                    </li>
                  @endif
                </ul>
              </div>
              <div class="d-flex justify-content-between align-items-end">
                <div class="role-heading">
                  <h5 class="mb-1">{{ $role->name }}</h5>
                  <a href="javascript:;" data-id="{{ $role->id }}" data-bs-toggle="modal" data-bs-target="#editRoleModal"
                    class="role-edit-modal"><span>Edit Role</span></a>
                </div>
                <a href="javascript:void(0);" data-id="{{ $role->id }}" data-bs-toggle="modal"
                  data-bs-target="#addRoleModal"><i class="icon-base ti tabler-copy icon-md text-heading"></i></a>
              </div>
            </div>
          </div>
        </div>
      @endif
    @empty

    @endforelse

    <div class="col-xl-4 col-lg-6 col-md-6">
      <div class="card h-100">
        <div class="row h-100">
          <div class="col-sm-5">
            <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4">
              <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}" class="img-fluid" alt="Image"
                width="83" />
            </div>
          </div>
          <div class="col-sm-7">
            <div class="card-body text-sm-end text-center ps-sm-0">
              <button data-bs-target="#addRoleModal" data-bs-toggle="modal"
                class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">Add New Role</button>
              <p class="mb-0">
                Add new role, <br />
                if it doesn't exist.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12">
      <h4 class="mt-6 mb-1">Total users with their roles</h4>
      <p class="mb-0">Find all of your company’s administrator accounts and their associate roles.</p>
    </div>
    <div class="col-12">
      <!-- Role Table -->
      <div class="card">
        <div class="card-datatable">
          <table class="datatables-users table border-top">
            <thead>
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
      </div>
      <!--/ Role Table -->
    </div>
  </div>
  <!--/ Role cards -->

  <!-- Add Role Modal -->
  @include('_partials/_modals/modal-add-role')
  <!-- / Add Role Modal -->
  <!-- Edit Role Modal -->
  @include('_partials/_modals/modal-edit-role')
  <!-- / Edit Role Modal -->
  <!-- Add User Modal -->
  @include('_partials/_modals/modal-add-user')
  <!-- / Add User Modal -->
  <!-- Edit User Modal -->
  @include('_partials/_modals/modal-ajax-edit-user')
  <!-- / Edit User Modal -->
@endsection