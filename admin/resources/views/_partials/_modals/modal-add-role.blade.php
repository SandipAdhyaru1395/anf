<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="role-title">Add New Role</h4>
                    <p class="text-body-secondary">Set role permissions</p>
                </div>
                <!-- Add role form -->
                <form id="addRoleForm" class="row g-3" onsubmit="return false" method="POST" action="{{ route('role.store') }}">
                    @csrf
                    <div class="col-12 form-control-validation mb-3">
                        <label class="form-label" for="modalRoleName">Role Name</label>
                        <input type="text" id="modalRoleName" name="modalRoleName" class="form-control"
                            placeholder="Enter a role name" tabindex="-1" />
                    </div>
                    <div class="col-12">
                        <h5 class="mb-6">Role Permissions</h5>
                        <!-- Permission table -->
                        <div class="table-responsive">
                            <table class="table table-flush-spacing">
                                <tbody>

                                    <tr>
                                        <td class="text-nowrap fw-medium">
                                            Administrator Access
                                            <i class="icon-base ti tabler-info-circle icon-xs" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Allows a full access to the system"></i>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" id="selectAll" />
                                                    <label class="form-check-label" for="selectAll"> Select All </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @forelse($menuData as $menu)
                                        @if (!empty($menu['children']))
                                            <tr>
                                                <td colspan="2" class="text-nowrap fw-medium text-heading">
                                                    {{ $menu['name'] }}</td>
                                            </tr>
                                            @foreach ($menu['children'] as $child)
                                                @if (!empty($child['children']))
                                                    <tr>
                                                        <td colspan="2" style="padding-left: 30px;"
                                                            class="text-nowrap fw-medium text-heading">
                                                            {{ $child['name'] }}</td>
                                                    </tr>
                                                    @foreach ($child['children'] as $subChild)
                                                        <tr>
                                                            <td style="padding-left:60px;"
                                                                class="text-nowrap fw-medium text-heading">
                                                                {{ $subChild['name'] }}</td>
                                                            <td>
                                                                <div class="d-flex justify-content-end">
                                                                    <div class="form-check mb-0 me-4 me-lg-12">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="{{ $subChild['slug']}}[read]" id="{{ $subChild['slug']}}_read" />
                                                                        <label class="form-check-label"
                                                                            for="{{ $subChild['slug']}}_read"> Read </label>
                                                                    </div>
                                                                    <div class="form-check mb-0 me-4 me-lg-12">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="{{ $subChild['slug']}}[write]" id="{{ $subChild['slug']}}_write" />
                                                                        <label class="form-check-label"
                                                                            for="{{ $subChild['slug']}}_write"> Write </label>
                                                                    </div>
                                                                    <div class="form-check mb-0">
                                                                        <input class="form-check-input" type="checkbox"
                                                                           name="{{ $subChild['slug']}}[create]" id="{{ $subChild['slug']}}_create" />
                                                                        <label class="form-check-label"
                                                                            for="{{ $subChild['slug']}}_create"> Create </label>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td style="padding-left:30px;"
                                                            class="text-nowrap fw-medium text-heading">
                                                            {{ $child['name'] }}</td>
                                                        
                                                        <td>
                                                            <div class="d-flex justify-content-end">
                                                                <div class="form-check mb-0 me-4 me-lg-12">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="{{ $child['slug']}}[read]" id="{{ $child['slug']}}_read" />
                                                                    <label class="form-check-label"
                                                                        for="{{ $child['slug']}}_read">
                                                                        Read </label>
                                                                </div>
                                                                <div class="form-check mb-0 me-4 me-lg-12">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="{{ $child['slug']}}[write]" id="{{ $child['slug']}}_write" />
                                                                    <label class="form-check-label"
                                                                        for="{{ $child['slug']}}_write">
                                                                        Write </label>
                                                                </div>
                                                                <div class="form-check mb-0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                       name="{{ $child['slug']}}[create]" id="{{ $child['slug']}}_create" />
                                                                    <label class="form-check-label"
                                                                        for="{{ $child['slug']}}_create">
                                                                        Create </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td style="padding-left:60px;"
                                                    class="text-nowrap fw-medium text-heading">
                                                    {{ $menu['name'] }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <div class="form-check mb-0 me-4 me-lg-12">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="{{ $menu['slug']}}[read]" id="{{ $menu['slug']}}_read" />
                                                            <label class="form-check-label" for="{{ $menu['slug']}}_read">
                                                                Read </label>
                                                        </div>
                                                        <div class="form-check mb-0 me-4 me-lg-12">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="{{ $menu['slug']}}[write]" id="{{ $menu['slug']}}_write" />
                                                            <label class="form-check-label" for="{{ $menu['slug']}}_write">
                                                                Write </label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                               name="{{ $menu['slug']}}[create]" id="{{ $menu['slug']}}_create" />
                                                            <label class="form-check-label"
                                                                for="{{ $menu['slug']}}_create">
                                                                Create </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Permission table -->
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-4 me-1">Submit</button>
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
