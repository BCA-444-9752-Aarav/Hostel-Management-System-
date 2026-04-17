<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

if (!isset($_POST['student_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Student ID not provided']);
    exit();
}

$student_id = $_POST['student_id'];

$stmt = $conn->prepare("SELECT s.*, r.room_number FROM students s LEFT JOIN rooms r ON s.room_id = r.id WHERE s.id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    
    // Prepare response data
    $response = [
        'success' => true,
        'data' => [
            'id' => $student['id'],
            'full_name' => $student['full_name'],
            'email' => $student['email'],
            'mobile' => $student['mobile'],
            'parent_name' => $student['parent_name'] ?? '',
            'parent_mobile' => $student['parent_mobile'] ?? '',
            'parent_email' => $student['parent_email'] ?? '',
            'emergency_contact' => $student['emergency_contact'] ?? '',
            'emergency_phone' => $student['emergency_phone'] ?? '',
            'address' => $student['address'] ?? '',
            'blood_group' => $student['blood_group'] ?? '',
            'medical_conditions' => $student['medical_conditions'] ?? '',
            'allergies' => $student['allergies'] ?? '',
            'room_id' => $student['room_id'],
            'room_number' => $student['room_number'],
            'bed_number' => $student['bed_number'] ?? 1,
            'room_status' => $student['room_id'] ? 'allocated' : 'not_allocated',
            'status' => $student['status'],
            'profile_photo' => $student['profile_photo'] ?? 'default_avatar.svg',
            'created_at' => $student['created_at']
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Student not found']);
}
?>
