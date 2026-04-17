<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Debug session state
error_log("get_notifications.php accessed - Session data: " . print_r($_SESSION, true));

// Security: Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    error_log("get_notifications.php: Admin not logged in");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Admin not logged in']);
    exit();
}

error_log("get_notifications.php: Admin authenticated - ID: " . $_SESSION['admin_id']);

header('Content-Type: application/json');

try {
    $admin_id = $_SESSION['admin_id'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Debug: Log the admin_id being used
    error_log("get_notifications.php: Using admin_id = " . $admin_id);
    
    // Get admin notifications with more detailed query
    $stmt = $conn->prepare("
        SELECT id, title, message, type, is_read, created_at 
        FROM notifications 
        WHERE user_type = 'admin' AND user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $admin_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'message' => htmlspecialchars($row['message']),
            'status' => $row['is_read'] ? 'read' : 'unread',
            'source' => 'system',
            'source_id' => null,
            'created_at' => $row['created_at'],
            'time_ago' => getTimeAgo($row['created_at'])
        ];
    }
    
    // Get unread count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM notifications 
        WHERE user_type = 'admin' AND user_id = ? AND is_read = FALSE
    ");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $unread_result = $stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['unread_count'];
    
    // Debug: Log results
    error_log("get_notifications.php: Found " . count($notifications) . " notifications, $unread_count unread");
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count,
        'total_count' => count($notifications),
        'debug' => [
            'admin_id' => $admin_id,
            'limit' => $limit,
            'query_time' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    error_log("get_notifications.php ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'admin_id' => isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'not_set',
            'error' => $e->getMessage()
        ]
    ]);
}

function getTimeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $time);
    }
}
?>
