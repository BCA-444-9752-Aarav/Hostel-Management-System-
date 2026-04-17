<?php
require_once '../config/db.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $mobile = $_POST['mobile'];
    $parent_mobile = $_POST['parent_mobile'];
    $address = $_POST['address'];
    
    // Full name validation - only letters and spaces allowed
    if (empty($full_name)) {
        $error = "Full name is required!";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $full_name)) {
        $error = "Full name can only contain letters and spaces (no numbers or special characters)!";
    } elseif (strlen(trim($full_name)) < 2) {
        $error = "Full name must be at least 2 characters long!";
    } elseif (strlen(trim($full_name)) > 50) {
        $error = "Full name must not exceed 50 characters!";
    } else {
        // Password validation - more flexible but still secure
        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long!";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = "Password must contain at least one lowercase letter!";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = "Password must contain at least one number!";
        } elseif ($password !== $_POST['confirm_password']) {
            $error = "Passwords do not match!";
        } else {
        // Hash the password after validation
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Handle profile photo upload
        $profile_photo = 'default_avatar.svg';
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_photo']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $newname = 'student_' . time() . '.' . $filetype;
                $upload_path = UPLOAD_PATH . $newname;
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                    $profile_photo = $newname;
                }
            }
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert new student
            $stmt = $conn->prepare("INSERT INTO students (full_name, email, password, mobile, parent_mobile, address, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $full_name, $email, $hashed_password, $mobile, $parent_mobile, $address, $profile_photo);
        
        if ($stmt->execute()) {
            $student_id = $conn->insert_id;
            
            // Create admin notification for new student registration
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
                
                $notification_title = "New Student Registration";
                $notification_message = "{$full_name} ({$email}) has registered and needs approval";
                
                $stmt = $conn->prepare("
                    INSERT INTO notifications (user_type, user_id, title, message, type, is_read) 
                    VALUES ('admin', ?, ?, ?, 'info', FALSE)
                ");
                $stmt->bind_param("iss", $admin_id, $notification_title, $notification_message);
                $stmt->execute();
                
            } catch (Exception $e) {
                error_log("Failed to create student registration notification: " . $e->getMessage());
            }
            
            $success = "Registration successful! Please wait for admin approval.";
        } else {
            $error = "Registration failed! Please try again.";
        }
        }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="bg-gradient min-vh-100">
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="row w-100">
            <div class="col-lg-6 col-xl-5 mx-auto">
                <div class="register-card animate__animated animate__fadeInUp shadow-lg" style="max-height: 90vh; overflow-y: auto;">
                    <div class="text-center mb-4">
                        <img src="../assets/logo.svg" alt="Aditya Boys Hostel" class="logo mb-3">
                        <h2 class="brand-title">Student Registration</h2>
                        <p class="text-muted">Join Aditya Boys Hostel - Your Home Away From Home</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success animate__animated animate__fadeIn" role="alert">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="registrationForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control form-control-lg" id="full_name" name="full_name" 
                                       minlength="2" maxlength="50" required
                                       pattern="[A-Za-z\s]+"
                                       title="Full name can only contain letters and spaces (no numbers or special characters)"
                                       placeholder="Enter your full name">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Only letters and spaces allowed (2-50 characters)
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                       minlength="8" required
                                       pattern="(?=.*\d)(?=.*[a-z]).{8,}"
                                       title="Password must be at least 8 characters long, contain at least one lowercase letter and one number">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Must be 8+ characters with lowercase letter and number
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" 
                                       minlength="8" required
                                       pattern="(?=.*\d)(?=.*[a-z]).{8,}"
                                       title="Please confirm your password">
                                <small class="form-text text-muted" id="password-match-feedback">
                                    <i class="fas fa-check-circle text-success d-none"></i>
                                    <i class="fas fa-times-circle text-danger d-none"></i>
                                    <span id="password-match-text">Enter the same password as above</span>
                                </small>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control form-control-lg" id="mobile" name="mobile" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="parent_mobile" class="form-label">Parent's Mobile</label>
                                <input type="tel" class="form-control form-control-lg" id="parent_mobile" name="parent_mobile" required>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control form-control-lg" id="address" name="address" rows="3" required></textarea>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control form-control-lg" id="profile_photo" name="profile_photo" accept="image/*">
                                <small class="text-muted">Optional: Upload your profile photo (JPG, PNG, GIF)</small>
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-4">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                        <i class="fas fa-user-plus me-2"></i>Register Now
                                    </button>
                                    <a href="../index.php" class="btn btn-outline-secondary btn-lg flex-fill">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Full name validation
        document.getElementById('full_name').addEventListener('input', function() {
            const fullName = this.value;
            const feedback = document.querySelector('#full_name + .form-text');
            
            if (fullName.length === 0) {
                feedback.innerHTML = '<i class="fas fa-info-circle"></i> Only letters and spaces allowed (2-50 characters)';
                feedback.className = 'form-text text-muted';
            } else if (fullName.length < 2) {
                feedback.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Name must be at least 2 characters';
                feedback.className = 'form-text text-danger';
            } else if (fullName.length > 50) {
                feedback.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Name must not exceed 50 characters';
                feedback.className = 'form-text text-danger';
            } else if (!/^[A-Za-z\s]+$/.test(fullName)) {
                feedback.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Only letters and spaces allowed';
                feedback.className = 'form-text text-danger';
            } else if (!/\s/.test(fullName.trim())) {
                feedback.innerHTML = '<i class="fas fa-exclamation-circle text-warning"></i> Please enter both first and last name';
                feedback.className = 'form-text text-warning';
            } else {
                feedback.innerHTML = '<i class="fas fa-check-circle text-success"></i> Valid name format';
                feedback.className = 'form-text text-success';
            }
        });
        
        // Password strength validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const feedback = document.querySelector('#password + .form-text');
            
            let strength = 0;
            let messages = [];
            
            if (password.length >= 8) {
                strength++;
            } else {
                messages.push('at least 8 characters');
            }
            
            if (/[a-z]/.test(password)) {
                strength++;
            } else {
                messages.push('one lowercase letter');
            }
            
            if (/[0-9]/.test(password)) {
                strength++;
            } else {
                messages.push('one number');
            }
            
            // Bonus points for special characters or uppercase
            if (/[^A-Za-z0-9]/.test(password)) {
                strength++;
            }
            if (/[A-Z]/.test(password)) {
                strength++;
            }
            
            // Update feedback message
            if (password.length === 0) {
                feedback.innerHTML = '<i class="fas fa-info-circle"></i> Must be 8+ characters with lowercase letter and number';
                feedback.className = 'form-text text-muted';
            } else if (strength >= 4) {
                feedback.innerHTML = '<i class="fas fa-check-circle text-success"></i> Strong password!';
                feedback.className = 'form-text text-success';
            } else if (strength >= 2) {
                feedback.innerHTML = '<i class="fas fa-exclamation-circle text-warning"></i> Add: ' + messages.join(', ');
                feedback.className = 'form-text text-warning';
            } else {
                feedback.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Add: ' + messages.join(', ');
                feedback.className = 'form-text text-danger';
            }
        });
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const feedback = document.getElementById('password-match-feedback');
            const checkIcon = feedback.querySelector('.fa-check-circle');
            const timesIcon = feedback.querySelector('.fa-times-circle');
            const text = document.getElementById('password-match-text');
            
            if (confirmPassword.length === 0) {
                checkIcon.classList.add('d-none');
                timesIcon.classList.add('d-none');
                text.textContent = 'Enter the same password as above';
                this.setCustomValidity('');
            } else if (password !== confirmPassword) {
                checkIcon.classList.add('d-none');
                timesIcon.classList.remove('d-none');
                text.textContent = 'Passwords do not match';
                this.setCustomValidity('Passwords do not match');
            } else {
                checkIcon.classList.remove('d-none');
                timesIcon.classList.add('d-none');
                text.textContent = 'Passwords match!';
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
