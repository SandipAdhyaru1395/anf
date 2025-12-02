<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
        <form class="add-new-user pt-0" id="addNewUserForm" onsubmit="return false" method="POST"
            action="{{ route('user.create') }}">
            @csrf
            <div class="mb-6 form-control-validation">
                <label class="form-label" for="modalAddUserName">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="modalAddUserName" placeholder="John Doe"
                    name="modalAddUserName" aria-label="John Doe" value="{{ old('modalAddUserName') }}"/>
                @error('modalAddUserName', 'addModal')
                    <span class="text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-6 form-control-validation">
                <label class="form-label" for="modalAddUserEmail">Email <span class="text-danger">*</span></label>
                <input type="text" id="modalAddUserEmail" class="form-control" placeholder="john.doe@example.com"
                    aria-label="john.doe@example.com" name="modalAddUserEmail" value="{{ old('modalAddUserEmail') }}"/>
                @error('modalAddUserEmail', 'addModal')
                    <span class="text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-6">
                <label class="form-label" for="modalAddUserPhone">Contact</label>
                <input type="text" id="modalAddUserPhone" class="form-control phone-mask"
                    placeholder="+1 (609) 988-44-11" aria-label="john.doe@example.com" name="modalAddUserPhone" value="{{ old('modalAddUserPhone') }}"/>
                @error('modalAddUserPhone', 'addModal')
                    <span class="text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-6">
                <label class="form-label" for="modalAddUserRole">Role</label>
                <select id="modalAddUserRole" name="modalAddUserRole" class="form-select select2">
                    <option value="">Select Role</option>
                    @forelse($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('modalAddUserRole') == $role->id)>{{ $role->name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <div class="mb-6">
                <label class="form-label" for="modalAddUserStatus">Select Status <span class="text-danger">*</span></label>
                <select id="modalAddUserStatus" name="modalAddUserStatus" class="form-select">
                    <option value="active" @selected(old('modalAddUserStatus') == 'active')>Active</option>
                    <option value="inactive" @selected(old('modalAddUserStatus') == 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="newPassword">New Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-merge">
                    <input class="form-control" type="password" id="newPassword" name="newPassword"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
                 @error('newPassword', 'addModal')
                    <span class="text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="confirmPassword">Confirm New Password <span class="text-danger">*</span></label> 
                <div class="input-group input-group-merge">
                    <input class="form-control" type="password" name="confirmPassword" id="confirmPassword"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
    </div>
</div>