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

$title = $_POST['title'] ?? '';
$message = $_POST['message'] ?? '';
$source = $_POST['source'] ?? 'manual';
$source_id = $_POST['source_id'] ?? null;

if (empty($title) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Title and message are required']);
    exit();
}

try {
    $admin_id = $_SESSION['admin_id'];
    
    // Insert new notification
    $stmt = $conn->prepare("
        INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
        VALUES ('admin', ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $admin_id, $title, $message, $source, $source_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification created successfully',
            'notification_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create notification'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Helper function to create notifications for different events
function createAdminNotification($title, $message, $source = 'system', $source_id = null) {
    global $conn;
    
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("
            INSERT INTO admin_notifications (user_type, user_id, title, message, source, source_id) 
            VALUES ('admin', ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $admin_id, $title, $message, $source, $source_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to create admin notification: " . $e->getMessage());
        return false;
    }
}
?>
