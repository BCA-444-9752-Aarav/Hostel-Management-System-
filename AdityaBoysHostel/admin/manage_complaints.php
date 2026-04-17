<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle complaint actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'update_status') {
        $complaint_id = $_POST['complaint_id'];
        $status = $_POST['status'];
        $admin_response = $_POST['admin_response'];
        
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, admin_response = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ssi", $status, $admin_response, $complaint_id);
        $stmt->execute();
        
        // Get student ID for notification
        $stmt = $conn->prepare("SELECT student_id FROM complaints WHERE id = ?");
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student_id = $result->fetch_assoc()['student_id'];
        
        // Add notification
        $notification_title = "Complaint " . ucfirst($status);
        $notification_message = "Your complaint has been marked as " . $status;
        if (!empty($admin_response)) {
            $notification_message .= ". Response: " . $admin_response;
        }
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES ('student', ?, ?, ?)");
        $stmt->bind_param("iss", $student_id, $notification_title, $notification_message);
        $stmt->execute();
        
        $success = "Complaint status updated successfully!";
    }
}

// Get filters
$status_filter = $_GET['status'] ?? 'all';
$category_filter = $_GET['category'] ?? 'all';

// Get complaints list
$complaints = [];
$sql = "SELECT c.*, s.full_name, s.email FROM complaints c JOIN students s ON c.student_id = s.id WHERE 1=1";
$params = [];
$types = '';

if ($status_filter != 'all') {
    $sql .= " AND c.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($category_filter != 'all') {
    $sql .= " AND c.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}

// Calculate statistics
$pending_count = 0;
$in_progress_count = 0;
$resolved_count = 0;

foreach ($complaints as $complaint) {
    switch($complaint['status']) {
        case 'pending':
            $pending_count++;
            break;
        case 'in_progress':
            $in_progress_count++;
            break;
        case 'resolved':
            $resolved_count++;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Management - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Modal improvements */
        .modal-dialog {
            margin: 1.75rem auto;
            max-width: 95vw;
            width: auto;
        }
        
        .modal-lg {
            max-width: 900px;
            min-width: 600px;
        }
        
        .modal-xl {
            max-width: 1140px;
            min-width: 800px;
        }
        
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 1.25rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .modal-title {
            font-weight: 600;
            margin: 0;
        }
        
        .modal-body {
            padding: 2rem;
            background: #ffffff;
            max-height: calc(90vh - 140px);
            overflow-y: auto;
        }
        
        .modal-footer {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 15px 15px;
            padding: 1.25rem 1.5rem;
            position: sticky;
            bottom: 0;
            z-index: 10;
        }
        
        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
        
        .btn-close:hover {
            opacity: 1;
        }
        
        /* Enhanced Dark Mode Support for Modal - IMPROVED VISIBILITY */
        .modal-content {
            background-color: #0f1419 !important;
            color: #ffffff !important;
            border: 1px solid #30363d !important;
            z-index: 10500 !important;
        }
        
        .modal-header {
            background-color: #161b22 !important;
            border-bottom: 1px solid #30363d !important;
            color: #ffffff !important;
        }
        
        .modal-title {
            color: #ffffff !important;
            font-weight: 600;
        }
        
        .modal-body {
            background-color: #0f1419 !important;
            color: #ffffff !important;
            z-index: 10501 !important;
            position: relative !important;
        }
        
        .modal-footer {
            background-color: #161b22 !important;
            border-top: 1px solid #30363d !important;
        }
        
        .btn-close {
            filter: brightness(0) invert(1) !important;
            opacity: 1 !important;
        }
        
        .btn-close:hover {
            opacity: 0.8 !important;
        }
        
        /* Enhanced form controls with IMPROVED visibility */
        .form-control {
            background-color: #21262d !important;
            border: 1px solid #30363d !important;
            color: #ffffff !important;
            pointer-events: auto !important;
            user-select: text !important;
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            opacity: 1 !important;
            z-index: 99999 !important;
            position: relative !important;
            border-radius: 6px !important;
        }
        
        .form-control:focus {
            background-color: #262c36 !important;
            border-color: #58a6ff !important;
            box-shadow: 0 0 0 0.25rem rgba(88, 166, 255, 0.25) !important;
            color: #ffffff !important;
            outline: none !important;
            z-index: 99999 !important;
        }
        
        .form-control::placeholder {
            color: #8b949e !important;
            opacity: 0.7 !important;
        }
        
        .form-label {
            color: #c9d1d9 !important;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .form-text {
            color: #8b949e !important;
            font-size: 0.875rem;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical !important;
        }
        
        /* Select dropdown styling */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%238b949e' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 16px 12px !important;
            padding-right: 2.5rem !important;
        }
        
        /* Force dark mode for all modal elements */
        .modal * {
            background-color: transparent !important;
        }
        
        .modal .form-control {
            background-color: #21262d !important;
        }
        
        .modal .form-control:focus {
            background-color: #262c36 !important;
        }
        
        /* Remove any modal backdrop blocking */
        .modal-backdrop {
            pointer-events: none !important;
        }
        
        .modal.show .modal-dialog {
            pointer-events: auto !important;
        }
        
        /* Force all elements to be clickable */
        * {
            pointer-events: auto !important;
        }
        
        /* Alert improvements */
        .alert {
            border: none;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }
        
        /* Modal overlay fix */
        .modal.show .modal-dialog {
            pointer-events: auto !important;
        }
        
        .modal-backdrop {
            pointer-events: auto !important;
        }
        
        /* Ensure modal content is above backdrop */
        .modal-content {
            z-index: 1055 !important;
        }
        
        .modal-body {
            z-index: 1056 !important;
            position: relative !important;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0.25rem;
                max-width: 98vw;
                width: 100%;
            }
            
            .modal-lg {
                max-width: 98vw;
                min-width: auto;
            }
            
            .modal-content {
                max-height: 95vh;
                margin: 0.25rem;
            }
            
            .modal-body {
                padding: 1rem;
                max-height: calc(95vh - 120px);
            }
            
            .modal-header,
            .modal-footer {
                padding: 0.75rem 1rem;
            }
            
            .modal-title {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0;
                max-width: 100vw;
                width: 100%;
                height: 100vh;
            }
            
            .modal-content {
                max-height: 100vh;
                margin: 0;
                border-radius: 0;
            }
            
            .modal-body {
                padding: 0.75rem;
                max-height: calc(100vh - 100px);
            }
            
            .modal-header {
                border-radius: 0;
            }
            
            .modal-footer {
                border-radius: 0;
            }
        }
        
        @media (min-width: 1400px) {
            .modal-xl {
                max-width: 1320px;
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
                <p class="text-white-50 mb-0">Admin Panel</p>
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
                <a href="payment_history.php" class="sidebar-menu-item">
                    <i class="fas fa-history"></i> Payment History
                </a>
                <a href="payment_info.php" class="sidebar-menu-item">
                    <i class="fas fa-info-circle"></i> Payment Information
                </a>
                <a href="manage_complaints.php" class="sidebar-menu-item active">
                    <i class="fas fa-comments"></i> Complaints
                </a>
                <a href="manage_notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-bell"></i> Notifications
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
                <h1 class="top-bar-title">Complaint Management</h1>
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
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp">
                            <div class="dashboard-card-icon pink">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $pending_count; ?></div>
                            <div class="dashboard-card-label">Pending Complaints</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $in_progress_count; ?></div>
                            <div class="dashboard-card-label">In Progress</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <div class="dashboard-card-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $resolved_count; ?></div>
                            <div class="dashboard-card-label">Resolved</div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-8 col-md-12">
                        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                            <div class="flex-grow-1" style="min-width: 150px;">
                                <select class="form-control" name="status">
                                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="flex-grow-1" style="min-width: 150px;">
                                <select class="form-control" name="category">
                                    <option value="all" <?php echo $category_filter == 'all' ? 'selected' : ''; ?>>All Categories</option>
                                    <option value="maintenance" <?php echo $category_filter == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="food" <?php echo $category_filter == 'food' ? 'selected' : ''; ?>>Food</option>
                                    <option value="security" <?php echo $category_filter == 'security' ? 'selected' : ''; ?>>Security</option>
                                    <option value="other" <?php echo $category_filter == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </form>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end mt-3 mt-lg-0">
                        <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                            <button class="btn btn-primary" onclick="window.location.href='manage_notifications.php'">
                                <i class="fas fa-bell me-2"></i>Send Notification
                            </button>
                            <button class="btn btn-success" onclick="exportToCSV('complaintsTable', 'complaints_report.csv')">
                                <i class="fas fa-file-csv me-2"></i>Export CSV
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Complaints Table -->
                <div class="table-container animate__animated animate__fadeInUp">
                    <div class="table-responsive">
                        <table class="table" id="complaintsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Resolved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td>#<?php echo $complaint['id']; ?></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($complaint['full_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($complaint['email']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                    <td>
                                        <?php
                                        $category_colors = [
                                            'maintenance' => 'badge-info',
                                            'food' => 'badge-warning',
                                            'security' => 'badge-danger',
                                            'other' => 'badge-secondary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $category_colors[$complaint['category']]; ?>">
                                            <?php echo ucfirst($complaint['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                             title="<?php echo htmlspecialchars($complaint['description']); ?>">
                                            <?php echo htmlspecialchars($complaint['description']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_colors = [
                                            'pending' => 'badge-danger',
                                            'in_progress' => 'badge-warning',
                                            'resolved' => 'badge-success'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $status_colors[$complaint['status']]; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></td>
                                    <td><?php echo $complaint['status'] === 'resolved' ? date('M d, Y H:i', strtotime($complaint['updated_at'])) : '-'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info me-1" onclick="viewComplaint(<?php echo $complaint['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success me-1" onclick="openResponseModal(<?php echo $complaint['id']; ?>)" title="Add Response">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($complaints)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No complaints found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Complaint Modal -->
    <div class="modal fade" id="updateComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="updateComplaintForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="complaint_id" id="updateComplaintId">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="updateStatus" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Response</label>
                            <textarea class="form-control" name="admin_response" id="updateAdminResponse" rows="4" placeholder="Enter your response to the student's complaint..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Complaint</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View Complaint Modal - FORCED DARK MODE -->
    <div class="modal fade" id="viewComplaintModal" tabindex="-1" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="complaintDetails">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading complaint details...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Response Modal - GUARANTEED WORKING VERSION -->
    <div class="modal fade" id="addResponseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Response to Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="addResponseForm" onsubmit="return handleResponseSubmit(event)">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="complaint_id" id="responseComplaintId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="responseComplaintIdDisplay" class="form-label">Complaint ID</label>
                                    <input type="text" class="form-control" id="responseComplaintIdDisplay" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="responseStatus" class="form-label">Status</label>
                                    <select class="form-control" name="status" id="responseStatus" required>
                                        <option value="">Select Status...</option>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="resolved">Resolved</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="responseAdminResponse" class="form-label">Admin Response *</label>
                            <textarea class="form-control" name="admin_response" id="responseAdminResponse" rows="5" 
                                      placeholder="Type your response here..." required
                                      style="background: #21262d !important; border: 2px solid #58a6ff !important; color: #ffffff !important;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Send Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Global variable for current complaint ID
        let currentComplaintId = null;
        
        // Ensure Bootstrap is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            // Check if Bootstrap is available
            if (typeof bootstrap === 'undefined') {
                console.error('❌ Bootstrap is not loaded!');
                alert('Error: Bootstrap is not loaded. Please refresh the page.');
                return;
            }
            
            console.log('✅ Bootstrap loaded successfully');
            
            // Check if modal exists
            const modal = document.getElementById('addResponseModal');
            if (modal) {
                console.log('✅ Modal found');
            } else {
                console.error('❌ Modal not found');
                return;
            }
            
            // Check if response form exists
            const responseForm = document.getElementById('addResponseForm');
            if (responseForm) {
                console.log('✅ Response form found');
            } else {
                console.error('❌ Response form not found');
                return;
            }
        });
        
        function viewComplaint(complaintId) {
            try {
                console.log('viewComplaint called with:', complaintId);
                currentComplaintId = complaintId;
                
                // Show loading state
                const detailsDiv = document.getElementById('complaintDetails');
                if (!detailsDiv) {
                    console.error('complaintDetails element not found');
                    alert('Error: Modal content area not found. Please refresh the page.');
                    return;
                }
                
                detailsDiv.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading complaint details...</p>
                    </div>
                `;
                
                // Show modal
                const modalElement = document.getElementById('viewComplaintModal');
                if (!modalElement) {
                    console.error('viewComplaintModal element not found');
                    alert('Error: Modal not found. Please refresh the page.');
                    return;
                }
                
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                
                // Load complaint details via AJAX
                fetch('get_complaint_details.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'complaint_id=' + complaintId
                })
                .then(response => {
                    console.log('AJAX response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('AJAX response received');
                    detailsDiv.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading complaint details:', error);
                    detailsDiv.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                            <h5>Error Loading Details</h5>
                            <p>Unable to load complaint details. Please try again.</p>
                            <button type="button" class="btn btn-primary mt-2" onclick="viewComplaint(${complaintId})">Retry</button>
                        </div>
                    `;
                });
                
            } catch (error) {
                console.error('Error in viewComplaint:', error);
                alert('Error opening complaint details. Please try again.');
            }
        }
        
        function openResponseModal(complaintId) {
            console.log('Opening response modal for complaint:', complaintId);
            
            // Set form values
            document.getElementById('responseComplaintId').value = complaintId;
            document.getElementById('responseComplaintIdDisplay').value = '#' + complaintId;
            document.getElementById('responseStatus').value = 'in_progress';
            
            // Clear response field
            const responseField = document.getElementById('responseAdminResponse');
            responseField.value = '';
            
            // Force enable the input field with IMPROVED visibility colors
            responseField.disabled = false;
            responseField.readOnly = false;
            responseField.style.pointerEvents = 'auto';
            responseField.style.userSelect = 'text';
            responseField.style.backgroundColor = '#21262d';
            responseField.style.color = '#ffffff';
            responseField.style.borderColor = '#30363d';
            responseField.style.zIndex = '99999';
            
            // Add event listeners for debugging
            responseField.addEventListener('input', function(e) {
                console.log('✅ Input working:', e.target.value);
            });
            
            responseField.addEventListener('click', function(e) {
                console.log('✅ Click working');
                e.target.focus();
            });
            
            responseField.addEventListener('focus', function(e) {
                console.log('✅ Focus working');
            });
            
            responseField.addEventListener('keydown', function(e) {
                console.log('✅ Keydown working:', e.key);
            });
            
            // Show modal WITHOUT backdrop to prevent blocking
            const modal = new bootstrap.Modal(document.getElementById('addResponseModal'), {
                backdrop: false,
                keyboard: true
            });
            modal.show();
            
            // Force focus on response field
            setTimeout(() => {
                responseField.focus();
                console.log('✅ Modal opened without backdrop, field forced to focus');
                
                // Test with manual input
                setTimeout(() => {
                    responseField.value = 'Test typing...';
                    responseField.focus();
                    console.log('✅ Manual test completed');
                }, 1000);
            }, 500);
        }
        
        function handleResponseSubmit(event) {
            event.preventDefault();
            console.log('✅ Form submission handled');
            
            const statusField = document.getElementById('responseStatus');
            const responseField = document.getElementById('responseAdminResponse');
            
            // Validation
            if (!statusField.value) {
                alert('Please select a status.');
                return false;
            }
            
            if (!responseField.value.trim()) {
                alert('Please enter an admin response.');
                return false;
            }
            
            console.log('✅ Form validation passed');
            console.log('Status:', statusField.value);
            console.log('Response:', responseField.value);
            
            // Submit the form normally
            event.target.submit();
            return true;
        }
    </script>
</body>
</html>
