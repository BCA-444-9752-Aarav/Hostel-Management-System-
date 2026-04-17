<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$room_id = $_GET['room_id'] ?? 0;

if ($room_id <= 0) {
    echo json_encode(['occupied_beds' => []]);
    exit();
}

// Get occupied beds for this room
$stmt = $conn->prepare("SELECT bed_number FROM students WHERE room_id = ? AND bed_number IS NOT NULL ORDER BY bed_number");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$occupied_beds = [];
while ($row = $result->fetch_assoc()) {
    $occupied_beds[] = (int)$row['bed_number'];
}

echo json_encode(['occupied_beds' => $occupied_beds]);
?>
