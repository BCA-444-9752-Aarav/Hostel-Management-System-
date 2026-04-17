<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in (admin or student)
$is_admin = isset($_SESSION['admin_id']);
$is_student = isset($_SESSION['student_id']);

if (!$is_admin && !$is_student) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    if ($is_admin) {
        $user_id = $_SESSION['admin_id'];
        $user_type = 'admin';
    } else {
        $user_id = $_SESSION['student_id'];
        $user_type = 'student';
    }
    
    // Get notifications for the user
    $stmt = $conn->prepare("
        SELECT id, title, message, type, is_read, created_at 
        FROM notifications 
        WHERE user_type = ? AND (user_id = ? OR user_id IS NULL)
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("sii", $user_type, $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'message' => htmlspecialchars($row['message']),
            'status' => $row['is_read'] ? 'read' : 'unread',
            'type' => $row['type'],
            'created_at' => $row['created_at'],
            'time_ago' => getTimeAgo($row['created_at'])
        ];
    }
    
    // Get unread count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM notifications 
        WHERE user_type = ? AND (user_id = ? OR user_id IS NULL) AND is_read = FALSE
    ");
    $stmt->bind_param("si", $user_type, $user_id);
    $stmt->execute();
    $unread_result = $stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['unread_count'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count,
        'total_count' => count($notifications)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
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
