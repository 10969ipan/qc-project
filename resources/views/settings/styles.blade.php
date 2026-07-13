<style>
    /* Settings Style Sidebar */
    .settings-sidebar-container {
        border-right: 1px solid #e2e8f0;
        background-color: #fff !important;
        border-radius: 0;
    }
    
    .settings-sidebar-container::-webkit-scrollbar {
        width: 6px;
    }
    .settings-sidebar-container::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .settings-sidebar-container::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 10px;
    }
    .settings-sidebar-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }

    .settings-sidebar-header {
        background-color: #f8f9fc;
        padding: 12px 16px;
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
        border-bottom: 1px solid #edf2f7;
        border-top: 1px solid #edf2f7;
    }
    
    .settings-sidebar-header:first-child {
        border-top: none;
    }

    .settings-sidebar-nav .settings-sidebar-item {
        padding: 12px 16px;
        color: #4a5568 !important;
        font-size: 0.9rem;
        border: none !important;
        border-bottom: 1px solid #edf2f7 !important;
        border-left: 4px solid transparent !important;
        border-radius: 0 !important;
        background-color: #fff !important;
        transition: all 0.2s ease;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .settings-sidebar-nav .settings-sidebar-item:hover {
        background-color: #f8fafc !important;
        color: #2d3748 !important;
    }

    .settings-sidebar-nav .settings-sidebar-item.active {
        background-color: #e6f2ff !important;
        color: #000 !important;
        border-left: 4px solid #0056b3 !important;
        font-weight: 500;
    }

    /* Modern UI Customization */
    body {
        background-color: #f4f6f9;
    }
    
    .rounded-lg { border-radius: 12px !important; }
    .rounded-top-lg { border-top-left-radius: 12px; border-top-right-radius: 12px; }
    .rounded-bottom-lg { border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
    
    .btn-sm-modern {
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.4rem 1rem;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .btn-sm-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.15) !important;}
    
    .btn-icon {
        width: 32px; height: 32px;
        display: inline-flex;
        align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .btn-icon:hover { transform: scale(1.1); }

    /* Choice Cards Styling */
    .status-choice-group {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .status-choice-item {
        position: relative;
        cursor: pointer;
    }
    
    .status-choice-item input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0; width: 0;
    }
    
    .status-card {
        padding: 12px 8px;
        border-radius: 10px;
        border: 2px solid #eaecf0;
        background: #fff;
        text-align: center;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #667085;
    }
    
    .status-choice-item:hover .status-card {
        border-color: #d0d5dd;
        background: #f9fafb;
    }
    
    .status-choice-item input:checked + .status-card.choice-active {
        border-color: #1cc88a;
        background: #f0fff4;
        color: #1cc88a;
        box-shadow: 0 4px 10px rgba(28, 200, 138, 0.1);
    }
    
    .status-choice-item input:checked + .status-card.choice-maint {
        border-color: #f6c23e;
        background: #fffdf0;
        color: #f6c23e;
        box-shadow: 0 4px 10px rgba(246, 194, 62, 0.1);
    }
    
    .status-choice-item input:checked + .status-card.choice-hidden {
        border-color: #e74a3b;
        background: #fff5f5;
        color: #e74a3b;
        box-shadow: 0 4px 10px rgba(231, 74, 59, 0.1);
    }

    .status-card i {
        font-size: 1.2rem;
        margin-bottom: 6px;
    }
    
    .status-card span {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Refined Detail Card */
    .detail-input-group {
        background: #fff;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid #eaecf0;
        margin-bottom: 1.25rem;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }
    
    .detail-input-group:focus-within {
        border-color: #4e73df;
        box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
    }
    
    .premium-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: block;
    }

    .premium-input {
        border: none !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        font-weight: 600;
        color: #1d2939;
        font-size: 0.95rem;
        height: auto !important;
    }

    .premium-textarea {
        border: none !important;
        resize: none;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        font-weight: 500;
        color: #344054;
        font-size: 0.85rem;
    }

    /* Premium Input Group for Role Selector */
    .premium-input-group {
        position: relative;
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        height: 44px;
    }

    .premium-input-group:hover {
        border-color: #d0d0d0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .premium-input-group:focus-within {
        border-color: #2d3436;
        box-shadow: 0 0 0 4px rgba(45, 52, 54, 0.05);
    }

    .premium-input-group .input-icon {
        position: absolute;
        left: 16px;
        color: #4a4a4a;
        font-size: 0.95rem;
        pointer-events: none;
        z-index: 2;
        opacity: 0.8;
    }

    .premium-input-group .premium-input {
        padding-left: 44px !important;
        padding-right: 32px !important;
        width: 100%;
        cursor: pointer;
        background: transparent !important;
        border: none !important;
        font-weight: 600;
        color: #2d3436;
        font-size: 0.9rem;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    
    .premium-input-group::after {
        content: '\f078';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 14px;
        font-size: 0.65rem;
        color: #a0a0a0;
        pointer-events: none;
        transition: transform 0.2s;
    }
    
    .premium-input-group:focus-within::after {
        transform: rotate(180deg);
        color: #2d3436;
    }

    .bg-light-faint {
        background-color: rgba(248, 249, 252, 0.6);
    }

    .font-weight-medium {
        font-weight: 500;
    }

    /* Animations */
    @keyframes fadeInCustom {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-quick {
        animation: fadeInCustom 0.3s ease-out forwards;
    }
    
    /* Sidebar Minimalist Nav */
    .custom-nav-pills-minimal .nav-link {
        border-radius: 10px;
        padding: 0.85rem 1.25rem;
        margin-bottom: 0.25rem;
        color: #6e707e;
        transition: all 0.2s ease;
        background-color: transparent;
        border: none;
    }
    .custom-nav-pills-minimal .nav-link:hover {
        background-color: rgba(78, 115, 223, 0.05);
        color: #4e73df;
    }
    .custom-nav-pills-minimal .nav-link.active {
        background-color: #fff;
        color: #4e73df;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .custom-nav-pills-minimal .nav-link i {
        font-size: 1.1rem;
        width: 20px;
        transition: color 0.2s ease;
    }
    .custom-nav-pills-minimal .nav-link.active i {
        color: #4e73df !important;
    }
    
    .font-weight-medium {
        font-weight: 500;
    }

    /* ============================================
       ACCORDION MODULE CARD â€” Permission Layout
       ============================================ */

    /* Wrapper card for each module */
    .perm-module-card {
        border: 1px solid #eaecf0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s;
    }
    .perm-module-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
    }

    /* Card Header (clickable to toggle collapse) */
    .perm-module-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        background: #fcfcfd;
        border-bottom: 1px solid #eaecf0;
        cursor: pointer;
        user-select: none;
        transition: background 0.18s;
    }
    .perm-module-card-header:hover {
        background: #f5f7fa;
    }

    /* Module icon circle */
    .perm-module-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #f0f2f8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        color: #4e73df;
        flex-shrink: 0;
        transition: background 0.2s;
    }
    .module-master-toggle.is-active ~ .d-flex .perm-module-icon,
    .perm-module-card:not(.module-is-off) .perm-module-icon {
        background: #eef0f8;
    }

    /* Module title */
    .perm-module-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1d2939;
        line-height: 1.2;
    }
    .perm-module-meta {
        font-size: 0.7rem;
        color: #98a2b3;
        font-weight: 500;
        margin-top: 2px;
    }

    /* Status badge in header */
    .perm-module-status-badge {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 0.25em 0.75em;
        border-radius: 20px;
        background: rgba(28,200,138,0.1);
        color: #1cc88a;
        border: 1px solid rgba(28,200,138,0.2);
        white-space: nowrap;
    }
    .perm-module-status-badge.is-off {
        background: rgba(231,74,59,0.08);
        color: #e74a3b;
        border-color: rgba(231,74,59,0.15);
    }

    /* Chevron rotation */
    .perm-collapse-arrow {
        font-size: 0.75rem;
        color: #98a2b3;
        transition: transform 0.25s ease;
    }
    .perm-module-card-header[aria-expanded="true"] .perm-collapse-arrow {
        transform: rotate(180deg);
    }

    /* Card Body (collapsible content) */
    .perm-module-card-body {
        padding: 1.25rem 1.25rem 0.5rem;
        background: #fff;
    }

    /* Module disabled visual state (power OFF) */
    .perm-module-card.module-is-off .perm-module-card-body .permission-row-item {
        opacity: 0.38;
        pointer-events: none;
    }
    .perm-module-card.module-is-off .perm-module-card-header {
        background: #fafafa;
    }
    .perm-module-card.module-is-off .perm-module-icon {
        background: #f0f0f0;
        color: #bbb;
    }
    .perm-module-card.module-is-off .perm-module-title {
        color: #adb5bd;
    }

    /* Power toggle button */
    .module-master-toggle {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background: #f9fafb;
        color: #98a2b3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
        cursor: pointer;
        transition: all 0.2s;
        pointer-events: auto !important;
    }
    .module-master-toggle:hover {
        border-color: #adb5bd;
        color: #495057;
        background: #f0f0f0;
    }
    /* ON state */
    .module-master-toggle.is-active {
        background: #e6f9f3;
        border-color: #1cc88a;
        color: #1cc88a;
    }
    /* OFF state */
    .module-master-toggle.is-inactive {
        background: #fff5f5;
        border-color: #f5c6c6;
        color: #e74a3b;
    }

    /* Permission grid header */
    .permission-grid-header {
        display: grid;
        grid-template-columns: 1fr repeat(6, 80px);
        gap: 12px;
        padding: 0.6rem 1rem;
        background: #f4f6fb;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border: 1px solid #eaecf0;
    }
    .permission-grid-header > div {
        font-size: 0.65rem;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
    }
    .permission-grid-header > div:first-child {
        text-align: left;
    }

    /* Permission row (parent and child) */
    .permission-row-item {
        display: grid;
        grid-template-columns: 1fr repeat(6, 80px);
        gap: 12px;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #f2f4f7;
        align-items: center;
        transition: background 0.15s;
    }
    .permission-row-item:last-child {
        border-bottom: none;
    }
    .permission-row-item:hover {
        background: #fafbfc;
    }

    /* Parent row (level 0) inside card body */
    .parent-module-row {
        background: #f8f9fd;
        border-bottom: 1px solid #e9ecf3 !important;
        border-radius: 4px;
    }
    .parent-module-row .permission-name {
        font-weight: 700;
        color: #2d3a4e;
        font-size: 0.85rem;
    }

    /* Sub-module row */
    .sub-module-row .permission-name {
        font-weight: 500;
        color: #475467;
        font-size: 0.82rem;
    }

    .permission-name {
        font-weight: 600;
        color: #344054;
        font-size: 0.9rem;
    }
    .permission-row-item .custom-control {
        display: inline-block;
        justify-self: center;
    }

    /* Cascade indicator badge on parent row */
    .cascade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: #eef0f8;
        color: #4e73df;
        font-size: 0.6rem;
        flex-shrink: 0;
        cursor: default;
    }

    /* Sub-module count badge (for intermediate nodes) */
    .sub-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #eaecf0;
        color: #667085;
        font-size: 0.6rem;
        font-weight: 700;
        padding: 0 4px;
    }

    /* Sub-module toggle button (inside a row, for intermediate nodes) */
    .sub-module-toggle {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        background: #f9fafb;
        color: #98a2b3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        flex-shrink: 0;
        cursor: pointer;
        transition: all 0.18s;
        padding: 0;
    }
    .sub-module-toggle:hover {
        border-color: #adb5bd;
        color: #495057;
        background: #efefef;
    }
    .sub-module-toggle.sub-is-active {
        background: #e6f9f3;
        border-color: #1cc88a;
        color: #1cc88a;
    }
    .sub-module-toggle.sub-is-inactive {
        background: #fff5f5;
        border-color: #f5c6c6;
        color: #e74a3b;
    }
    /* Dim rows that belong to a sub-toggled-off group */
    .permission-row-item.sub-group-disabled {
        opacity: 0.38;
        pointer-events: none;
    }
    /* But keep the sub-toggle itself clickable */
    .permission-row-item.sub-group-disabled .sub-module-toggle {
        pointer-events: auto !important;
        opacity: 1 !important;
    }

    /* Accordion card entry animation */
    @keyframes cardSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0);   }
    }
    .perm-module-card {
        animation: cardSlideIn 0.3s ease forwards;
    }
    .perm-module-card:nth-child(1) { animation-delay: 0.02s; }
    .perm-module-card:nth-child(2) { animation-delay: 0.06s; }
    .perm-module-card:nth-child(3) { animation-delay: 0.10s; }
    .perm-module-card:nth-child(4) { animation-delay: 0.14s; }
    .perm-module-card:nth-child(5) { animation-delay: 0.18s; }
    
    /* Icon utilities */
    .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
    .icon-circle {
        width: 40px; height: 40px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .icon-square {
        width: 36px; height: 36px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .avatar-modern {
        width: 44px; height: 44px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; font-weight: 800;
        background: #ffffff;
        color: #495057;
        border: 2px solid #f8f9fa;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        flex-shrink: 0;
        text-transform: uppercase;
        transition: all 0.2s ease;
    }
    .avatar-modern:hover {
        transform: scale(1.05);
        border-color: #e9ecef;
        background-color: #f8f9fa;
    }
    
    /* Software Badge Colors */
    .bg-soft-primary { background-color: rgba(78, 115, 223, 0.15); }
    .bg-soft-success { background-color: rgba(28, 200, 138, 0.15); }
    .bg-soft-info { background-color: rgba(54, 185, 204, 0.15); }
    .bg-soft-warning { background-color: rgba(246, 194, 62, 0.15); }
    
    .badge-soft-primary { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .badge-soft-success { background-color: rgba(28, 200, 138, 0.1); color: #1cc88a; }
    .badge-soft-info { background-color: rgba(54, 185, 204, 0.1); color: #36b9cc; }
    .badge-soft-warning { background-color: rgba(246, 194, 62, 0.1); color: #f6c23e; }
    .pill-badge { font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 1rem; font-weight: 700;}
    
    /* Alert Soft */
    .alert-soft-success {
        background-color: rgba(28, 200, 138, 0.08);
        border-left: 4px solid #1cc88a !important;
    }
    
    /* Custom Tables */
    .custom-table th { font-size: 0.8rem; letter-spacing: 0.5px; background-color: transparent !important; color: #858796 !important; border-top: none; }
    .custom-table td { font-size: 0.85rem; padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
    .permission-table td { padding: 0.75rem 0.5rem; }
    
    /* Custom Switch Colors */
    .custom-switch-primary .custom-control-input:checked ~ .custom-control-label::before { background-color: #4e73df; border-color: #4e73df; }
    .custom-switch-success .custom-control-input:checked ~ .custom-control-label::before { background-color: #1cc88a; border-color: #1cc88a; }
    .custom-switch-info .custom-control-input:checked ~ .custom-control-label::before { background-color: #36b9cc; border-color: #36b9cc; }
    .custom-switch-warning .custom-control-input:checked ~ .custom-control-label::before { background-color: #f6c23e; border-color: #f6c23e; }
    .custom-switch-secondary .custom-control-input:checked ~ .custom-control-label::before { background-color: #858796; border-color: #858796; }
    
    /* Animations */
    .slide-in {
        animation: slideInUp 0.4s ease forwards;
        opacity: 0;
        transform: translateY(15px);
    }
    @keyframes slideInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Sortable Menu */
    .sortable-menu { padding: 0.5rem; }
    .menu-item { border: none !important; margin-bottom: 0.5rem; border-radius: 8px !important; transition: all 0.2s; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .menu-item.selected { border-left: 4px solid #4e73df !important; background-color: #f8f9fc; }
    .menu-item:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: grab; }
    .drag-handle { cursor: grab; display: inline-flex; padding: 0.5rem; margin: -0.5rem; }
    
    /* Config Card Focus */
    .border-left-warning { border-left: 4px solid #f6c23e !important; }
    
    /* Minimalist Professional Table Override Global */
    .table.table-minimalist {
        border-collapse: collapse;
        border: none !important;
    }
    /* Ultra-Specific Override for White Header - Refined */
    .table-minimalist thead th,
    .table-minimalist thead td,
    .table-minimalist thead tr,
    table.table-minimalist thead th,
    table.table-minimalist thead tr,
    body .table.table-minimalist thead th,
    body .table.table-minimalist thead tr,
    #users table.table-minimalist thead th,
    #users table.table-minimalist thead tr,
    #activity-logs table.table-minimalist thead th,
    #activity-logs table.table-minimalist thead tr {
        background-color: #ffffff !important;
        background: #ffffff !important;
        color: #2e3b4e !important;
        border: none !important;
        border-bottom: 2px solid #f8f9fa !important;
        padding: 1.25rem 0.75rem !important;
        font-weight: 700 !important;
        font-size: 0.72rem;
        letter-spacing: 0.05rem;
        text-transform: uppercase;
    }
    
    .table.table-minimalist tbody td {
        background-color: #ffffff !important;
        border: none !important;
        border-top: 1px solid #f8f9fa !important;
        vertical-align: middle;
        color: #4a4a4a;
        padding: 1.1rem 0.75rem !important;
    }
    .table-row-hover {
        transition: background-color 0.2s ease;
    }
    .table-row-hover:hover {
        background-color: #fdfdfe !important;
    }
    .table-row-hover:hover td {
        background-color: transparent !important;
    }
    
    /* Buttons Grayscale */
    .btn-dark {
        background-color: #2e3b4e;
        border-color: #2e3b4e;
        color: #fff;
    }
    .btn-dark:hover {
        background-color: #1a2533;
        border-color: #1a2533;
        transform: translateY(-2px);
    }
    
    /* Modern "Flat & Smooth" Toggle Switch */
    .custom-switch-md {
        padding-left: 2.8rem;
    }
    .custom-switch-md .custom-control-label::before {
        height: 1.4rem;
        width: 2.6rem;
        border-radius: 100px;
        background-color: #eaedf2;
        border: none !important;
        top: 0;
        left: -2.8rem;
        transition: background-color 0.25s ease;
    }
    .custom-switch-md .custom-control-label::after {
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #fff;
        top: 0.2rem;
        left: calc(-2.8rem + 0.2rem);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.25s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.2rem) !important;
    }
    .custom-switch-success .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #2ec4b6 !important; /* Modern Teal-Green */
    }
    
    /* Remove any lingering blue text/icons */
    .text-primary { color: #2d3436 !important; }
    .btn-primary { background-color: #2d3436; border-color: #2d3436; }
    .btn-primary:hover { background-color: #000; border-color: #000; }
    .icon-square.bg-soft-primary { background-color: #f1f3f7; color: #636e72; }
    .fa-circle-notch.text-primary { color: #b2bec3 !important; }
    
    .btn-dark {
        background-color: #2d3436;
        border-color: #2d3436;
    }

    /* Password Toggle Styling */
    .password-field-wrapper {
        position: relative;
    }
    .password-toggle-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #a0a0a0;
        transition: all 0.2s ease;
        z-index: 10;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .password-toggle-icon:hover {
        color: #2d3436;
        background-color: rgba(0,0,0,0.05);
    }

    /* Global Override to prevent forced uppercase */
    .custom-nav-pills-minimal .nav-link,
    .modal-title,
    .form-group label,
    .btn,
    .table th,
    input, 
    select,
    textarea {
        text-transform: none !important;
    }
</style>

