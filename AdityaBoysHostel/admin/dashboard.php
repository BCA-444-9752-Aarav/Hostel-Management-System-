<?php
session_start();
require_once '../config/db.php';

// Debug session state
error_log("Dashboard accessed - Session data: " . print_r($_SESSION, true));

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    error_log("Admin not logged in - redirecting to login");
    header('Location: ../index.php');
    exit();
}

error_log("Admin logged in - ID: " . $_SESSION['admin_id'] . ", Name: " . $_SESSION['admin_name']);

// Get dashboard statistics
$total_students = 0;
$pending_approvals = 0;
$total_fees = 0;
$pending_complaints = 0;

// Total students
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE status = 'approved'");
$stmt->execute();
$result = $stmt->get_result();
$total_students = $result->fetch_assoc()['count'];

// Pending approvals
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
$pending_approvals = $result->fetch_assoc()['count'];

// Total fees (current month)
$current_month = date('F');
$current_year = date('Y');
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM fees WHERE month = ? AND year = ? AND status = 'paid'");
$stmt->bind_param("si", $current_month, $current_year);
$stmt->execute();
$result = $stmt->get_result();
$total_fees = $result->fetch_assoc()['total'] ?? 0;

// Pending complaints
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM complaints WHERE status IN ('pending', 'in_progress')");
$stmt->execute();
$result = $stmt->get_result();
$pending_complaints = $result->fetch_assoc()['count'];

// Get recent activities
$recent_students = [];
$stmt = $conn->prepare("SELECT full_name, email, created_at FROM students ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_students[] = $row;
}

// Get payment statistics
$payment_stats = [
    'total_payments' => 0,
    'pending_payments' => 0,
    'approved_payments' => 0,
    'total_collected' => 0
];
try {
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count, SUM(amount) as total FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $payment_stats['total_payments'] += $row['count'];
        if ($row['status'] == 'Pending Verification') {
            $payment_stats['pending_payments'] = $row['count'];
        } elseif ($row['status'] == 'Approved') {
            $payment_stats['approved_payments'] = $row['count'];
            $payment_stats['total_collected'] += $row['total'];
        }
    }
} catch (Exception $e) {
    // Payments table might not exist yet
    error_log("Payment stats error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Fix for animation issues */
        .animate__animated {
            animation-duration: 0.8s;
            animation-fill-mode: both;
        }
        
        .animate__fadeInUp {
            animation-name: fadeInUp;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 30px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .animate__fadeInUp {
            animation-name: fadeInUp;
        }
        
        /* Custom Styles */
        .toast {
            min-width: 250px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
                <p class="text-white-50 mb-0">Admin Panel</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item active">
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
                <a href="payment_history.php" class="sidebar-menu-item">
                    <i class="fas fa-history"></i> Payment History
                </a>
                <a href="payment_info.php" class="sidebar-menu-item">
                    <i class="fas fa-info-circle"></i> Payment Information
                </a>
                <a href="manage_notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </a>
                <a href="manage_complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comments"></i> Complaints
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
                <h1 class="top-bar-title">Dashboard</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Admin" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <!-- Statistics Cards -->
                <div class="dashboard-cards">
                    <div class="dashboard-card animate__animated animate__fadeInUp">
                        <div class="dashboard-card-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="dashboard-card-value"><?php echo $total_students; ?></div>
                        <div class="dashboard-card-label">Total Students</div>
                    </div>
                    
                    <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                        <div class="dashboard-card-icon pink">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div class="dashboard-card-value"><?php echo $pending_approvals; ?></div>
                        <div class="dashboard-card-label">Pending Approvals</div>
                    </div>
                    
                    <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                        <div class="dashboard-card-icon green">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="dashboard-card-value"><?php echo number_format($total_fees, 0); ?></div>
                        <div class="dashboard-card-label">Total Fees (<?php echo $current_month; ?>)</div>
                    </div>
                    
                    <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                        <div class="dashboard-card-icon blue">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="dashboard-card-value"><?php echo $pending_complaints; ?></div>
                        <div class="dashboard-card-label">Pending Complaints</div>
                    </div>
                    
                    <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                        <div class="dashboard-card-icon green">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="dashboard-card-value"><?php echo $payment_stats['total_payments']; ?></div>
                        <div class="dashboard-card-label">Recent Payments</div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="dashboard-activities">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="table-container animate__animated animate__fadeInUp">
                                <h5 class="mb-3">Recent Student Registrations</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                                <h5 class="mb-3">Recent Payments</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $recent_payments = [];
                                            try {
                                                $stmt = $conn->prepare("SELECT p.*, s.full_name FROM payments p JOIN students s ON p.student_id = s.id ORDER BY p.created_at DESC LIMIT 5");
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                while ($row = $result->fetch_assoc()) {
                                                    $recent_payments[] = $row;
                                                }
                                            } catch (Exception $e) {
                                                // Payments table might not exist yet
                                            }
                                            
                                            foreach ($recent_payments as $payment): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                                                <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $payment['status'] == 'Approved' ? 'bg-success' : ($payment['status'] == 'Rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                                        <?php echo $payment['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($recent_payments)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No recent payments</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <a href="payment_history.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-history me-1"></i>View All Payments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                                <h5 class="mb-3">Quick Actions</h5>
                                <div class="row g-3">
                                    <div class="col-md-3 col-6">
                                        <a href="manage_students.php" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user-plus me-2"></i> Approve Students
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="manage_rooms.php" class="btn btn-success w-100 h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-door-open me-2"></i> Allocate Rooms
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="manage_fees.php" class="btn btn-warning w-100 h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-money-check-alt me-2"></i> Manage Fees
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="manage_complaints.php" class="btn btn-info w-100 h-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-clipboard-check me-2"></i> Review Complaints
                                        </a>
                                    </div>
                                </div>
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
        // Initialize animations when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard loaded successfully');
            
            // Add animation classes to elements if they don't have them
            const animatedElements = document.querySelectorAll('.dashboard-card, .table-container');
            animatedElements.forEach((element, index) => {
                if (!element.classList.contains('animate__animated')) {
                    element.classList.add('animate__animated', 'animate__fadeInUp');
                    element.style.animationDelay = (index * 0.1) + 's';
                }
            });
        });
        
        // Sidebar Toggle Functionality
        function initSidebarToggle() {
            const toggle = document.getElementById("sidebarToggle");
            const sidebar = document.getElementById("sidebar");
            
            if (toggle && sidebar) {
                toggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    sidebar.classList.toggle("active");
                });
                
                // Close sidebar when clicking outside
                document.addEventListener("click", function(e) {
                    if (sidebar.classList.contains("active") && 
                        !sidebar.contains(e.target) && 
                        !toggle.contains(e.target)) {
                        sidebar.classList.remove("active");
                    }
                });
                
                // Close sidebar on escape key
                document.addEventListener("keydown", function(e) {
                    if (e.key === "Escape" && sidebar.classList.contains("active")) {
                        sidebar.classList.remove("active");
                    }
                });
                
                // Handle window resize
                window.addEventListener("resize", function() {
                    if (window.innerWidth > 992) {
                        sidebar.classList.remove("active");
                    }
                });
            }
        }
        
        // Initialize sidebar toggle
        initSidebarToggle();
        
        // Responsive layout handler
        function handleResponsiveLayout() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");
            const screenWidth = window.innerWidth;
            
            // Adjust main content margin based on screen size
            if (screenWidth <= 992) {
                mainContent.style.marginLeft = '0';
            } else {
                mainContent.style.marginLeft = '260px';
            }
            
            // Close sidebar on resize if it's open and we're on desktop
            if (screenWidth > 992 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
            
            // Update dashboard cards grid
            updateDashboardCardsGrid(screenWidth);
            
            // Update table responsiveness
            updateTableResponsiveness(screenWidth);
            
            // Update modal positioning
            updateModalPositioning(screenWidth);
            
            console.log('Responsive layout updated for screen width:', screenWidth);
        }
        
        // Update dashboard cards grid based on screen size
        function updateDashboardCardsGrid(screenWidth) {
            const cards = document.querySelectorAll('.dashboard-cards');
            cards.forEach(container => {
                if (screenWidth >= 1400) {
                    container.style.gridTemplateColumns = 'repeat(6, 1fr)';
                } else if (screenWidth >= 1200) {
                    container.style.gridTemplateColumns = 'repeat(5, 1fr)';
                } else if (screenWidth >= 992) {
                    container.style.gridTemplateColumns = 'repeat(4, 1fr)';
                } else if (screenWidth >= 768) {
                    container.style.gridTemplateColumns = 'repeat(3, 1fr)';
                } else if (screenWidth >= 576px) {
                    container.style.gridTemplateColumns = 'repeat(2, 1fr)';
                } else {
                    container.style.gridTemplateColumns = '1fr';
                }
            });
            
            // Update charts grid
            const charts = document.querySelectorAll('.dashboard-charts');
            charts.forEach(container => {
                if (screenWidth >= 992) {
                    container.style.gridTemplateColumns = 'repeat(3, 1fr)';
                } else if (screenWidth >= 768) {
                    container.style.gridTemplateColumns = 'repeat(2, 1fr)';
                } else {
                    container.style.gridTemplateColumns = '1fr';
                }
            });
            
            // Update activities grid
            const activities = document.querySelectorAll('.dashboard-activities');
            activities.forEach(container => {
                container.style.gridTemplateColumns = '1fr';
            });
        }
        
        // Update table responsiveness
        function updateTableResponsiveness(screenWidth) {
            const tables = document.querySelectorAll('.table-responsive');
            tables.forEach(table => {
                if (screenWidth <= 768) {
                    table.style.fontSize = '0.75rem';
                } else {
                    table.style.fontSize = '0.875rem';
                }
            });
        }
        
        // Update modal positioning for mobile
        function updateModalPositioning(screenWidth) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (screenWidth <= 768) {
                    modal.style.padding = '0';
                }
            });
        }
        
        // Debounced resize handler
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                handleResponsiveLayout();
            }, 250);
        });
        
        // Handle orientation change
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                handleResponsiveLayout();
            }, 100);
        });
        
        // Initial responsive layout setup
        handleResponsiveLayout();
        
        // Handle device pixel ratio changes
        window.addEventListener('device-pixel-ratio-change', function() {
            handleResponsiveLayout();
        });
        
        // Performance optimization for mobile
        if (window.innerWidth <= 768) {
            // Disable animations on mobile for better performance
            document.body.classList.add('mobile-optimized');
        }
        
        // Function to manually trigger responsive updates
        window.updateResponsiveLayout = handleResponsiveLayout;
    </script>
</body>
</html>
