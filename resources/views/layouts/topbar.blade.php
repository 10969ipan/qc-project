<nav class="navbar navbar-expand navbar-dark bg-primary topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars text-white"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw fa-lg"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter d-none" id="notification-badge" style="margin-top: -5px; margin-right: -2px;">0</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Notifications
                </h6>
                <div id="notification-list">
                    <!-- Notifications will be loaded here -->
                    <div class="text-center p-3 small text-muted">Loading...</div>
                </div>
                <a class="dropdown-item text-center small text-gray-500" href="#" id="mark-all-read">Mark All as
                    Read</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-white small">{{ Auth::user()->name ?? 'User' }}</span>
                <span class="d-lg-none text-white small">{{ mb_substr(Auth::user()->name ?? 'User', 0, 10) }}</span>
                <img class="img-profile rounded-circle"
                    src="{{ asset('startbootstrap-sb-admin-2-gh-pages/img/undraw_profile.svg') }}" alt="User">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item btn-logout" href="#">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>


</nav>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationList = document.getElementById('notification-list');
            const notificationBadge = document.getElementById('notification-badge');
            const markAllReadBtn = document.getElementById('mark-all-read');

            function fetchNotifications() {
                fetch('{{ route('notifications.index') }}')
                    .then(response => response.json())
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

                // Add click events to mark as read
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
                }).catch(error => console.error('Error marking read:', error));
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
                }).then(() => fetchNotifications());
            });

            function formatTimeAgo(date) {
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) return 'Recent';
                if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
                if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';

                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            // Initial fetch and poll
            fetchNotifications();
            setInterval(fetchNotifications, 30000);
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
            white-space: normal;
        }
    </style>
@endpush