<?php
require_once '../config/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get student information
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Get student's fees
$fees = [];
$stmt = $conn->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY year DESC, month DESC");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fees[] = $row;
}

// Get payment information from database - same method as admin side
$payment_info = null;
$payment_methods = [];
try {
    // Get only the specific payment methods: upi, bank_transfer, google_pay, paytm, phonepe
    $allowed_methods = ['upi', 'bank_transfer', 'google_pay', 'paytm', 'phonepe'];
    
    // First try with is_active column and allowed methods
    $stmt = $conn->prepare("SELECT * FROM payment_info WHERE is_active = TRUE AND payment_method IN ('" . implode("','", $allowed_methods) . "') ORDER BY display_order ASC, id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get all matching payment methods
    while ($row = $result->fetch_assoc()) {
        $payment_methods[] = $row;
    }
    
    // If no results, try without is_active condition but with allowed methods
    if (empty($payment_methods)) {
        $stmt = $conn->prepare("SELECT * FROM payment_info WHERE payment_method IN ('" . implode("','", $allowed_methods) . "') ORDER BY display_order ASC, id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $payment_methods[] = $row;
        }
    }
    
    // Merge payment methods to create consolidated payment_info
    $payment_info = [
        'upi_id' => null,
        'phone_number' => null,
        'bank_name' => null,
        'account_number' => null,
        'ifsc_code' => null,
        'account_holder' => null
    ];
    
    // Get UPI ID from any UPI-based method
    foreach ($payment_methods as $method) {
        if (in_array($method['payment_method'], ['upi', 'google_pay', 'phonepe', 'paytm']) && !empty($method['upi_id'])) {
            $payment_info['upi_id'] = $method['upi_id'];
            break;
        }
    }
    
    // Get phone number from any method
    foreach ($payment_methods as $method) {
        if (!empty($method['phone_number'])) {
            $payment_info['phone_number'] = $method['phone_number'];
            break;
        }
    }
    
    // Get bank details
    foreach ($payment_methods as $method) {
        if (!empty($method['bank_name'])) {
            $payment_info['bank_name'] = $method['bank_name'];
        }
        if (!empty($method['account_number'])) {
            $payment_info['account_number'] = $method['account_number'];
        }
        if (!empty($method['ifsc_code'])) {
            $payment_info['ifsc_code'] = $method['ifsc_code'];
        }
        if (!empty($method['account_holder_name'])) {
            $payment_info['account_holder'] = $method['account_holder_name'];
        }
    }
    
    // If still no payment info, use default values
    if (empty($payment_methods)) {
        $payment_info = [
            'upi_id' => 'aaravraj799246@okaxis',
            'phone_number' => '7992465964',
            'account_holder' => 'Aarav Raj',
            'account_number' => '123456789012345',
            'bank_name' => 'State Bank of India',
            'ifsc_code' => 'SBIN0001234'
        ];
    }
    
} catch (Exception $e) {
    // If table doesn't exist or error, use default values
    $payment_info = [
        'upi_id' => 'aaravraj799246@okaxis',
        'phone_number' => '7992465964',
        'account_holder' => 'Aarav Raj',
        'account_number' => '123456789012345',
        'bank_name' => 'State Bank of India',
        'ifsc_code' => 'SBIN0001234'
    ];
}

// Calculate statistics
$total_fees = 0;
$paid_fees = 0;
$unpaid_fees = 0;
$partial_fees = 0;
$collected_amount = 0;
$pending_amount = 0;

foreach ($fees as $fee) {
    $total_fees += $fee['amount'];
    if ($fee['status'] == 'paid') {
        $paid_fees++;
        $collected_amount += $fee['paid_amount'];
    } elseif ($fee['status'] == 'partial') {
        $partial_fees++;
        $collected_amount += $fee['paid_amount'];
        $pending_amount += ($fee['amount'] - $fee['paid_amount']);
    } else {
        $unpaid_fees++;
        $pending_amount += $fee['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.svg" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Aditya Boys Hostel</h3>
                <p class="text-white-50 mb-0">Student Portal</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="profile.php" class="sidebar-menu-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="fees.php" class="sidebar-menu-item active">
                    <i class="fas fa-rupee-sign"></i> My Fees
                </a>
                <a href="complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comments"></i> My Complaints
                </a>
                <a href="notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-bell"></i> My Notifications
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
                <h1 class="top-bar-title">My Fees</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['student_name']; ?></span>
                    <img src="../uploads/<?php echo $student['profile_photo'] ?: 'default_avatar.svg'; ?>" alt="Student" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo count($fees); ?></div>
                            <div class="dashboard-card-label">Total Fees</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="dashboard-card-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $paid_fees; ?></div>
                            <div class="dashboard-card-label">Paid Fees</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <div class="dashboard-card-icon pink">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $unpaid_fees + $partial_fees; ?></div>
                            <div class="dashboard-card-label">Pending Fees</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div class="dashboard-card-value">₹<?php echo number_format($pending_amount, 0); ?></div>
                            <div class="dashboard-card-label">Pending Amount</div>
                        </div>
                    </div>
                </div>
                
                <!-- Fee Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-container animate__animated animate__fadeInUp">
                            <h5 class="mb-3">Fee Summary</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Total Amount</h6>
                                        <h4 class="text-primary">₹<?php echo number_format($total_fees, 2); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Collected Amount</h6>
                                        <h4 class="text-success">₹<?php echo number_format($collected_amount, 2); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Pending Amount</h6>
                                        <h4 class="text-warning">₹<?php echo number_format($pending_amount, 2); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Payment Progress</h6>
                                        <div class="progress" style="height: 25px;">
                                            <?php 
                                            $progress = $total_fees > 0 ? ($collected_amount / $total_fees) * 100 : 0;
                                            $progress_color = $progress >= 100 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                                            ?>
                                            <div class="progress-bar bg-<?php echo $progress_color; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $progress; ?>%"
                                                 aria-valuenow="<?php echo $progress; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?php echo round($progress, 1); ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fees Table -->
                <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Fee Details</h5>
                        <button class="btn btn-sm btn-primary" onclick="printStatement()">
                            <i class="fas fa-print me-2"></i>Print Statement
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table" id="feesTable">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Payment Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fees as $fee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fee['month']); ?></td>
                                    <td><?php echo $fee['year']; ?></td>
                                    <td>₹<?php echo number_format($fee['amount'], 2); ?></td>
                                    <td>₹<?php echo number_format($fee['paid_amount'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $balance = $fee['amount'] - $fee['paid_amount'];
                                        if ($balance > 0) {
                                            echo '<span class="text-danger fw-bold">₹' . number_format($balance, 2) . '</span>';
                                        } else {
                                            echo '<span class="text-success fw-bold">₹0.00</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        $status_icon = '';
                                        switch($fee['status']) {
                                            case 'paid':
                                                $status_class = 'badge-success';
                                                $status_icon = 'fa-check-circle';
                                                break;
                                            case 'unpaid':
                                                $status_class = 'badge-danger';
                                                $status_icon = 'fa-times-circle';
                                                break;
                                            case 'partial':
                                                $status_class = 'badge-warning';
                                                $status_icon = 'fa-clock';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                            <?php echo ucfirst($fee['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $fee['payment_date'] ? date('M d, Y', strtotime($fee['payment_date'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($fee['payment_method']) {
                                            $method_icons = [
                                                'cash' => 'fa-money-bill-wave',
                                                'bank_transfer' => 'fa-university',
                                                'upi' => 'fa-mobile-alt',
                                                'cheque' => 'fa-file-invoice-dollar'
                                            ];
                                            $icon = $method_icons[$fee['payment_method']] ?? 'fa-credit-card';
                                            echo '<i class="fas ' . $icon . ' me-1"></i>' . ucfirst(str_replace('_', ' ', $fee['payment_method']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($fee['status'] == 'unpaid' || $fee['status'] == 'partial'): ?>
                                            <a href="payment.php?fee_id=<?php echo $fee['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-credit-card me-1"></i>Pay Now
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($fees)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No fee records found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Payment Instructions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <h5 class="mb-3">Payment Instructions</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-university me-2"></i>Bank Transfer</h6>
                                    <p class="text-muted">
                                        Account Name: <?php echo htmlspecialchars($payment_info['account_holder'] ?? 'Aarav Raj'); ?><br>
                                        Account Number: <?php echo htmlspecialchars($payment_info['account_number'] ?? '123456789012345'); ?><br>
                                        Bank: <?php echo htmlspecialchars($payment_info['bank_name'] ?? 'State Bank of India'); ?><br>
                                        IFSC: <?php echo htmlspecialchars($payment_info['ifsc_code'] ?? 'SBIN0001234'); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-mobile-alt me-2"></i>UPI Payment</h6>
                                    <p class="text-muted">
                                        UPI ID: <?php echo htmlspecialchars($payment_info['upi_id'] ?? 'aaravraj799246@okaxis'); ?><br>
                                        PhonePe: <?php echo htmlspecialchars($payment_info['phone_number'] ?? '7992465964'); ?><br>
                                        Google Pay: <?php echo htmlspecialchars($payment_info['phone_number'] ?? '7992465964'); ?><br>
                                        Paytm: <?php echo htmlspecialchars($payment_info['phone_number'] ?? '7992465964'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Please share your payment receipt with the hostel administration after making any payment. Your fee status will be updated within 24 hours.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Print functionality
        function printStatement() {
            console.log('=== printStatement function called ===');
            try {
                alert('Print function started!');
                
                // Get current filter values from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const monthFilter = urlParams.get('month') || 'all';
                const yearFilter = urlParams.get('year') || 'all';
                const statusFilter = urlParams.get('status') || 'all';
                
                console.log('Filters:', { monthFilter, yearFilter, statusFilter });
                
                // Get student information from page
                const studentName = document.querySelector('.top-bar-user span')?.textContent || 'Student';
                console.log('Student name:', studentName);
                
                // Check if table exists
                const table = document.getElementById('feesTable');
                if (table) {
                    console.log('Table found! Rows:', table.rows.length);
                } else {
                    console.error('Table not found!');
                    alert('Error: Fees table not found!');
                    return;
                }
                
                // Build print content step by step
                let printContent = '<html><head><title>Fee Statement - Aditya Boys Hostel</title>';
                printContent += '<style>';
                printContent += 'body { font-family: Arial, sans-serif; margin: 20px; color: #000000; }';
                printContent += '.header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }';
                printContent += '.footer { text-align: center; margin-top: 30px; border-top: 2px solid #333; padding-top: 20px; }';
                printContent += 'table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }';
                printContent += 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
                printContent += 'th { background-color: #f2f2f2; font-weight: bold; }';
                printContent += '.amount { text-align: right; font-weight: bold; }';
                printContent += '.status { text-align: center; }';
                printContent += '.text-center { text-align: center; }';
                printContent += '.badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }';
                printContent += '.badge-success { background-color: #28a745; color: white; }';
                printContent += '.badge-warning { background-color: #ffc107; color: #000; }';
                printContent += '.badge-danger { background-color: #dc3545; color: white; }';
                printContent += '.text-muted { color: #666; }';
                printContent += '</style></head><body>';
                
                // Add header
                printContent += '<div class="header">';
                printContent += '<h1>Fee Statement</h1>';
                printContent += '<h3>Aditya Boys Hostel</h3>';
                printContent += '<p>Generated on: ' + new Date().toLocaleDateString('en-IN') + '</p>';
                printContent += '<p>Student: ' + studentName + '</p>';
                printContent += '<p>Student ID: ' + (document.querySelector('.user-avatar')?.alt || 'N/A') + '</p>';
                printContent += '</div>';
                
                // Add filter information if filters are applied
                if (monthFilter !== 'all' || yearFilter !== 'all' || statusFilter !== 'all') {
                    printContent += '<p><strong>Filters Applied:</strong></p><ul>';
                    if (monthFilter !== 'all') {
                        printContent += '<li>Month: ' + monthFilter + '</li>';
                    }
                    if (yearFilter !== 'all') {
                        printContent += '<li>Year: ' + yearFilter + '</li>';
                    }
                    if (statusFilter !== 'all') {
                        printContent += '<li>Status: ' + statusFilter + '</li>';
                    }
                    printContent += '</ul>';
                }
                
                // Add table content with correct payment status
                printContent += '<div class="table-content"><table>';
                printContent += '<thead><tr><th>Month</th><th>Year</th><th>Total Amount</th><th>Paid Amount</th><th>Balance</th><th>Status</th><th>Payment Date</th><th>Payment Method</th></tr></thead>';
                printContent += '<tbody>';
                
                <?php foreach ($fees as $fee): ?>
                printContent += '<tr>';
                printContent += '<td><?php echo htmlspecialchars($fee['month']); ?></td>';
                printContent += '<td><?php echo $fee['year']; ?></td>';
                printContent += '<td>₹<?php echo number_format($fee['amount'], 2); ?></td>';
                printContent += '<td>₹<?php echo number_format($fee['paid_amount'], 2); ?></td>';
                printContent += '<td>';
                <?php 
                $balance = $fee['amount'] - $fee['paid_amount'];
                if ($balance > 0) {
                    echo 'printContent += \'<span class="text-danger fw-bold">₹' . number_format($balance, 2) . '</span>\';';
                } else {
                    echo 'printContent += \'<span class="text-success fw-bold">₹0.00</span>\';';
                }
                ?>
                printContent += '</td>';
                printContent += '<td>';
                <?php 
                // Check if payment is approved in payments table
                $is_approved = false;
                $payment_date = '';
                $payment_method = '';
                
                if ($fee['status'] == 'paid') {
                    // Check if there's an approved payment for this fee
                    $stmt = $conn->prepare("SELECT status, payment_method, approved_at FROM payments WHERE fee_id = ? AND status = 'approved' ORDER BY approved_at DESC LIMIT 1");
                    $stmt->bind_param("i", $fee['id']);
                    $stmt->execute();
                    $payment_result = $stmt->get_result();
                    $payment_data = $payment_result->fetch_assoc();
                    
                    if ($payment_data) {
                        $is_approved = true;
                        $payment_date = date('M d, Y', strtotime($payment_data['approved_at']));
                        $payment_method = $payment_data['payment_method'];
                    }
                }
                
                if ($is_approved && $fee['status'] == 'paid') {
                    echo 'printContent += \'<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Paid</span>\';';
                } elseif ($fee['status'] == 'partial') {
                    echo 'printContent += \'<span class="badge badge-warning"><i class="fas fa-clock me-1"></i>Partial</span>\';';
                } else {
                    echo 'printContent += \'<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>Unpaid</span>\';';
                }
                ?>
                printContent += '</td>';
                printContent += '<td><?php echo $payment_date ?: '-'; ?></td>';
                printContent += '<td><?php echo $payment_method ? str_replace('_', ' ', ucfirst($payment_method)) : '-'; ?></td>';
                printContent += '</tr>';
                <?php endforeach; ?>
                
                printContent += '</tbody></table></div>';
                
                // Add footer
                printContent += '<div class="footer">';
                printContent += '<p><em>This is an official fee statement from Aditya Boys Hostel</em></p>';
                printContent += '<p>Generated on ' + new Date().toLocaleDateString('en-IN') + '</p>';
                printContent += '</div>';
                printContent += '</body></html>';
                
                console.log('Creating print window...');
                console.log('Print content length:', printContent.length);
                
                // Create print window and print
                const printWindow = window.open('', '_blank', 'width=800,height=600');
                if (printWindow) {
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.focus();
                    
                    // Add a delay before printing to ensure content is loaded
                    setTimeout(() => {
                        printWindow.print();
                        console.log('Print command sent to window');
                    }, 500);
                    
                    console.log('Print window created successfully');
                } else {
                    console.error('Failed to open print window');
                    alert('Failed to open print window. Please check your browser popup blocker.');
                }
                
            } catch (error) {
                console.error('Error printing statement:', error);
                alert('Error generating statement: ' + error.message);
            }
        }
        
        // Add search/filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    
    <style>
        @media print {
            .sidebar, .top-bar, .btn, .alert-info {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .table-container {
                break-inside: avoid;
            }
        }
    </style>
</body>
</html>
