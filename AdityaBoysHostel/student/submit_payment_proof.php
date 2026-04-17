<?php
require_once '../config/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $fee_id = $_POST['fee_id'] ?? 0;
        $student_id = $_SESSION['student_id'];
        $transaction_id = $_POST['transaction_id'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        
        // Validate inputs
        if (empty($fee_id) || empty($transaction_id) || empty($payment_method) || empty($amount)) {
            header('Location: dashboard.php?error=All fields are required');
            exit();
        }
        
        // Validate transaction ID length (6-12 characters, alphanumeric)
        if (strlen($transaction_id) < 6 || strlen($transaction_id) > 12 || !preg_match('/^[A-Za-z0-9]+$/', $transaction_id)) {
            header('Location: dashboard.php?error=Transaction ID must be 6-12 characters long and contain only letters and numbers');
            exit();
        }
        
        // Handle file upload
        $payment_proof_path = '';
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['payment_proof'];
            $file_name = time() . '_' . $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            
            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                header('Location: dashboard.php?error=File size must be less than 5MB');
                exit();
            }
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $file_type = mime_content_type($file_tmp);
            if (!in_array($file_type, $allowed_types)) {
                header('Location: dashboard.php?error=Only JPG, PNG, GIF, and PDF files are allowed');
                exit();
            }
            
            // Create uploads directory if it doesn't exist
            $upload_dir = dirname(__DIR__) . '/uploads/payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $payment_proof_path = 'uploads/payment_proofs/' . $file_name;
            } else {
                header('Location: dashboard.php?error=Failed to upload file');
                exit();
            }
        }
        
        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (student_id, fee_id, transaction_id, payment_method, payment_proof, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending Verification', NOW())");
        if ($stmt) {
            $stmt->bind_param("iissss", $student_id, $fee_id, $transaction_id, $payment_method, $payment_proof_path, $amount);
            if ($stmt->execute()) {
                $payment_id = $conn->insert_id;
                
                // Add notification for admin
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
                    
                    // Get student details for notification
                    $student_stmt = $conn->prepare("SELECT full_name, email FROM students WHERE id = ?");
                    $student_stmt->bind_param("i", $student_id);
                    $student_stmt->execute();
                    $student_data = $student_stmt->get_result()->fetch_assoc();
                    
                    $notification_title = "Payment Proof Submitted";
                    $notification_message = "Payment proof of ₹{$amount} via {$payment_method} by {$student_data['full_name']} ({$student_data['email']})";
                    
                    $stmt2 = $conn->prepare("
                        INSERT INTO notifications (user_type, user_id, title, message, type, is_read) 
                        VALUES ('admin', ?, ?, ?, 'info', FALSE)
                    ");
                    if ($stmt2) {
                        $stmt2->bind_param("iss", $admin_id, $notification_title, $notification_message);
                        $stmt2->execute();
                        
                        // Debug: Log notification creation
                        error_log("Admin notification created for payment proof. Admin ID: " . $admin_id . ", Payment ID: " . $payment_id);
                    }
                } catch (Exception $notification_error) {
                    // Log notification error but don't fail the payment submission
                    error_log("Notification error: " . $notification_error->getMessage());
                }
                
                // Redirect with success message
                header("Location: dashboard.php?success=Payment proof submitted successfully! Admin will verify your payment within 24 hours.");
                exit();
            } else {
                header('Location: dashboard.php?error=Failed to submit payment proof. Please try again.');
                exit();
            }
        } else {
            header('Location: dashboard.php?error=Database error. Please try again.');
            exit();
        }
        
    } catch (Exception $e) {
        header('Location: dashboard.php?error=Database error: ' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: dashboard.php?error=Invalid request method');
    exit();
}
?>
