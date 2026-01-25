@php
    // Roles that can VIEW all plants (for reports/laporan)
    $canViewAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift']);

    // Roles that can INPUT in all plants
    $canInputAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc']);
@endphp

<nav class="navbar topbar static-top shadow px-4 py-0 d-flex align-items-center justify-content-between">
    <!-- Left Section: Branding, Plant, & Navigation -->
    <div class="d-flex align-items-center flex-grow-1">
        <button class="menu-toggle mr-2" id="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="sidebar-brand d-flex align-items-center text-decoration-none mr-3" href="/">
            <div class="sidebar-brand-icon rotate-n-15 mr-2">
                <i class="fas fa-laugh-wink text-white" style="font-size: 1.4rem;"></i>
            </div>
            <div class="sidebar-brand-text font-weight-bold text-white h6 mb-0">QC APPS</div>
        </a>
        @if(auth()->check() && auth()->user()->plant)
            <div class="px-2 py-1 border border-white rounded text-white small font-weight-bold mr-4"
                style="font-size: 0.65rem; border-color: rgba(255,255,255,0.4) !important;">
                {{ strtoupper(auth()->user()->plant->name) }}
            </div>
        @endif

        <!-- Navigation Menu (Left Aligned) -->
        <div class="nav-menu-container d-none d-lg-block" id="topbar-nav-menu">
            <ul class="main-nav d-flex align-items-center list-unstyled mb-0">
                <li class="{{ Request::is('/') ? 'active' : '' }}">
                    <a href="/"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                </li>

                <!-- Quality Control Dropdown -->
                @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager', 'inspector', 'karu_qc'])))
                    <li class="dropdown-item-hover">
                        <a href="#"><i class="fas fa-clipboard-check mr-1"></i> Quality Control <i
                                class="fas fa-chevron-down ml-1 small"></i></a>
                        <ul class="dropdown-menu">
                            <!-- Plant Jakarta QC -->
                            @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                                <li class="has-submenu">
                                    <a href="#" class="dropdown-item d-flex justify-content-between">PLANT JAKARTA <i
                                            class="fas fa-chevron-right small"></i></a>
                                    <ul class="dropdown-menu sub-menu">
                                        @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager']))
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">MASTER DATA <i
                                                        class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.items.index', ['plant' => 'jakarta']) }}">Data
                                                            Item</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.categories.index', ['plant' => 'jakarta']) }}">Kategori</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endif
                                        @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager']))
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">ANALYSIS <i
                                                        class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('analysis.monthly_ng', ['plant' => 'jakarta']) }}">Sub
                                                            Assy Anls</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('analysis.monthly_ng_in_process', ['plant' => 'jakarta']) }}">Inprocess
                                                            Anls</a></li>
                                                </ul>
                                            </li>
                                        @endif
                                        <li class="has-submenu">
                                            <a href="#" class="dropdown-item d-flex justify-content-between">CHECKSHEET <i
                                                    class="fas fa-chevron-right small"></i></a>
                                            <ul class="dropdown-menu sub-menu">
                                                <li><a class="dropdown-item"
                                                        href="{{ route('checksheet.sub_assy', ['plant' => 'jakarta']) }}">Sub
                                                        Assy</a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('in_process.create', ['plant' => 'jakarta']) }}">Inprocess</a>
                                                </li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('sortir.create', ['plant' => 'jakarta']) }}">Sortir</a>
                                                </li>
                                            </ul>
                                        </li>
                                        @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc']))
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">LAPORAN <i
                                                        class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.checksheets.index', ['plant' => 'jakarta']) }}">Sub
                                                            Assy</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('in_process.index', ['plant' => 'jakarta']) }}">Inprocess</a>
                                                    </li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('sortir.index', ['plant' => 'jakarta']) }}">Sortir</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            <!-- Plant Karawang QC -->
                            @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                                <li class="has-submenu">
                                    <a href="#" class="dropdown-item d-flex justify-content-between">PLANT KARAWANG <i
                                            class="fas fa-chevron-right small"></i></a>
                                    <ul class="dropdown-menu sub-menu">
                                        @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager']))
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">MASTER DATA <i
                                                        class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.items.index', ['plant' => 'karawang']) }}">Data
                                                            Item</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.categories.index', ['plant' => 'karawang']) }}">Kategori</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endif
                                        @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager']))
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">ANALYSIS <i
                                                        class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('analysis.monthly_ng', ['plant' => 'karawang']) }}">Sub
                                                            Assy Anls</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('analysis.monthly_ng_in_process', ['plant' => 'karawang']) }}">Inprocess
                                                            Anls</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('analysis.monthly_ng_cross_cut', ['plant' => 'karawang']) }}">Cross
                                                            Cut Anls</a></li>
                                                </ul>
                                            </li>
                                        @endif
                                        <li class="has-submenu">
                                            <a href="#" class="dropdown-item d-flex justify-content-between">CHECKSHEET <i
                                                    class="fas fa-chevron-right small"></i></a>
                                            <ul class="dropdown-menu sub-menu">
                                                <li><a class="dropdown-item"
                                                        href="{{ route('checksheet.sub_assy', ['plant' => 'karawang']) }}">Sub
                                                        Assy</a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('in_process.create', ['plant' => 'karawang']) }}">Inprocess</a>
                                                </li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('cross_cut.create', ['plant' => 'karawang']) }}">Cross
                                                        Cut</a></li>
                                                <li><a class="dropdown-item"
                                                        href="{{ route('sortir.create', ['plant' => 'karawang']) }}">Sortir</a>
                                                </li>
                                            </ul>
                                        </li>
                                        @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'inspector', 'kashift', 'asst_manager', 'manager', 'karu_qc']))
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">LAPORAN <i
                                                        class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('admin.checksheets.index', ['plant' => 'karawang']) }}">Sub
                                                            Assy</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('in_process.index', ['plant' => 'karawang']) }}">Inprocess</a>
                                                    </li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('cross_cut.index', ['plant' => 'karawang']) }}">Cross
                                                            Cut</a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('sortir.index', ['plant' => 'karawang']) }}">Sortir</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Quality Assurance -->
                @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager'])))
                    <li class="dropdown-item-hover">
                        <a href="#"><i class="fas fa-award mr-1"></i> Quality Assurance <i
                                class="fas fa-chevron-down ml-1 small"></i></a>
                        <ul class="dropdown-menu">
                            @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                                <li class="has-submenu">
                                    <a href="#" class="dropdown-item d-flex justify-content-between">PLANT JAKARTA <i
                                            class="fas fa-chevron-right small"></i></a>
                                    <ul class="dropdown-menu sub-menu">
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.customer-claims.index', ['plant' => 'jakarta']) }}">Claim
                                                Customer</a></li>
                                    </ul>
                                </li>
                            @endif
                            @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                                <li class="has-submenu">
                                    <a href="#" class="dropdown-item d-flex justify-content-between">PLANT KARAWANG <i
                                            class="fas fa-chevron-right small"></i></a>
                                    <ul class="dropdown-menu sub-menu">
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.customer-claims.index', ['plant' => 'karawang']) }}">Claim
                                                Customer</a></li>
                                    </ul>
                                </li>
                            @endif
                            @if($canInputAllPlants)
                                <div class="dropdown-divider"></div>
                                <li>
                                    <a class="dropdown-item font-weight-bold text-primary"
                                        href="{{ route('admin.customer-claims.index', ['plant' => 'total']) }}">
                                        <i class="fas fa-plus-circle mr-1"></i> Input Total Claim Customer
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Quality System -->
                @if(auth()->check() && (in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift', 'asst_manager', 'manager'])))
                    <li class="dropdown-item-hover">
                        <a href="#"><i class="fas fa-chart-bar mr-1"></i> Quality System <i
                                class="fas fa-chevron-down ml-1 small"></i></a>
                        <ul class="dropdown-menu">
                            @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'jakarta'))
                                <li class="has-submenu">
                                    <a href="#" class="dropdown-item d-flex justify-content-between">PLANT JAKARTA <i
                                            class="fas fa-chevron-right small"></i></a>
                                    <ul class="dropdown-menu sub-menu">
                                        <li><a class="dropdown-item"
                                                href="{{ route('calibration.schedule.index', ['plant' => 'jakarta']) }}">Jadwal
                                                Kalibrasi</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('calibration.verifications.index', ['plant' => 'jakarta']) }}">Hasil
                                                Verif</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('calibration.tools.index', ['plant' => 'jakarta']) }}">Daftar
                                                Alat</a></li>
                                    </ul>
                                </li>
                            @endif
                            @if($canInputAllPlants || (auth()->user()->plant && auth()->user()->plant->code === 'karawang'))
                                <li class="has-submenu">
                                    <a href="#" class="dropdown-item d-flex justify-content-between">PLANT KARAWANG <i
                                            class="fas fa-chevron-right small"></i></a>
                                    <ul class="dropdown-menu sub-menu">
                                        <li><a class="dropdown-item"
                                                href="{{ route('calibration.schedule.index', ['plant' => 'karawang']) }}">Jadwal
                                                Kalibrasi</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('calibration.verifications.index', ['plant' => 'karawang']) }}">Hasil
                                                Verif</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('calibration.tools.index', ['plant' => 'karawang']) }}">Daftar
                                                Alat</a></li>
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Right Section: Utils, Notifications, Profile -->
    <div class="d-flex align-items-center">
        <!-- Notifications -->
        <div class="dropdown no-arrow mx-2">
            <a class="nav-link dropdown-toggle text-white p-0" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown">
                <i class="fas fa-bell fa-fw" style="font-size: 1.1rem;"></i>
                <span class="badge badge-danger badge-counter d-none" id="notification-badge">0</span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in">
                <h6 class="dropdown-header">Notifications</h6>
                <div id="notification-list">
                    <div class="text-center p-3 small text-muted">Loading...</div>
                </div>
                <div class="d-flex justify-content-between border-top">
                    <a class="dropdown-item text-center small text-gray-500 border-right" href="#" id="mark-all-read">
                        <i class="fas fa-check-double mr-1"></i> Mark Read
                    </a>
                    <a class="dropdown-item text-center small text-danger" href="#" id="clear-all-notifications">
                        <i class="fas fa-trash-alt mr-1"></i> Clear All
                    </a>
                </div>
            </div>
        </div>

        <div class="topbar-divider d-none d-sm-block mx-3"
            style="height: 24px; width: 1px; background: rgba(255,255,255,0.2);"></div>

        <!-- Profil Stacked -->
        <div class="dropdown no-arrow d-flex align-items-center">
            <a class="dropdown-toggle d-flex align-items-center text-decoration-none" href="#" id="userDropdown"
                role="button" data-toggle="dropdown">
                <div class="text-right mr-2 d-none d-lg-block">
                    <div class="text-white font-weight-bold" style="font-size: 0.85rem; line-height: 1.1;">
                        {{ Auth::user()->name ?? 'User' }}
                    </div>
                    <div class="text-white-50 small" style="font-size: 0.65rem;">
                        {{ Auth::user()->role_display_name ?? strtoupper(Auth::user()->role ?? 'STAFF') }}
                    </div>
                </div>
                <img class="img-profile rounded-circle"
                    style="width: 32px; height: 32px; border: 2px solid rgba(255,255,255,0.3);"
                    src="{{ asset('startbootstrap-sb-admin-2-gh-pages/img/undraw_profile.svg') }}">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                <a class="dropdown-item btn-logout" href="#">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                </a>
            </div>
        </div>
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

            const clearAllBtn = document.getElementById('clear-all-notifications');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch('{{ route('notifications.clear-all') }}', {
                        method: 'DELETE',
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
                        return response.json();
                    }).then(data => {
                        fetchNotifications();
                    }).catch(error => console.error('Error clearing notifications:', error));
                });
            }

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

            // Mobile Menu Toggle
            const menuToggle = document.getElementById('mobile-menu-toggle');
            const navMenu = document.getElementById('topbar-nav-menu');

            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function () {
                    navMenu.classList.toggle('show');
                });

                // Close menu when clicking outside
                document.addEventListener('click', function (event) {
                    if (!navMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                        navMenu.classList.remove('show');
                    }
                });
            }

            // Collapsible Menu Toggle (Desktop)
            const mainNavItems = document.querySelectorAll('.main-nav > li.dropdown-item-hover > a');
            mainNavItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const parentLi = this.parentElement;
                    const dropdownMenu = this.nextElementSibling;

                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        // Toggle expanded class for chevron rotation
                        parentLi.classList.toggle('expanded');

                        // Toggle show class for dropdown
                        dropdownMenu.classList.toggle('show');

                        // Close other open menus at the same level
                        const siblings = parentLi.parentElement.querySelectorAll(':scope > li.dropdown-item-hover');
                        siblings.forEach(sibling => {
                            if (sibling !== parentLi) {
                                sibling.classList.remove('expanded');
                                const siblingMenu = sibling.querySelector(':scope > .dropdown-menu');
                                if (siblingMenu) {
                                    siblingMenu.classList.remove('show');
                                }
                            }
                        });
                    }
                });
            });

            // Nested submenu toggle
            const subMenuItems = document.querySelectorAll('.main-nav .dropdown-menu .has-submenu > a');
            subMenuItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const parentLi = this.parentElement;
                    const dropdownMenu = this.nextElementSibling;

                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        // Toggle expanded class
                        this.classList.toggle('expanded');

                        // Toggle show class
                        dropdownMenu.classList.toggle('show');

                        // Close other submenus at same level
                        const siblings = parentLi.parentElement.querySelectorAll(':scope > li.has-submenu');
                        siblings.forEach(sibling => {
                            if (sibling !== parentLi) {
                                const siblingLink = sibling.querySelector(':scope > a');
                                const siblingMenu = sibling.querySelector(':scope > .dropdown-menu');
                                if (siblingLink) siblingLink.classList.remove('expanded');
                                if (siblingMenu) siblingMenu.classList.remove('show');
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        #notification-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .line-clamp-notification {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Navigation Sub-menus Blue Style */
        #topbar-nav-menu .dropdown-menu,
        #topbar-nav-menu .dropdown-menu .sub-menu {
            background-color: #4e73df !important;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        #topbar-nav-menu .dropdown-item,
        #topbar-nav-menu .dropdown-menu .dropdown-item {
            color: #ffffff !important;
        }

        #topbar-nav-menu .dropdown-item:hover,
        #topbar-nav-menu .dropdown-item:active,
        #topbar-nav-menu .dropdown-item:focus {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            outline: none;
            box-shadow: none;
        }

        #topbar-nav-menu a:focus,
        #topbar-nav-menu a:active,
        .dropdown-item:focus,
        .dropdown-item:active {
            outline: none !important;
            box-shadow: none !important;
        }

        #topbar-nav-menu .dropdown-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        #topbar-nav-menu .dropdown-item i {
            color: rgba(255, 255, 255, 0.7);
        }

        #topbar-nav-menu .dropdown-item:hover i {
            color: #ffffff;
        }

        /* Badge Positioning Fix */
        .dropdown.no-arrow .nav-link {
            position: relative;
        }
        
        .badge-counter {
            position: absolute;
            transform: translate(50%, -50%);
            transform-origin: top right;
            top: 10% !important; 
            right: 10% !important; 
            margin-top: 0;
        }

        /* Desktop specific adjustments */
        @media (min-width: 992px) {
            .dropdown-item-hover {
                position: relative;
            }

            /* Ensure submenus are positioned correctly */
            .has-submenu {
                position: relative;
            }

            .has-submenu > .sub-menu {
                top: 0;
                left: 100%;
                margin-top: -1px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ... existing code ...

            // Desktop Hover Interaction
            const minDesktopWidth = 992;

            function setupHoverMenu() {
                if (window.innerWidth < minDesktopWidth) return;

                // Top Level Menus
                const menuItems = document.querySelectorAll('.dropdown-item-hover');
                menuItems.forEach(item => {
                    item.addEventListener('mouseenter', function() {
                        if (window.innerWidth < minDesktopWidth) return;
                        const menu = this.querySelector('.dropdown-menu');
                        const link = this.querySelector('a');
                        if (menu) menu.classList.add('show');
                        if (link) link.classList.add('expanded');
                    });

                    item.addEventListener('mouseleave', function() {
                        if (window.innerWidth < minDesktopWidth) return;
                        const menu = this.querySelector('.dropdown-menu');
                        const link = this.querySelector('a');
                        if (menu) menu.classList.remove('show');
                        if (link) link.classList.remove('expanded');
                    });
                });

                // Nested Sub-Menus
                const subMenuItems = document.querySelectorAll('.has-submenu');
                subMenuItems.forEach(item => {
                    item.addEventListener('mouseenter', function(e) {
                        if (window.innerWidth < minDesktopWidth) return;
                        e.stopPropagation(); // Prevent bubbling causing issues
                        const menu = this.querySelector('.sub-menu');
                        const link = this.querySelector('a');
                        if (menu) menu.classList.add('show');
                        if (link) link.classList.add('expanded');
                    });

                    item.addEventListener('mouseleave', function(e) {
                        if (window.innerWidth < minDesktopWidth) return;
                        e.stopPropagation();
                        const menu = this.querySelector('.sub-menu');
                        const link = this.querySelector('a');
                        if (menu) menu.classList.remove('show');
                        if (link) link.classList.remove('expanded');
                    });
                });
            }

            setupHoverMenu();
            
            // Re-run on resize to handle orientation changes etc
            window.addEventListener('resize', () => {
                // Optional: clear styles if moving to mobile?
                // For now just ensuring logic checks width
            });
        });
    </script>
@endpush