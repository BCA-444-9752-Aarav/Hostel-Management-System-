<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Security: Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$notification_id = $_POST['notification_id'] ?? null;

if (!$notification_id || !is_numeric($notification_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit();
}

try {
    $admin_id = $_SESSION['admin_id'];
    
    // Mark notification as read
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = TRUE, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND user_type = 'admin' AND user_id = ? AND is_read = FALSE
    ");
    $stmt->bind_param("ii", $notification_id, $admin_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Get updated unread count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as unread_count 
            FROM notifications 
            WHERE user_type = 'admin' AND user_id = ? AND is_read = FALSE
        ");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $unread_count = $result->fetch_assoc()['unread_count'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read',
            'unread_count' => $unread_count
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Notification not found or already read'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
