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

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_complaint'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    $stmt = $conn->prepare("INSERT INTO complaints (student_id, title, description, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $_SESSION['student_id'], $title, $description, $category);
    $stmt->execute();
    
    // Get the inserted complaint ID
    $complaint_id = $conn->insert_id;
    
    // Create admin notification for new complaint
    try {
        // Get the actual admin ID from database (use the first active admin)
        $admin_stmt = $conn->prepare("SELECT id FROM admins WHERE is_active = TRUE LIMIT 1");
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin_row = $admin_result->fetch_assoc();
        $admin_id = $admin_row['id'];
        
        if (!$admin_id) {
            throw new Exception("No active admin found");
        }
        
        $notification_title = "New Complaint Submitted";
        $notification_message = "Complaint: {$title} by {$student['full_name']} ({$student['email']})";
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_type, user_id, title, message, type, is_read) 
            VALUES ('admin', ?, ?, ?, 'info', FALSE)
        ");
        $stmt->bind_param("iss", $admin_id, $notification_title, $notification_message);
        $stmt->execute();
        
        // Create student notification for complaint submission confirmation
        $student_notification_title = "Complaint Submitted Successfully";
        $student_notification_message = "Your complaint '{$title}' has been submitted and will be reviewed by the administration.";
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_type, user_id, title, message, type, is_read) 
            VALUES ('student', ?, ?, ?, 'success', FALSE)
        ");
        $stmt->bind_param("iss", $_SESSION['student_id'], $student_notification_title, $student_notification_message);
        $stmt->execute();
        
    } catch (Exception $e) {
        error_log("Failed to create complaint notification: " . $e->getMessage());
    }
    
    $success = "Complaint submitted successfully!";
}

// Get student's complaints
$complaints = [];
$stmt = $conn->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}

// Calculate statistics
$total_complaints = count($complaints);
$pending_complaints = 0;
$in_progress_complaints = 0;
$resolved_complaints = 0;

foreach ($complaints as $complaint) {
    switch($complaint['status']) {
        case 'pending':
            $pending_complaints++;
            break;
        case 'in_progress':
            $in_progress_complaints++;
            break;
        case 'resolved':
            $resolved_complaints++;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .complaint-card {
            background-color: transparent !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }
        .complaint-card:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .complaint-card h6 {
            color: #ffffff !important;
        }
        .complaint-card p {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .complaint-card .badge {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }
        .complaint-card .badge-warning {
            background-color: rgba(255, 193, 7, 0.3) !important;
        }
        .complaint-card .badge-info {
            background-color: rgba(13, 202, 240, 0.3) !important;
        }
        .complaint-card .badge-success {
            background-color: rgba(40, 167, 69, 0.3) !important;
        }
        .complaint-card .badge-light {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }
        .complaint-card .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        .complaint-card .admin-response {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }
        .complaint-card .admin-response .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
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
                <a href="complaints.php" class="sidebar-menu-item active">
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
                <h1 class="top-bar-title">My Complaints</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['student_name']; ?></span>
                    <img src="../uploads/<?php echo $student['profile_photo'] ?: 'default_avatar.svg'; ?>" alt="Student" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $total_complaints; ?></div>
                            <div class="dashboard-card-label">Total Complaints</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="dashboard-card-icon pink">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $pending_complaints; ?></div>
                            <div class="dashboard-card-label">Pending</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $in_progress_complaints; ?></div>
                            <div class="dashboard-card-label">In Progress</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <div class="dashboard-card-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $resolved_complaints; ?></div>
                            <div class="dashboard-card-label">Resolved</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Submit New Complaint -->
                    <div class="col-lg-5 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp">
                            <h5 class="mb-4">Submit New Complaint</h5>
                            <form method="POST">
                                <input type="hidden" name="submit_complaint" value="1">
                                
                                <div class="mb-3">
                                    <label class="form-label">Complaint Category</label>
                                    <select class="form-control" name="category" required>
                                        <option value="">Select a category...</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="food">Food & Mess</option>
                                        <option value="security">Security</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Complaint Title</label>
                                    <input type="text" class="form-control" name="title" 
                                           placeholder="Brief description of the issue" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Detailed Description</label>
                                    <textarea class="form-control" name="description" rows="5" 
                                              placeholder="Provide detailed information about your complaint..." required></textarea>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Please note:</strong> Your complaint will be reviewed by the hostel administration. You will receive updates on the status through this portal.
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Complaint
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Complaints List -->
                    <div class="col-lg-7 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">My Complaints</h5>
                                <span class="badge bg-primary"><?php echo $total_complaints; ?> Total</span>
                            </div>
                            
                            <div class="complaints-list">
                                <?php if (empty($complaints)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No complaints submitted yet</p>
                                        <small class="text-muted">Submit your first complaint using the form</small>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($complaints as $complaint): ?>
                                        <div class="complaint-card mb-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <h6 class="mb-0 me-3"><?php echo htmlspecialchars($complaint['title']); ?></h6>
                                                        <?php
                                                        $status_class = '';
                                                        $status_icon = '';
                                                        switch($complaint['status']) {
                                                            case 'pending':
                                                                $status_class = 'badge-warning';
                                                                $status_icon = 'fa-clock';
                                                                break;
                                                            case 'in_progress':
                                                                $status_class = 'badge-info';
                                                                $status_icon = 'fa-spinner';
                                                                break;
                                                            case 'resolved':
                                                                $status_class = 'badge-success';
                                                                $status_icon = 'fa-check-circle';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <span class="badge bg-light text-dark me-2">
                                                            <i class="fas fa-tag me-1"></i><?php echo ucfirst($complaint['category']); ?>
                                                        </span>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?php echo date('M d, Y h:i A', strtotime($complaint['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <p class="text-muted mb-2">
                                                        <?php echo htmlspecialchars(substr($complaint['description'], 0, 150)); ?>
                                                        <?php if (strlen($complaint['description']) > 150): ?>...<?php endif; ?>
                                                    </p>
                                                    
                                                    <?php if ($complaint['admin_response']): ?>
                                                        <div class="admin-response mt-2 p-2 bg-light rounded">
                                                            <small class="text-muted d-block mb-1">
                                                                <i class="fas fa-reply me-1"></i>Admin Response
                                                            </small>
                                                            <p class="mb-0 small"><?php echo htmlspecialchars($complaint['admin_response']); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($complaint['status'] === 'resolved'): ?>
                                                        <small class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Resolved on <?php echo date('M d, Y', strtotime($complaint['updated_at'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Guidelines -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <h5 class="mb-3">Complaint Guidelines</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-check-circle text-success me-2"></i>Do's</h6>
                                    <ul class="text-muted">
                                        <li>Provide accurate and detailed information</li>
                                        <li>Choose the appropriate category for your complaint</li>
                                        <li>Be respectful and professional in your description</li>
                                        <li>Submit one complaint per issue</li>
                                        <li>Follow up on pending complaints regularly</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-times-circle text-danger me-2"></i>Don'ts</h6>
                                    <ul class="text-muted">
                                        <li>Submit false or misleading complaints</li>
                                        <li>Use inappropriate language</li>
                                        <li>Submit duplicate complaints for the same issue</li>
                                        <li>Include personal information of others</li>
                                        <li>Expect immediate resolution for complex issues</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Resolution Time:</strong> Most complaints are resolved within 24-48 hours. Complex issues may take longer. You will be notified of any updates through this portal.
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
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const complaintForm = document.querySelector('form[method="POST"]');
            if (complaintForm) {
                complaintForm.addEventListener('submit', function(e) {
                    const title = document.querySelector('input[name="title"]').value.trim();
                    const description = document.querySelector('textarea[name="description"]').value.trim();
                    const category = document.querySelector('select[name="category"]').value;
                    
                    if (!category || !title || !description) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    } else if (title.length < 5) {
                        e.preventDefault();
                        alert('Complaint title must be at least 5 characters long.');
                    } else if (description.length < 20) {
                        e.preventDefault();
                        alert('Complaint description must be at least 20 characters long.');
                    }
                });
            }
        });
        
        // Character counter for description
        const descriptionField = document.querySelector('textarea[name="description"]');
        if (descriptionField) {
            descriptionField.addEventListener('input', function() {
                const remaining = 500 - this.value.length;
                // You can add a character counter display here if needed
            });
        }
    </script>
    
    <style>
        .complaint-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        .complaint-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .admin-response {
            border-left: 4px solid var(--primary-blue);
        }
        
        .complaints-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .complaints-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .complaints-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .complaints-list::-webkit-scrollbar-thumb {
            background: var(--primary-blue);
            border-radius: 3px;
        }
    </style>
</body>
</html>
