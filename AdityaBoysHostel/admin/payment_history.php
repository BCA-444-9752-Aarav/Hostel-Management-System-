<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT p.*, s.full_name, s.email, s.mobile, f.month, f.year 
        FROM payments p 
        JOIN students s ON p.student_id = s.id 
        JOIN fees f ON p.fee_id = f.id 
        WHERE 1=1";

$params = [];
$types = '';

// Apply filters
if ($filter != 'all') {
    $sql .= " AND p.status = ?";
    $params[] = $filter;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= " AND (s.full_name LIKE ? OR s.email LIKE ? OR s.mobile LIKE ? OR p.transaction_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

if (!empty($date_from)) {
    $sql .= " AND DATE(p.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $sql .= " AND DATE(p.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$sql .= " ORDER BY p.created_at DESC";

// Execute query
$payments = [];
try {
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    // Payments table doesn't exist or other database error
    $error = "Database error: " . $e->getMessage() . ". Please run the payment system setup first.";
    
    // Check if payments table exists
    $table_check = $conn->prepare("SHOW TABLES LIKE 'payments'");
    $table_check->execute();
    $table_result = $table_check->get_result();
    
    if ($table_result->num_rows == 0) {
        $error = "Payment system is not set up yet. <a href='setup_payment_system.php' class='alert-link'>Click here to set up the payment system</a>.";
    }
}

// Get payment statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'total_amount' => 0
];

foreach ($payments as $payment) {
    $stats['total']++;
    
    // Normalize status to lowercase and ensure key exists
    $status = strtolower($payment['status']);
    if (!isset($stats[$status])) {
        $stats[$status] = 0;
    }
    $stats[$status]++;
    
    // Add to total amount for approved payments
    if (strtolower($payment['status']) == 'approved') {
        $stats['total_amount'] += $payment['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-history-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .payment-row {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .payment-row:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
        .status-approved { background: rgba(40, 167, 69, 0.2); color: #28a745; }
        .status-rejected { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
        .proof-thumbnail {
            max-width: 60px;
            max-height: 60px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .proof-thumbnail:hover {
            transform: scale(1.1);
        }
        .method-icon {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.8rem;
        }
        .method-google_pay { background: #4285F4; color: white; }
        .method-paytm { background: #003B7F; color: white; }
        .method-phonepe { background: #7B3FF2; color: white; }
        .method-bhim { background: #FF6B6B; color: white; }
        .method-amazon_pay { background: #FF9900; color: white; }
        .method-bank_transfer { background: #28a745; color: white; }
        
        /* Light Theme Styles */
        body:not(.dark-theme) .payment-history-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #495057;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }
        
        body:not(.dark-theme) .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            border-radius: 15px;
        }
        
        body:not(.dark-theme) .payment-row {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #dee2e6;
            color: #495057;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        body:not(.dark-theme) .payment-row:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #ced4da;
            color: #495057;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        body:not(.dark-theme) .status-badge {
            border: none;
            border-radius: 20px;
            font-weight: 600;
        }
        
        body:not(.dark-theme) .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #ffffff;
            border-color: #ffeaa7;
        }
        
        body:not(.dark-theme) .status-approved {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #ffffff;
            border-color: #c3e6cb;
        }
        
        body:not(.dark-theme) .status-rejected {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: #ffffff;
            border-color: #f5c6cb;
        }
        
        body:not(.dark-theme) .proof-thumbnail {
            border: 2px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        
        body:not(.dark-theme) .text-muted {
            color: #6c757d !important;
        }
        
        /* Update filter card */
        body:not(.dark-theme) .card.bg-dark {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3) !important;
            border-radius: 15px !important;
        }
        
        body:not(.dark-theme) .card.bg-dark .form-label {
            color: #ffffff !important;
            font-weight: 500 !important;
        }
        
        body:not(.dark-theme) .card.bg-dark .form-control,
        body:not(.dark-theme) .card.bg-dark .form-select {
            background-color: rgba(255, 255, 255, 0.9) !important;
            color: #333 !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 8px !important;
        }
        
        body:not(.dark-theme) .card.bg-dark .form-control:focus,
        body:not(.dark-theme) .card.bg-dark .form-select:focus {
            background-color: rgba(255, 255, 255, 1) !important;
            border-color: #ffffff !important;
            color: #333 !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3) !important;
        }
        
        body:not(.dark-theme) .alert-success {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
        }
        
        body:not(.dark-theme) .alert-danger {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
        }
        
        body:not(.dark-theme) .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3) !important;
        }
        
        body:not(.dark-theme) .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5c636a 100%) !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3) !important;
        }
        
        body:not(.dark-theme) .form-control {
            background-color: #ffffff !important;
            color: #495057 !important;
            border-color: #ced4da !important;
        }
        
        body:not(.dark-theme) .form-control:focus {
            background-color: #ffffff !important;
            color: #495057 !important;
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
        }
        
        body:not(.dark-theme) .form-select {
            background-color: #ffffff !important;
            color: #495057 !important;
            border-color: #ced4da !important;
        }
        
        body:not(.dark-theme) .btn-close {
            color: #333 !important;
        }
        
        body:not(.dark-theme) .modal-content {
            background-color: #ffffff !important;
            color: #495057 !important;
        }
        
        body:not(.dark-theme) .modal-header {
            background-color: #f8f9fa !important;
            border-bottom-color: #dee2e6 !important;
        }
        
        body:not(.dark-theme) .modal-title {
            color: #495057 !important;
        }
        
        body:not(.dark-theme) .text-primary {
            color: #0d6efd !important;
        }
        
        body:not(.dark-theme) .text-secondary {
            color: #6c757d !important;
        }
        
        /* Dark theme specific improvements */
        body.dark-theme .payment-row {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        
        body.dark-theme .payment-row:hover {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        body.dark-theme .payment-row h6 {
            color: #ffffff !important;
        }
        
        body.dark-theme .payment-row strong {
            color: #ffffff !important;
        }
        
        body.dark-theme .payment-row .text-muted {
            color: #b8c5d6 !important;
        }
        
        /* Light theme text visibility fixes */
        body:not(.dark-theme) .payment-row {
            background: #f8f9fa !important;
            border: 2px solid #dee2e6 !important;
            color: #212529 !important;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        body:not(.dark-theme) .payment-row:hover {
            background: #e9ecef !important;
            border: 2px solid #adb5bd !important;
            color: #212529 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        body:not(.dark-theme) .payment-row h6 {
            color: #212529 !important;
            font-weight: 700;
        }
        
        body:not(.dark-theme) .payment-row strong {
            color: #212529 !important;
            font-weight: 700;
        }
        
        body:not(.dark-theme) .payment-row .text-muted {
            color: #6c757d !important;
            font-weight: 500;
        }
        
        body:not(.dark-theme) .payment-row small {
            color: #495057 !important;
            font-weight: 500;
        }
        
        /* Dark theme improvements */
        body.dark-theme .payment-row {
            background: #2c3e50 !important;
            border: 2px solid #34495e !important;
            color: #ffffff !important;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-theme .payment-row:hover {
            background: #34495e !important;
            border: 2px solid #4a5f7a !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            transform: translateY(-2px);
        }
        
        body.dark-theme .payment-row h6 {
            color: #ffffff !important;
            font-weight: 700;
        }
        
        body.dark-theme .payment-row strong {
            color: #ffffff !important;
            font-weight: 700;
        }
        
        body.dark-theme .payment-row .text-muted {
            color: #b8c5d6 !important;
            font-weight: 500;
        }
        
        body.dark-theme .payment-row small {
            color: #ecf0f1 !important;
            font-weight: 500;
        }
    </style>
</head>
<body class="dark-theme">
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
                <a href="manage_fees.php" class="sidebar-menu-item">
                    <i class="fas fa-rupee-sign"></i> Fee Management
                </a>
                <a href="payment_verification.php" class="sidebar-menu-item">
                    <i class="fas fa-credit-card"></i> Payment Verification
                </a>
                <a href="payment_history.php" class="sidebar-menu-item active">
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
                <h1 class="top-bar-title">Payment History</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Admin" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card animate__animated animate__fadeInUp">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <div class="stats-number"><?php echo $stats['total']; ?></div>
                            <div>Total Payments</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <div class="stats-number"><?php echo $stats['pending']; ?></div>
                            <div>Pending</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <div class="stats-number"><?php echo $stats['approved']; ?></div>
                            <div>Approved</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <i class="fas fa-rupee-sign fa-2x mb-2"></i>
                            <div class="stats-number">₹<?php echo number_format($stats['total_amount'], 2); ?></div>
                            <div>Total Collected</div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="filter" class="form-select">
                                            <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                            <option value="Pending Verification" <?php echo $filter == 'Pending Verification' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?php echo $filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="Rejected" <?php echo $filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Search</label>
                                        <input type="text" name="search" class="form-control" placeholder="Student name, email, transaction ID..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">From Date</label>
                                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">To Date</label>
                                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter me-1"></i>Filter
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payments List -->
                <div class="payment-history-container">
                    <h3 class="mb-4"><i class="fas fa-history me-2"></i>Payment Records (<?php echo count($payments); ?>)</h3>
                    
                    <?php if (empty($payments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payment records found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <div class="payment-row animate__animated animate__fadeInUp">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="method-icon method-<?php echo $payment['payment_method']; ?>">
                                                    <?php
                                                    $icons = [
                                                        'google_pay' => 'G',
                                                        'paytm' => 'P',
                                                        'phonepe' => 'Ph',
                                                        'bhim' => 'B',
                                                        'amazon_pay' => 'A',
                                                        'bank_transfer' => 'B'
                                                    ];
                                                    echo $icons[$payment['payment_method']] ?? 'U';
                                                    ?>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($payment['full_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($payment['email']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div>
                                            <strong>₹<?php echo number_format($payment['amount'], 2); ?></strong><br>
                                            <small class="text-muted"><?php echo $payment['month']; ?> <?php echo $payment['year']; ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong><br>
                                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <?php if ($payment['payment_proof']): ?>
                                            <img src="../<?php echo htmlspecialchars($payment['payment_proof']); ?>" 
                                                 alt="Payment Proof" 
                                                 class="proof-thumbnail"
                                                 onclick="window.open('../<?php echo htmlspecialchars($payment['payment_proof']); ?>', '_blank')">
                                        <?php else: ?>
                                            <span class="text-muted">No proof</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $payment['status'])); ?>">
                                                <?php echo $payment['status']; ?>
                                            </span>
                                            <?php if ($payment['status'] == 'Pending Verification'): ?>
                                                <div class="ms-2">
                                                    <a href="payment_verification.php" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
