<?php
session_start();
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

$admin_id = $_SESSION['admin_id'];
$last_checked = isset($_GET['last_checked']) ? (int)$_GET['last_checked'] : 0;

// Function to send SSE message
function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Send initial connection message
sendSSE([
    'type' => 'connected',
    'message' => 'Real-time notifications connected',
    'timestamp' => time()
]);

// Check for new notifications every 3 seconds
while (true) {
    // Get new notifications since last check
    $stmt = $conn->prepare("
        SELECT id, title, message, type, is_read, created_at 
        FROM notifications 
        WHERE user_type = 'admin' AND user_id = ? AND created_at > FROM_UNIXTIME(?)
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("ii", $admin_id, $last_checked);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $new_notifications = [];
    while ($row = $result->fetch_assoc()) {
        $new_notifications[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'message' => htmlspecialchars($row['message']),
            'type' => $row['type'],
            'status' => $row['is_read'] ? 'read' : 'unread',
            'created_at' => $row['created_at'],
            'time_ago' => getTimeAgo($row['created_at'])
        ];
    }
    
    // If new notifications found, send them
    if (!empty($new_notifications)) {
        sendSSE([
            'type' => 'new_notifications',
            'notifications' => $new_notifications,
            'count' => count($new_notifications),
            'timestamp' => time()
        ]);
        
        // Update last checked time
        $last_checked = time();
    }
    
    // Send heartbeat every 30 seconds to keep connection alive
    if (time() % 30 === 0) {
        sendSSE([
            'type' => 'heartbeat',
            'timestamp' => time()
        ]);
    }
    
    // Sleep for 3 seconds before next check
    sleep(3);
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
