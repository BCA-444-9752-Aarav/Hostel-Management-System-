<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'generate_monthly_fees') {
        $month = $_POST['month'] ?? '';
        $year = $_POST['year'] ?? '';
        $amount = $_POST['amount'] ?? '';
        
        if ($month && $year && $amount) {
            try {
                // Get all approved students
                $stmt = $conn->prepare("SELECT id FROM students WHERE status = 'approved'");
                $stmt->execute();
                $students = $stmt->get_result();
                
                $generated_count = 0;
                $skipped_count = 0;
                
                while ($student = $students->fetch_assoc()) {
                    // Check if fee already exists
                    $check_stmt = $conn->prepare("SELECT id FROM fees WHERE student_id = ? AND month = ? AND year = ?");
                    $check_stmt->bind_param("isi", $student['id'], $month, $year);
                    $check_stmt->execute();
                    $existing = $check_stmt->get_result();
                    
                    if ($existing->num_rows == 0) {
                        // Insert new fee record
                        $insert_stmt = $conn->prepare("INSERT INTO fees (student_id, month, year, amount, status) VALUES (?, ?, ?, ?, 'unpaid')");
                        $insert_stmt->bind_param("isid", $student['id'], $month, $year, $amount);
                        $insert_stmt->execute();
                        $generated_count++;
                    } else {
                        $skipped_count++;
                    }
                }
                
                $success = "Successfully generated {$generated_count} fee records. {$skipped_count} records were skipped (already exist).";
                
            } catch (Exception $e) {
                $error = "Error generating fees: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    }
    
    if ($action == 'update_fee_status') {
        $fee_id = $_POST['fee_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $paid_amount = $_POST['paid_amount'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $transaction_id = $_POST['transaction_id'] ?? '';
        
        if ($fee_id && $status) {
            try {
                $payment_date = ($status == 'paid') ? date('Y-m-d') : null;
                
                $stmt = $conn->prepare("UPDATE fees SET status = ?, paid_amount = ?, payment_date = ?, payment_method = ?, transaction_id = ? WHERE id = ?");
                $stmt->bind_param("sdsssi", $status, $paid_amount, $payment_date, $payment_method, $transaction_id, $fee_id);
                $stmt->execute();
                
                $success = "Fee status updated successfully!";
                
            } catch (Exception $e) {
                $error = "Error updating fee status: " . $e->getMessage();
            }
        } else {
            $error = "Missing required information.";
        }
    }
    
    if ($action == 'delete_fee') {
        $fee_id = $_POST['fee_id'] ?? '';
        
        if ($fee_id) {
            try {
                $stmt = $conn->prepare("DELETE FROM fees WHERE id = ?");
                $stmt->bind_param("i", $fee_id);
                $stmt->execute();
                
                $success = "Fee record deleted successfully!";
                
            } catch (Exception $e) {
                $error = "Error deleting fee record: " . $e->getMessage();
            }
        }
    }
}

// Get fee statistics
$total_students = 0;
$total_pending = 0;
$total_paid = 0;
$total_partial = 0;
$current_month_revenue = 0;

try {
    // Total approved students
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE status = 'approved'");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_students = $result->fetch_assoc()['count'];
    
    // Fee status counts for current month
    $current_month = date('F');
    $current_year = date('Y');
    
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count, SUM(paid_amount) as total FROM fees WHERE month = ? AND year = ? GROUP BY status");
    $stmt->bind_param("si", $current_month, $current_year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        switch ($row['status']) {
            case 'unpaid':
                $total_pending = $row['count'];
                break;
            case 'paid':
                $total_paid = $row['count'];
                $current_month_revenue = $row['total'];
                break;
            case 'partial':
                $total_partial = $row['count'];
                break;
        }
    }
    
} catch (Exception $e) {
    $error = "Error fetching statistics: " . $e->getMessage();
}

// Handle search and filters
$search = $_GET['search'] ?? '';
$month_filter = $_GET['month'] ?? '';
$year_filter = $_GET['year'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$query = "SELECT f.*, s.full_name, s.email, s.mobile FROM fees f JOIN students s ON f.student_id = s.id WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (s.full_name LIKE ? OR s.email LIKE ? OR s.mobile LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($month_filter) {
    $query .= " AND f.month = ?";
    $params[] = $month_filter;
    $types .= "s";
}

if ($year_filter) {
    $query .= " AND f.year = ?";
    $params[] = $year_filter;
    $types .= "i";
}

if ($status_filter) {
    $query .= " AND f.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY f.created_at DESC";

// Execute query
$fees = [];
try {
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $fees = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching fees: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Management - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .fee-management-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .fee-table {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .fee-table th {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-weight: 600;
            padding: 15px;
        }
        
        .fee-table td {
            border: none;
            color: white;
            padding: 15px;
            vertical-align: middle;
        }
        
        .fee-table tbody tr {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .fee-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #28a745;
            color: white;
        }
        
        .status-unpaid {
            background: #dc3545;
            color: white;
        }
        
        .status-empty {
            background: #6c757d;
            color: white;
        }
        
        .status-partial {
            background: #ffc107;
            color: #212529;
        }
        
        /* Light theme styles (default) */
        .fee-table {
            background: white;
            color: #333;
        }
        
        .fee-table th {
            background: #f8f9fa;
            color: #333;
            border-bottom: 1px solid #dee2e6;
        }
        
        .fee-table td {
            color: #333;
            border-bottom: 1px solid #dee2e6;
        }
        
        .fee-table tbody tr {
            background: white;
        }
        
        .fee-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .filter-section h5 {
            color: #ffffff;
            font-weight: 600;
        }
        
        .filter-section .form-label {
            color: #ffffff;
            font-weight: 500;
        }
        
        .filter-section .form-control,
        .filter-section .form-select {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .filter-section .form-control::placeholder {
            color: #6c757d;
        }
        
        .filter-section .form-control:focus,
        .filter-section .form-select:focus {
            background-color: rgba(255, 255, 255, 1);
            border-color: #ffffff;
            color: #333;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
        }
        
        /* Light theme card styles (default) */
        .card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        
        .card .card-title {
            color: #ffffff;
            font-weight: 600;
        }
        
        .card .form-label {
            color: #ffffff;
            font-weight: 500;
        }
        
        .card .form-control,
        .card .form-select {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .card .form-control::placeholder {
            color: #6c757d;
        }
        
        .card .form-control:focus,
        .card .form-select:focus {
            background-color: rgba(255, 255, 255, 1);
            border-color: #ffffff;
            color: #333;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
        }
        
        /* Light theme modal styles (default) */
        .modal-content {
            background: white;
            border: 1px solid #dee2e6;
            color: #333;
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-title {
            color: #333;
        }
        
        .modal-body .form-label {
            color: #333;
        }
        
        .modal-body .form-control,
        .modal-body .form-select {
            background-color: #fff;
            color: #333;
            border: 1px solid #ced4da;
        }
        
        .modal-body .form-control::placeholder {
            color: #6c757d;
        }
        
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background-color: #fff;
            border-color: #86b7fe;
            color: #333;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        /* Ensure modal inputs are always enabled and visible */
        .modal-body .form-control,
        .modal-body .form-select {
            pointer-events: auto !important;
            opacity: 1 !important;
            background-color: #fff !important;
            color: #333 !important;
        }
        
        .btn-close {
            filter: none;
        }
        
        /* Dark theme styles */
        body.dark-theme .fee-table {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        body.dark-theme .fee-table th {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-weight: 600;
            padding: 15px;
        }
        
        body.dark-theme .fee-table td {
            border: none;
            color: white;
            padding: 15px;
            vertical-align: middle;
        }
        
        body.dark-theme .fee-table tbody tr {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .fee-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .filter-section {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-theme .filter-section h5 {
            color: #ffffff;
        }
        
        body.dark-theme .filter-section .form-label {
            color: #ffffff;
        }
        
        body.dark-theme .filter-section .form-control,
        body.dark-theme .filter-section .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        body.dark-theme .filter-section .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        body.dark-theme .filter-section .form-control:focus,
        body.dark-theme .filter-section .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .card {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-theme .card .card-title {
            color: #ffffff;
        }
        
        body.dark-theme .card .form-label {
            color: #ffffff;
        }
        
        body.dark-theme .card .form-control,
        body.dark-theme .card .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        body.dark-theme .card .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        body.dark-theme .card .form-control:focus,
        body.dark-theme .card .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .modal-content {
            background: rgba(0, 0, 0, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        
        body.dark-theme .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .modal-title {
            color: #ffffff;
        }
        
        body.dark-theme .modal-body .form-label {
            color: #ffffff;
        }
        
        body.dark-theme .modal-body .form-control,
        body.dark-theme .modal-body .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        body.dark-theme .modal-body .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        body.dark-theme .modal-body .form-control:focus,
        body.dark-theme .modal-body .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .btn-close {
            filter: invert(1);
        }
        
        /* Generate Button Styles */
        .btn-generate {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-generate:hover {
            background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
            color: #ffffff !important;
        }
        
        .btn-generate:focus {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.5);
        }
        
        .btn-generate:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(255, 107, 107, 0.3);
        }
        
        /* Modal specific fixes */
        .modal-dialog {
            max-width: 600px;
            margin: 1.75rem auto;
        }
        
        .modal-content {
            background: white;
            border: 1px solid #dee2e6;
            color: #333;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }
        
        .modal-title {
            color: #333;
            font-weight: 600;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-body .form-label {
            color: #333;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .modal-body .form-control,
        .modal-body .form-select {
            background-color: #fff;
            color: #333;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .modal-body .form-control::placeholder {
            color: #6c757d;
        }
        
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background-color: #fff;
            border-color: #86b7fe;
            color: #333;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .modal-body .form-control:disabled,
        .modal-body .form-select:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }
        
        .btn-close {
            filter: none;
        }
        
        @media (max-width: 768px) {
            .stat-number {
                font-size: 2rem;
            }
            
            .fee-management-container {
                padding: 15px;
            }
            
            .fee-table {
                font-size: 0.9rem;
            }
            
            .fee-table th,
            .fee-table td {
                padding: 10px;
            }
            
            .modal-dialog {
                max-width: 95%;
                margin: 0.5rem auto;
            }
            
            .modal-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.svg" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Aditya Boys Hostel</h3>
                <p class="text-white-50 mb-0">Admin Portal</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_students.php" class="sidebar-menu-item">
                    <i class="fas fa-users"></i> Manage Students
                </a>
                <a href="manage_rooms.php" class="sidebar-menu-item">
                    <i class="fas fa-bed"></i> Room Management
                </a>
                <a href="manage_fees.php" class="sidebar-menu-item active">
                    <i class="fas fa-rupee-sign"></i> Fee Management
                </a>
                <a href="payment_verification.php" class="sidebar-menu-item">
                    <i class="fas fa-credit-card"></i> Payment Verification
                </a>
                <a href="payment_history.php" class="sidebar-menu-item">
                    <i class="fas fa-history"></i> Payment History
                </a>
                <a href="payment_info.php" class="sidebar-menu-item">
                    <i class="fas fa-info-circle"></i> Payment Information
                </a>
                <a href="manage_complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comment-dots"></i> Manage Complaints
                </a>
                <a href="manage_notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </a>
                <a href="../logout.php" class="sidebar-menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="top-bar-title">Fee Management</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Admin" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="fee-management-container">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger animate__animated animate__fadeInDown" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card animate__animated animate__fadeInUp">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <div class="stat-number"><?php echo $total_students; ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <i class="fas fa-times-circle fa-2x mb-2"></i>
                            <div class="stat-number"><?php echo $total_pending; ?></div>
                            <div class="stat-label">Pending Fees</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <div class="stat-number"><?php echo $total_paid; ?></div>
                            <div class="stat-label">Paid Fees</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <i class="fas fa-rupee-sign fa-2x mb-2"></i>
                            <div class="stat-number">₹<?php echo number_format($current_month_revenue, 2); ?></div>
                            <div class="stat-label">Month Revenue</div>
                        </div>
                    </div>
                </div>
                
                <!-- Generate Fees Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm animate__animated animate__fadeInUp">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="fas fa-plus-circle me-2"></i>Generate Monthly Fees
                                </h5>
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="generate_monthly_fees">
                                    <div class="col-md-3">
                                        <label for="month" class="form-label">Month</label>
                                        <select class="form-select" id="month" name="month" required>
                                            <option value="">Select Month</option>
                                            <option value="January">January</option>
                                            <option value="February">February</option>
                                            <option value="March">March</option>
                                            <option value="April">April</option>
                                            <option value="May">May</option>
                                            <option value="June">June</option>
                                            <option value="July">July</option>
                                            <option value="August">August</option>
                                            <option value="September">September</option>
                                            <option value="October">October</option>
                                            <option value="November">November</option>
                                            <option value="December">December</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="year" class="form-label">Year</label>
                                        <input type="number" class="form-control" id="year" name="year" min="2020" max="2050" placeholder="Enter year" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="amount" class="form-label">Amount (₹)</label>
                                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-generate w-100">
                                                <i class="fas fa-magic me-1"></i>Generate
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters Section -->
                <div class="filter-section animate__animated animate__fadeInUp">
                    <h5 class="mb-3">
                        <i class="fas fa-filter me-2"></i>Filter Fees
                    </h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Student</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Email, Mobile">
                        </div>
                        <div class="col-md-2">
                            <label for="month" class="form-label">Month</label>
                            <select class="form-select" id="month" name="month">
                                <option value="">All Months</option>
                                <option value="January" <?php echo ($month_filter == 'January') ? 'selected' : ''; ?>>January</option>
                                <option value="February" <?php echo ($month_filter == 'February') ? 'selected' : ''; ?>>February</option>
                                <option value="March" <?php echo ($month_filter == 'March') ? 'selected' : ''; ?>>March</option>
                                <option value="April" <?php echo ($month_filter == 'April') ? 'selected' : ''; ?>>April</option>
                                <option value="May" <?php echo ($month_filter == 'May') ? 'selected' : ''; ?>>May</option>
                                <option value="June" <?php echo ($month_filter == 'June') ? 'selected' : ''; ?>>June</option>
                                <option value="July" <?php echo ($month_filter == 'July') ? 'selected' : ''; ?>>July</option>
                                <option value="August" <?php echo ($month_filter == 'August') ? 'selected' : ''; ?>>August</option>
                                <option value="September" <?php echo ($month_filter == 'September') ? 'selected' : ''; ?>>September</option>
                                <option value="October" <?php echo ($month_filter == 'October') ? 'selected' : ''; ?>>October</option>
                                <option value="November" <?php echo ($month_filter == 'November') ? 'selected' : ''; ?>>November</option>
                                <option value="December" <?php echo ($month_filter == 'December') ? 'selected' : ''; ?>>December</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year" name="year" min="2020" max="2050" placeholder="Enter year" value="<?php echo htmlspecialchars($year_filter); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="paid" <?php echo ($status_filter == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="unpaid" <?php echo ($status_filter == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="partial" <?php echo ($status_filter == 'partial') ? 'selected' : ''; ?>>Partial</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="manage_fees.php" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Fees Table -->
                <div class="fee-table animate__animated animate__fadeInUp">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Month/Year</th>
                                    <th>Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Payment Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fees)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No fee records found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($fees as $fee): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($fee['full_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($fee['email']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($fee['month']); ?> <?php echo $fee['year']; ?>
                                            </td>
                                            <td>
                                                <strong>₹<?php echo number_format($fee['amount'], 2); ?></strong>
                                            </td>
                                            <td>
                                                ₹<?php echo number_format($fee['paid_amount'], 2); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $fee['status'] ?: 'empty'; ?>">
                                                    <?php echo $fee['status'] ?: 'unpaid'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $fee['payment_date'] ? date('M d, Y', strtotime($fee['payment_date'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <?php echo $fee['payment_method'] ?: '-'; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-update" onclick="openUpdateFeeModal(<?php echo $fee['id']; ?>, '<?php echo $fee['status']; ?>', <?php echo $fee['paid_amount']; ?>, '<?php echo $fee['payment_method']; ?>', '<?php echo $fee['transaction_id']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this fee record?');">
                                                        <input type="hidden" name="action" value="delete_fee">
                                                        <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div> <!-- fee-management-container -->
            </div>
        </div>
    </div>
    
    <!-- Update Fee Modal -->
    <div class="modal fade" id="updateFeeModal" tabindex="-1" aria-labelledby="updateFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateFeeModalLabel">Update Fee Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="updateFeeForm" action="manage_fees.php">
                        <input type="hidden" name="action" value="update_fee_status">
                        <input type="hidden" name="fee_id" id="modal_fee_id">
                        
                        <div class="mb-3">
                            <label for="modal_status" class="form-label">Status</label>
                            <select class="form-select" id="modal_status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modal_paid_amount" class="form-label">Paid Amount (₹)</label>
                            <input type="number" class="form-control" id="modal_paid_amount" name="paid_amount" step="0.01" min="0" placeholder="Enter paid amount">
                        </div>
                        
                        <div class="mb-3">
                            <label for="modal_payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="modal_payment_method" name="payment_method">
                                <option value="">Select Method</option>
                                <option value="upi">UPI</option>
                                <option value="google_pay">Google Pay</option>
                                <option value="phonepe">PhonePe</option>
                                <option value="paytm">Paytm</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modal_transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="modal_transaction_id" name="transaction_id" placeholder="Enter transaction ID">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Fee
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Fix modal issues */
        #updateFeeModal {
            z-index: 1055 !important;
        }
        
        #updateFeeModal .modal-dialog {
            z-index: 1060 !important;
            pointer-events: auto !important;
        }
        
        #updateFeeModal .modal-content {
            z-index: 1061 !important;
            background: white !important;
        }
        
        #updateFeeModal .form-control,
        #updateFeeModal .form-select,
        #updateFeeModal input,
        #updateFeeModal select,
        #updateFeeModal textarea,
        #updateFeeModal button {
            pointer-events: auto !important;
            user-select: auto !important;
            opacity: 1 !important;
            z-index: 1070 !important;
            position: relative !important;
        }
        
        #updateFeeModal .form-control:focus,
        #updateFeeModal .form-select:focus {
            outline: none !important;
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25) !important;
            z-index: 1071 !important;
        }
        
        .modal-backdrop {
            z-index: 1050 !important;
            background-color: rgba(0,0,0,0.5) !important;
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Direct function to open update fee modal
        function openUpdateFeeModal(feeId, status, paidAmount, paymentMethod, transactionId) {
            console.log('Opening update modal for fee:', feeId);
            
            // Set form values first
            document.getElementById('modal_fee_id').value = feeId || '';
            document.getElementById('modal_status').value = status || '';
            document.getElementById('modal_paid_amount').value = paidAmount || '0';
            document.getElementById('modal_payment_method').value = paymentMethod || '';
            document.getElementById('modal_transaction_id').value = transactionId || '';
            
            // Show modal using Bootstrap
            const modal = document.getElementById('updateFeeModal');
            
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                console.log('✅ Modal opened with Bootstrap');
                
                // Fix inputs immediately after modal opens
                setTimeout(() => {
                    fixModalInputs();
                }, 200);
                
                // Also fix when modal is fully shown
                modal.addEventListener('shown.bs.modal', function() {
                    fixModalInputs();
                });
            }
            else {
                alert('Bootstrap is not loaded. Please refresh the page.');
            }
        }
        
        // Function to fix modal inputs
        function fixModalInputs() {
            const modal = document.getElementById('updateFeeModal');
            const inputs = modal.querySelectorAll('input, select, textarea, button, label');
            
            inputs.forEach(input => {
                input.style.pointerEvents = 'auto';
                input.style.userSelect = 'auto';
                input.style.opacity = '1';
                input.disabled = false;
                input.readOnly = false;
                input.style.visibility = 'visible';
                input.style.display = '';
                input.style.zIndex = '9999';
            });
            
            console.log('✅ All modal inputs fixed');
        }
        
        // Handle form submission
        const updateForm = document.getElementById('updateFeeForm');
        if (updateForm) {
            updateForm.addEventListener('submit', function(e) {
                const feeId = document.getElementById('modal_fee_id').value;
                const status = document.getElementById('modal_status').value;
                const paidAmount = document.getElementById('modal_paid_amount').value;
                
                // Validation
                if (!feeId) {
                    alert('Fee ID is required');
                    e.preventDefault();
                    return false;
                }
                
                if (!status) {
                    alert('Please select a status');
                    e.preventDefault();
                    return false;
                }
                
                if (!paidAmount || paidAmount < 0) {
                    alert('Please enter a valid paid amount');
                    e.preventDefault();
                    return false;
                }
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                
                console.log('Form validation passed, submitting...');
                // Allow form to submit normally
            });
        }
    </script>
</body>
</html>
