<?php
require_once 'config/db.php';

/**
 * Add a notification to the database
 */
function addNotification($user_type, $user_id, $title, $message, $type = 'info') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_type, user_id, title, message, type, is_read, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, FALSE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->bind_param("sssss", $user_type, $user_id, $title, $message, $type);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error adding notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notifications for a user
 */
function getUserNotifications($user_type, $user_id, $limit = 10) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT * FROM notifications 
            WHERE user_type = ? AND user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ssi", $user_type, $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        return $notifications;
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notification_id, $user_type, $user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = TRUE, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND user_type = ? AND user_id = ?
        ");
        $stmt->bind_param("iss", $notification_id, $user_type, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notification count
 */
function getUnreadCount($user_type, $user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_type = ? AND user_id = ? AND is_read = FALSE
        ");
        $stmt->bind_param("si", $user_type, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    } catch (Exception $e) {
        error_log("Error getting unread count: " . $e->getMessage());
        return 0;
    }
}
?>
