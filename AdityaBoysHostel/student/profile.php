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

// Determine display status
$display_status = 'Active';
$status_class = 'badge-success';
$status_icon = 'fa-check-circle';
$status_description = 'Your account is active and fully functional';

if ($student['status'] == 'pending') {
    $display_status = 'Pending Approval';
    $status_class = 'badge-warning';
    $status_icon = 'fa-clock';
    $status_description = 'Your account is pending admin approval';
} elseif ($student['status'] == 'rejected') {
    $display_status = 'Deactivated';
    $status_class = 'badge-danger';
    $status_icon = 'fa-times-circle';
    $status_description = 'Your account has been deactivated';
} elseif ($student['status'] == 'inactive') {
    $display_status = 'Inactive';
    $status_class = 'badge-secondary';
    $status_icon = 'fa-pause-circle';
    $status_description = 'Your account is temporarily inactive';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $mobile = $_POST['mobile'];
    $parent_mobile = $_POST['parent_mobile'];
    $address = $_POST['address'];
    
    // Handle profile photo upload
    $profile_photo = $student['profile_photo'];
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            $file_name = 'profile_' . $_SESSION['student_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = '../uploads/' . $file_name;
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                $profile_photo = $file_name;
            }
        }
    }
    
    $stmt = $conn->prepare("UPDATE students SET full_name = ?, mobile = ?, parent_mobile = ?, address = ?, profile_photo = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $full_name, $mobile, $parent_mobile, $address, $profile_photo, $_SESSION['student_id']);
    $stmt->execute();
    
    // Update session name
    $_SESSION['student_name'] = $full_name;
    
    // Refresh student data
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['student_id']);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    $success = "Profile updated successfully!";
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $student['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['student_id']);
            $stmt->execute();
            
            $password_success = "Password changed successfully!";
        } else {
            $password_error = "New passwords do not match!";
        }
    } else {
        $password_error = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .badge {
            font-size: 0.9em;
            padding: 0.5em 1em;
            font-weight: 600;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
        }
        .badge-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        .badge-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            border: none;
            color: #000 !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
        }
        .badge-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }
        .badge-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }
        .profile-img {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .profile-img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .status-indicator {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .status-active .status-indicator {
            color: #28a745;
        }
        .status-pending .status-indicator {
            color: #ffc107;
        }
        .status-danger .status-indicator {
            color: #dc3545;
        }
        .status-inactive .status-indicator {
            color: #6c757d;
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
                <a href="profile.php" class="sidebar-menu-item active">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="fees.php" class="sidebar-menu-item">
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
                <h1 class="top-bar-title">My Profile</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['student_name']; ?></span>
                    <img src="../uploads/<?php echo $student['profile_photo'] ?: 'default_avatar.svg'; ?>" alt="Student" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($password_success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <?php echo $password_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($password_error)): ?>
                    <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                        <?php echo $password_error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-lg-4 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp">
                            <div class="text-center">
                                <img src="../uploads/<?php echo $student['profile_photo'] ?: 'default_avatar.svg'; ?>" 
                                     alt="Profile" class="rounded-circle mb-3 profile-img" 
                                     style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary-blue);">
                                <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($student['email']); ?></p>
                                
                                <div class="text-center mb-3 status-<?php echo $student['status']; ?>">
                                    <span class="badge <?php echo $status_class; ?> mb-2">
                                        <i class="fas <?php echo $status_icon; ?> me-1 status-indicator"></i>
                                        <?php echo $display_status; ?>
                                    </span>
                                    <small class="text-muted d-block"><?php echo $status_description; ?></small>
                                </div>
                                
                                <?php if ($student['room_id']): ?>
                                    <div class="mt-3">
                                        <small class="text-muted">Room: <?php echo htmlspecialchars($student['room_id']); ?> - Bed: <?php echo htmlspecialchars($student['bed_number']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Profile Form -->
                    <div class="col-lg-8 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <h5 class="mb-4">Edit Profile Information</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                                        <small class="text-muted">Email cannot be changed</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="tel" class="form-control" name="mobile" 
                                               value="<?php echo htmlspecialchars($student['mobile']); ?>" 
                                               pattern="[0-9]{10}" maxlength="10" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent's Mobile</label>
                                        <input type="tel" class="form-control" name="parent_mobile" 
                                               value="<?php echo htmlspecialchars($student['parent_mobile']); ?>" 
                                               pattern="[0-9]{10}" maxlength="10" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="3" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" name="profile_photo" accept="image/*">
                                    <small class="text-muted">Upload JPG, PNG, or GIF (Max 2MB)</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <h5 class="mb-4">Change Password</h5>
                            <form method="POST">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" 
                                           minlength="8" required>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" 
                                           minlength="8" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-lock me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="col-lg-6 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <h5 class="mb-4">Account Information</h5>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Registration Date:</strong></td>
                                    <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td><?php echo date('M d, Y', strtotime($student['updated_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Account Status:</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge <?php echo $status_class; ?> me-2">
                                                <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                <?php echo $display_status; ?>
                                            </span>
                                            <small class="text-muted ms-2"><?php echo $status_description; ?></small>
                                        </div>
                                    </td>
                                </tr>
                                <?php if ($student['room_id']): ?>
                                <tr>
                                    <td><strong>Room Allocation:</strong></td>
                                    <td>Room <?php echo htmlspecialchars($student['room_id']); ?> - Bed <?php echo htmlspecialchars($student['bed_number']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
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
            // Password confirmation validation
            const changePasswordForm = document.querySelector('form[name="change_password"]');
            if (changePasswordForm) {
                changePasswordForm.addEventListener('submit', function(e) {
                    const newPassword = document.querySelector('input[name="new_password"]').value;
                    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New passwords do not match!');
                    }
                });
            }
            
            // Phone number validation
            const phoneInputs = document.querySelectorAll('input[type="tel"]');
            phoneInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });
        });
    </script>
</body>
</html>
