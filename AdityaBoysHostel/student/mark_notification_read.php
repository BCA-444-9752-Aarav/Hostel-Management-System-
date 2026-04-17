<?php
require_once '../config/db.php';

if (!isset($_SESSION['student_id'])) {
    exit('Unauthorized access');
}

$notification_id = $_POST['notification_id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_type = 'student' AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $_SESSION['student_id']);
$stmt->execute();

echo 'success';
?>
