<!-- Edit User Modal -->
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">Edit User Information</h4>
          <p>Updating user details will receive a privacy audit.</p>
        </div>
        <form id="editUserForm" class="row g-6" onsubmit="return false" method="POST" action="{{ route('user.update') }}">
          @csrf
          <input type="hidden" name="id" id="id" value="{{ $user->id }}">
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="modalEditUserName">Name <span class="text-danger">*</span></label>
            <input type="text" id="modalEditUserName" name="modalEditUserName" class="form-control" placeholder="John" value="{{ $user->name }}" />
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="modalEditUserEmail">Email <span class="text-danger">*</span></label>
            <input type="text" id="modalEditUserEmail" name="modalEditUserEmail" class="form-control" placeholder="example@email.com" value="{{ $user->email }}" />
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="modalEditUserStatus">Status <span class="text-danger">*</span></label>
            <select id="modalEditUserStatus" name="modalEditUserStatus" class="select2 form-select" aria-label="Default select example">
              <option value="active" @selected($user->status=='active')>Active</option>
              <option value="inactive" @selected($user->status=='inactive')>Inactive</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserPhone">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text">IN (+91)</span>
              <input type="text" id="modalEditUserPhone" name="modalEditUserPhone" class="form-control phone-number-mask" placeholder="202 555 0111" value="{{ $user->phone }}" />
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserRole">Role</label>
            <select id="modalEditUserRole" name="modalEditUserRole" class="select2 form-select" data-allow-clear="true">
              <option value="">Select</option>
              @forelse($roles as $role)
                <option value="{{$role->id}}" @selected($user->role_id == $role->id)>{{$role->name}}</option>
              @empty
                <option>No roles found</option>
              @endforelse
            </select>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->