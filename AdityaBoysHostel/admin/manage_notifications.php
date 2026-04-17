<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'send_notification') {
        $target_type = $_POST['target_type']; // 'all' or 'specific'
        $title = $_POST['title'];
        $message = $_POST['message'];
        $type = $_POST['type'] ?? 'info';
        $priority = $_POST['priority'] ?? 'medium';
        
        // Validate inputs
        if (empty($title) || empty($message)) {
            $error = "Title and message are required!";
        } else {
            try {
                // Start transaction
                $conn->begin_transaction();
                
                if ($target_type == 'all') {
                    // Send to all students
                    $stmt = $conn->prepare("SELECT id FROM students WHERE status = 'approved'");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $notification_count = 0;
                    while ($student = $result->fetch_assoc()) {
                        $insert_stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message, type, priority) VALUES ('student', ?, ?, ?, ?, ?)");
                        $insert_stmt->bind_param("issss", $student['id'], $title, $message, $type, $priority);
                        $insert_stmt->execute();
                        $notification_count++;
                    }
                    
                    $success = "Notification sent to {$notification_count} students successfully!";
                    
                } elseif ($target_type == 'specific') {
                    // Send to specific students
                    $student_ids = $_POST['student_ids'] ?? [];
                    
                    if (empty($student_ids)) {
                        $error = "Please select at least one student!";
                    } else {
                        $notification_count = 0;
                        foreach ($student_ids as $student_id) {
                            $insert_stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message, type, priority) VALUES ('student', ?, ?, ?, ?, ?)");
                            $insert_stmt->bind_param("issss", $student_id, $title, $message, $type, $priority);
                            $insert_stmt->execute();
                            $notification_count++;
                        }
                        
                        $success = "Notification sent to {$notification_count} selected students successfully!";
                    }
                }
                
                $conn->commit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error sending notification: " . $e->getMessage();
            }
        }
    } elseif ($action == 'delete_notification') {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_type = 'student'");
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
        
        $success = "Notification deleted successfully!";
    }
}

// Get students list for targeting
$students = [];
$stmt = $conn->prepare("SELECT id, full_name, email FROM students WHERE status = 'approved' ORDER BY full_name");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Get recent notifications sent
$recent_notifications = [];
$stmt = $conn->prepare("SELECT n.*, s.full_name, s.email FROM notifications n LEFT JOIN students s ON n.user_id = s.id WHERE n.user_type = 'student' ORDER BY n.created_at DESC LIMIT 20");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $recent_notifications[] = $row;
}

// Get notification statistics
$total_notifications = 0;
$unread_notifications = 0;

// Debug: Get total count of all notifications
$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_type = 'student'");
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_debug = $total_result->fetch_assoc();

// Debug: Get unread count
$unread_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_type = 'student' AND is_read = FALSE");
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_debug = $unread_result->fetch_assoc();

// Use the debug values
$total_notifications = $total_debug['total'];
$unread_notifications = $unread_debug['unread'];

// Debug: Log the values for troubleshooting
error_log("Notification Stats - Total: " . $total_notifications . ", Unread: " . $unread_notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Management - Aditya Boys Hostel</title>
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
                <a href="manage_complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comments"></i> Complaints
                </a>
                <a href="manage_notifications.php" class="sidebar-menu-item active">
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
                <h1 class="top-bar-title">Notification Management</h1>
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
                    <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $total_notifications; ?></div>
                            <div class="dashboard-card-label">Total Notifications</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="dashboard-card-icon orange">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $unread_notifications; ?></div>
                            <div class="dashboard-card-label">Unread Notifications</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <div class="dashboard-card-icon green">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo count($students); ?></div>
                            <div class="dashboard-card-label">Active Students</div>
                        </div>
                    </div>
                </div>
                
                <!-- Send Notification Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-container animate__animated animate__fadeInUp">
                            <div class="p-4">
                                <h5 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Send New Notification</h5>
                                <form method="POST" id="notificationForm">
                                    <input type="hidden" name="action" value="send_notification">
                                    
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label class="form-label">Target Audience</label>
                                            <select class="form-control" name="target_type" id="targetType" required>
                                                <option value="all">All Students</option>
                                                <option value="specific">Specific Students</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3" id="studentSelection" style="display: none;">
                                        <div class="col-12">
                                            <label class="form-label">Select Students</label>
                                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                                <?php foreach ($students as $student): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" id="student_<?php echo $student['id']; ?>">
                                                        <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                                            <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllStudents()">Select All</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllStudents()">Deselect All</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Notification Title</label>
                                        <input type="text" class="form-control" name="title" placeholder="Enter notification title..." required>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Type</label>
                                            <select class="form-control" name="type">
                                                <option value="info">Information</option>
                                                <option value="success">Success</option>
                                                <option value="warning">Warning</option>
                                                <option value="error">Error</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Priority</label>
                                            <select class="form-control" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="4" placeholder="Enter your message here..." required></textarea>
                                        <small class="text-muted">This message will be sent to selected students immediately.</small>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Send Notification
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Notifications -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <div class="p-4">
                                <h5 class="mb-4"><i class="fas fa-history me-2"></i>Recent Notifications</h5>
                                <div class="table-responsive">
                                    <table class="table" id="notificationsTable">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th>Student</th>
                                                <th>Type</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Sent At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_notifications as $notification): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($notification['title']); ?></strong></td>
                                                <td>
                                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                                         title="<?php echo htmlspecialchars($notification['message']); ?>">
                                                        <?php echo htmlspecialchars($notification['message']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo $notification['full_name'] ? htmlspecialchars($notification['full_name']) : 'Unknown Student (ID: ' . $notification['user_id'] . ')'; ?></td>
                                                <td>
                                                    <?php 
                                                    $type_colors = [
                                                        'info' => 'primary',
                                                        'success' => 'success', 
                                                        'warning' => 'warning',
                                                        'error' => 'danger'
                                                    ];
                                                    $color = $type_colors[$notification['type']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($notification['type']); ?></span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $priority_colors = [
                                                        'low' => 'secondary',
                                                        'medium' => 'info', 
                                                        'high' => 'danger'
                                                    ];
                                                    $color = $priority_colors[$notification['priority']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($notification['priority']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($notification['is_read']): ?>
                                                        <span class="badge bg-success">Read</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Unread</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteNotification(<?php echo $notification['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    
                                    <?php if (empty($recent_notifications)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No notifications sent yet</p>
                                        </div>
                                    <?php endif; ?>
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
        // Toggle student selection based on target type
        document.getElementById('targetType').addEventListener('change', function() {
            const studentSelection = document.getElementById('studentSelection');
            if (this.value === 'specific') {
                studentSelection.style.display = 'block';
            } else {
                studentSelection.style.display = 'none';
            }
        });
        
        // Select all students
        function selectAllStudents() {
            const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }
        
        // Deselect all students
        function deselectAllStudents() {
            const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }
        
        // Clear form
        function clearForm() {
            document.getElementById('notificationForm').reset();
            document.getElementById('studentSelection').style.display = 'none';
        }
        
        // Delete notification
        function deleteNotification(notificationId) {
            if (confirm('Are you sure you want to delete this notification?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_notification">
                    <input type="hidden" name="notification_id" value="${notificationId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Form validation
        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            const targetType = document.getElementById('targetType').value;
            const title = document.querySelector('input[name="title"]').value.trim();
            const message = document.querySelector('textarea[name="message"]').value.trim();
            
            if (!title || !message) {
                e.preventDefault();
                alert('Please fill in both title and message fields.');
                return false;
            }
            
            if (targetType === 'specific') {
                const selectedStudents = document.querySelectorAll('input[name="student_ids[]"]:checked');
                if (selectedStudents.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one student when targeting specific students.');
                    return false;
                }
            }
            
            return confirm('Are you sure you want to send this notification?');
        });
    </script>
</body>
</html>
