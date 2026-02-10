<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">

            {{-- LEFT --}}
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button type="button" class="button-toggle-menu nav-link">
                        <iconify-icon icon="tabler:align-left" class="fs-20 align-middle text-dark topbar-button"></iconify-icon>
                    </button>
                </li>
            </ul>

            {{-- RIGHT --}}
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

                 <!-- <li class="dropdown notification-list topbar-dropdown">
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
                </li> -->

                {{-- NOTIFICATIONS --}}
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <iconify-icon icon="tabler:bell" class="fs-20 text-dark align-middle topbar-button"></iconify-icon>
                        {{-- Counter Badge (Initially Hidden) --}}
                        <span id="notif-badge" class="badge bg-danger rounded-circle noti-icon-badge d-none">0</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end dropdown-xl">
                        <div class="dropdown-item noti-title">
                            <h5 class="m-0 fs-16">
                                Notifications
                                <span class="float-end">
                                    <form action="{{ route('warehouse.notifications.readAll') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-dark p-0" title="Mark all as read">
                                            <iconify-icon icon="tabler:checks" class="fs-18 align-middle"></iconify-icon>
                                        </button>
                                    </form>
                                </span>
                            </h5>
                        </div>

                        {{-- Dynamic List --}}
                        <div class="noti-scroll" id="topbar-notif-list" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                        </div>

                        <a href="{{ route('warehouse.notifications.index') }}" class="dropdown-item text-center text-primary notify-item notify-all border-top">
                            View All <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </li>

                {{-- USER PROFILE (Unchanged) --}}
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#">
                        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle border" width="45" height="45" style="object-fit: cover;" alt="User Avatar">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome {{ auth()->user()->name }}!</h6>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item notify-item">
                            <iconify-icon icon="tabler:user-square-rounded" class="fs-18 align-middle"></iconify-icon>
                            <span>My Account</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchNotifications();

        // Refresh every 60 seconds (optional)
        // setInterval(fetchNotifications, 60000); 
    });

    function fetchNotifications() {
        fetch("{{ route('warehouse.notifications.fetch') }}")
            .then(response => response.json())
            .then(data => {
                // Update Badge
                const badge = document.getElementById('notif-badge');
                if (data.count > 0) {
                    badge.innerText = data.count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                // Update List
                const list = document.getElementById('topbar-notif-list');
                list.innerHTML = '';

                if (data.notifications.length === 0) {
                    list.innerHTML = '<div class="text-center py-4 text-muted small">No new notifications</div>';
                    return;
                }

                data.notifications.forEach(notif => {
                    // Icon based on Type
                    let iconClass = 'bg-primary';
                    let icon = 'tabler:info-circle';

                    if (notif.type === 'success') { iconClass = 'bg-success'; icon = 'tabler:check'; }
                    else if (notif.type === 'danger') { iconClass = 'bg-danger'; icon = 'tabler:alert-triangle'; }
                    else if (notif.type === 'warning') { iconClass = 'bg-warning'; icon = 'tabler:bell'; }

                    const bgClass = notif.read ? '' : 'bg-light';

                    const item = `
                        <a href="${notif.url}" class="dropdown-item notify-item ${bgClass}" onclick="markAsRead(${notif.id})">
                            <div class="d-flex align-items-center">
                                <div class="notify-icon ${iconClass} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                                    <iconify-icon icon="${icon}"></iconify-icon>
                                </div>
                                <div class="ms-2" style="overflow: hidden;">
                                    <p class="notify-details mb-0 fw-bold text-truncate">${notif.title}</p>
                                    <p class="text-muted mb-0 small text-truncate">${notif.message}</p>
                                    <small class="text-muted" style="font-size: 10px;">${notif.time}</small>
                                </div>
                            </div>
                        </a>
                    `;
                    list.innerHTML += item;
                });
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function markAsRead(id) {
        fetch(`/warehouse/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
    }
</script>