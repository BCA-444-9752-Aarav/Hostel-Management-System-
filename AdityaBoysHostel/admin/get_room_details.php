<?php
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized access');
}

$room_id = $_POST['room_id'];

$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $room = $result->fetch_assoc();
    
    // Set bed capacity from room_type field
    $room['bed_capacity'] = $room['room_type'];
    
    echo json_encode($room);
} else {
    echo json_encode(['error' => 'Room not found']);
}
?>
