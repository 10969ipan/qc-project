@php
    // Roles that can VIEW all plants (for reports/laporan)
    $canViewAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift']);

    // Roles that can INPUT in all plants
    $canInputAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc']);
@endphp

<nav class="navbar topbar mb-4 static-top shadow p-0">
    <!-- Top Row: Branding and User Profile -->
    <div class="top-brand-row">
        <div class="d-flex align-items-center">
            <a class="sidebar-brand d-flex align-items-center text-decoration-none" href="/">
                <div class="sidebar-brand-icon rotate-n-15 mr-2">
                    <i class="fas fa-laugh-wink text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <div class="sidebar-brand-text font-weight-bold text-dark h5 mb-0">QC APPS
                    @if(auth()->check() && auth()->user()->plant)
                        <span class="badge badge-primary ml-2"
                            style="font-size: 0.65rem;">{{ strtoupper(auth()->user()->plant->name) }}</span>
                    @endif
                </div>
            </a>
        </div>

        <ul class="navbar-nav ml-auto flex-row align-items-center">
            <!-- Notifications -->
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle text-gray-600" href="#" id="alertsDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <span class="badge badge-danger badge-counter d-none" id="notification-badge">0</span>
                </a>
                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                    aria-labelledby="alertsDropdown">
                    <h6 class="dropdown-header">Notifications</h6>
                    <div id="notification-list">
                        <div class="text-center p-3 small text-muted">Loading...</div>
                    </div>
                    <div class="d-flex justify-content-between border-top">
                        <a class="dropdown-item text-center small text-gray-500 flex-grow-1" href="#"
                            id="mark-all-read">
                            <i class="fas fa-check-double mr-1"></i> Tandai Dibaca
                        </a>
                    </div>
                </div>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- User Info -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                    data-toggle="dropdown">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name ?? 'User' }}</span>
                    <img class="img-profile rounded-circle" style="width: 30px; height: 30px;"
                        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/img/undraw_profile.svg') }}">
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                    <a class="dropdown-item btn-logout" href="#">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>

    <!-- Bottom Row: Navigation Menu -->
    <div class="nav-menu-row">
        <ul class="main-nav">
            <li class="{{ Request::is('/') ? 'active' : '' }}">
                <a href="/"><i class="fas fa-tachometer-alt mr-1"></i> DASHBOARD</a>
            </li>

            <!-- Quality Control -->
            @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'inspector', 'karu_qc'])))
                <li>
                    <a href="#"><i class="fas fa-search mr-1"></i> QC <i class="fas fa-chevron-down ml-1 small"></i></a>
                    <ul class="dropdown-menu">
                        <!-- Plant Jakarta QC -->
                        @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                            <li>
                                <a href="#" class="dropdown-item">PLANT JAKARTA <i
                                        class="fas fa-chevron-right submenu-arrow"></i></a>
                                <ul class="dropdown-menu sub-menu">
                                    <a class="dropdown-item font-weight-bold bg-light" href="#">MASTER DATA</a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.items.index', ['plant' => 'jakarta']) }}">Data Item</a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.categories.index', ['plant' => 'jakarta']) }}">Kategori</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item font-weight-bold bg-light" href="#">CHECKSHEET</a>
                                    <a class="dropdown-item"
                                        href="{{ route('checksheet.sub_assy', ['plant' => 'jakarta']) }}">Sub Assy</a>
                                    <a class="dropdown-item"
                                        href="{{ route('in_process.create', ['plant' => 'jakarta']) }}">Inprocess</a>
                                </ul>
                            </li>
                        @endif

                        <!-- Plant Karawang QC -->
                        @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                            <li>
                                <a href="#" class="dropdown-item">PLANT KARAWANG <i
                                        class="fas fa-chevron-right submenu-arrow"></i></a>
                                <ul class="dropdown-menu sub-menu">
                                    <a class="dropdown-item font-weight-bold bg-light" href="#">MASTER DATA</a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.items.index', ['plant' => 'karawang']) }}">Data Item</a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.categories.index', ['plant' => 'karawang']) }}">Kategori</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item font-weight-bold bg-light" href="#">CHECKSHEET</a>
                                    <a class="dropdown-item"
                                        href="{{ route('checksheet.sub_assy', ['plant' => 'karawang']) }}">Sub Assy</a>
                                    <a class="dropdown-item"
                                        href="{{ route('in_process.create', ['plant' => 'karawang']) }}">Inprocess</a>
                                    <a class="dropdown-item"
                                        href="{{ route('cross_cut.create', ['plant' => 'karawang']) }}">Cross Cut</a>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Quality Assurance -->
            @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager'])))
                <li>
                    <a href="#"><i class="fas fa-award mr-1"></i> QA <i class="fas fa-chevron-down ml-1 small"></i></a>
                    <ul class="dropdown-menu">
                        @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                            <a class="dropdown-item"
                                href="{{ route('admin.customer-claims.index', ['plant' => 'jakarta']) }}">CLAIM JKT</a>
                        @endif
                        @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                            <a class="dropdown-item"
                                href="{{ route('admin.customer-claims.index', ['plant' => 'karawang']) }}">CLAIM KRW</a>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Quality System -->
            @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager'])))
                <li>
                    <a href="#"><i class="fas fa-chart-bar mr-1"></i> QS <i class="fas fa-chevron-down ml-1 small"></i></a>
                    <ul class="dropdown-menu">
                        @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                            <li>
                                <a href="#" class="dropdown-item">PLANT JAKARTA <i
                                        class="fas fa-chevron-right submenu-arrow"></i></a>
                                <ul class="dropdown-menu sub-menu">
                                    <a class="dropdown-item"
                                        href="{{ route('calibration.schedule.index', ['plant' => 'jakarta']) }}">Jadwal
                                        Kalibrasi</a>
                                    <a class="dropdown-item"
                                        href="{{ route('calibration.verifications.index', ['plant' => 'jakarta']) }}">Hasil
                                        Verif</a>
                                    <a class="dropdown-item"
                                        href="{{ route('calibration.tools.index', ['plant' => 'jakarta']) }}">Daftar Alat</a>
                                </ul>
                            </li>
                        @endif
                        @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                            <li>
                                <a href="#" class="dropdown-item">PLANT KARAWANG <i
                                        class="fas fa-chevron-right submenu-arrow"></i></a>
                                <ul class="dropdown-menu sub-menu">
                                    <a class="dropdown-item"
                                        href="{{ route('calibration.schedule.index', ['plant' => 'karawang']) }}">Jadwal
                                        Kalibrasi</a>
                                    <a class="dropdown-item"
                                        href="{{ route('calibration.verifications.index', ['plant' => 'karawang']) }}">Hasil
                                        Verif</a>
                                    <a class="dropdown-item"
                                        href="{{ route('calibration.tools.index', ['plant' => 'karawang']) }}">Daftar Alat</a>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</nav>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationList = document.getElementById('notification-list');
            const notificationBadge = document.getElementById('notification-badge');
            const markAllReadBtn = document.getElementById('mark-all-read');

            function fetchNotifications() {
                fetch('{{ route('notifications.index') }}')
                    .then(response => {
                        if (response.status === 419) {
                            window.location.reload();
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        updateBadge(data.unread_count);
                        renderNotifications(data.notifications);
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            }

            function updateBadge(count) {
                if (count > 0) {
                    notificationBadge.textContent = count > 9 ? '9+' : count;
                    notificationBadge.classList.remove('d-none');
                } else {
                    notificationBadge.classList.add('d-none');
                }
            }

            function renderNotifications(notifications) {
                if (notifications.length === 0) {
                    notificationList.innerHTML = '<div class="text-center p-3 small text-muted">No notifications</div>';
                    return;
                }

                notificationList.innerHTML = notifications.map(notif => {
                    let iconClass = 'bg-primary';
                    let icon = 'fas fa-info-circle';

                    if (notif.type === 'ng_finding') {
                        iconClass = 'bg-danger';
                        icon = 'fas fa-exclamation-triangle';
                    } else if (notif.type === 'approval') {
                        iconClass = 'bg-success';
                        icon = 'fas fa-check-double';
                    } else if (notif.type === 'abnormal') {
                        iconClass = 'bg-warning';
                        icon = 'fas fa-exclamation-circle';
                    }

                    const timeAgo = formatTimeAgo(new Date(notif.created_at));
                    const detailUrl = notif.data && notif.data.url ? notif.data.url : '#';
                    const unreadClass = notif.is_read ? '' : 'font-weight-bold bg-light';

                    return `
                        <a class="dropdown-item d-flex align-items-center notification-item ${unreadClass}" href="${detailUrl}" data-id="${notif.id}">
                            <div class="mr-3">
                                <div class="icon-circle ${iconClass}">
                                    <i class="${icon} text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500">${timeAgo}</div>
                                <span class="${unreadClass}">${notif.title}</span>
                                <div class="small text-gray-600 line-clamp-notification">${notif.message}</div>
                            </div>
                        </a>
                    `;
                }).join('');

                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function (e) {
                        const id = this.getAttribute('data-id');
                        markAsRead(id);
                    });
                });
            }

            function markAsRead(id) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.status === 419) {
                        window.location.reload();
                    }
                });
            }

            markAllReadBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.status === 419) {
                        window.location.reload();
                        return;
                    }
                    fetchNotifications();
                });
            });

            function formatTimeAgo(date) {
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);
                if (diffInSeconds < 60) return 'Recent';
                if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
                if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
                return date.toLocaleDateString();
            }

            fetchNotifications();
            setInterval(fetchNotifications, 60000);
        });
    </script>
    <style>
        #notification-list { max-height: 340px; overflow-y: auto; }
        .line-clamp-notification { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
@endpush