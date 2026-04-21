<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('ipp.png') }}" type="image/png">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet" type="text/css">

    <link href="{{ asset('fonts/ibm-plex-sans.css') }}" rel="stylesheet">
    <link href="{{ asset('fonts/nunito.css') }}" rel="stylesheet">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-responsive.css') }}?v={{ time() }}" rel="stylesheet">

    <link href="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">

    <script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
    <script src="{{ asset('js/vendor/chartjs-plugin-datalabels.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.widgets.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.theme.fusion.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vendor/fusioncharts.theme.gammel.js') }}"></script>

    <style>
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
            font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif !important;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga' 1, 'calt' 1;
        }

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

        p,
        span,
        div,
        td,
        th,
        li {
            font-weight: 400;
            letter-spacing: -0.011em;
        }

        strong,
        b,
        .font-weight-bold {
            font-weight: 600;
        }

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

        table tbody tr:first-child th,
        .table tbody tr:first-child th {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }

        table th:not(table table th):not(td table th):not(.table .table th),
        .table th:not(table table th):not(td table th):not(.table .table th) {
            background-color: #4e73df !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-color: #3a5bc7 !important;
        }

        td table th,
        td table tbody th,
        td table thead th,
        td .table th,
        td .table tbody th,
        td .table thead th,
        .kimia-col table th,
        .kimia-col .table th,
        .kimia-col table tbody th,
        .kimia-col table thead th,
        table table th,
        table table tbody th,
        table table thead th,
        .table .table th,
        .table .table tbody th,
        .table .table thead th,
        table tbody td table th,
        .table tbody td table th,
        tbody td table th,
        tbody td table tbody th,
        tbody td table thead th,
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

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.08);
        }

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

        
        .table-bordered {
            border: 1.5px solid #dee2e6 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1.5px solid #dee2e6 !important;
        }

        
        .sidebar .nav-item .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-item .nav-link i {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
        }

        
        .sidebar .nav-item:hover .nav-link i {
            transform: scale(1.2) rotate(10deg);
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        
        .sidebar .nav-item .nav-link:hover {
            padding-left: 1.5rem !important;
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }

        
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

        
        .btn-logout i {
            transition: transform 0.3s ease;
        }

        .btn-logout:hover i {
            transform: translateX(10px) scale(1.2) !important;
            color: #e74a3b !important;
            text-shadow: 0 0 10px rgba(231, 74, 59, 0.4);
        }

        
        .sidebar.toggled .nav-item .nav-link i {
            transform: none !important;
        }

        
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

        
        .btn i,
        .btn-sm i {
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .btn:hover i,
        .btn-sm:hover i {
            transform: scale(1.2);
        }

        
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

        @media (max-width: 991.98px) {
            .topbar {
                flex-wrap: nowrap !important;
                padding: 0 0.5rem !important;
            }

            .topbar>div:first-child {
                flex-grow: 0 !important;
                flex-shrink: 1 !important;
                min-width: 0 !important;
                max-width: 70% !important;
            }

            .nav-menu-container:not(.show) {
                display: none !important;
            }

            .topbar>div:last-child {
                flex-shrink: 0 !important;
                margin-left: auto !important;
            }

            .topbar .sidebar-brand {
                margin-right: 0.5rem !important;
            }

            .topbar .mr-4 {
                margin-right: 0.5rem !important;
            }

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

            .main-nav .dropdown-menu,
            .main-nav .dropdown-menu .dropdown-menu,
            .main-nav .dropdown-menu .sub-menu {
                position: static !important;
                float: none !important;
                width: 100% !important;
                background: #1e40af !important;
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

            .main-nav .dropdown-menu.show,
            .main-nav .dropdown-menu .dropdown-menu.show,
            .main-nav .dropdown-menu .sub-menu.show {
                display: block !important;
            }

            .main-nav .dropdown-menu .dropdown-menu,
            .main-nav .dropdown-menu .sub-menu {
                background: #1e3a8a !important;
                padding-left: 2rem !important;
            }

            .main-nav .dropdown-menu .dropdown-menu .dropdown-menu {
                background: #172554 !important;
                padding-left: 3rem !important;
            }

            .main-nav .dropdown-item {
                color: rgba(255, 255, 255, 0.85) !important;
                padding: 0.7rem 0 !important;
                font-size: 0.85rem;
            }

            .main-nav .dropdown-item:hover {
                background: rgba(255, 255, 255, 0.1) !important;
                color: white !important;
            }

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
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.8rem;
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

        .main-nav .dropdown-item i.submenu-arrow,
        .main-nav>li>a i.fa-chevron-down {
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        .main-nav>li.expanded>a i.fa-chevron-down,
        .main-nav .dropdown-item.expanded i.submenu-arrow {
            transform: rotate(180deg);
        }

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

        .main-nav .dropdown-menu .dropdown-menu.show,
        .main-nav .dropdown-menu .sub-menu.show {
            display: block !important;
            max-height: none !important;
        }

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

        .main-nav .dropdown-menu li {
            position: relative;
        }


        .sidebar {
            display: none !important;
        }

        .form-control {
            font-size: 0.85rem !important;
            padding: 0.3rem 0.5rem !important;
            height: auto !important;
            min-height: calc(1.5em + 0.5rem + 2px);
        }

        select.form-control {
            padding-right: 1.5rem !important;
        }

        .form-control-sm {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.4rem !important;
            min-height: calc(1.25em + 0.4rem + 2px);
        }

        .btn {
            font-size: 0.85rem !important;
            padding: 0.3rem 0.6rem !important;
        }

        .btn-sm {
            font-size: 0.75rem !important;
            padding: 0.2rem 0.4rem !important;
        }

        table th,
        table td,
        .table th,
        .table td {
            padding: 0.35rem 0.5rem !important;
            font-size: 0.8rem !important;
        }

        label {
            font-size: 0.8rem !important;
            margin-bottom: 0.2rem !important;
        }

        .input-group-text {
            padding: 0.3rem 0.5rem !important;
            font-size: 0.85rem !important;
        }

        .card-body {
            padding: 1rem !important;
        }

        .badge-counter {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 0.25em 0.4em !important;
            line-height: normal !important;
        }

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

        .topbar .dropdown-list {
            width: 320px !important;
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

        .topbar .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            z-index: 1060 !important;
            margin-top: 0 !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .topbar .nav-item.dropdown {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
        }

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
                flex-direction: column;
                height: auto !important;
                padding: 0.5rem 1.5rem !important;
                background: #224abe !important;
                position: absolute;
                top: 50px;
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

            .main-nav .sub-menu {
                padding-left: 1.5rem !important;
            }

            .topbar .topbar-divider {
                display: none;
            }
        }

        #global-loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loader-card {
            background: transparent;
            padding: 2rem;
            text-align: center;
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
            background-clip: text;
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
                <div class="container-fluid px-3 px-md-4 px-xl-5 py-4">

                    @yield('content')

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery/jquery.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/js/sb-admin-2.min.js') }}"></script>

    <script src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script
        src="{{ asset('startbootstrap-sb-admin-2-gh-pages/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <script src="{{ asset('sweetalert/sweetalert2.all.min.js') }}"></script>
    <script id="layouts-admin-data" type="application/json"
        data-session-ping-url="{{ route('session.ping') }}">
        @json($unreadRejections ?? [])
    </script>
    <script>
        (function() {
            const dataEl = document.getElementById('layouts-admin-data');
            if (dataEl) {
                const unreadRejections = JSON.parse(dataEl.textContent);
                window.__LAYOUTS_ADMIN__ = {
                    sessionPingUrl: dataEl.getAttribute('data-session-ping-url'),
                    unreadRejections: unreadRejections.map(r => ({
                        ...r,
                        markReadUrl: `{{ url('notifications') }}/${r.id}/read`
                    }))
                };
            }
        })();
    </script>
    <script src="{{ asset('js/layouts/layouts-admin.js') }}"></script>

    @if(session('success'))
        <div id="session-success-data" class="d-none" data-message="{{ session('success') }}"></div>
        <script>
            (function() {
                const successEl = document.getElementById('session-success-data');
                if (successEl) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: successEl.getAttribute('data-message'),
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            })();
        </script>
    @endif

    @if(session('error'))
        <div id="session-error-data" class="d-none" data-message="{{ session('error') }}"></div>
        <script>
            (function() {
                const errorEl = document.getElementById('session-error-data');
                if (errorEl) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorEl.getAttribute('data-message'),
                    });
                }
            })();
        </script>
    @endif

    @if(session('warning'))
        <div id="session-warning-data" class="d-none" data-message="{{ session('warning') }}"></div>
        <script>
            (function() {
                const warningEl = document.getElementById('session-warning-data');
                if (warningEl) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: warningEl.getAttribute('data-message'),
                    });
                }
            })();
        </script>
    @endif

    @if(session('info'))
        <div id="session-info-data" class="d-none" data-message="{{ session('info') }}"></div>
        <script>
            (function() {
                const infoEl = document.getElementById('session-info-data');
                if (infoEl) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: infoEl.getAttribute('data-message'),
                    });
                }
            })();
        </script>
    @endif

    @if(session('maintenance_alert'))
        <div id="session-maintenance-data" class="d-none" data-message="{{ session('maintenance_alert') }}"></div>
        <script>
            (function() {
                const maintEl = document.getElementById('session-maintenance-data');
                if (maintEl) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maintenance',
                        text: maintEl.getAttribute('data-message'),
                    });
                }
            })();
        </script>
    @endif

    <script src="{{ asset('js/sticky-scroll.js') }}?v={{ time() }}"></script>

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
