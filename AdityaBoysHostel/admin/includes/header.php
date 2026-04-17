<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - Aditya Boys Hostel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --navbar-height: 60px;
            --primary-color: #4f46e5;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #3730a3 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .sidebar-title {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin: 0;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .sidebar-menu-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: white;
        }

        .sidebar-menu-item i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        .sidebar-menu-item span {
            font-weight: 500;
        }

        .sidebar.collapsed .sidebar-menu-item span {
            display: none;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Navbar */
        .top-navbar {
            height: var(--navbar-height);
            background: white;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-search {
            position: relative;
        }

        .navbar-search input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            background: var(--light-color);
            width: 250px;
        }

        .navbar-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-user img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .navbar-user-name {
            font-weight: 500;
            color: var(--dark-color);
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 2rem;
            background: var(--light-color);
        }

        /* Responsive */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .navbar-search input {
                width: 150px;
            }

            .navbar-user-name {
                display: none;
            }
        }

        /* Status Badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: var(--success-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .badge-danger {
            background: var(--danger-color);
            color: white;
        }

        .badge-info {
            background: var(--info-color);
            color: white;
        }

        /* Alert Messages */
        .alert {
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #d1fae5;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        .alert-info {
            background: #cfe2ff;
            border-color: #b8daff;
            color: #084298;
        }

        /* Tables */
        .modern-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .modern-table table {
            width: 100%;
            margin-bottom: 0;
        }

        .modern-table thead {
            background: var(--light-color);
        }

        .modern-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid var(--border-color);
        }

        .modern-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .modern-table tbody tr:hover {
            background: var(--light-color);
        }

        /* Forms */
        .modern-form {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
            outline: 0;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-info {
            background: var(--info-color);
            color: white;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Dashboard Cards */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }

        .stat-card-icon.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card-icon.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stat-card-icon.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-card-icon.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-card-icon.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            line-height: 1;
        }

        .stat-card-label {
            color: var(--secondary-color);
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/logo.svg" alt="Logo" class="sidebar-logo">
            <h3 class="sidebar-title">Aditya Hostel</h3>
            <p class="sidebar-subtitle">Admin Panel</p>
        </div>
        
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_students.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_students.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>Students</span>
            </a>
            <a href="manage_rooms.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_rooms.php' ? 'active' : ''; ?>">
                <i class="bi bi-house-door"></i>
                <span>Rooms</span>
            </a>
            <a href="manage_fees.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_fees.php' ? 'active' : ''; ?>">
                <i class="bi bi-currency-rupee"></i>
                <span>Fees</span>
            </a>
            <a href="payment_verification.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'payment_verification.php' ? 'active' : ''; ?>">
                <i class="bi bi-credit-card"></i>
                <span>Payments</span>
            </a>
            <a href="payment_history.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'payment_history.php' ? 'active' : ''; ?>">
                <i class="bi bi-clock-history"></i>
                <span>History</span>
            </a>
            <a href="manage_complaints.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_complaints.php' ? 'active' : ''; ?>">
                <i class="bi bi-chat-dots"></i>
                <span>Complaints</span>
            </a>
            <a href="reports.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark"></i>
                <span>Reports</span>
            </a>
            <a href="settings.php" class="sidebar-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
            <a href="../logout.php" class="sidebar-menu-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <a href="dashboard.php" class="navbar-brand">
                <i class="bi bi-speedometer2"></i>
                Admin Dashboard
            </a>
            
            <div class="navbar-actions">
                <div class="navbar-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Search..." id="searchInput">
                </div>
                
                <div class="navbar-user">
                    <span class="navbar-user-name"><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                    <img src="../assets/default_avatar.svg" alt="User" class="navbar-user-avatar">
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
