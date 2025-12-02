<!-- User Sidebar -->
<div class="col-xl-4 col-lg-5 order-1 order-md-0">
  <!-- User Card -->
  <div class="card mb-6">
    <div class="card-body pt-12">
      <div class="user-avatar-section">
        <div class=" d-flex align-items-center flex-column">
          <img class="img-fluid rounded mb-4" src="{{ $user->image ?? asset('assets/img/avatars/1.png') }}" height="120" width="120"
            alt="User avatar" />
          <div class="user-info text-center">
            <h5>{{ $user->name }}</h5>
            @if($user->role)
              <span class="badge bg-label-secondary">{{ $user?->role?->name }}</span>
            @endif
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4">
        <div class="d-flex align-items-center me-5 gap-4">
          <div class="avatar">
            <div class="avatar-initial bg-label-primary rounded">
              <i class="icon-base ti tabler-checkbox icon-lg"></i>
            </div>
          </div>
          <div>
            <h5 class="mb-0">1.23k</h5>
            <span>Task Done</span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-4">
          <div class="avatar">
            <div class="avatar-initial bg-label-primary rounded">
              <i class="icon-base ti tabler-briefcase icon-lg"></i>
            </div>
          </div>
          <div>
            <h5 class="mb-0">568</h5>
            <span>Project Done</span>
          </div>
        </div>
      </div>
      <h5 class="pb-4 border-bottom mb-4">Details</h5>
      <div class="info-container">
        <ul class="list-unstyled mb-6">
          <li class="mb-2">
            <span class="h6">Email:</span>
            <span>{{ $user->email }}</span>
          </li>
          <li class="mb-2">
            <span class="h6">Status:</span>
            <span>{{ ucfirst($user->status) }}</span>
          </li>
          @if($user->role)
            <li class="mb-2">
              <span class="h6">Role:</span>
              <span>{{ $user->role->name }}</span>
            </li>
          @endif
        </ul>
        <div class="d-flex justify-content-center">
          <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
          <!-- <a href="javascript:;" class="btn btn-label-danger suspend-user">Suspend</a> -->
        </div>
      </div>
    </div>
  </div>
  <!-- /User Card -->
</div>
<!--/ User Sidebar -->