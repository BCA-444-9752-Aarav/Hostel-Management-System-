<?php
require_once '../config/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if admin is logged in (since this is called from admin panel)
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $fee_id = $_POST['fee_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $transaction_id = $_POST['transaction_id'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        
        // Validate inputs
        if (empty($fee_id) || empty($transaction_id) || empty($payment_method) || empty($amount)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }
        
        // Get fee information to find student ID
        $stmt = $conn->prepare("SELECT student_id, month, year FROM fees WHERE id = ?");
        $stmt->bind_param("i", $fee_id);
        $stmt->execute();
        $fee_result = $stmt->get_result();
        $fee = $fee_result->fetch_assoc();
        
        if (!$fee) {
            echo json_encode(['success' => false, 'message' => 'Fee not found']);
            exit();
        }
        
        $student_id = $fee['student_id'];
        
        // Handle file upload
        $payment_proof_path = '';
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['payment_proof'];
            $file_name = time() . '_' . $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            
            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
                exit();
            }
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $file_type = mime_content_type($file_tmp);
            if (!in_array($file_type, $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and PDF files are allowed']);
                exit();
            }
            
            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $payment_proof_path = 'uploads/payment_proofs/' . $file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
                exit();
            }
        }
        
        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (student_id, fee_id, transaction_id, payment_method, payment_proof, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending Verification', NOW())");
        $stmt->bind_param("iisssd", $student_id, $fee_id, $transaction_id, $payment_method, $payment_proof_path, $amount);
        $stmt->execute();
        $payment_id = $conn->insert_id;
        
        // Update fee status to pending verification
        $stmt = $conn->prepare("UPDATE fees SET status = 'pending_verification' WHERE id = ?");
        $stmt->bind_param("i", $fee_id);
        $stmt->execute();
        
        // Add notification for admin
        $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message) VALUES ('admin', NULL, 'Payment Proof Submitted', CONCAT('Payment proof submitted for fee ID: ', ?, '. Amount: ₹', ?))");
        $stmt->bind_param("id", $fee_id, $amount);
        $stmt->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment proof submitted successfully! Admin will verify the payment.',
            'payment_id' => $payment_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
