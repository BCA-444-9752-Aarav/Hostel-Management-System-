<?php
require_once '../config/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle marking notifications as read
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'mark_read') {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_type = 'student' AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $_SESSION['student_id']);
        $stmt->execute();
        
        $success = "Notification marked as read!";
    } elseif ($_POST['action'] == 'mark_all_read') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_type = 'student' AND user_id = ?");
        $stmt->bind_param("i", $_SESSION['student_id']);
        $stmt->execute();
        
        $success = "All notifications marked as read!";
    } elseif ($_POST['action'] == 'delete_notification') {
        $notification_id = $_POST['notification_id'];
        
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_type = 'student' AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $_SESSION['student_id']);
        $stmt->execute();
        
        $success = "Notification deleted successfully!";
    }
}

// Get all notifications for this student (both read and unread)
$notifications = [];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_type = 'student' AND user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Get student information
$student = null;
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Count unread notifications
$unread_count = 0;
foreach ($notifications as $notification) {
    if (!$notification['is_read']) {
        $unread_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        /* Dark mode compatible notification styles */
        .notification-item {
            background-color: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            transition: all 0.3s ease;
        }
        
        .notification-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }
        
        .notification-item.unread {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            border-left: 4px solid #3b82f6;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .notification-item.unread {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-left: 4px solid #60a5fa;
        }
        
        [data-theme="dark"] .notification-item {
            background-color: #1e293b;
            border-color: #374151;
            color: #f1f5f9;
        }
        
        [data-theme="dark"] .notification-item:hover {
            background-color: #334155;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        [data-theme="dark"] .text-muted {
            color: #9ca3af !important;
        }
        
        [data-theme="dark"] h5 {
            color: #f1f5f9 !important;
        }
        
        [data-theme="dark"] p {
            color: #d1d5db !important;
        }
        
        [data-theme="dark"] .dropdown-menu {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        [data-theme="dark"] .dropdown-item {
            color: #f3f4f6;
        }
        
        [data-theme="dark"] .dropdown-item:hover {
            background-color: #374151;
            color: #f3f4f6;
        }
        
        [data-theme="dark"] .btn-outline-secondary {
            border-color: #6b7280;
            color: #9ca3af;
        }
        
        [data-theme="dark"] .btn-outline-secondary:hover {
            background-color: #374151;
            border-color: #6b7280;
            color: #f3f4f6;
        }
        
        /* Custom scrollbar for notifications list */
        .notifications-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .notifications-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .notifications-list::-webkit-scrollbar-track {
            background: var(--bs-gray-100);
            border-radius: 10px;
        }
        
        .notifications-list::-webkit-scrollbar-thumb {
            background: var(--bs-gray-300);
            border-radius: 10px;
        }
        
        .notifications-list::-webkit-scrollbar-thumb:hover {
            background: var(--bs-gray-400);
        }
        
        [data-theme="dark"] .notifications-list::-webkit-scrollbar-track {
            background: #374151;
        }
        
        [data-theme="dark"] .notifications-list::-webkit-scrollbar-thumb {
            background: #6b7280;
        }
        
        [data-theme="dark"] .notifications-list::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .notification-item {
                margin-bottom: 8px;
            }
        }
        
        /* Dark mode styles for table container */
        [data-theme="dark"] .table-container {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        [data-theme="dark"] .dashboard-card {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        [data-theme="dark"] .dashboard-card-icon {
            background-color: #374151;
        }
        
        [data-theme="dark"] .dashboard-card-value {
            color: #f3f4f6;
        }
        
        [data-theme="dark"] .dashboard-card-label {
            color: #9ca3af;
        }
        
        /* Dark mode for badges */
        [data-theme="dark"] .badge {
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        [data-theme="dark"] .badge.bg-primary {
            background-color: #3b82f6 !important;
        }
        
        [data-theme="dark"] .badge.bg-danger {
            background-color: #ef4444 !important;
        }
        
        [data-theme="dark"] .badge.bg-warning {
            background-color: #f59e0b !important;
        }
        
        /* Dark mode for buttons */
        [data-theme="dark"] .btn-outline-primary {
            border-color: #60a5fa;
            color: #93c5fd;
        }
        
        [data-theme="dark"] .btn-outline-primary:hover {
            background-color: #3b82f6;
            border-color: #60a5fa;
            color: #ffffff;
        }
        
        /* Dark mode for empty state */
        [data-theme="dark"] .text-muted {
            color: #9ca3af !important;
        }
        
        [data-theme="dark"] .fa-bell-slash {
            color: #6b7280 !important;
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
                <p class="text-white-50 mb-0">Student Portal</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="profile.php" class="sidebar-menu-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="fees.php" class="sidebar-menu-item">
                    <i class="fas fa-rupee-sign"></i> My Fees
                </a>
                <a href="complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comments"></i> My Complaints
                </a>
                <a href="notifications.php" class="sidebar-menu-item active">
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
                <h1 class="top-bar-title">My Notifications</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['student_name']; ?></span>
                    <?php 
                    $avatar_path = '../uploads/' . ($student['profile_photo'] ?? '');
                    $default_avatar = '../assets/default_avatar.svg';
                    
                    if (!empty($student['profile_photo']) && file_exists($avatar_path)) {
                        echo '<img src="' . $avatar_path . '" alt="Student" class="user-avatar">';
                    } else {
                        echo '<img src="' . $default_avatar . '" alt="Student" class="user-avatar">';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Notification Stats -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo count($notifications); ?></div>
                            <div class="dashboard-card-label">Total Notifications</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="dashboard-card-icon orange">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $unread_count; ?></div>
                            <div class="dashboard-card-label">Unread</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <div class="dashboard-card-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo count($notifications) - $unread_count; ?></div>
                            <div class="dashboard-card-label">Read</div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <?php if ($unread_count > 0): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <h5 class="mb-0">Notifications</h5>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="mark_all_read">
                                <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Mark all notifications as read?')">
                                    <i class="fas fa-check-double me-1"></i> Mark All as Read
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Notifications List -->
                <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <div class="notifications-list">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Notifications</h5>
                                <p class="text-muted">You don't have any notifications at the moment.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item border rounded p-3 mb-3 animate__animated animate__fadeInUp <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
                                     style="animation-delay: 0.<?php echo ($notification['id'] % 5) + 1; ?>s">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="mb-0 me-2">
                                                    <i class="fas fa-<?php echo getNotificationIcon($notification['type']); ?> me-2 text-<?php echo getNotificationColor($notification['type']); ?>"></i>
                                                    <?php echo htmlspecialchars($notification['title']); ?>
                                                </h5>
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge bg-primary">New</span>
                                                <?php endif; ?>
                                                <?php if ($notification['priority'] == 'high'): ?>
                                                    <span class="badge bg-danger ms-2">High Priority</span>
                                                <?php elseif ($notification['priority'] == 'medium'): ?>
                                                    <span class="badge bg-warning ms-2">Medium</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                                <?php 
                                                $time_diff = time() - strtotime($notification['created_at']);
                                                if ($time_diff < 3600) {
                                                    echo '• ' . floor($time_diff / 60) . ' minutes ago';
                                                } elseif ($time_diff < 86400) {
                                                    echo '• ' . floor($time_diff / 3600) . ' hours ago';
                                                } elseif ($time_diff < 604800) {
                                                    echo '• ' . floor($time_diff / 86400) . ' days ago';
                                                } else {
                                                    echo '• ' . date('M j, Y', strtotime($notification['created_at']));
                                                }
                                                ?>
                                            </small>
                                            <?php if ($notification['action_url']): ?>
                                                <div class="mt-2">
                                                    <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-external-link-alt me-1"></i> View Details
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ms-3">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <li>
                                                            <form method="POST" style="margin: 0;">
                                                                <input type="hidden" name="action" value="mark_read">
                                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-check me-2"></i>Mark as Read
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <form method="POST" style="margin: 0;">
                                                            <input type="hidden" name="action" value="delete_notification">
                                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this notification?')">
                                                                <i class="fas fa-trash me-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            // You can add auto-refresh logic here if needed
        }, 30000);
        
        // Add smooth scroll behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Animate notifications on load
            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach((notification, index) => {
                notification.style.animationDelay = (index * 0.1) + 's';
            });
            
            // Apply dark mode theme if set
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark-theme');
            }
            
            // Listen for theme changes
            const themeToggle = document.querySelector('.theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    
                    if (newTheme === 'dark') {
                        document.body.classList.add('dark-theme');
                    } else {
                        document.body.classList.remove('dark-theme');
                    }
                });
            }
        });
    </script>
    
    <?php
    // Helper functions
    function getNotificationIcon($type) {
        $icons = [
            'info' => 'info-circle',
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'times-circle'
        ];
        return $icons[$type] ?? 'info-circle';
    }
    
    function getNotificationColor($type) {
        $colors = [
            'info' => 'primary',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger'
        ];
        return $colors[$type] ?? 'primary';
    }
    ?>
</body>
</html>
