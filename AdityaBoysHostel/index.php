<?php
session_start();
require_once 'config/db.php';

// Check if database tables exist
$needs_setup = false;
$result = $conn->query("SHOW TABLES LIKE 'students'");
if ($result->num_rows == 0) {
    $needs_setup = true;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prevent login if database is not set up
    if ($needs_setup) {
        $error = "Please set up the database first. Click the setup link below.";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user_type = $_POST['user_type'];
        
        if ($user_type == 'student') {
            $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? AND status = 'approved'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                if (password_verify($password, $student['password'])) {
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['student_name'] = $student['full_name'];
                    $_SESSION['user_type'] = 'student';
                    header('Location: student/dashboard.php');
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Email not found or account not approved!";
            }
        } else {
            $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['user_type'] = 'admin';
                    header('Location: admin/dashboard.php');
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Email not found!";
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
    <title>Aditya Boys Hostel - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="bg-gradient">
    <!-- Animated Background Elements -->
    <div class="animated-bg-elements">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
        <div class="floating-shape shape-5"></div>
        <div class="floating-shape shape-6"></div>
    </div>
    
    <!-- Animated Particles -->
    <div class="particles-container">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-lg-6 mx-auto">
                <div class="login-card animate__animated animate__fadeInUp">
                    <div class="text-center mb-4">
                        <img src="assets/logo.svg" alt="Aditya Boys Hostel" class="logo mb-3">
                        <h1 class="brand-title">Aditya Boys Hostel</h1>
                        <p class="text-muted">Welcome Back! Please login to continue</p>
                    </div>
                    
                    <?php if ($needs_setup): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Database Setup Required!</strong><br>
                            The database tables need to be created. 
                            <a href="setup_database.php" class="alert-link">Click here to setup the database</a>.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($needs_setup): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h5>Database Setup Required</h5>
                        <p>Please set up the database first before attempting to login.</p>
                        <a href="setup_database.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-database me-2"></i>Setup Database
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Login As</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="user_type" id="student" value="student" checked>
                                <label class="btn btn-outline-primary" for="student">Student</label>
                                
                                <input type="radio" class="btn-check" name="user_type" id="admin" value="admin">
                                <label class="btn btn-outline-primary" for="admin">Admin</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">New Student? <a href="student/register.php" class="text-decoration-none">Register Here</a></p>
                        </div>
                    </form>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/responsive.js"></script>
</body>
</html>
