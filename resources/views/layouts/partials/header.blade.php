<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">

            {{-- LEFT --}}
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button type="button" class="button-toggle-menu nav-link">
                        <iconify-icon icon="tabler:align-left"
                            class="fs-20 align-middle text-dark topbar-button"></iconify-icon>
                    </button>
                </li>
            </ul>

            {{-- RIGHT --}}
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                {{-- NOTIFICATIONS --}}
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                        <iconify-icon icon="tabler:bell"
                            class="fs-20 text-dark align-middle topbar-button"></iconify-icon>
                        <span class="badge bg-danger rounded-circle noti-icon-badge">5</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end dropdown-xl">
                        <div class="dropdown-item noti-title">
                            <h5 class="m-0 fs-16">
                                Notification
                                <span class="float-end">
                                    <iconify-icon icon="tabler:x" class="fs-18 text-dark align-middle"></iconify-icon>
                                </span>
                            </h5>
                        </div>

                        <div class="noti-scroll" data-simplebar></div>

                        <a href="#" class="dropdown-item text-center text-dark notify-item notify-all bg-light">
                            View all
                        </a>
                    </div>
                </li>

                {{-- USER PROFILE --}}
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#">
                        {{-- UPDATED: Dynamic Avatar Image --}}
                        @if (auth()->user() && auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name ?? 'User' }}"
                                class="rounded-circle border border-light shadow-sm" width="32" height="32"
                                style="object-fit: cover;">
                        @else
                            <img src="{{ asset('images/default-avatar.png') }}" alt="Default Avatar"
                                class="rounded-circle border border-light shadow-sm" width="32" height="32"
                                style="object-fit: cover;">
                        @endif
                    </a>

                    <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">
                                Welcome {{ auth()->user()->name ?? 'Admin' }}!
                            </h6>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="dropdown-item notify-item">
                            <iconify-icon icon="tabler:user-square-rounded" class="fs-18 align-middle"></iconify-icon>
                            <span>My Account</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        {{-- LOGOUT --}}
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf

                            <button type="submit" class="dropdown-item notify-item text-danger">
                                <iconify-icon icon="tabler:logout" class="fs-18 align-middle"></iconify-icon>
                                <span>Logout</span>
                            </button>
                        </form>

                    </div>
                </li>

            </ul>

        </div>
    </div>
</div>
