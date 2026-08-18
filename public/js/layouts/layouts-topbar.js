document.addEventListener('DOMContentLoaded', function () {
    const config = window.__LAYOUTS_TOPBAR__ || {};
    const notificationList = document.getElementById('notification-list');
    const notificationBadge = document.getElementById('notification-badge');
    const markAllReadBtn = document.getElementById('mark-all-read');
    const clearAllBtn = document.getElementById('clear-all-notifications');
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.getElementById('topbar-nav-menu');

    function closeAllDropdowns() {
        const openMenus = document.querySelectorAll('.main-nav .dropdown-menu.show, .main-nav .sub-menu.show');
        const expandedItems = document.querySelectorAll('.main-nav .expanded');
        
        openMenus.forEach(menu => menu.classList.remove('show'));
        expandedItems.forEach(item => item.classList.remove('expanded'));
    }

    function fetchNotifications() {
        if (!config.notificationsIndexUrl) return;

        fetch(config.notificationsIndexUrl)
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
        if (!notificationBadge) return;
        if (count > 0) {
            notificationBadge.textContent = count > 9 ? '9+' : count;
            notificationBadge.classList.remove('d-none');
        } else {
            notificationBadge.classList.add('d-none');
        }
    }

    function renderNotifications(notifications) {
        if (!notificationList) return;

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
                const href = this.getAttribute('href');

                // Immediately remove the clicked element from the dropdown list
                this.remove();

                // Show 'No notifications' if list is empty
                const remaining = document.querySelectorAll('.notification-item');
                if (remaining.length === 0) {
                    if (notificationList) {
                        notificationList.innerHTML = '<div class="text-center p-3 small text-muted">No notifications</div>';
                    }
                }

                // Update/decrement badge count
                let currentBadgeText = notificationBadge ? notificationBadge.textContent : '0';
                if (currentBadgeText.includes('+')) {
                    fetchNotifications();
                } else {
                    let count = parseInt(currentBadgeText) || 0;
                    if (count > 0) {
                        updateBadge(count - 1);
                    }
                }

                const readUrl = config.markAsReadUrlTemplate
                    ? config.markAsReadUrlTemplate.replace(':id', id)
                    : `/notifications/${id}/read`;

                // Handle navigation and marking as read synchronously/sequentially
                if (e.button === 1 || e.ctrlKey || e.metaKey || e.shiftKey) {
                    // Middle click or ctrl/cmd/shift click: let browser handle new tab/window,
                    // but still fire markAsRead in background.
                    markAsRead(id);
                } else {
                    e.preventDefault();
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    fetch(readUrl, {
                        method: 'POST',
                        keepalive: true,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).then(response => {
                        if (response.status === 419) {
                            window.location.reload();
                        } else if (href && href !== '#') {
                            window.location.href = href;
                        }
                    }).catch(() => {
                        if (href && href !== '#') {
                            window.location.href = href;
                        }
                    });
                }
            });
        });
    }

    function markAsRead(id) {
        const readUrl = config.markAsReadUrlTemplate
            ? config.markAsReadUrlTemplate.replace(':id', id)
            : `/notifications/${id}/read`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(readUrl, {
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

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!config.notificationsMarkAllReadUrl) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(config.notificationsMarkAllReadUrl, {
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
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!config.notificationsClearAllUrl) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(config.notificationsClearAllUrl, {
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

    // Mobile Menu Toggle
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            navMenu.classList.toggle('show');
        });

        document.addEventListener('click', function (event) {
            if (navMenu.classList.contains('show') && !navMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                navMenu.classList.remove('show');
                closeAllDropdowns();
            }
        });
    }

    $(document).on('show.bs.modal', function () {
        closeAllDropdowns();
        if (navMenu) navMenu.classList.remove('show');
    });

    const leafItems = document.querySelectorAll('.main-nav a:not([href="#"])');
    leafItems.forEach(item => {
        item.addEventListener('click', function () {
            closeAllDropdowns();
            if (navMenu) navMenu.classList.remove('show');
        });
    });

    // Collapsible Menu Toggle (Manual)
    const mainNavItems = document.querySelectorAll('.main-nav > li.dropdown-item-hover > a');
    mainNavItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            const dropdownMenu = this.nextElementSibling;

            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                const isOpen = dropdownMenu.classList.contains('show');

                const siblings = parentLi.parentElement.querySelectorAll(':scope > li.dropdown-item-hover');
                siblings.forEach(sibling => {
                    if (sibling !== parentLi) {
                        sibling.classList.remove('expanded');
                        const siblingLink = sibling.querySelector(':scope > a');
                        if (siblingLink) siblingLink.classList.remove('expanded');
                        const siblingMenu = sibling.querySelector(':scope > .dropdown-menu');
                        if (siblingMenu) siblingMenu.classList.remove('show');
                    }
                });

                if (isOpen) {
                    this.classList.remove('expanded');
                    dropdownMenu.classList.remove('show');
                } else {
                    this.classList.add('expanded');
                    dropdownMenu.classList.add('show');
                }
            }
        });
    });

    // Nested submenu toggle (Manual)
    const subMenuItems = document.querySelectorAll('.main-nav .dropdown-menu .has-submenu > a');
    subMenuItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                const isOpen = dropdownMenu.classList.contains('show');

                const siblings = this.parentElement.parentElement.querySelectorAll(':scope > li.has-submenu');
                siblings.forEach(sibling => {
                    if (sibling !== this.parentElement) {
                        const siblingLink = sibling.querySelector(':scope > a');
                        const siblingMenu = sibling.querySelector(':scope > .dropdown-menu');
                        if (siblingLink) siblingLink.classList.remove('expanded');
                        if (siblingMenu) siblingMenu.classList.remove('show');
                    }
                });

                if (isOpen) {
                    this.classList.remove('expanded');
                    dropdownMenu.classList.remove('show');
                } else {
                    this.classList.add('expanded');
                    dropdownMenu.classList.add('show');
                }
            }
        });
    });
});
