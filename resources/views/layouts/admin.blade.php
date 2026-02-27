<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('ipp.png') }}" type="image/png">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet" type="text/css">

    <!-- Professional Corporate Fonts (Localized) -->
    <link href="{{ asset('fonts/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('fonts/nunito.css') }}" rel="stylesheet">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-responsive.css') }}?v={{ time() }}" rel="stylesheet">

    <!-- Custom styles for DataTables -->
    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

    <!-- Chart.js for Dashboard Charts -->
    <script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chartjs-plugin-datalabels.min.js') }}"></script>

    <!-- FusionCharts for Gauge Charts -->
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.widgets.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.theme.fusion.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.theme.gammel.js') }}"></script>

    <style>
        /* Professional Corporate Font - Inter */
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span:not([class*="fa"]):not([class*="icon"]),
        div:not([class*="fa"]):not([class*="icon"]),
        a:not([class*="fa"]):not([class*="icon"]),
        button:not([class*="fa"]):not([class*="icon"]),
        input,
        textarea,
        select,
        label,
        td,
        th,
        li {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif !important;
        }

        /* Improve readability */
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga' 1, 'calt' 1;
        }

        /* Headings optimization */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6 {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Body text optimization */
        p,
        span,
        div,
        td,
        th,
        li {
            font-weight: 400;
            letter-spacing: -0.011em;
        }

        /* Bold text */
        strong,
        b,
        .font-weight-bold {
            font-weight: 600;
        }

        /* Table Header Styling - Professional Background */
        table thead th,
        table thead td,
        .table thead th,
        .table thead td {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        /* Input form tables (without thead) - Target first row with th */
        table tr:first-child th,
        .table tr:first-child th,
        table>tr:first-child th,
        .table>tr:first-child th {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        /* For tables with tbody but th in first row */
        table tbody tr:first-child th,
        .table tbody tr:first-child th {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        /* All th elements in tables (fallback) - EXCEPT nested tables */
        table th:not(table table th):not(td table th):not(.table .table th),
        .table th:not(table table th):not(td table th):not(.table .table th) {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
        }

        /* MAXIMUM PRIORITY: Remove ALL blue backgrounds from nested tables */
        /* Layer 1: Direct nested table selectors */
        td table th,
        td table tbody th,
        td table thead th,
        td .table th,
        td .table tbody th,
        td .table thead th,
        /* Layer 2: Kimia column specific */
        .kimia-col table th,
        .kimia-col .table th,
        .kimia-col table tbody th,
        .kimia-col table thead th,
        /* Layer 3: Table within table */
        table table th,
        table table tbody th,
        table table thead th,
        .table .table th,
        .table .table tbody th,
        .table .table thead th,
        /* Layer 4: tbody td combinations */
        table tbody td table th,
        .table tbody td table th,
        tbody td table th,
        tbody td table tbody th,
        tbody td table thead th,
        /* Layer 5: Additional specificity */
        table.table-bordered tbody td table th,
        .table.table-bordered tbody td table th {
            background-color: white !important;
            background: white !important;
            color: #5a5c69 !important;
            font-weight: 500 !important;
            border-color: #dee2e6 !important;
            font-size: 0.85rem !important;
            padding: 0.25rem !important;
        }

        /* MAXIMUM PRIORITY: Nested table cells - NO BACKGROUND */
        td table td,
        td table tbody td,
        td .table td,
        td .table tbody td,
        .kimia-col table td,
        .kimia-col .table td,
        .kimia-col table tbody td,
        table table td,
        table table tbody td,
        .table .table td,
        .table .table tbody td,
        table tbody td table td,
        .table tbody td table td,
        tbody td table td,
        tbody td table tbody td,
        table.table-bordered tbody td table td,
        .table.table-bordered tbody td table td {
            background-color: white !important;
            background: white !important;
            color: #5a5c69 !important;
            border-color: #dee2e6 !important;
            padding: 0.25rem !important;
        }

        /* Nested table itself - FORCE white background */
        td table,
        td .table,
        .kimia-col table,
        .kimia-col .table,
        table tbody td table,
        .table tbody td table,
        tbody td table {
            background-color: white !important;
            background: white !important;
        }

        /* Table striped rows for better readability */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Table hover effect */
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.08);
        }

        /* Table Border Enhancement - Clear but not too thick */
        table,
        .table {
            border: 1.5px solid #dee2e6 !important;
        }

        table th,
        table td,
        .table th,
        .table td {
            border: 1.5px solid #dee2e6 !important;
        }

        /* Table bordered variant */
        .table-bordered {
            border: 1.5px solid #dee2e6 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1.5px solid #dee2e6 !important;
        }

        /* Premium Sidebar Animations */
        /* Premium Sidebar Animations */
        .sidebar .nav-item .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-item .nav-link i {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
        }

        /* Icon Hover Effect */
        .sidebar .nav-item:hover .nav-link i {
            transform: scale(1.2) rotate(10deg);
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        /* Link Slide & Glow Effect */
        .sidebar .nav-item .nav-link:hover {
            padding-left: 1.5rem !important;
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }

        /* Active State Pulse Animation */
        @keyframes activePulse {
            0% {
                background: rgba(255, 255, 255, 0.15);
            }

            50% {
                background: rgba(255, 255, 255, 0.25);
            }

            100% {
                background: rgba(255, 255, 255, 0.15);
            }
        }

        .sidebar .nav-item.active {
            position: relative;
        }

        .sidebar .nav-item.active .nav-link {
            animation: activePulse 2s infinite ease-in-out;
            border-left: 4px solid #fff;
            font-weight: 700;
        }

        /* Sub-menu implementation (collapse-inner) */
        .sidebar .collapse-inner .collapse-item {
            transition: all 0.2s ease;
            border-left: 0 solid transparent;
        }

        .sidebar .collapse-inner .collapse-item:hover {
            padding-left: 2rem !important;
            border-left: 4px solid #4e73df;
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            transform: translateX(5px);
        }

        /* Logout Slide-out Animation */
        .btn-logout i {
            transition: transform 0.3s ease;
        }

        .btn-logout:hover i {
            transform: translateX(10px) scale(1.2) !important;
            color: #e74a3b !important;
            text-shadow: 0 0 10px rgba(231, 74, 59, 0.4);
        }

        /* Smooth Collapse Animation */
        .sidebar.toggled .nav-item .nav-link i {
            transform: none !important;
        }

        /* Sidebar Brand Hover */
        .sidebar-brand {
            transition: all 0.3s ease;
        }

        .sidebar-brand:hover {
            transform: scale(1.05);
        }

        .sidebar-brand:hover .sidebar-brand-icon {
            animation: rotateBrand 0.5s ease-in-out;
        }

        @keyframes rotateBrand {
            0% {
                transform: rotate(-15deg);
            }

            50% {
                transform: rotate(15deg);
            }

            100% {
                transform: rotate(-15deg);
            }
        }

        /* Generic Button & Action Animations (Inspired by Login) */
        .btn,
        .btn-sm {
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .btn:hover,
        .btn-sm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15) !important;
        }

        .btn:active,
        .btn-sm:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
        }

        /* Color-specific glow effects */
        .btn-primary:hover {
            box-shadow: 0 8px 15px rgba(78, 115, 223, 0.4) !important;
        }

        .btn-success:hover {
            box-shadow: 0 8px 15px rgba(28, 200, 138, 0.4) !important;
        }

        .btn-danger:hover {
            box-shadow: 0 8px 15px rgba(231, 74, 59, 0.4) !important;
        }

        .btn-warning:hover {
            box-shadow: 0 8px 15px rgba(246, 194, 62, 0.4) !important;
        }

        .btn-info:hover {
            box-shadow: 0 8px 15px rgba(54, 185, 204, 0.4) !important;
        }

        /* Icon within button animation */
        .btn i,
        .btn-sm i {
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .btn:hover i,
        .btn-sm:hover i {
            transform: scale(1.2);
        }

        /* Topbar & Navbar Redesign (Top Nav Mode) */
        #content-wrapper {
            background-color: #f8f9fc;
        }

        .topbar {
            height: 60px !important;
            padding: 0 1rem !important;
            flex-direction: row !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            background-color: #4e73df !important;
            background-image: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .nav-menu-central {
            display: none;
        }

        .main-nav {
            display: flex;
            height: 100%;
            align-items: center;
        }

        /* Mobile Navigation Overlay */
        @media (max-width: 991.98px) {

            /* Keep topbar in single row on mobile */
            .topbar {
                flex-wrap: nowrap !important;
                padding: 0 0.5rem !important;
            }

            /* Left section should NOT grow on mobile */
            .topbar>div:first-child {
                flex-grow: 0 !important;
                flex-shrink: 1 !important;
                min-width: 0 !important;
                max-width: 70% !important;
            }

            /* Hide the nav-menu-container when not toggled */
            .nav-menu-container:not(.show) {
                display: none !important;
            }

            /* Right section stays on the right */
            .topbar>div:last-child {
                flex-shrink: 0 !important;
                margin-left: auto !important;
            }

            /* Reduce spacing on mobile */
            .topbar .sidebar-brand {
                margin-right: 0.5rem !important;
            }

            .topbar .mr-4 {
                margin-right: 0.5rem !important;
            }

            /* Hide plant badge text on very small screens */
            @media (max-width: 576px) {
                .topbar .sidebar-brand-text {
                    display: none !important;
                }
            }

            .nav-menu-container {
                display: none;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
                z-index: 1050;
                padding: 1rem 0;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }

            .nav-menu-container.show {
                display: block !important;
            }

            .main-nav {
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                padding: 0 1rem;
            }

            .main-nav>li {
                width: 100%;
                height: auto;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .main-nav>li>a {
                padding: 0.8rem 0 !important;
                height: auto;
                display: flex;
                justify-content: space-between;
                width: 100%;
            }

            /* All dropdowns on mobile appear BELOW parent */
            .main-nav .dropdown-menu,
            .main-nav .dropdown-menu .dropdown-menu,
            .main-nav .dropdown-menu .sub-menu {
                position: static !important;
                float: none !important;
                width: 100% !important;
                background: #1e40af !important;
                /* Solid blue */
                border: none !important;
                box-shadow: none !important;
                padding: 0.5rem 0 0.5rem 1rem !important;
                margin: 0 !important;
                left: auto !important;
                right: auto !important;
                top: auto !important;
                display: none !important;
                max-height: none !important;
                border-radius: 0 !important;
            }

            /* Show dropdowns when they have show class */
            .main-nav .dropdown-menu.show,
            .main-nav .dropdown-menu .dropdown-menu.show,
            .main-nav .dropdown-menu .sub-menu.show {
                display: block !important;
            }

            /* Nested levels - deeper blue for hierarchy */
            .main-nav .dropdown-menu .dropdown-menu,
            .main-nav .dropdown-menu .sub-menu {
                background: #1e3a8a !important;
                /* Deeper blue */
                padding-left: 2rem !important;
            }

            .main-nav .dropdown-menu .dropdown-menu .dropdown-menu {
                background: #172554 !important;
                /* Deepest blue */
                padding-left: 3rem !important;
            }

            /* Dropdown items on mobile */
            .main-nav .dropdown-item {
                color: rgba(255, 255, 255, 0.85) !important;
                padding: 0.7rem 0 !important;
                font-size: 0.85rem;
            }

            .main-nav .dropdown-item:hover {
                background: rgba(255, 255, 255, 0.1) !important;
                color: white !important;
            }

            /* Chevron rotation on mobile */
            .main-nav>li.expanded>a i.fa-chevron-down,
            .main-nav .dropdown-item.expanded i {
                transform: rotate(180deg);
            }
        }

        .main-nav>li {
            height: 100%;
            display: flex;
            align-items: center;
        }

        .main-nav>li>a {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0 1rem !important;
            height: 40px;
            display: flex;
            align-items: center;
            border-radius: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
            border-bottom: none !important;
        }

        .main-nav>li:hover>a,
        .main-nav>li.active>a {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item-hover:hover>.dropdown-menu {
            display: block !important;
        }

        /* Submenus drop down below instead of to the side */
        .has-submenu:hover>.dropdown-menu {
            display: block !important;
            left: 0 !important;
            top: 100% !important;
            margin-top: 0 !important;
        }

        .theme-toggle:hover {
            color: white !important;
            transform: rotate(30deg);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .topbar .topbar-divider {
            border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
        }

        .main-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-nav li {
            position: relative;
        }

        .main-nav>li>a {
            display: block;
            padding: 0.55rem 1.25rem;
            /* Even more compact */
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.8rem;
            /* Slightly smaller font */
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.2s;
            border-bottom: 3px solid transparent;
        }

        .main-nav>li:hover>a,
        .main-nav>li.active>a {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border-bottom-color: #36b9cc;
        }

        /* Multi-level Dropdown - Collapsible Style */
        .main-nav .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 250px;
            background: white;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            margin-top: 0;
            padding: 0.5rem 0;
            z-index: 1050;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }

        .main-nav .dropdown-menu.show {
            display: block;
            max-height: 600px;
            padding: 0.5rem 0;
        }

        /* Bridge gap between top nav and dropdown */
        .main-nav .dropdown-menu::before {
            display: none;
        }

        .main-nav li:hover>.dropdown-menu {
            display: none;
            /* Disable hover, use click instead */
        }

        .main-nav .dropdown-item {
            padding: 0.75rem 1.25rem;
            font-size: 0.85rem;
            color: #4e73df;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
            cursor: pointer;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .main-nav .dropdown-item:hover {
            background-color: #f8f9fc;
            color: #224abe;
            padding-left: 1.5rem;
        }

        /* Chevron rotation for expandable items */
        .main-nav .dropdown-item i.submenu-arrow,
        .main-nav>li>a i.fa-chevron-down {
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        .main-nav>li.expanded>a i.fa-chevron-down,
        .main-nav .dropdown-item.expanded i.submenu-arrow {
            transform: rotate(180deg);
        }

        /* Nested submenus appear BELOW parent in same dropdown */
        .main-nav .dropdown-menu .dropdown-menu,
        .main-nav .dropdown-menu .sub-menu,
        .main-nav .dropdown-menu li .dropdown-menu,
        .main-nav .dropdown-menu .has-submenu>.dropdown-menu,
        .main-nav .dropdown-menu .has-submenu>.sub-menu {
            position: static !important;
            display: none !important;
            box-shadow: none !important;
            border: none !important;
            background: #f8f9fc !important;
            padding: 0.5rem 0 0.5rem 1.5rem !important;
            margin: 0 !important;
            border-radius: 0 !important;
            width: 100% !important;
            min-width: auto !important;
            left: 0 !important;
            right: auto !important;
            top: auto !important;
            bottom: auto !important;
            float: none !important;
            transform: none !important;
        }

        /* Show nested dropdowns when they have show class */
        .main-nav .dropdown-menu .dropdown-menu.show,
        .main-nav .dropdown-menu .sub-menu.show {
            display: block !important;
            max-height: none !important;
        }

        /* Third level nested menus */
        .main-nav .dropdown-menu .dropdown-menu .dropdown-menu {
            background: #eaecf4 !important;
            padding-left: 2.5rem !important;
        }

        .main-nav .dropdown-menu .dropdown-menu .dropdown-item,
        .main-nav .dropdown-menu .sub-menu .dropdown-item {
            font-size: 0.8rem;
            padding: 0.6rem 1rem;
            color: #5a5c69 !important;
        }

        .main-nav .dropdown-menu .dropdown-menu .dropdown-item:hover,
        .main-nav .dropdown-menu .sub-menu .dropdown-item:hover {
            background-color: #e2e6ea !important;
            color: #4e73df !important;
        }

        /* Ensure parent LI provides the correct context */
        .main-nav .dropdown-menu li {
            position: relative;
        }

        /* Remove bridge gap for nested menus */
        .main-nav .dropdown-menu .dropdown-menu::before {
            display: none;
        }

        /* Hide sidebar on all screens in Top Nav mode */
        .sidebar {
            display: none !important;
        }

        /* --- COMPACT UI MODE (User Requested) --- */
        /* Reduce font sizes and padding for dense information display */

        /* Form Inputs */
        .form-control {
            font-size: 0.85rem !important;
            /* Smaller text */
            padding: 0.3rem 0.5rem !important;
            /* Smaller padding */
            height: auto !important;
            /* Let height adjust to content */
            min-height: calc(1.5em + 0.5rem + 2px);
        }

        select.form-control {
            padding-right: 1.5rem !important;
            /* Space for arrow */
        }

        .form-control-sm {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.4rem !important;
            min-height: calc(1.25em + 0.4rem + 2px);
        }

        /* Buttons */
        .btn {
            font-size: 0.85rem !important;
            padding: 0.3rem 0.6rem !important;
        }

        .btn-sm {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.4rem !important;
        }

        /* Tables */
        table th,
        table td,
        .table th,
        .table td {
            padding: 0.35rem 0.5rem !important;
            /* Tighter cells */
            font-size: 0.8rem !important;
            /* Smaller table text */
        }

        /* Labels */
        label {
            font-size: 0.8rem !important;
            margin-bottom: 0.2rem !important;
        }

        /* Input Groups */
        .input-group-text {
            padding: 0.3rem 0.5rem !important;
            font-size: 0.85rem !important;
        }

        /* Adjust layout spacing */
        .card-body {
            padding: 1rem !important;
        }

        /* Notification Badge Fix */
        .badge-counter {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 0.25em 0.4em !important;
            line-height: normal !important;
        }

        /* --- TOPBAR & NAV REFINEMENTS --- */
        .top-brand-row {
            padding: 0.35rem 1.5rem !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .main-nav>li>a {
            padding: 0.6rem 1.25rem !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
            display: flex;
            align-items: center;
        }

        /* Fix for potential layout shift on dropdown open */
        body.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        #content-wrapper {
            transition: all 0.3s ease;
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
        }

        body.sidebar-toggled #content-wrapper {
            width: 100% !important;
        }

        /* Notification Dropdown Overrides for Top Nav */
        .topbar .dropdown-list {
            width: 320px !important;
            /* Slightly wider for better text display */
            padding: 0;
            border: 1px solid #e3e6f0 !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
            margin-top: 0 !important;
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            z-index: 1060 !important;
        }

        .topbar .dropdown-list .dropdown-header {
            background-color: #4e73df;
            padding: 0.5rem 1rem;
            color: white;
            text-transform: uppercase;
            font-weight: 800;
            font-size: 0.65rem;
            border-radius: 0;
        }

        .topbar .dropdown-list .dropdown-item {
            white-space: normal;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #e3e6f0;
            font-size: 0.75rem;
        }

        .topbar .dropdown-list .dropdown-item:last-child {
            border-bottom: none;
        }

        .topbar .dropdown-list .dropdown-item:active {
            background-color: #f8f9fc;
            color: #4e73df;
        }

        #notification-list {
            max-height: 350px;
            overflow-y: auto;
        }

        /* General Dropdown Overrides for Topbar */
        .topbar .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            z-index: 1060 !important;
            margin-top: 0 !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Ensure dropdowns don't expand their parents in flex containers */
        .topbar .nav-item.dropdown {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
        }

        /* --- MOBILE NAVIGATION RESPONSIVENESS --- */
        .menu-toggle {
            display: none;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-right: 0.5rem;
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-toggle:focus {
            outline: none;
            border-color: white;
        }

        @media (max-width: 991.98px) {
            .menu-toggle {
                display: block;
            }

            .nav-menu-row {
                display: none;
                /* Hidden by default on mobile */
                flex-direction: column;
                height: auto !important;
                padding: 0.5rem 1.5rem !important;
                background: #224abe !important;
                /* Solid background for readability */
                position: absolute;
                top: 50px;
                /* Below top-brand-row */
                left: 0;
                right: 0;
                z-index: 1050;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
            }

            .nav-menu-row.show {
                display: flex;
            }

            .main-nav {
                flex-direction: column;
                width: 100%;
            }

            .main-nav>li {
                width: 100%;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            .main-nav>li:last-child {
                border-bottom: none;
            }

            .main-nav>li>a {
                padding: 0.75rem 0 !important;
                justify-content: space-between;
                width: 100%;
            }

            /* Sub-menus adjustment for mobile */
            .main-nav .dropdown-menu {
                position: static !important;
                float: none;
                width: 100% !important;
                background: rgba(255, 255, 255, 0.05) !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0.5rem 0 0.5rem 1rem !important;
            }

            .main-nav .dropdown-menu .dropdown-item {
                color: rgba(255, 255, 255, 0.8) !important;
                padding: 0.5rem 0 !important;
                font-size: 0.75rem !important;
            }

            .main-nav .dropdown-menu .dropdown-item:hover {
                background: transparent !important;
                color: white !important;
            }

            /* Sub-sub-menus (3rd level) adjustment for mobile */
            .main-nav .sub-menu {
                padding-left: 1.5rem !important;
            }

            .topbar .topbar-divider {
                display: none;
                /* Hide dividers in vertical list */
            }
        }

        /* --- ULTRA-MODERN GLOBAL LOADER (Glassmorphism + Premium Pulse) --- */
        #global-loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loader-card {
            background: rgba(255, 255, 255, 0.85);
            padding: 2.5rem 3.5rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: loaderPop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        @keyframes loaderPop {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Modern Modern Triple-Dot DNA Loader */
        .dna-loader {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .dna-dot {
            width: 16px;
            height: 16px;
            background: #4e73df;
            border-radius: 50%;
            animation: dnaPulse 1.2s infinite ease-in-out;
        }

        .dna-dot:nth-child(2) {
            animation-delay: 0.2s;
            background: #224abe;
        }

        .dna-dot:nth-child(3) {
            animation-delay: 0.4s;
            background: #26d873ff;
        }

        @keyframes dnaPulse {

            0%,
            100% {
                transform: scale(0.8);
                opacity: 0.4;
            }

            50% {
                transform: scale(1.4);
                opacity: 1;
                box-shadow: 0 0 20px rgba(78, 115, 223, 0.4);
            }
        }

        .loader-brand {
            font-size: 1.1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .loader-status {
            color: #5a5c69;
            font-weight: 500;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .loader-status::after {
            content: "";
            width: 12px;
            height: 12px;
            border: 2px solid #4e73df;
            border-top-color: transparent;
            border-radius: 50%;
            display: inline-block;
            animation: miniSpin 0.6s linear infinite;
        }

        @keyframes miniSpin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

</head>

<body id="page-top">

    <div id="wrapper">


        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                @include('layouts.topbar')
                <div class="container-fluid px-3 px-md-4 py-3 py-md-4">

                    {{-- Konten Utama Halaman Diletakkan di sini --}}
                    @yield('content')

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    {{-- Hidden Logout Form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery/jquery.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    {{-- SweetAlert2 --}}
    <script src="{{ asset('sweetalert/sweetalert2.all.min.js') }}"></script>
    <script>
        // Flash Messages
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 1500
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: @json(session('error')),
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: @json(session('warning')),
            });
        @endif

        @if(session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: @json(session('info')),
            });
        @endif

        // Global Delete Confirmation
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Logout Confirmation
        $(document).on('click', '.btn-logout', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin ingin keluar?',
                text: "Anda akan keluar dari sesi ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });

        // Catatan: Auto logout saat browser ditutup ditangani oleh config session.php
        // dengan expire_on_close => true. Session cookie akan otomatis expire saat browser ditutup.
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto Uppercase for all text inputs and textareas
            $(document).on('input', 'input[type="text"]:not(.no-autoupper), textarea:not(.no-autoupper)', function () {
                let start = this.selectionStart;
                let end = this.selectionEnd;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(start, end);
            });

            // Global 419 Handler for jQuery AJAX
            $.ajaxSetup({
                error: function (jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 419) {
                        window.location.reload();
                    }
                }
            });

            // Global 419 Handler for Fetch API
            const originalFetch = window.fetch;
            window.fetch = function () {
                return originalFetch.apply(this, arguments)
                    .then(async response => {
                        if (response.status === 419) {
                            window.location.reload();
                            // Keep the promise pending so downstream .then() doesn't execute with broken state
                            return new Promise(() => { });
                        }
                        return response;
                    });
            };

            // Global Form Submission Loader
            $(document).on('submit', 'form', function (e) {
                // Ignore search/filter forms with GET method
                if ($(this).attr('method') && $(this).attr('method').toUpperCase() === 'GET') {
                    return;
                }

                // Ignore if form is invalid (standard browser validation)
                if (this.checkValidity && !this.checkValidity()) {
                    return;
                }

                // For checksheet form, specific checks if needed
                if ($(this).attr('id') === 'checksheetForm') {
                    // Form is already validated by judgment logic usually
                }

                // Show loader
                $('#global-loader').css('display', 'flex');

                // Optional: Disable submit button to prevent double clicks
                // But wait a tick so the browser can actually start the submission
                setTimeout(() => {
                    $(this).find('button[type="submit"]').prop('disabled', true);
                }, 10);
            });

            // Page Navigation Loader (when switching menus)
            $(document).on('click', 'a', function (e) {
                const href = $(this).attr('href');

                // Conditions to NOT show the loader:
                // 1. Link is empty or just '#'
                // 2. Link is a javascript action
                // 3. Link has target="_blank" (opens in new tab)
                // 4. Link is an anchor on the same page (starts with #)
                // 5. Link is for Logout (handled by form submission separately)
                // 6. Link has some specific classes to ignore
                if (!href ||
                    href === '#' ||
                    href.startsWith('javascript:') ||
                    $(this).attr('target') === '_blank' ||
                    href.startsWith('#') ||
                    $(this).hasClass('no-loader') ||
                    $(this).hasClass('btn-logout') ||
                    $(this).hasClass('dropdown-toggle')) {
                    return;
                }

                // Show loader on page transition
                $('#global-loader').css('display', 'flex');
            });

            // Session Heartbeat to prevent logout/CSRF mismatch
            setInterval(function () {
                $.get("{{ route('session.ping') }}").catch(function (err) {
                    console.warn("Session heartbeat failed");
                });
            }, 10 * 60 * 1000); // 10 minutes

            // Global AJAX Setup for CSRF and Errors
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ajaxError(function (event, xhr, settings) {
                if (xhr.status === 419) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sesi Berakhir',
                        text: 'Sesi anda telah berakhir atau token keamanan kadaluarsa. Silakan refresh halaman untuk melanjutkan.',
                        confirmButtonText: 'Refresh Halaman',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            });

            // Specific handler for downloads (PDF exports)
            // Since downloads don't trigger page navigation, we need to hide the loader after a timeout
            $(document).on('click', '.btn-download', function () {
                $('#global-loader').css('display', 'flex');
                setTimeout(function () {
                    $('#global-loader').fadeOut();
                }, 5000); // 5 seconds is usually enough for the server to generate and the browser to start downloading
            });
        });
    </script>

    {{-- Sticky Horizontal Scroll --}}
    <script src="{{ asset('js/sticky-scroll.js') }}?v={{ time() }}"></script>

    {{-- Tambahkan script lain yang dibutuhkan di sini --}}
    <div id="global-loader">
        <div class="loader-card">
            <div class="dna-loader">
                <div class="dna-dot"></div>
                <div class="dna-dot"></div>
                <div class="dna-dot"></div>
            </div>
        </div>
    </div>

    @stack('scripts')

</body>

</html>