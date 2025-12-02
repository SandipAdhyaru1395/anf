<div class="row">
    <div class="col-12">
        <div class="card mb-6">
            <div class="user-profile-header-banner">
                <img src="{{ asset('assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top" />
            </div>
            <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
                <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                    <img src="{{ auth()->user()->image ?? asset('assets/img/avatars/1.png') }}" alt="user image"
                        class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" />
                </div>
                <div class="flex-grow-1 mt-3 mt-lg-5">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                        <div class="user-profile-info">
                            <h4 class="mb-2 mt-lg-6">{{ auth()->user()->name }}</h4>
                            <ul
                                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                @if (auth()->user()->role)
                                    @php
                                        $icon = [
                                            'Super Admin' => 'tabler-crown',
                                            'Manager' => 'tabler-chart-pie',
                                            'Administrator' => 'tabler-users',
                                            'User' => 'tabler-user',
                                        ];
                                    @endphp
                                    <li class="list-inline-item d-flex gap-2 align-items-center"><i
                                            class="icon-base ti {{ $icon[auth()->user()->role->name] ?? '' }} icon-lg"></i><span
                                            class="fw-medium">{{ auth()->user()->role->name }}</span></li>
                                @endif
                                <li class="list-inline-item d-flex gap-2 align-items-center"><i
                                        class="icon-base ti tabler-map-pin  icon-lg"></i><span class="fw-medium">Vatican
                                        City</span></li>
                                <li class="list-inline-item d-flex gap-2 align-items-center"><i
                                        class="icon-base ti tabler-calendar  icon-lg"></i><span class="fw-medium">
                                        Joined April 2021</span></li>
                            </ul>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-primary mb-1"> <i
                                class="icon-base ti tabler-user-check icon-xs me-2"></i>Connected </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
