<?php
require_once '../config/db.php';

/**
 * Admin Notification Helper Functions
 * Use these functions to automatically create admin notifications
 */

/**
 * Create admin notification for new student registration
 */
function createStudentRegistrationNotification($student_id) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        // Get student details
        $stmt = $conn->prepare("SELECT full_name, email FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        
        if ($student) {
            $title = "New Student Registration";
            $message = "{$student['full_name']} ({$student['email']}) has registered and needs approval";
            
            $admin_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("
                INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
                VALUES ('admin', ?, ?, ?, 'student', ?)
            ");
            $stmt->bind_param("issi", $admin_id, $title, $message, $student_id);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Failed to create student registration notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin notification for new complaint
 */
function createComplaintNotification($complaint_id) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        // Get complaint details
        $stmt = $conn->prepare("
            SELECT c.title, c.description, s.full_name, s.email 
            FROM complaints c 
            JOIN students s ON c.student_id = s.id 
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $complaint = $stmt->get_result()->fetch_assoc();
        
        if ($complaint) {
            $title = "New Complaint Submitted";
            $message = "Complaint: {$complaint['title']} by {$complaint['full_name']}";
            
            $admin_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("
                INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
                VALUES ('admin', ?, ?, ?, 'complaint', ?)
            ");
            $stmt->bind_param("issi", $admin_id, $title, $message, $complaint_id);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Failed to create complaint notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin notification for payment submission
 */
function createPaymentNotification($payment_id) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        // Get payment details
        $stmt = $conn->prepare("
            SELECT p.amount, p.method, s.full_name, s.email 
            FROM payments p 
            JOIN students s ON p.student_id = s.id 
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if ($payment) {
            $title = "Payment Submitted";
            $message = "Payment of ₹{$payment['amount']} via {$payment['method']} by {$payment['full_name']}";
            
            $admin_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("
                INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
                VALUES ('admin', ?, ?, ?, 'payment', ?)
            ");
            $stmt->bind_param("issi", $admin_id, $title, $message, $payment_id);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Failed to create payment notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin notification for system events
 */
function createSystemNotification($title, $message) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("
            INSERT INTO admin_notifications (user_type, user_id, title, message, source) 
            VALUES ('admin', ?, ?, ?, 'system')
        ");
        $stmt->bind_param("iss", $admin_id, $title, $message);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to create system notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin notification for message
 */
function createMessageNotification($message_id) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        // Get message details (assuming messages table exists)
        $stmt = $conn->prepare("SELECT title, content FROM messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $message_data = $stmt->get_result()->fetch_assoc();
        
        if ($message_data) {
            $title = "New Message Received";
            $message_content = substr($message_data['content'], 0, 100) . "...";
            
            $admin_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("
                INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
                VALUES ('admin', ?, ?, ?, 'message', ?)
            ");
            $stmt->bind_param("issi", $admin_id, $title, $message_content, $message_id);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Failed to create message notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin notification for general events
 */
function createAdminNotification($title, $message, $source = 'manual', $source_id = null) {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("
            INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
            VALUES ('admin', ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssi", $admin_id, $title, $message, $source, $source_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to create admin notification: " . $e->getMessage());
        return false;
    }
}
?>
