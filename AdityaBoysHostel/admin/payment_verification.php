<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

header('Content-Type: text/html; charset=UTF-8');

// Function to convert payment method to readable name
function getPaymentMethodName($method) {
    $methods = [
        'google_pay' => 'Google Pay',
        'paytm' => 'Paytm',
        'phonepe' => 'PhonePe',
        'bhim' => 'BHIM UPI',
        'amazon_pay' => 'Amazon Pay',
        'bank_transfer' => 'Bank Transfer',
        'upi' => 'UPI',
        'cash' => 'Cash',
        'cheque' => 'Cheque'
    ];
    return $methods[$method] ?? ucfirst(str_replace('_', ' ', $method));
}

// Handle payment verification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $payment_id = $_POST['payment_id'] ?? 0;
    
    if ($action == 'approve') {
        try {
            $conn->begin_transaction();
            
            // Get payment details
            $stmt = $conn->prepare("SELECT p.*, f.month, f.year, s.full_name, s.email FROM payments p JOIN fees f ON p.fee_id = f.id JOIN students s ON p.student_id = s.id WHERE p.id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();
            
            if ($payment) {
                // Update payment status to approved
                $stmt = $conn->prepare("UPDATE payments SET status = 'Approved' WHERE id = ?");
                $stmt->bind_param("i", $payment_id);
                $stmt->execute();
                
                // Try to update approved_at and approved_by if columns exist
                try {
                    $stmt = $conn->prepare("UPDATE payments SET approved_at = NOW(), approved_by = ? WHERE id = ?");
                    $stmt->bind_param("si", $_SESSION['admin_name'], $payment_id);
                    $stmt->execute();
                } catch (Exception $e) {
                    // Columns don't exist, continue without them
                }
                
                // Update fee status to fully paid
                $stmt = $conn->prepare("UPDATE fees SET status = 'paid', paid_amount = ?, payment_date = NOW(), payment_method = ?, transaction_id = ? WHERE id = ?");
                $stmt->bind_param("dsss", $payment['amount'], $payment['payment_method'], $payment['transaction_id'], $payment['fee_id']);
                $stmt->execute();
                
                // Add notification for student
                $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES ('student', ?, 'Payment Approved', 'Your payment for " . $payment['month'] . " " . $payment['year'] . " has been approved. Amount: ₹" . number_format($payment['amount'], 2) . "')");
                $stmt->bind_param("i", $payment['student_id']);
                $stmt->execute();
            }
            
            $conn->commit();
            $success = "Payment approved successfully!";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action == 'reject') {
        try {
            // Get payment details first
            $stmt = $conn->prepare("SELECT p.*, f.month, f.year FROM payments p JOIN fees f ON p.fee_id = f.id WHERE p.id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();
            
            // Check if rejected_reason column exists
            $column_exists = false;
            try {
                $check_column = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'rejected_reason'");
                $check_column->execute();
                $column_exists = $check_column->get_result()->num_rows > 0;
            } catch (Exception $e) {
                // Column doesn't exist, continue without it
            }
            
            if ($column_exists) {
                // Update payment with rejection reason
                $stmt = $conn->prepare("UPDATE payments SET status = 'Rejected', rejected_reason = ?, approved_by = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['rejected_reason'], $_SESSION['admin_name'], $payment_id);
                $stmt->execute();
            } else {
                // Update payment without rejection reason (for backward compatibility)
                $stmt = $conn->prepare("UPDATE payments SET status = 'Rejected', approved_by = ? WHERE id = ?");
                $stmt->bind_param("si", $_SESSION['admin_name'], $payment_id);
                $stmt->execute();
            }
            
            // Add notification for student
            if ($payment) {
                $reason_text = $_POST['rejected_reason'] ?? 'No reason provided';
                $notification_message = 'Your payment for ' . $payment['month'] . ' ' . $payment['year'] . ' has been rejected. Reason: ' . $reason_text;
                
                $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES ('student', ?, 'Payment Rejected', ?)");
                $stmt->bind_param("is", $payment['student_id'], $notification_message);
                $stmt->execute();
            }
            
            $success = "Payment rejected successfully!";
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get pending payments - also check for other statuses that might need verification
$pending_payments = [];
try {
    // First try to get payments with "Pending Verification" status
    $stmt = $conn->prepare("SELECT p.*, f.month, f.year, s.full_name, s.email FROM payments p JOIN fees f ON p.fee_id = f.id JOIN students s ON p.student_id = s.id WHERE p.status = 'Pending Verification' ORDER BY p.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pending_payments[] = $row;
    }
    
    // If no pending payments found, check for other possible statuses
    if (empty($pending_payments)) {
        error_log("No 'Pending Verification' payments found, checking for other statuses");
        
        // Check for payments with 'pending' status (lowercase)
        $stmt = $conn->prepare("SELECT p.*, f.month, f.year, s.full_name, s.email FROM payments p JOIN fees f ON p.fee_id = f.id JOIN students s ON p.student_id = s.id WHERE p.status = 'pending' ORDER BY p.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['status'] = 'Pending Verification'; // Normalize status
            $pending_payments[] = $row;
        }
        
        // If still no payments, get all recent payments that are not approved/rejected
        if (empty($pending_payments)) {
            $stmt = $conn->prepare("SELECT p.*, f.month, f.year, s.full_name, s.email FROM payments p JOIN fees f ON p.fee_id = f.id JOIN students s ON p.student_id = s.id WHERE p.status NOT IN ('Approved', 'Rejected', 'approved', 'rejected') ORDER BY p.created_at DESC LIMIT 10");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['status'] = 'Pending Verification'; // Normalize status
                $pending_payments[] = $row;
            }
        }
    }
    
    // Debug: Log the query results
    error_log("Pending payments found: " . count($pending_payments));
    foreach ($pending_payments as $payment) {
        error_log("Payment ID: " . $payment['id'] . ", Student: " . $payment['full_name'] . ", Proof: " . $payment['payment_proof'] . ", Status: " . $payment['status']);
    }
    
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .verification-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .payment-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .proof-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .proof-image:hover {
            transform: scale(1.05);
        }
        .status-pending {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid #ffc107;
        }
        .btn-approve {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .btn-reject {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        /* Fix modal input issues */
        .modal .form-control {
            pointer-events: auto !important;
            user-select: auto !important;
            z-index: 1052 !important;
            position: relative !important;
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-color: #4a5568 !important;
        }
        
        .modal .form-control:focus {
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
        }
        
        .modal .form-label {
            color: #e2e8f0 !important;
        }
        
        .modal .modal-content {
            background-color: #1a202c !important;
            color: #e2e8f0 !important;
            pointer-events: auto !important;
            z-index: 1052 !important;
            border: 1px solid #2d3748 !important;
        }
        
        .modal .modal-header {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid #4a5568 !important;
        }
        
        .modal .modal-body {
            background-color: #1a202c !important;
            color: #e2e8f0 !important;
            pointer-events: auto !important;
            z-index: 1051 !important;
        }
        
        .modal .modal-footer {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
            border-top: 1px solid #4a5568 !important;
        }
        
        .modal .modal-title {
            color: #e2e8f0 !important;
        }
        
        .modal .btn-close {
            color: #e2e8f0 !important;
            filter: invert(1) !important;
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
            pointer-events: none !important;
        }
        
        .modal.show {
            pointer-events: auto !important;
        }
        
        /* Ensure textarea is clickable */
        textarea.form-control {
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
        }
        
        /* Fix reject modal size */
        .modal-dialog {
            max-width: 500px !important;
            width: 90vw !important;
            margin: 1.75rem auto !important;
        }
        
        .modal-content {
            border-radius: 8px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }
        
        .modal-header {
            padding: 1rem 1.5rem !important;
            border-bottom: 1px solid #dee2e6 !important;
        }
        
        .modal-body {
            padding: 1.5rem !important;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem !important;
            border-top: 1px solid #dee2e6 !important;
        }
        
        @media (max-width: 576px) {
            .modal-dialog {
                max-width: 95vw !important;
                margin: 0.5rem auto !important;
            }
            
            .modal-body {
                padding: 1rem !important;
            }
            
            .modal-header {
                padding: 0.75rem 1rem !important;
            }
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
                <a href="payment_verification.php" class="sidebar-menu-item active">
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
                <h1 class="top-bar-title">Payment Verification</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Admin" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
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
                
                <!-- Pending Payments -->
                <div class="verification-container">
                    <h3 class="mb-4"><i class="fas fa-clock me-2"></i>Pending Payment Verifications (<?php echo count($pending_payments); ?>)</h3>
                    
                    <?php if (empty($pending_payments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No pending payment verifications</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pending_payments as $payment): ?>
                            <div class="payment-card animate__animated animate__fadeInUp">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($payment['full_name']); ?></h5>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($payment['email']); ?></p>
                                        <p><strong>Fee:</strong> <?php echo htmlspecialchars($payment['month']); ?> <?php echo $payment['year']; ?> - ₹<?php echo number_format($payment['amount'], 2); ?></p>
                                        <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo getPaymentMethodName($payment['payment_method']); ?></p>
                                        <p><strong>Submitted:</strong> <?php echo date('d M, Y H:i', strtotime($payment['created_at'])); ?></p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <?php if ($payment['payment_proof']): ?>
                                            <?php 
                                            // Simple direct path construction with proper URL encoding
                                            $proof_path = $payment['payment_proof'];
                                            $display_path = '../' . $proof_path;
                                            
                                            // URL encode the path to handle spaces and special characters
                                            $encoded_path = '../' . str_replace(' ', '%20', $proof_path);
                                            ?>
                                            <img src="<?php echo htmlspecialchars($encoded_path); ?>" 
                                                 alt="Payment Proof" 
                                                 class="proof-image"
                                                 onclick="window.open('<?php echo htmlspecialchars($encoded_path); ?>', '_blank')"
                                                 onerror="this.src='../assets/images/no-image.png'; this.alt='Payment proof not found';">
                                            <p class="small text-muted mt-2">Click to enlarge</p>
                                        <?php else: ?>
                                            <div class="text-center">
                                                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                                <p class="text-muted small">No proof uploaded</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <?php if ($payment['status'] == 'Pending Verification'): ?>
                                        <form method="POST" class="d-inline-block me-2">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" class="btn btn-approve btn-sm">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-reject btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $payment['id']; ?>">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reject Modal -->
    <?php foreach ($pending_payments as $payment): ?>
        <div class="modal fade" id="rejectModal<?php echo $payment['id']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?php echo $payment['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel<?php echo $payment['id']; ?>">Reject Payment - <?php echo htmlspecialchars($payment['full_name']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="rejected_reason<?php echo $payment['id']; ?>" class="form-label">Rejection Reason *</label>
                                <textarea class="form-control" id="rejected_reason<?php echo $payment['id']; ?>" name="rejected_reason" rows="3" required
                                          placeholder="Please specify the reason for rejection..."></textarea>
                            </div>
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                            <button type="submit" class="btn btn-reject">
                                <i class="fas fa-times me-1"></i>Reject Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
