<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KB Supplier' }} - KB Supplier</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('Karunia Berkah.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Flatpickr -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
            --primary-gradient: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #3674ab 100%);
            --sidebar-bg: #0f1b2d;
            --sidebar-hover: rgba(255,255,255,0.08);
            --sidebar-active: rgba(54, 116, 171, 0.3);
            --sidebar-text: rgba(255,255,255,0.7);
            --sidebar-text-active: #ffffff;
            --body-bg: #f0f2f5;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --card-shadow-hover: 0 10px 25px rgba(0,0,0,0.1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--body-bg);
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1050;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }

        /* Collapsed Sidebar */
        body.sidebar-collapsed .sidebar { width: 80px; }
        body.sidebar-collapsed .sidebar .sidebar-brand-text,
        body.sidebar-collapsed .sidebar .sidebar-section,
        body.sidebar-collapsed .sidebar .sidebar-nav .nav-link span,
        body.sidebar-collapsed .sidebar .sidebar-nav .nav-link::after,
        body.sidebar-collapsed .sidebar .sidebar-nav .nav-link .badge,
        body.sidebar-collapsed .sidebar .sidebar-brand-sub { display: none; }
        body.sidebar-collapsed .sidebar .sidebar-brand { justify-content: center; padding: 16px 0; }
        body.sidebar-collapsed .sidebar .sidebar-nav .nav-link { justify-content: center; padding: 10px 0; }
        body.sidebar-collapsed .sidebar .sidebar-nav .nav-link i { margin: 0; font-size: 20px; }
        body.sidebar-collapsed .main-content { margin-left: 80px; }
        body.sidebar-collapsed .topbar { left: 80px; }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .sidebar-brand {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 8px;
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-brand-text {
            color: white;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: -0.3px;
        }

        .sidebar-brand-sub {
            color: rgba(255,255,255,0.4);
            font-size: 11px;
            font-weight: 400;
        }

        .sidebar-section {
            padding: 12px 16px 6px;
            font-size: 10px;
            font-weight: 700;
            color: rgba(255,255,255,0.3);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0 8px;
            margin: 0;
        }

        .sidebar-nav .nav-item { margin: 2px 0; }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 13.5px;
            font-weight: 500;
            position: relative;
        }

        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-active);
        }

        .sidebar-nav .nav-link.active {
            background: var(--sidebar-active);
            color: var(--sidebar-text-active);
        }

        .sidebar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: #3674ab;
            border-radius: 0 3px 3px 0;
        }

        .sidebar-nav .nav-link i {
            font-size: 18px;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link .badge {
            margin-left: auto;
            font-size: 10px;
            padding: 3px 7px;
        }

        /* Submenu */
        .sidebar-submenu {
            list-style: none;
            padding: 4px 0 4px 36px;
            margin: 0;
        }

        .sidebar-submenu .nav-link {
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 400;
        }

        .sidebar-submenu .nav-link::before {
            display: none !important;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid #e5e7eb;
            z-index: 1040;
            display: flex;
            align-items: center;
            padding: 0 24px;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .topbar-toggle {
            background: none;
            border: none;
            font-size: 20px;
            color: #6b7280;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .topbar-toggle:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-notification {
            position: relative;
            background: none;
            border: none;
            font-size: 20px;
            color: #6b7280;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .topbar-notification:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .topbar-notification .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            border-radius: 50%;
            font-size: 10px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .topbar-user:hover {
            background: #f3f4f6;
        }

        .topbar-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .topbar-user-info {
            line-height: 1.3;
        }

        .topbar-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }

        .topbar-user-role {
            font-size: 11px;
            color: #6b7280;
            text-transform: capitalize;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 24px;
            min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .page-header .breadcrumb {
            margin: 4px 0 0;
            padding: 0;
            font-size: 13px;
        }

        .page-header .breadcrumb-item a {
            color: #6b7280;
            text-decoration: none;
        }

        .page-header .breadcrumb-item.active {
            color: #3674ab;
        }

        /* ===== CARDS ===== */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f3f4f6;
            padding: 16px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-body {
            padding: 20px;
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }

        .stat-card .stat-icon {
            font-size: 32px;
            opacity: 0.8;
            margin-bottom: 8px;
        }

        .stat-card .stat-value {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .stat-card .stat-label {
            font-size: 13px;
            opacity: 0.85;
        }

        .stat-card-primary { background: linear-gradient(135deg, #1e3a5f, #3674ab); }
        .stat-card-success { background: linear-gradient(135deg, #065f46, #059669); }
        .stat-card-warning { background: linear-gradient(135deg, #92400e, #d97706); }
        .stat-card-danger { background: linear-gradient(135deg, #991b1b, #dc2626); }
        .stat-card-info { background: linear-gradient(135deg, #1e40af, #3b82f6); }

        /* ===== TABLE ===== */
        .table { font-size: 13.5px; }
        .table th {
            font-weight: 600;
            color: #374151;
            border-bottom-width: 1px;
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table td {
            padding: 12px 16px;
            vertical-align: middle;
            color: #4b5563;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(54, 116, 171, 0.04);
        }

        /* ===== BUTTONS ===== */
        .btn {
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3a5f, #3674ab);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #162d4a, #2d5a87);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.4);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .btn-action {
            padding: 5px 8px;
            font-size: 14px;
            line-height: 1;
            border-radius: 6px;
        }

        /* ===== FORMS ===== */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            font-size: 13.5px;
            padding: 9px 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3674ab;
            box-shadow: 0 0 0 3px rgba(54, 116, 171, 0.15);
        }

        .form-text {
            font-size: 12px;
            color: #9ca3af;
        }

        /* ===== BADGES ===== */
        .badge {
            font-weight: 600;
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 6px;
        }

        /* ===== SIDEBAR OVERLAY (Mobile) ===== */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1045;
            display: none;
            backdrop-filter: blur(2px);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay.show {
                display: block;
            }
            .topbar {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in-up {
            animation: fadeInUp 0.4s ease forwards;
        }

        .fade-in-up:nth-child(1) { animation-delay: 0.05s; }
        .fade-in-up:nth-child(2) { animation-delay: 0.1s; }
        .fade-in-up:nth-child(3) { animation-delay: 0.15s; }
        .fade-in-up:nth-child(4) { animation-delay: 0.2s; }

        /* Select2 Override */
        .select2-container--bootstrap-5 .select2-selection {
            font-size: 13.5px;
            padding: 5px 10px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            min-height: 40px;
        }

        /* DataTable Override */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 6px 12px;
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 4px 8px;
            font-size: 13px;
        }

        /* Toast position */
        .toast-container {
            z-index: 9999;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon" style="background: transparent; padding: 0;">
                <img src="{{ asset('Karunia Berkah.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div>
                <div class="sidebar-brand-text">KB Supplier</div>
                <div class="sidebar-brand-sub">Supply Management</div>
            </div>
        </div>

        <div class="sidebar-section">Menu Utama</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        @if(auth()->user()->isSuperadmin())
        <div class="sidebar-section">Master Data</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    <i class="bi bi-box-fill"></i>
                    <span>Item / Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill"></i>
                    <span>Client</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Users</span>
                </a>
            </li>
        </ul>
        @endif

        <div class="sidebar-section">Transaksi</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-fill"></i>
                    <span>Purchase Order</span>
                    @php
                        $pendingCount = \App\Models\PurchaseOrder::where('status', 'pending_approval')->count();
                    @endphp
                    @if($pendingCount > 0 && auth()->user()->isSuperadmin())
                        <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('delivery-notes.index') }}" class="nav-link {{ request()->routeIs('delivery-notes.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span>Surat Jalan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Invoice</span>
                    @php
                        $overdueCount = \App\Models\Invoice::where('status', 'overdue')->count();
                    @endphp
                    @if($overdueCount > 0)
                        <span class="badge bg-danger">{{ $overdueCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-fill"></i>
                    <span>Pembayaran</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Keuangan</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('modals.index') }}" class="nav-link {{ request()->routeIs('modals.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet-fill"></i>
                    <span>Modal</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Pengeluaran</span>
                </a>
            </li>
            @if(auth()->user()->isSuperadmin())
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Laporan</span>
                </a>
            </li>
            @endif
        </ul>
    </aside>

    <!-- Top Navbar -->
    <nav class="topbar">
        <button class="topbar-toggle" id="sidebarToggle" type="button">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-right">
            <div class="dropdown">
                <button class="topbar-notification" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
                    <i class="bi bi-bell"></i>
                    @php
                        $notifCount = ($pendingCount ?? 0) + ($overdueCount ?? 0);
                    @endphp
                    @if($notifCount > 0)
                        <span class="notification-badge">{{ $notifCount }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                    <li class="dropdown-header">Notifikasi</li>
                    @if(($pendingCount ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('purchase-orders.index', ['status' => 'pending_approval']) }}">
                                <span class="badge bg-warning text-dark"><i class="bi bi-cart"></i></span>
                                <div>
                                    <div class="small fw-bold">{{ $pendingCount }} PO Pending</div>
                                    <div class="small text-muted">Menunggu approval</div>
                                </div>
                            </a>
                        </li>
                    @endif
                    @if(($overdueCount ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('invoices.index', ['status' => 'overdue']) }}">
                                <span class="badge bg-danger"><i class="bi bi-receipt"></i></span>
                                <div>
                                    <div class="small fw-bold">{{ $overdueCount }} Invoice Overdue</div>
                                    <div class="small text-muted">Jatuh tempo terlewat</div>
                                </div>
                            </a>
                        </li>
                    @endif
                    @if(($pendingCount ?? 0) == 0 && ($overdueCount ?? 0) == 0)
                        <li><div class="dropdown-item text-center text-muted small py-3">Tidak ada notifikasi</div></li>
                    @endif
                </ul>
            </div>

            <div class="dropdown">
                <div class="topbar-user" data-bs-toggle="dropdown">
                    <div class="topbar-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="topbar-user-info d-none d-sm-block">
                        <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                        <div class="topbar-user-role">{{ auth()->user()->role }}</div>
                    </div>
                    <i class="bi bi-chevron-down ms-1" style="font-size: 12px; color: #9ca3af;"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 180px;">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2"></i>Profil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');

        toggle.addEventListener('click', () => {
            if (window.innerWidth >= 992) {
                document.body.classList.toggle('sidebar-collapsed');
            } else {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Global DataTable defaults
        $.extend($.fn.dataTable.defaults, {
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                paginate: { previous: "‹", next: "›" }
            },
            pageLength: 25,
            responsive: true,
        });

        // Global Select2 defaults
        $.fn.select2.defaults.set('theme', 'bootstrap-5');
        $.fn.select2.defaults.set('width', '100%');

        // Initialize Flatpickr for date inputs
        document.querySelectorAll('.datepicker').forEach(el => {
            flatpickr(el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y' });
        });

        // CSRF for AJAX
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        // Confirm Delete
        function confirmDelete(formId) {
            Swal.fire({
                title: 'Yakin hapus data ini?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
