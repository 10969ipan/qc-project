@php
    $canViewAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc']);

    $canInputAllPlants = auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc']);
@endphp

<style>
    .main-nav {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .main-nav > li {
        position: relative;
        margin: 0 2px;
        z-index: 1001;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
    .main-nav > li > a {
        font-family: 'Inter', sans-serif !important;
        color: rgba(255, 255, 255, 0.85) !important;
        padding: 0 0.9rem !important;
        height: 38px !important;
        line-height: 38px !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: background 0.2s ease, color 0.2s ease;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        border-radius: 4px;
        text-decoration: none !important;
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        box-sizing: border-box !important;
    }
    .main-nav i,
    #topbar-nav-menu i,
    .main-nav .fas,
    .main-nav .fa,
    #topbar-nav-menu .fas,
    #topbar-nav-menu .fa {
        font-family: "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        font-style: normal !important;
        display: inline-block !important;
        font-size: 0.85rem !important;
        line-height: 1 !important;
        width: 1.1em !important;
        text-align: center !important;
        margin-right: 0.35rem !important;
        flex-shrink: 0 !important;
        opacity: 0.9;
    }
</style>

<nav class="navbar topbar shadow px-4 d-flex align-items-center justify-content-between flex-nowrap" style="position: fixed; top: 0; left: 0; width: 100%; z-index: 1030; height: 60px; min-height: 60px;">
    <div class="d-flex align-items-center flex-grow-1">
        <button class="menu-toggle mr-2" id="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="sidebar-brand d-flex align-items-center text-decoration-none mr-3" href="{{ url('/') }}" style="border: none !important; outline: none !important; background: transparent !important; box-shadow: none !important;">
            <div class="sidebar-brand-text font-weight-bold text-white h6 mb-0" style="border: none !important; outline: none !important; background: transparent !important;">QC APPS</div>
        </a>
        @if(auth()->check() && auth()->user()->plant)
            <div class="px-2 py-1 rounded text-white small font-weight-bold mr-4"
                style="font-size: 0.65rem; border: 1px solid rgba(255,255,255,0.4) !important; background: transparent !important; outline: none !important; box-shadow: none !important;">
                {{ strtoupper(auth()->user()->plant->name) }}
            </div>
        @endif

        <div class="nav-menu-container flex-grow-1" id="topbar-nav-menu" style="z-index: 1000;">
            <div class="mobile-header d-lg-none px-4 py-3 font-weight-bold text-white"
                style="background: rgba(255,255,255,0.15); border-bottom: 2px solid rgba(255,255,255,0.2); font-size: 0.9rem; letter-spacing: 1px;">
                QC APPS
            </div>
            <ul class="main-nav d-flex align-items-center list-unstyled mb-0">
                    @foreach($dynamicMenus ?? [] as $menu)
                        @php
                            $menuRoutePath = trim($menu->route, '/');
                            $isActive = false;
                            
                            if ($menu->route && $menu->route !== '#') {
                                if ($menuRoutePath === '') { // Root path '/'
                                    $isActive = request()->is('/');
                                } else {
                                    $isActive = request()->is($menuRoutePath . '*') || request()->routeIs($menu->route);
                                }
                            }

                            if (!$isActive) {
                                foreach($menu->children as $c) {
                                    $cPath = trim($c->route, '/');
                                    if ($c->route && $c->route !== '#' && $cPath !== '') {
                                        if (request()->is($cPath . '*') || request()->routeIs($c->route)) { $isActive = true; break; }
                                    } elseif ($c->route === '/' || $cPath === '') {
                                        if (request()->is('/')) { $isActive = true; break; }
                                    }
                                    
                                    foreach($c->children as $gc) {
                                        $gcPath = trim($gc->route, '/');
                                        if ($gc->route && $gc->route !== '#' && $gcPath !== '') {
                                            if (request()->is($gcPath . '*') || request()->routeIs($gc->route)) { $isActive = true; break; }
                                        } elseif ($gc->route === '/' || $gcPath === '') {
                                            if (request()->is('/')) { $isActive = true; break; }
                                        }
                                    }
                                    if ($isActive) break;
                                }
                            }
                        @endphp
                        
                        @if($menu->children->isEmpty())
                            <li class="{{ $isActive ? 'active' : '' }}">
                                <a href="{{ $menu->route ? (Route::has($menu->route) ? route($menu->route) : url($menu->route)) : '#' }}" 
                                @if($menu->is_maintenance) 
                                    class="menu-maintenance-trigger" 
                                    data-message="{{ $menu->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.' }}"
                                    onclick="return false;" 
                                @endif>
                                <i class="{{ $menu->icon }} mr-1"></i> {{ $menu->name }}
                                </a>
                            </li>
                        @else
                            <li class="dropdown-item-hover @if($menu->is_maintenance) menu-maintenance @endif">
                                <a href="#" class="{{ $isActive ? 'expanded' : '' }} @if($menu->is_maintenance) menu-maintenance-trigger @endif"
                                @if($menu->is_maintenance) 
                                    data-message="{{ $menu->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.' }}"
                                    onclick="return false;" 
                                @endif>
                                <i class="{{ $menu->icon }} mr-1"></i> {{ $menu->name }} <i class="fas fa-chevron-down ml-1 small"></i>
                                </a>
                                <ul class="dropdown-menu">
                                     @foreach($menu->children as $child)
                                        @php
                                            $childPlant = $child->plant_code ?: request('plant');
                                            $childUrl = $child->route ? (Route::has($child->route) ? route($child->route, $childPlant ? ['plant' => $childPlant] : []) : url($child->route)) : '#';
                                        @endphp
                                        @if($child->children->isEmpty())
                                            <li>
                                                <a class="dropdown-item @if($child->is_maintenance) menu-maintenance-trigger @endif" href="{{ $childUrl }}"
                                                    @if($child->is_maintenance) 
                                                    data-message="{{ $child->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.' }}"
                                                    onclick="return false;" 
                                                    @endif>
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @else
                                            <li class="has-submenu">
                                                <a href="#" class="dropdown-item d-flex justify-content-between">{{ $child->name }} <i class="fas fa-chevron-right small"></i></a>
                                                <ul class="dropdown-menu sub-menu">
                                                    @foreach($child->children as $grand)
                                                        @php
                                                            $grandPlant = $grand->plant_code ?: $childPlant ?: request('plant');
                                                            $grandUrl = $grand->route ? (Route::has($grand->route) ? route($grand->route, $grandPlant ? ['plant' => $grandPlant] : []) : url($grand->route)) : '#';
                                                        @endphp
                                                        @if($grand->children->isEmpty())
                                                            <li>
                                                                <a class="dropdown-item @if($grand->is_maintenance) menu-maintenance-trigger @endif" href="{{ $grandUrl }}"
                                                                @if($grand->is_maintenance) 
                                                                    data-message="{{ $grand->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.' }}"
                                                                    onclick="return false;" 
                                                                @endif>
                                                                {{ $grand->name }}
                                                            </a>
                                                            </li>
                                                        @else
                                                            <li class="has-submenu">
                                                                <a href="#" class="dropdown-item d-flex justify-content-between">{{ $grand->name }} <i class="fas fa-chevron-right small"></i></a>
                                                                <ul class="dropdown-menu sub-menu">
                                                                    @foreach($grand->children as $sub)
                                                                        @php
                                                                            $subPlant = $sub->plant_code ?: $grandPlant;
                                                                            $subUrl = $sub->route ? (Route::has($sub->route) ? route($sub->route, $subPlant ? ['plant' => $subPlant] : []) : url($sub->route)) : '#';
                                                                        @endphp
                                                                        @if($sub->children->isEmpty())
                                                                            <li>
                                                                                <a class="dropdown-item @if($sub->is_maintenance) menu-maintenance-trigger @endif" href="{{ $subUrl }}"
                                                                                @if($sub->is_maintenance) 
                                                                                    data-message="{{ $sub->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.' }}"
                                                                                    onclick="return false;" 
                                                                                @endif>
                                                                                {{ $sub->name }}
                                                                                </a>
                                                                            </li>
                                                                        @else
                                                                            <li class="has-submenu">
                                                                                <a href="#" class="dropdown-item d-flex justify-content-between">{{ $sub->name }} <i class="fas fa-chevron-right small"></i></a>
                                                                                <ul class="dropdown-menu sub-menu">
                                                                                    @foreach($sub->children as $deep)
                                                                                        @php
                                                                                            $deepPlant = $deep->plant_code ?: $subPlant;
                                                                                            $deepUrl = $deep->route ? (Route::has($deep->route) ? route($deep->route, $deepPlant ? ['plant' => $deepPlant] : []) : url($deep->route)) : '#';
                                                                                        @endphp
                                                                                        <li>
                                                                                            <a class="dropdown-item @if($deep->is_maintenance) menu-maintenance-trigger @endif" href="{{ $deepUrl }}"
                                                                                            @if($deep->is_maintenance) 
                                                                                                data-message="{{ $deep->maintenance_message ?: 'Modul ini sedang dalam pemeliharaan.' }}"
                                                                                                onclick="return false;" 
                                                                                            @endif>
                                                                                            {{ $deep->name }}
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
        </div>
    </div>

    <div class="d-flex align-items-center flex-shrink-0">
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
    <script id="layouts-topbar-data" type="application/json"
        data-notifications-index-url="{{ route('notifications.index') }}"
        data-mark-all-read-url="{{ route('notifications.mark-all-read') }}"
        data-clear-all-notifications-url="{{ route('notifications.clear-all') }}"
        data-mark-as-read-url-template="{{ route('notifications.mark-as-read', ['id' => ':id']) }}">
        {}
    </script>
    <script>
        (function() {
            const dataEl = document.getElementById('layouts-topbar-data');
            if (dataEl) {
                window.__LAYOUTS_TOPBAR__ = {
                    notificationsIndexUrl: dataEl.getAttribute('data-notifications-index-url'),
                    notificationsMarkAllReadUrl: dataEl.getAttribute('data-mark-all-read-url'),
                    notificationsClearAllUrl: dataEl.getAttribute('data-clear-all-notifications-url'),
                    markAsReadUrlTemplate: dataEl.getAttribute('data-mark-as-read-url-template')
                };
            }

            // Maintenance Alert Handler
            document.querySelectorAll('.menu-maintenance-trigger').forEach(el => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maintenance',
                        text: this.getAttribute('data-message')
                    });
                });
            });
        })();
    </script>
    <script src="{{ asset('js/layouts/layouts-topbar.js') }}"></script>
    <style>
        #notification-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .line-clamp-notification {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .main-nav > li {
            position: relative;
            margin: 0 2px;
            z-index: 1001;
            height: 100%;
            display: flex;
            align-items: center;
        }

        .main-nav > li > a {
            font-family: 'Inter', sans-serif !important;
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 0 0.9rem !important;
            height: 38px !important;
            line-height: 38px !important;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: background 0.2s ease, color 0.2s ease;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            border-radius: 4px;
            text-decoration: none !important;
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
        }

        .main-nav > li > a:hover,
        .main-nav > li.dropdown-item-hover > a.expanded {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.15) !important;
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .main-nav > li.active > a {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.25) !important;
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .main-nav i,
        #topbar-nav-menu i,
        .main-nav .fas,
        .main-nav .fa,
        #topbar-nav-menu .fas,
        #topbar-nav-menu .fa {
            font-family: "Font Awesome 5 Free" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            display: inline-block !important;
            font-size: 0.85rem !important;
            line-height: 1 !important;
            width: 1.1em !important;
            text-align: center !important;
            margin-right: 0.35rem !important;
            flex-shrink: 0 !important;
            opacity: 0.9;
        }

        #topbar-nav-menu .dropdown-menu,
        #topbar-nav-menu .dropdown-menu .sub-menu {
            background-color: #4e73df !important;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            max-height: 70vh; /* Responsive max height */
            overflow-y: auto;  /* Enable vertical scroll */
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
            z-index: 1050 !important;
        }

        #topbar-nav-menu .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }
        #topbar-nav-menu .dropdown-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        #topbar-nav-menu .dropdown-menu::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        #topbar-nav-menu .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }

        #topbar-nav-menu .dropdown-item,
        #topbar-nav-menu .dropdown-menu .dropdown-item {
            color: #ffffff !important;
            padding-top: 6px !important;    /* Reduced padding for compactness */
            padding-bottom: 6px !important; /* Reduced padding for compactness */
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

        .dropdown-item-hover .dropdown-menu {
            display: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            margin-top: 0 !important;
            z-index: 1000;
        }

        .dropdown-item-hover .dropdown-menu.show {
            display: block;
            opacity: 1;
        }

        .has-submenu>.sub-menu {
            position: static !important; /* Make nested menu inline/accordion style */
            width: 100% !important;
            box-shadow: none !important;
            background: rgba(0, 0, 0, 0.08) !important;
            margin: 0 !important;
            padding-left: 10px;
            display: none;
        }

        .has-submenu>.sub-menu.show {
            display: block;
        }

        .badge-counter {
            position: absolute;
            transform: translate(50%, -50%);
            transform-origin: top right;
            top: 10% !important;
            right: 10% !important;
            margin-top: 0;
        }

        @media (max-width: 991.98px) {
            #topbar-nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: #4e73df !important;
                z-index: 1000;
                padding: 10px 0 20px 0;
                box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
                max-height: calc(100vh - 70px);
                overflow-y: auto;
            }

            #topbar-nav-menu.show {
                display: block;
            }

            .main-nav {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .main-nav>li {
                width: 100%;
                display: block !important;
                height: auto !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .main-nav>li:last-child {
                border-bottom: none;
            }

            .main-nav>li>a {
                padding: 12px 20px !important;
                font-size: 0.85rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            #topbar-nav-menu .dropdown-menu {
                display: none;
                position: static !important;
                float: none !important;
                width: 100% !important;
                background: rgba(0, 0, 0, 0.1) !important;
                border-radius: 0;
                padding: 0;
                margin: 0 !important;
                box-shadow: none;
                opacity: 1 !important;
                transition: none;
                max-height: none !important; /* Disable individual scroll on mobile */
                overflow-y: visible !important;
            }

            #topbar-nav-menu .dropdown-menu.show {
                display: block !important;
            }

            .main-nav>li.active>a {
                background: rgba(255, 255, 255, 0.15);
                font-weight: bold;
            }

            #topbar-nav-menu .dropdown-item {
                padding: 12px 20px 12px 40px !important;
                font-size: 0.85rem;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            #topbar-nav-menu .dropdown-item:last-child {
                border-bottom: none;
            }

            #topbar-nav-menu .has-submenu {
                display: block !important;
                width: 100% !important;
            }

            #topbar-nav-menu .has-submenu>.sub-menu {
                display: none;
                position: static !important;
                width: 100% !important;
                background: rgba(0, 0, 0, 0.2) !important;
                padding-left: 0;
            }

            #topbar-nav-menu .has-submenu>.sub-menu.show {
                display: block !important;
            }

            #topbar-nav-menu .sub-menu .dropdown-item {
                padding-left: 60px !important;
            }

            #topbar-nav-menu .sub-menu .sub-menu .dropdown-item {
                padding-left: 80px !important;
            }

            .main-nav>li.dropdown-item-hover>a i:last-child,
            .has-submenu>a i:last-child {
                margin-left: auto !important;
            }

            .main-nav>li.dropdown-item-hover>a.expanded i.fa-chevron-down {
                transform: rotate(180deg);
            }

            .has-submenu>a.expanded i:last-child {
                transform: rotate(90deg);
            }
        }

        @media (min-width: 992px) {
            .nav-menu-container {
                display: block !important;
            }

            .dropdown-item-hover {
                position: relative;
            }

            .has-submenu {
                position: relative;
            }

            .dropdown-item-hover>a.expanded i.fa-chevron-down {
                transform: rotate(180deg);
            }

            .has-submenu>a.expanded i.fa-chevron-right {
                transform: rotate(90deg);
            }

            .dropdown-item-hover>a i,
            .has-submenu>a i {
                transition: transform 0.2s ease;
            }
        }
    </style>
@endpush
