<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $student_id = $_POST['student_id'];
    
    if ($action == 'approve') {
        $stmt = $conn->prepare("UPDATE students SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        // Add notification
        $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES ('student', ?, 'Account Approved', 'Your hostel account has been approved. You can now login.')");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $success = "Student approved successfully!";
    } elseif ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE students SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $success = "Student rejected successfully!";
    } elseif ($action == 'activate') {
        $stmt = $conn->prepare("UPDATE students SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $success = "Student activated successfully!";
    } elseif ($action == 'deactivate') {
        $stmt = $conn->prepare("UPDATE students SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $success = "Student deactivated successfully!";
    } elseif ($action == 'delete_student') {
        // Check if student has allocated room
        $stmt = $conn->prepare("SELECT room_id FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        
        if ($student['room_id']) {
            // Update room occupancy
            $stmt = $conn->prepare("UPDATE rooms SET occupied_beds = occupied_beds - 1 WHERE id = ?");
            $stmt->bind_param("i", $student['room_id']);
            $stmt->execute();
        }
        
        // Delete student
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $success = "Student deleted successfully!";
    } elseif ($action == 'edit_student') {
        $student_id = $_POST['student_id'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $parent_mobile = $_POST['parent_mobile'] ?? '';
        $address = $_POST['address'] ?? '';
        $bed_number = $_POST['bed_number'] ?? null;
        $new_password = $_POST['new_password'] ?? '';
        
        // Validate required fields
        if (empty($full_name) || empty($email) || empty($mobile)) {
            $error = "Required fields (name, email, mobile) cannot be empty!";
            echo json_encode(['success' => false, 'error' => $error]);
            exit();
        }
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Check if email is being changed and if it already exists for another student
            $current_email_stmt = $conn->prepare("SELECT email FROM students WHERE id = ?");
            $current_email_stmt->bind_param("i", $student_id);
            $current_email_stmt->execute();
            $current_student = $current_email_stmt->get_result()->fetch_assoc();
            $current_email = $current_student['email'];
            
            // If email is being changed, check for duplicates
            if ($email !== $current_email) {
                $check_email_stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
                $check_email_stmt->bind_param("si", $email, $student_id);
                $check_email_stmt->execute();
                $existing_student = $check_email_stmt->get_result()->fetch_assoc();
                
                if ($existing_student) {
                    $conn->rollback();
                    $error = "Email '$email' is already registered to another student!";
                    echo json_encode(['success' => false, 'error' => $error]);
                    exit();
                }
            }
            
            // Update student details with database fields only
            $stmt = $conn->prepare("UPDATE students SET 
                full_name = ?, 
                email = ?, 
                mobile = ?, 
                parent_mobile = ?, 
                address = ?, 
                bed_number = ? 
                WHERE id = ?");
            
            $stmt->bind_param("ssssssi", 
                $full_name, 
                $email, 
                $mobile, 
                $parent_mobile, 
                $address, 
                $bed_number, 
                $student_id);
            $stmt->execute();
            
            // Handle password update separately
            $password_updated = false;
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
                $password_stmt->bind_param("si", $hashed_password, $student_id);
                $password_stmt->execute();
                $password_updated = true;
                
                $password_msg = " Password updated to: $new_password";
                $notification_title = 'Password Updated';
                $notification_message = "Your password has been updated by admin. New password: $new_password";
            } else {
                $password_msg = "";
                $notification_title = 'Profile Updated';
                $notification_message = 'Your profile details have been updated by admin.';
            }
            
            // Add notification for details update
            $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES ('student', ?, ?, ?)");
            $stmt->bind_param("iss", $student_id, $notification_title, $notification_message);
            
            if ($stmt->execute()) {
                $conn->commit();
                $success = "Student details updated successfully!$password_msg";
            } else {
                $conn->rollback();
                $error = "Failed to update student details. Please try again.";
                echo json_encode(['success' => false, 'error' => $error]);
                exit();
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Database error: " . $e->getMessage();
            echo json_encode(['success' => false, 'error' => $error]);
            exit();
        }
    }
}

// Get students list
$students = [];
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM students";
$params = [];
$types = '';

if ($filter != 'all') {
    $sql .= " WHERE status = ?";
    $params[] = $filter;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= ($filter != 'all' ? " AND" : " WHERE") . " (full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom Modal Styles -->
    <style>
        /* Main Modal Container */
        .modal.fade {
            transition: opacity 0.3s ease-out;
        }
        
        .modal.show {
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Modal Dialog - Proper Sizing and Centering */
        .modal-dialog {
            margin: 2rem auto;
            max-width: 650px;
            width: 90%;
            max-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (min-width: 1200px) {
            .modal-dialog {
                max-width: 700px;
            }
        }
        
        @media (min-width: 992px) and (max-width: 1199px) {
            .modal-dialog {
                max-width: 600px;
            }
        }
        
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 1rem auto;
                max-width: 95%;
                width: 95%;
            }
        }
        
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem auto;
                max-width: 98%;
                width: 98%;
            }
        }
        
        /* Modal Content - Match Website Design System */
        .modal-content {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            transform: translateZ(0);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            backface-visibility: hidden;
            will-change: transform;
        }
        
        /* Modal Header - Match Website Theme */
        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
            flex-shrink: 0;
        }
        
        .modal-header .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
            margin: 0;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        
        /* Modal Body - Match Website Design */
        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            flex: 1;
            max-height: calc(90vh - 140px);
            pointer-events: auto;
            cursor: default;
            transform: translateZ(0);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1;
            background: var(--card-bg);
        }
        
        @media (max-width: 768px) {
            .modal-body {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .modal-body {
                padding: 1rem;
            }
        }
        
        /* Form Layout */
        .modal-body .row {
            margin: 0;
            gap: 1.5rem;
        }
        
        .modal-body .col-md-6 {
            padding: 0;
            flex: 1;
        }
        
        /* Form Groups */
        .modal-body .mb-3 {
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .modal-body .mb-3 {
                margin-bottom: 1.25rem;
            }
        }
        
        /* Form Labels - Match Website Typography */
        .modal-body .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1;
        }
        
        /* Form Controls - Match Website Form Styles */
        .modal-body .form-control,
        .modal-body .form-select {
            width: 100%;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background-color: var(--card-bg);
            color: var(--text-primary);
            min-height: 44px;
            box-sizing: border-box;
            cursor: pointer;
            pointer-events: auto;
            user-select: text;
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            transform: translateZ(0);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1;
            box-shadow: var(--shadow-sm);
        }
        
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            outline: none;
            background-color: var(--card-bg);
            cursor: text;
            transform: translateZ(0);
        }
        
        .modal-body .form-control:disabled,
        .modal-body .form-select:disabled {
            background-color: #f8f9fa;
            opacity: 0.6;
            pointer-events: none;
            cursor: not-allowed;
        }
        
        /* Textarea */
        .modal-body textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }
        
        /* Input Groups */
        .modal-body .input-group {
            display: flex;
            align-items: stretch;
        }
        
        .modal-body .input-group .form-control {
            flex: 1;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .modal-body .input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-left: none;
        }
        
        /* Alerts */
        .modal-body .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .modal-body .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            color: #1565c0;
            border-left: 4px solid #667eea;
        }
        
        .modal-body .alert-warning {
            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
            color: #f57c00;
            border-left: 4px solid #ffa726;
        }
        
        /* Modal Footer */
        .modal-footer {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 1.5rem 2rem;
            flex-shrink: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.75rem;
            position: sticky;
            bottom: 0;
            width: 100%;
            box-sizing: border-box;
        }
        
        @media (max-width: 768px) {
            .modal-footer {
                padding: 1rem 1.5rem;
                flex-direction: column;
                gap: 0.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .modal-footer {
                padding: 1rem;
            }
        }
        
        /* Footer Buttons - Match Website Button Styles */
        .modal-footer .btn {
            min-width: 120px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            white-space: nowrap;
            position: relative;
            z-index: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1;
            box-shadow: var(--shadow-sm);
        }
        
        @media (max-width: 768px) {
            .modal-footer .btn {
                width: 100%;
                min-width: auto;
            }
        }
        
        .modal-footer .btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .modal-footer .btn-primary:hover {
            background: var(--gradient-primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .modal-footer .btn-secondary {
            background: var(--text-muted);
            border: none;
            color: white;
        }
        
        .modal-footer .btn-secondary:hover {
            background: var(--text-secondary);
        }
        
        /* Modal Backdrop - Lighter and Less Intrusive */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(2px);
            pointer-events: none;
        }
        
        /* Fix Modal Interaction */
        .modal.show {
            pointer-events: auto;
        }
        
        .modal-dialog {
            pointer-events: auto;
        }
        
        .modal-content {
            pointer-events: auto;
        }
        
        .modal-body {
            pointer-events: auto;
        }
        
        .modal-body * {
            pointer-events: auto;
        }
        
        .modal-footer {
            pointer-events: auto;
        }
        
        .modal-footer * {
            pointer-events: auto;
        }
        
        /* Button Groups */
        .modal-body .d-flex.gap-2 {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .modal-body .d-flex.gap-2 .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }
        
        /* Small text */
        .modal-body small.text-muted {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: block;
        }
        
        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .modal-body .row {
                gap: 1rem;
            }
            
            .modal-body .col-md-6 {
                width: 100%;
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
                <a href="manage_students.php" class="sidebar-menu-item active">
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
                <a href="manage_complaints.php" class="sidebar-menu-item">
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
                <h1 class="top-bar-title">Manage Students</h1>
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
                
                <!-- Filters and Search -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group" role="group">
                                <a href="?filter=all" class="btn <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                                <a href="?filter=pending" class="btn <?php echo $filter == 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                                <a href="?filter=approved" class="btn <?php echo $filter == 'approved' ? 'btn-primary' : 'btn-outline-primary'; ?>">Active</a>
                                <a href="?filter=inactive" class="btn <?php echo $filter == 'inactive' ? 'btn-primary' : 'btn-outline-primary'; ?>">Inactive</a>
                                <a href="?filter=rejected" class="btn <?php echo $filter == 'rejected' ? 'btn-primary' : 'btn-outline-primary'; ?>">Rejected</a>
                            </div>
                            <button type="button" class="btn btn-success" onclick="sendAllBills()" title="Send Monthly Bills to All Active Students">
                                <i class="fas fa-envelope me-2"></i>Send All Bills
                            </button>
                            <button type="button" class="btn btn-danger" onclick="generatePDFReport()" title="Generate Student Payment Report in PDF">
                                <i class="fas fa-file-pdf me-2"></i>Generate PDF Report
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Students Table -->
                <div class="table-container animate__animated animate__fadeInUp">
                    <div class="table-responsive">
                        <table class="table" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Parent Mobile</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <img src="../uploads/<?php echo htmlspecialchars($student['profile_photo'] ?? 'default_avatar.svg'); ?>" 
                                             alt="Profile" class="rounded-circle" width="40" height="40"
                                             onerror="this.src='../assets/default_avatar.svg';">
                                    </td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                    <td><?php echo htmlspecialchars($student['parent_mobile']); ?></td>
                                    <td>
                                        <?php 
                                        if ($student['room_id']) {
                                            $stmt = $conn->prepare("SELECT room_number FROM rooms WHERE id = ?");
                                            $stmt->bind_param("i", $student['room_id']);
                                            $stmt->execute();
                                            $room = $stmt->get_result()->fetch_assoc();
                                            echo htmlspecialchars($room['room_number']) . ' - Bed ' . $student['bed_number'];
                                        } else {
                                            echo 'Not Assigned';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        switch($student['status']) {
                                            case 'approved':
                                                $status_class = 'badge-success';
                                                $status_text = 'Active';
                                                break;
                                            case 'pending':
                                                $status_class = 'badge-warning';
                                                $status_text = 'Pending';
                                                break;
                                            case 'rejected':
                                                $status_class = 'badge-danger';
                                                $status_text = 'Rejected';
                                                break;
                                            case 'inactive':
                                                $status_class = 'badge-secondary';
                                                $status_text = 'Inactive';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($student['status'] == 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-success" onclick="approveStudent(<?php echo $student['id']; ?>)" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="rejectStudent(<?php echo $student['id']; ?>)" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($student['status'] == 'approved'): ?>
                                                <button type="button" class="btn btn-sm btn-primary edit-student-btn" 
                                                        data-student-id="<?php echo $student['id']; ?>" 
                                                        data-student-name="<?php echo htmlspecialchars($student['full_name']); ?>" 
                                                        onclick="console.log('Direct edit onclick: ID=<?php echo $student['id']; ?>'); editStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name'], ENT_QUOTES); ?>');"
                                                        title="Edit Student">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="deactivateStudent(<?php echo $student['id']; ?>)" title="Deactivate">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($student['status'] == 'inactive'): ?>
                                                <button type="button" class="btn btn-sm btn-primary edit-student-btn" 
                                                        data-student-id="<?php echo $student['id']; ?>" 
                                                        data-student-name="<?php echo htmlspecialchars($student['full_name']); ?>" 
                                                        onclick="console.log('Direct edit onclick: ID=<?php echo $student['id']; ?>'); editStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name'], ENT_QUOTES); ?>');"
                                                        title="Edit Student">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" onclick="activateStudent(<?php echo $student['id']; ?>)" title="Reactivate Student">
                                                    <i class="fas fa-play"></i> Reactivate
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-sm btn-info view-student-btn" 
                                                    data-student-id="<?php echo $student['id']; ?>" 
                                                    onclick="console.log('Direct onclick: ID=<?php echo $student['id']; ?>'); viewStudent(<?php echo $student['id']; ?>);"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>)" title="Delete Student">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($students)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No students found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Student Details Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Details & Status Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="studentDetails">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editStudentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_student">
                        <input type="hidden" name="student_id" id="editStudentId">
                        
                        <!-- Student Info -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Editing details for: <strong id="editStudentName"></strong>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" id="editFullName" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="editEmail" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Mobile</label>
                                    <input type="tel" class="form-control" name="mobile" id="editMobile" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Parent Mobile</label>
                                    <input type="tel" class="form-control" name="parent_mobile" id="editParentMobile">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" id="editAddress" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Room Status</label>
                                    <select class="form-control" name="room_status" id="editRoomStatus">
                                        <option value="allocated">Allocated</option>
                                        <option value="not_allocated">Not Allocated</option>
                                        <option value="vacated">Vacated</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Bed Number</label>
                                    <input type="text" class="form-control" name="bed_number" id="editBedNumber">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">New Password (Optional)</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="new_password" id="editNewPassword" 
                                               minlength="8" placeholder="Leave empty to keep current password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleEditPassword()">
                                            <i class="fas fa-eye" id="editPasswordToggle"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimum 8 characters. Leave empty to keep current password.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Quick Password Options</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setEditPassword('password123')">
                                            Default
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setEditPassword('student123')">
                                            Student
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="generateEditPassword()">
                                            Random
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearEditPassword()">
                                            Clear
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> If you change the password, the student will be notified of the new password.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Student Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Debug function
        function debug(message) {
            console.log('[DEBUG]', message);
        }
        
        // Function to thoroughly check modal structure
        function debugModalStructure(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                debug('ERROR: Modal element not found: ' + modalId);
                return false;
            }
            
            debug('Checking modal structure for: ' + modalId);
            debug('Modal exists: ' + !!modal);
            debug('Modal classes: ' + modal.className);
            debug('Modal display: ' + modal.style.display);
            debug('Modal visibility: ' + modal.style.visibility);
            
            // Check critical fields for edit modal
            if (modalId === 'editStudentModal') {
                const criticalFields = [
                    'editStudentId',
                    'editStudentName', 
                    'editFullName',
                    'editEmail',
                    'editMobile',
                    'editParentMobile',
                    'editAddress',
                    'editRoomStatus',
                    'editBedNumber',
                    'editNewPassword'
                ];
                
                debug('Checking critical fields:');
                criticalFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    debug('  ' + fieldId + ': ' + (field ? 'EXISTS' : 'MISSING'));
                    if (field) {
                        debug('    - Type: ' + field.tagName);
                        debug('    - Name: ' + field.name);
                        debug('    - Value: ' + field.value);
                    }
                });
            }
            
            // Check modal body for view modal
            if (modalId === 'studentModal') {
                const modalBody = document.getElementById('studentDetails');
                debug('studentDetails body: ' + (modalBody ? 'EXISTS' : 'MISSING'));
                if (modalBody) {
                    debug('Body content length: ' + modalBody.innerHTML.length);
                }
            }
            
            return true;
        }
        
        // Function to safely reset modal without removing form fields
        function safeResetModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return false;
            
            // Dispose existing instance only
            const existingInstance = bootstrap.Modal.getInstance(modal);
            if (existingInstance) {
                existingInstance.dispose();
                debug('Modal instance disposed for: ' + modalId);
            }
            
            // Remove any leftover backdrop
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // Reset modal classes but keep structure intact
            modal.classList.remove('show');
            modal.style.display = '';
            modal.style.visibility = '';
            modal.setAttribute('aria-modal', 'false');
            modal.setAttribute('role', 'dialog');
            
            debug('Safe modal reset completed for: ' + modalId);
            return true;
        }
        
        // Function to recreate edit modal if it's corrupted
        function recreateEditModal() {
            try {
                debug('Recreating edit modal...');
                
                // Remove existing modal
                const existingModal = document.getElementById('editStudentModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Create new modal HTML
                const modalHTML = `
                    <div class="modal fade" id="editStudentModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Student Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" id="editStudentForm">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit_student">
                                        <input type="hidden" name="student_id" id="editStudentId">
                                        
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Editing details for: <strong id="editStudentName"></strong>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Full Name</label>
                                                    <input type="text" class="form-control" name="full_name" id="editFullName" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email" id="editEmail" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Mobile</label>
                                                    <input type="tel" class="form-control" name="mobile" id="editMobile" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Parent Mobile</label>
                                                    <input type="tel" class="form-control" name="parent_mobile" id="editParentMobile">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Address</label>
                                                    <textarea class="form-control" name="address" id="editAddress" rows="3"></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Room Status</label>
                                                    <select class="form-control" name="room_status" id="editRoomStatus">
                                                        <option value="allocated">Allocated</option>
                                                        <option value="not_allocated">Not Allocated</option>
                                                        <option value="vacated">Vacated</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Bed Number</label>
                                                    <input type="text" class="form-control" name="bed_number" id="editBedNumber">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">New Password (Optional)</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" name="new_password" id="editNewPassword" 
                                                               minlength="8" placeholder="Leave empty to keep current password">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleEditPassword()">
                                                            <i class="fas fa-eye" id="editPasswordToggle"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">Minimum 8 characters. Leave empty to keep current password.</small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Quick Password Options</label>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setEditPassword('password123')">
                                                            Default
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setEditPassword('student123')">
                                                            Student
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="generateEditPassword()">
                                                            Random
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearEditPassword()">
                                                            Clear
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <strong>Important:</strong> If you change the password, the student will be notified of the new password.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Student Details
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                debug('Edit modal recreated successfully');
                return true;
                
            } catch (error) {
                debug('Error recreating modal: ' + error.message);
                return false;
            }
        }
        
        function viewStudent(studentId) {
            try {
                debug('viewStudent called with ID: ' + studentId);
                
                if (!studentId) {
                    debug('Student ID is missing');
                    alert('Error: Student ID is missing');
                    return;
                }
                
                // Show loading state
                const modalBody = document.getElementById('studentDetails');
                if (!modalBody) {
                    debug('Modal body not found');
                    alert('Error: Modal body not found');
                    return;
                }
                
                modalBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading student details...</p></div>';
                
                // Get modal element and reset it
                const modalElement = document.getElementById('studentModal');
                if (!modalElement) {
                    debug('Student modal not found');
                    alert('Error: Modal not found');
                    return;
                }
                
                // Reset modal to clean state
                safeResetModal('studentModal');
                
                // Show modal first with NO backdrop for maximum brightness
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: false,
                    keyboard: true,
                    focus: true
                });
                modal.show();
                debug('Modal opened without backdrop');
                
                // Load student data via AJAX
                ajaxRequest('get_student_details.php', 'POST', 'student_id=' + studentId, function(response) {
                    try {
                        debug('Student details loaded successfully');
                        modalBody.innerHTML = response;
                        
                        // The onclick attributes in the loaded HTML will handle the button clicks
                        // No need to re-bind event listeners
                        
                    } catch (error) {
                        debug('Error processing student details: ' + error.message);
                        modalBody.innerHTML = '<div class="alert alert-danger">Error loading student details. Please try again.</div>';
                    }
                }, function(error) {
                    debug('AJAX error: ' + error);
                    modalBody.innerHTML = '<div class="alert alert-danger">Error loading student details. Please check your connection and try again.</div>';
                });
                
            } catch (error) {
                debug('Error viewing student: ' + error.message);
                alert('Error loading student details. Please try again.');
            }
        }
        
        function resetPassword(studentId, studentName) {
            try {
                console.log('resetPassword called with:', { studentId, studentName });
                
                if (!studentId || !studentName) {
                    console.error('Student ID or name is missing:', { studentId, studentName });
                    alert('Error: Student information is missing. Please refresh the page and try again.');
                    return;
                }
                
                // Wait a moment for DOM to be ready
                setTimeout(() => {
                    // Check if modal elements exist
                    const modal = document.getElementById('resetPasswordModal');
                    const studentIdField = document.getElementById('resetStudentId');
                    const studentNameField = document.getElementById('resetStudentName');
                    const newPasswordField = document.getElementById('newPassword');
                    const confirmPasswordField = document.getElementById('confirmPassword');
                    
                    console.log('Modal elements check:', {
                        modal: !!modal,
                        studentIdField: !!studentIdField,
                        studentNameField: !!studentNameField,
                        newPasswordField: !!newPasswordField,
                        confirmPasswordField: !!confirmPasswordField
                    });
                    
                    if (!modal || !studentIdField || !studentNameField || !newPasswordField || !confirmPasswordField) {
                        console.error('Modal elements not found, attempting to recreate modal...');
                        
                        // Try to find the modal in the DOM
                        const modalContainer = document.querySelector('#resetPasswordModal');
                        if (!modalContainer) {
                            console.error('Modal container completely missing from DOM');
                            alert('Error: Reset password form not found in page. Please refresh the page and try again.');
                            return;
                        }
                        
                        // Re-initialize modal elements
                        const recheckStudentIdField = document.getElementById('resetStudentId');
                        const recheckStudentNameField = document.getElementById('resetStudentName');
                        const recheckNewPasswordField = document.getElementById('newPassword');
                        const recheckConfirmPasswordField = document.getElementById('confirmPassword');
                        
                        if (!recheckStudentIdField || !recheckStudentNameField || !recheckNewPasswordField || !recheckConfirmPasswordField) {
                            console.error('Modal form elements missing even after recheck');
                            alert('Error: Reset password form elements are missing. Please refresh the page and try again.');
                            return;
                        }
                        
                        // Use rechecked elements
                        setupAndShowModal(modalContainer, recheckStudentIdField, recheckStudentNameField, recheckNewPasswordField, recheckConfirmPasswordField, studentId, studentName);
                    } else {
                        setupAndShowModal(modal, studentIdField, studentNameField, newPasswordField, confirmPasswordField, studentId, studentName);
                    }
                }, 100);
                
            } catch (error) {
                console.error('Error in resetPassword function:', error);
                alert('Error opening reset password form: ' + error.message + '. Please try again.');
            }
        }
        
        function setupAndShowModal(modal, studentIdField, studentNameField, newPasswordField, confirmPasswordField, studentId, studentName) {
            try {
                console.log('Setting up modal with data...');
                
                // Clear any previous form data
                studentIdField.value = studentId;
                studentNameField.textContent = studentName;
                newPasswordField.value = '';
                confirmPasswordField.value = '';
                
                // Clear any validation states
                newPasswordField.classList.remove('is-invalid', 'is-valid');
                confirmPasswordField.classList.remove('is-invalid', 'is-valid');
                
                console.log('Opening modal...');
                
                // Dispose any existing modal instance
                const existingModal = bootstrap.Modal.getInstance(modal);
                if (existingModal) {
                    existingModal.dispose();
                    console.log('Existing modal instance disposed');
                }
                
                // Show modal
                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();
                
                console.log('Modal opened successfully');
                
            } catch (error) {
                console.error('Error setting up modal:', error);
                alert('Error setting up reset password form: ' + error.message + '. Please try again.');
            }
        }
        
        function setPassword(password) {
            document.getElementById('newPassword').value = password;
            document.getElementById('confirmPassword').value = password;
        }
        
        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < 10; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            setPassword(password);
        }
        
        function togglePassword() {
            const passwordField = document.getElementById('newPassword');
            const toggleIcon = document.getElementById('passwordToggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Form validation and event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // View Student button click handler
            document.addEventListener('click', function(e) {
                // Check for view student button
                if (e.target.closest('.view-student-btn')) {
                    e.preventDefault();
                    console.log('View student button clicked');
                    const btn = e.target.closest('.view-student-btn');
                    const studentId = btn.getAttribute('data-student-id');
                    console.log('View student ID:', studentId);
                    if (studentId) {
                        viewStudent(studentId);
                    } else {
                        console.error('No student ID found on button');
                        alert('Error: Student ID not found');
                    }
                    return;
                }
                
                // Check for edit student button
                if (e.target.closest('.edit-student-btn')) {
                    e.preventDefault();
                    console.log('Edit student button clicked');
                    const btn = e.target.closest('.edit-student-btn');
                    const studentId = btn.getAttribute('data-student-id');
                    const studentName = btn.getAttribute('data-student-name');
                    console.log('Edit student data:', { studentId, studentName });
                    
                    // Check if attributes are properly set
                    if (!studentId || !studentName) {
                        console.error('Missing attributes on button:', {
                            studentId: studentId,
                            studentName: studentName,
                            button: btn.outerHTML
                        });
                        alert('Error: Button data is missing. Please refresh the page and try again.');
                        return;
                    }
                    
                    editStudent(studentId, studentName);
                    return;
                }
                
                // Handle direct action buttons (not in modal)
                if (e.target.closest('.btn-success[onclick*="approveStudent"]')) {
                    e.preventDefault();
                    const btn = e.target.closest('.btn-success[onclick*="approveStudent"]');
                    const studentId = btn.getAttribute('onclick').match(/approveStudent\((\d+)\)/);
                    if (studentId) {
                        approveStudent(parseInt(studentId[1]));
                    }
                    return;
                }
                
                if (e.target.closest('.btn-danger[onclick*="rejectStudent"]')) {
                    e.preventDefault();
                    const btn = e.target.closest('.btn-danger[onclick*="rejectStudent"]');
                    const studentId = btn.getAttribute('onclick').match(/rejectStudent\((\d+)\)/);
                    if (studentId) {
                        rejectStudent(parseInt(studentId[1]));
                    }
                    return;
                }
                
                if (e.target.closest('.btn-danger[onclick*="deleteStudent"]')) {
                    e.preventDefault();
                    const btn = e.target.closest('.btn-danger[onclick*="deleteStudent"]');
                    const studentId = btn.getAttribute('onclick').match(/deleteStudent\((\d+)\)/);
                    if (studentId) {
                        deleteStudent(parseInt(studentId[1]));
                    }
                    return;
                }
            });
            
            const editForm = document.getElementById('editStudentForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    console.log('Edit form submitted');
                    
                    const newPassword = document.getElementById('editNewPassword').value;
                    
                    if (newPassword && newPassword.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long!');
                        return false;
                    }
                    
                    const confirmed = confirm('Are you sure you want to update this student\'s details? The student will be notified of any changes.');
                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }
                    
                    console.log('Form submission proceeding...');
                    return true;
                });
            }
            
            // Reset form when modal is hidden
            const editModal = document.getElementById('editStudentModal');
            if (editModal) {
                editModal.addEventListener('hidden.bs.modal', function() {
                    // Clear form
                    editForm.reset();
                    
                    // Clear student info
                    document.getElementById('editStudentId').value = '';
                    document.getElementById('editStudentName').textContent = '';
                    
                    // Dispose modal instance
                    const modalInstance = bootstrap.Modal.getInstance(editModal);
                    if (modalInstance) {
                        modalInstance.dispose();
                        console.log('Edit modal instance disposed');
                    }
                });
            }
        });
        
        // AJAX request function for edit functionality
        function ajaxRequest(url, method, data, successCallback, errorCallback) {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        successCallback(xhr.responseText);
                    } else {
                        console.error('AJAX request failed:', xhr.status, xhr.statusText);
                        if (errorCallback) {
                            errorCallback(xhr.status, xhr.statusText);
                        }
                    }
                }
            };
            
            xhr.onerror = function() {
                console.error('AJAX request error');
                if (errorCallback) {
                    errorCallback('network error');
                }
            };
            
            xhr.send(data);
        }
        
        function editStudent(studentId, studentName) {
            try {
                console.log('editStudent called with:', { studentId, studentName });
                
                if (!studentId || !studentName) {
                    console.error('Student ID or name is missing:', { studentId, studentName });
                    alert('Error: Student information is missing. Please refresh the page and try again.');
                    return;
                }
                
                // Wait a moment for DOM to be ready
                setTimeout(() => {
                    // Get modal element and reset it
                    const modal = document.getElementById('editStudentModal');
                    if (!modal) {
                        console.error('Edit modal not found');
                        alert('Error: Edit modal not found. Please refresh the page and try again.');
                        return;
                    }
                    
                    // Reset modal to clean state
                    safeResetModal('editStudentModal');
                    
                    // Debug modal structure before checking fields
                    debugModalStructure('editStudentModal');
                    
                    // Get modal fields
                    const studentIdField = document.getElementById('editStudentId');
                    const studentNameField = document.getElementById('editStudentName');
                    
                    console.log('Modal fields check:', {
                        modal: !!modal,
                        studentIdField: !!studentIdField,
                        studentNameField: !!studentNameField
                    });
                    
                    if (!studentIdField || !studentNameField) {
                        console.error('Modal fields not found:', {
                            studentIdField: !!studentIdField,
                            studentNameField: !!studentNameField
                        });
                        
                        // Try to recreate modal if fields are missing
                        debug('Attempting to recreate modal...');
                        if (recreateEditModal()) {
                            // Try again after recreation
                            const newStudentIdField = document.getElementById('editStudentId');
                            const newStudentNameField = document.getElementById('editStudentName');
                            if (newStudentIdField && newStudentNameField) {
                                debug('Modal recreated successfully');
                                studentIdField = newStudentIdField;
                                studentNameField = newStudentNameField;
                            } else {
                                alert('Error: Modal fields not found and recreation failed. Please refresh the page and try again.');
                                return;
                            }
                        } else {
                            alert('Error: Modal fields not found. Please refresh the page and try again.');
                            return;
                        }
                    }
                    
                    // Set student info
                    studentIdField.value = studentId;
                    studentNameField.textContent = studentName;
                    
                    // Clear form fields and ensure they are enabled for input
                    const fields = [
                        'editFullName', 'editEmail', 'editMobile', 'editParentMobile',
                        'editAddress', 'editRoomStatus', 'editBedNumber', 'editNewPassword'
                    ];
                    
                    fields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            field.value = '';
                            field.disabled = false;
                            field.readOnly = false;
                            field.removeAttribute('disabled');
                            field.removeAttribute('readonly');
                            field.style.pointerEvents = 'auto';
                            field.style.userSelect = 'text';
                            field.style.webkitUserSelect = 'text';
                            field.style.mozUserSelect = 'text';
                            field.style.msUserSelect = 'text';
                        }
                    });
                    
                    console.log('Form setup completed');
                    
                    // Show modal immediately
                    const modalInstance = new bootstrap.Modal(modal, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    modalInstance.show();
                    
                    // Debug: Check if modal is visible
                    setTimeout(() => {
                        const modalElement = document.getElementById('editStudentModal');
                        if (modalElement) {
                            console.log('Modal classes:', modalElement.className);
                            console.log('Modal display:', modalElement.style.display);
                            console.log('Modal visibility:', window.getComputedStyle(modalElement).visibility);
                            
                            // Check if footer is visible
                            const footer = modalElement.querySelector('.modal-footer');
                            if (footer) {
                                console.log('Footer found:', footer.innerHTML);
                                console.log('Footer styles:', window.getComputedStyle(footer));
                            } else {
                                console.error('Footer not found in modal!');
                            }
                        }
                    }, 100);
                    
                    // Load student data asynchronously
                    console.log('Loading student data for ID:', studentId);
                    ajaxRequest('get_student_data_json.php', 'POST', 'student_id=' + studentId, function(response) {
                        try {
                            console.log('Student data response received:', response);
                            
                            // Parse JSON response
                            const jsonResponse = JSON.parse(response);
                            
                            if (!jsonResponse.success) {
                                console.error('Error loading student data:', jsonResponse.error);
                                alert('Error loading student data: ' + jsonResponse.error);
                                return;
                            }
                            
                            const studentData = jsonResponse.data;
                            console.log('Parsed student data:', studentData);
                            
                            // Populate form fields with JSON data and ensure they are enabled
                            const fullNameField = document.getElementById('editFullName');
                            const emailField = document.getElementById('editEmail');
                            const mobileField = document.getElementById('editMobile');
                            const parentMobileField = document.getElementById('editParentMobile');
                            const addressField = document.getElementById('editAddress');
                            const roomStatusField = document.getElementById('editRoomStatus');
                            const bedNumberField = document.getElementById('editBedNumber');
                            
                            // Set values and ensure fields are enabled
                            if (fullNameField) {
                                fullNameField.value = studentData.full_name || studentName;
                                fullNameField.disabled = false;
                                fullNameField.readOnly = false;
                            }
                            if (emailField) {
                                emailField.value = studentData.email || '';
                                emailField.disabled = false;
                                emailField.readOnly = false;
                            }
                            if (mobileField) {
                                mobileField.value = studentData.mobile || '';
                                mobileField.disabled = false;
                                mobileField.readOnly = false;
                            }
                            if (parentMobileField) {
                                parentMobileField.value = studentData.parent_mobile || '';
                                parentMobileField.disabled = false;
                                parentMobileField.readOnly = false;
                            }
                            if (addressField) {
                                addressField.value = studentData.address || '';
                                addressField.disabled = false;
                                addressField.readOnly = false;
                            }
                            if (roomStatusField) {
                                roomStatusField.value = studentData.room_status || 'not_allocated';
                                roomStatusField.disabled = false;
                            }
                            if (bedNumberField) {
                                bedNumberField.value = studentData.bed_number || '';
                                bedNumberField.disabled = false;
                                bedNumberField.readOnly = false;
                            }
                            
                            console.log('Form populated with student data');
                            
                        } catch (error) {
                            console.error('Error processing student data:', error);
                            alert('Error processing student data. Please try again.');
                        }
                    }, function(error) {
                        console.error('AJAX error loading student data:', error);
                        alert('Error loading student data. Please check your connection and try again.');
                    });
                    
                }, 100); // Small delay to ensure DOM is ready
                
            } catch (error) {
                console.error('Error opening edit student modal:', error);
                alert('Error opening edit student form: ' + error.message + '. Please try again.');
            }
        }
        
        function parseStudentDataFromResponse(response) {
            // Parse student data from the actual HTML structure returned by get_student_details.php
            const data = {};
            
            try {
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                
                // Extract full_name from h5 tag
                const nameElement = doc.querySelector('h5');
                if (nameElement) {
                    data.full_name = nameElement.textContent.trim();
                    console.log('Found name:', data.full_name);
                }
                
                // Look for table rows and extract data
                const tableRows = doc.querySelectorAll('table tr');
                
                tableRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 2) {
                        const label = cells[0].textContent.trim();
                        const value = cells[1].textContent.trim();
                        
                        console.log('Found data pair:', label, ':', value);
                        
                        // Map labels to data keys
                        switch(label) {
                            case 'Email:':
                                data.email = value;
                                break;
                            case 'Mobile:':
                                data.mobile = value;
                                break;
                            case 'Parent\'s Mobile:':
                                data.parent_mobile = value;
                                break;
                            case 'Address:':
                                data.address = value;
                                break;
                            case 'Room:':
                                // Extract room info for room status and bed number
                                if (value.toLowerCase().includes('not allocated')) {
                                    data.room_status = 'not_allocated';
                                } else {
                                    data.room_status = 'allocated';
                                    // Extract bed number from room info (e.g., "101 - Bed 2")
                                    const bedMatch = value.match(/Bed\s+(\d+)/);
                                    if (bedMatch) {
                                        data.bed_number = bedMatch[1];
                                    }
                                }
                                break;
                            case 'Course:':
                                data.course = value;
                                break;
                            case 'Year/Semester:':
                                data.year_semester = value;
                                break;
                        }
                    }
                });
                
                console.log('Extracted data from HTML:', data);
                return data;
                
            } catch (e) {
                console.error('Error parsing HTML response:', e);
                return {};
            }
        }
        
        // Password functions for edit modal
        function toggleEditPassword() {
            const passwordField = document.getElementById('editNewPassword');
            const toggleIcon = document.getElementById('editPasswordToggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        function setEditPassword(password) {
            document.getElementById('editNewPassword').value = password;
        }
        
        function generateEditPassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < 10; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            setEditPassword(password);
        }
        
        function clearEditPassword() {
            document.getElementById('editNewPassword').value = '';
        }
        
        function approveStudent(studentId) {
            if (confirm('Are you sure you want to approve this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function rejectStudent(studentId) {
            if (confirm('Are you sure you want to reject this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteStudent(studentId) {
            if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function activateStudent(studentId) {
            if (confirm('Are you sure you want to activate this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deactivateStudent(studentId) {
            if (confirm('Are you sure you want to deactivate this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="deactivate">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function emailBillOfMonth(studentId, month = null, year = null) {
            if (confirm('Are you sure you want to send the monthly bill to this student?')) {
                // Show loading message
                const loadingMsg = document.createElement('div');
                loadingMsg.className = 'alert alert-info';
                loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending bill email... Please wait.';
                document.querySelector('.content').insertBefore(loadingMsg, document.querySelector('.content').firstChild);
                
                // Create and submit form for clean individual email
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'clean_individual_email.php';
                form.innerHTML = `
                    <input type="hidden" name="student_id" value="${studentId}">
                    ${month ? `<input type="hidden" name="month" value="${month}">` : ''}
                    ${year ? `<input type="hidden" name="year" value="${year}">` : ''}
                `;
                document.body.appendChild(form);
                
                // Submit form and handle response
                fetch('clean_individual_email.php', {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading message
                    loadingMsg.remove();
                    
                    // Show result message
                    const resultMsg = document.createElement('div');
                    resultMsg.className = data.success ? 'alert alert-success' : 'alert alert-danger';
                    resultMsg.innerHTML = `<i class="fas fa-${data.success ? 'check' : 'times'} me-2"></i>${data.message}`;
                    document.querySelector('.content').insertBefore(resultMsg, document.querySelector('.content').firstChild);
                    
                    // Auto-remove message after 5 seconds
                    setTimeout(() => {
                        resultMsg.remove();
                    }, 5000);
                })
                .catch(error => {
                    // Remove loading message
                    loadingMsg.remove();
                    
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger';
                    errorMsg.innerHTML = '<i class="fas fa-times me-2"></i>Error sending bill email. Please try again.';
                    document.querySelector('.content').insertBefore(errorMsg, document.querySelector('.content').firstChild);
                    
                    // Auto-remove message after 5 seconds
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 5000);
                    
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Clean up form
                    form.remove();
                });
            }
        }
        
        function sendAllBills() {
            if (confirm('Are you sure you want to send professional fee bills to ALL active students? This will send beautifully designed bills to their email addresses.')) {
                // Show progress message
                const progressMsg = document.createElement('div');
                progressMsg.className = 'alert alert-info';
                progressMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending professional fee bills... Please wait.';
                document.querySelector('.content').insertBefore(progressMsg, document.querySelector('.content').firstChild);
                
                // Create and submit form for professional bill sending
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'professional_bill_email_final.php';
                form.innerHTML = '';
                document.body.appendChild(form);
                
                // Submit form and handle response
                fetch('professional_bill_email_final.php', {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    // Remove progress message
                    progressMsg.remove();
                    
                    // Show result message
                    const resultMsg = document.createElement('div');
                    resultMsg.className = data.success ? 'alert alert-success' : 'alert alert-danger';
                    
                    if (data.success) {
                        resultMsg.innerHTML = `
                            <i class="fas fa-check me-2"></i>
                            <strong>Professional Fee Bills Sent Successfully!</strong><br>
                            <small>
                                Successfully sent: ${data.sent_count} professional bills<br>
                                Failed: ${data.failed_count} bills<br>
                                Total processed: ${data.total_count} students
                            </small>
                        `;
                    } else {
                        resultMsg.innerHTML = `<i class="fas fa-times me-2"></i>${data.message}`;
                    }
                    
                    document.querySelector('.content').insertBefore(resultMsg, document.querySelector('.content').firstChild);
                    
                    // Auto-remove message after 10 seconds
                    setTimeout(() => {
                        resultMsg.remove();
                    }, 10000);
                })
                .catch(error => {
                    // Remove progress message
                    progressMsg.remove();
                    
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger';
                    errorMsg.innerHTML = '<i class="fas fa-times me-2"></i>Error sending bulk emails. Please try again.';
                    document.querySelector('.content').insertBefore(errorMsg, document.querySelector('.content').firstChild);
                    
                    // Auto-remove message after 5 seconds
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 5000);
                    
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Clean up form
                    form.remove();
                });
            }
        }
        
        function generatePDFReport() {
            // Show loading message
            const loadingMsg = document.createElement('div');
            loadingMsg.className = 'alert alert-info';
            loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating PDF report... Please wait.';
            document.querySelector('.content').insertBefore(loadingMsg, document.querySelector('.content').firstChild);
            
            // Create form to trigger PDF generation
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'generate_student_pdf_report.php';
            form.innerHTML = `
                <input type="hidden" name="filter" value="${new URLSearchParams(window.location.search).get('filter') || 'all'}">
                <input type="hidden" name="search" value="${new URLSearchParams(window.location.search).get('search') || ''}">
            `;
            document.body.appendChild(form);
            
            // Submit form
            form.submit();
            
            // Remove loading message after a delay
            setTimeout(() => {
                if (loadingMsg.parentNode) {
                    loadingMsg.remove();
                }
            }, 3000);
            
            // Clean up form
            setTimeout(() => {
                form.remove();
            }, 4000);
        }
        
        // Initialize table filter
        filterTable('studentsTable', 'search');
        
        // Global modal event handlers
        // Handle modal close events
        document.addEventListener('hidden.bs.modal', function(e) {
            const modal = e.target;
            const modalId = modal.id;
            
            // Clean up modal content and dispose instance
            if (modalId === 'studentModal' || modalId === 'editStudentModal') {
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    modalBody.innerHTML = '';
                }
                
                // Dispose modal instance to prevent memory leaks
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.dispose();
                    console.log('Modal instance disposed for:', modalId);
                }
            }
        });
    </script>
</body>
</html>
