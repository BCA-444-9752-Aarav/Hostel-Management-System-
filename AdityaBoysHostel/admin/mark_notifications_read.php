<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'mark_all_read') {
        try {
            // Check if notifications table exists first
            $check_table = $conn->prepare("SHOW TABLES LIKE 'notifications'");
            $check_table->execute();
            $table_result = $check_table->get_result();
            $notifications_table_exists = $table_result->num_rows > 0;
            
            if ($notifications_table_exists) {
                // Mark all notifications for this specific admin as read
                $admin_id = $_SESSION['admin_id'];
                $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_type = 'admin' AND user_id = ? AND is_read = FALSE");
                $stmt->bind_param("i", $admin_id);
                $result = $stmt->execute();
                
                if ($result) {
                    $updated_rows = $stmt->affected_rows;
                    echo json_encode(['success' => true, 'message' => "$updated_rows notifications marked as read"]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update notifications']);
                }
            } else {
                // Notifications table doesn't exist, but that's not an error
                echo json_encode(['success' => true, 'message' => 'No notifications table exists']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
