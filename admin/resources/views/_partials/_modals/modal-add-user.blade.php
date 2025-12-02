<!-- Add Role Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="role-title">Add New User</h4>
                </div>
                <!-- Add role form -->
                <form id="addUserForm" class="row g-6" onsubmit="return false" method="POST"
                    action="{{ route('user.create') }}">
                    @csrf
                    <div class="col-12 col-md-6 form-control-validation">
                        <label class="form-label" for="modalAddUserName">Name</label>
                        <input type="text" id="modalAddUserName" name="modalAddUserName" class="form-control"
                            placeholder="John" />
                    </div>
                    <div class="col-12 col-md-6 form-control-validation">
                        <label class="form-label" for="modalAddUserEmail">Email</label>
                        <input type="text" id="modalAddUserEmail" name="modalAddUserEmail" class="form-control"
                            placeholder="example@email.com" />
                    </div>
                    <div class="col-12 col-md-6 form-control-validation">
                        <label class="form-label" for="modalAddUserStatus">Status</label>
                        <select id="modalAddUserStatus" name="modalAddUserStatus" class="select2 form-select"
                            aria-label="Default select example">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="modalAddUserPhone">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">IN (+91)</span>
                            <input type="text" id="modalAddUserPhone" name="modalAddUserPhone"
                                class="form-control phone-number-mask" placeholder="202 555 0111" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="modalAddUserRole">Role</label>
                        <select id="modalAddUserRole" name="modalAddUserRole" class="select2 form-select"
                            data-allow-clear="true">
                            <option value="">Select</option>
                            @forelse($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @empty
                                <option>No roles found</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-12 col-md-6 form-password-toggle form-control-validation">
                        <label class="form-label" for="newPassword">New Password</label>
                        <div class="input-group input-group-merge">
                            <input class="form-control" type="password" id="newPassword" name="newPassword"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i
                                    class="icon-base ti tabler-eye-off"></i></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 form-password-toggle form-control-validation">
                        <label class="form-label" for="confirmPassword">Confirm New Password</label>
                        <div class="input-group input-group-merge">
                            <input class="form-control" type="password" name="confirmPassword" id="confirmPassword"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i
                                    class="icon-base ti tabler-eye-off"></i></span>
                        </div>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">Cancel</button>
                    </div>
                </form>
                <!--/ Add role form -->
            </div>
        </div>
    </div>
</div>
<!--/ Add Role Modal -->
