<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle room actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_room') {
        $room_number = $_POST['room_number'] ?? '';
        $floor_number = $_POST['floor_number'] ?? 0;
        $total_beds = $_POST['total_beds'] ?? 0;
        $bed_capacity = $_POST['bed_capacity'] ?? '';
        $price_per_month = $_POST['price_per_month'] ?? 0;
        
        if (empty($room_number) || empty($floor_number) || empty($total_beds) || empty($bed_capacity) || empty($price_per_month)) {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $conn->prepare("INSERT INTO rooms (room_number, floor_number, total_beds, room_type, price_per_month) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siisd", $room_number, $floor_number, $total_beds, $bed_capacity, $price_per_month);
            
            if ($stmt->execute()) {
                $success = "Room added successfully!";
                // Redirect to prevent form resubmission
                header("Location: manage_rooms.php?success=1");
                exit();
            } else {
                $error = "Error adding room: " . $stmt->error;
            }
        }
    } elseif ($action == 'update_room') {
        $room_id = $_POST['room_id'];
        $room_number = $_POST['room_number'];
        $floor_number = $_POST['floor_number'];
        $total_beds = $_POST['total_beds'];
        $bed_capacity = $_POST['bed_capacity'];
        $price_per_month = $_POST['price_per_month'];
        
        $stmt = $conn->prepare("UPDATE rooms SET room_number = ?, floor_number = ?, total_beds = ?, room_type = ?, price_per_month = ? WHERE id = ?");
        $stmt->bind_param("siisdi", $room_number, $floor_number, $total_beds, $bed_capacity, $price_per_month, $room_id);
        
        if ($stmt->execute()) {
            $success = "Room updated successfully!";
        } else {
            $error = "Error updating room: " . $stmt->error;
        }
    } elseif ($action == 'delete_room') {
        $room_id = $_POST['room_id'];
        
        // Check if room has students
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count == 0) {
            $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $success = "Room deleted successfully!";
        } else {
            $error = "Cannot delete room. Students are assigned to this room.";
        }
    } elseif ($action == 'allocate_room') {
        $student_id = $_POST['student_id'];
        $room_id = $_POST['room_id'];
        $bed_number = $_POST['bed_number'];
        
        // Get room details
        $stmt = $conn->prepare("SELECT total_beds, occupied_beds, room_type FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $room = $stmt->get_result()->fetch_assoc();
        
        // Validate bed number is within room capacity
        if ($bed_number > $room['total_beds']) {
            $error = "Invalid bed number! Room {$room['room_number']} only has {$room['total_beds']} bed(s).";
        }
        // Check if bed is already occupied
        else {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE room_id = ? AND bed_number = ?");
            $stmt->bind_param("ii", $room_id, $bed_number);
            $stmt->execute();
            $bed_occupied = $stmt->get_result()->fetch_assoc()['count'];
            
            if ($bed_occupied > 0) {
                $error = "Bed {$bed_number} is already occupied in this room!";
            }
            // Check if room has space
            elseif ($room['occupied_beds'] >= $room['total_beds']) {
                $error = "Room is full!";
            }
            else {
                // Remove student from previous room if any
                $stmt = $conn->prepare("UPDATE students SET room_id = NULL, bed_number = NULL WHERE id = ?");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                
                // Assign to new room
                $stmt = $conn->prepare("UPDATE students SET room_id = ?, bed_number = ? WHERE id = ?");
                $stmt->bind_param("iii", $room_id, $bed_number, $student_id);
                $stmt->execute();
                
                // Update room occupancy
                $new_occupied = $room['occupied_beds'] + 1;
                $stmt = $conn->prepare("UPDATE rooms SET occupied_beds = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_occupied, $room_id);
                $stmt->execute();
                
                $success = "Room allocated successfully!";
            }
        }
    } elseif ($action == 'deallocate_room') {
        $student_id = $_POST['student_id'];
        
        // Get student's current room
        $stmt = $conn->prepare("SELECT room_id FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        
        if ($student['room_id']) {
            // Remove from room
            $stmt = $conn->prepare("UPDATE students SET room_id = NULL, bed_number = NULL WHERE id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            
            // Update room occupancy
            $stmt = $conn->prepare("UPDATE rooms SET occupied_beds = occupied_beds - 1 WHERE id = ?");
            $stmt->bind_param("i", $student['room_id']);
            $stmt->execute();
            
            $success = "Room deallocated successfully!";
        }
    }
}

// Check for success parameter in URL
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = "Room added successfully!";
}

// Get rooms list
$rooms = [];
$stmt = $conn->prepare("SELECT * FROM rooms ORDER BY floor_number, room_number");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

// Get unallocated students
$unallocated_students = [];
$stmt = $conn->prepare("SELECT id, full_name, email FROM students WHERE status = 'approved' AND room_id IS NULL ORDER BY full_name");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $unallocated_students[] = $row;
}

// Get allocated students
$allocated_students = [];
$stmt = $conn->prepare("SELECT s.id, s.full_name, s.email, s.bed_number, r.room_number FROM students s JOIN rooms r ON s.room_id = r.id WHERE s.status = 'approved' ORDER BY r.room_number, s.bed_number");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $allocated_students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.svg" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Aditya Boys Hostel</h3>
                <p class="text-white-50 mb-0">Admin Panel</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_students.php" class="sidebar-menu-item">
                    <i class="fas fa-users"></i> Manage Students
                </a>
                <a href="manage_rooms.php" class="sidebar-menu-item active">
                    <i class="fas fa-bed"></i> Room Management
                </a>
                <a href="manage_fees.php" class="sidebar-menu-item">
                    <i class="fas fa-rupee-sign"></i> Fee Management
                </a>
                <a href="payment_verification.php" class="sidebar-menu-item">
                    <i class="fas fa-credit-card"></i> Payment Verification
                </a>
                <a href="payment_history.php" class="sidebar-menu-item">
                    <i class="fas fa-history"></i> Payment History
                </a>
                <a href="payment_info.php" class="sidebar-menu-item">
                    <i class="fas fa-info-circle"></i> Payment Information
                </a>
                <a href="manage_complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comments"></i> Complaints
                </a>
                <a href="../logout.php" class="sidebar-menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="top-bar-title">Room Management</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Admin" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger animate__animated animate__shakeX" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Room Button -->
                <div class="mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus me-2"></i>Add New Room
                    </button>
                </div>
                
                <!-- Rooms Grid -->
                <div class="row">
                    <?php foreach ($rooms as $room): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="dashboard-card animate__animated animate__fadeInUp">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Room <?php echo htmlspecialchars($room['room_number']); ?></h5>
                                    <small class="text-muted">Floor <?php echo $room['floor_number']; ?></small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="editRoom(<?php echo $room['id']; ?>)">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteRoom(<?php echo $room['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Occupancy</span>
                                    <span class="badge badge-info"><?php echo $room['occupied_beds']; ?>/<?php echo $room['total_beds']; ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <?php 
                                    $occupancy_percentage = ($room['occupied_beds'] / $room['total_beds']) * 100;
                                    $progress_color = $occupancy_percentage < 50 ? 'bg-success' : ($occupancy_percentage < 80 ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <div class="progress-bar <?php echo $progress_color; ?>" 
                                         style="width: <?php echo $occupancy_percentage; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <?php 
                                $bed_capacity = $room['room_type'];
                                $capacity_colors = [
                                    'single' => 'success',
                                    'double' => 'info', 
                                    'triple' => 'warning'
                                ];
                                ?>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-<?php echo $capacity_colors[$bed_capacity] ?? 'secondary'; ?>">
                                        <i class="fas fa-bed me-1"></i>
                                        <?php echo ucfirst($bed_capacity); ?> Bed Room
                                    </span>
                                </div>
                                <div class="text-muted">
                                    <small>₹<?php echo number_format($room['price_per_month'], 0); ?>/month per bed</small>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button class="btn btn-sm btn-primary" onclick="allocateStudent(<?php echo $room['id']; ?>)">
                                    <i class="fas fa-user-plus me-1"></i>Allocate Student
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Allocated Students -->
                <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                    <h5 class="mb-3">Allocated Students</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>Room</th>
                                    <th>Bed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allocated_students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['room_number']); ?></td>
                                    <td><?php echo $student['bed_number']; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="deallocate_room">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Deallocate">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($allocated_students)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No students allocated to rooms yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" onsubmit="return validateAddRoomForm()">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_room">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Room Number</label>
                                    <input type="text" class="form-control" name="room_number" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Floor Number</label>
                                    <input type="number" class="form-control" name="floor_number" min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bed Capacity</label>
                                    <select class="form-control" name="bed_capacity" id="bedCapacity" required onchange="updateMaxBeds()">
                                        <option value="single">Single Bed</option>
                                        <option value="double">Double Bed</option>
                                        <option value="triple">Triple Bed</option>
                                    </select>
                                    <small class="text-muted">Select the type of bed arrangement in this room</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Beds</label>
                                    <input type="number" class="form-control" name="total_beds" id="totalBeds" min="1" max="4" required>
                                    <small class="text-muted" id="bedCapacityInfo">Number of beds available in the room</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Price per Month (₹)</label>
                                    <input type="number" class="form-control" name="price_per_month" min="0" step="100" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Allocate Student Modal -->
    <div class="modal fade" id="allocateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Allocate Student to Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="allocateForm" onsubmit="return validateAllocateForm()">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="allocate_room">
                        <input type="hidden" name="room_id" id="allocateRoomId">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select class="form-control" name="student_id" required>
                                <option value="">Choose a student...</option>
                                <?php foreach ($unallocated_students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bed Number</label>
                            <input type="number" class="form-control" name="bed_number" id="bedNumber" min="1" max="1" required>
                            <small class="text-muted" id="bedNumberInfo">Select bed number based on room capacity</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Allocate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_room">
                        <input type="hidden" name="room_id" id="editRoomId">
                        
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" class="form-control" name="room_number" id="editRoomNumber" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Floor Number</label>
                            <input type="number" class="form-control" name="floor_number" id="editFloorNumber" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bed Capacity</label>
                            <select class="form-control" name="bed_capacity" id="editBedCapacity" required>
                                <option value="single">Single Bed</option>
                                <option value="double">Double Bed</option>
                                <option value="triple">Triple Bed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Total Beds</label>
                            <input type="number" class="form-control" name="total_beds" id="editTotalBeds" min="1" max="4" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Price per Month (₹)</label>
                            <input type="number" class="form-control" name="price_per_month" id="editPricePerMonth" min="0" step="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <style>
        /* Ensure modal inputs are focusable and clickable */
        .modal .form-control,
        .modal .form-select {
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            z-index: auto !important;
            position: relative !important;
        }
        
        .modal .modal-body {
            pointer-events: auto !important;
            z-index: auto !important;
            position: relative !important;
        }
        
        .modal .modal-content {
            pointer-events: auto !important;
            z-index: 1051 !important;
            position: relative !important;
        }
        
        .modal .modal-dialog {
            pointer-events: auto !important;
            z-index: 1051 !important;
            position: relative !important;
        }
        
        /* Fix allocate modal size */
        #allocateModal .modal-dialog {
            max-width: 600px !important;
            width: 90vw !important;
        }
        
        #allocateModal .modal-body {
            padding: 20px !important;
        }
        
        #allocateModal select.form-control {
            min-height: 45px !important;
            font-size: 14px !important;
        }
        
        /* Ensure modal backdrop doesn't block inputs */
        .modal-backdrop {
            z-index: 1049 !important;
        }
        
        .modal {
            z-index: 1050 !important;
        }
        
        /* Fix any overlay issues */
        .modal.show {
            pointer-events: auto !important;
        }
        
        /* Add Room Modal Custom Sizing */
        #addRoomModal .modal-dialog {
            max-width: 600px !important;
            width: 90vw !important;
            margin: 1.75rem auto !important;
        }
        
        #addRoomModal .modal-body {
            padding: 1.5rem !important;
            max-height: 70vh !important;
            overflow-y: auto !important;
        }
        
        #addRoomModal .form-control {
            min-height: 38px !important;
            font-size: 14px !important;
        }
        
        #addRoomModal .form-label {
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #addRoomModal .modal-dialog {
                max-width: 95vw !important;
                margin: 0.5rem auto !important;
            }
            
            #addRoomModal .modal-body {
                padding: 1rem !important;
            }
        }
        
</style>
    
    <script>
        function openAddRoomModal() {
            // This function is no longer needed since we're using data-bs-toggle="modal"
            console.log('Modal will be opened by Bootstrap automatically');
        }
        
        function closeAddRoomModal() {
            // This function is no longer needed since Bootstrap handles modal closing
            console.log('Modal will be closed by Bootstrap automatically');
        }
        
        function validateAddRoomForm() {
            const roomNumber = document.querySelector('input[name="room_number"]');
            const floorNumber = document.querySelector('input[name="floor_number"]');
            const totalBeds = document.querySelector('input[name="total_beds"]');
            const bedCapacity = document.querySelector('select[name="bed_capacity"]');
            const pricePerMonth = document.querySelector('input[name="price_per_month"]');
            
            // Debug: Log values to console
            console.log('Room Number:', roomNumber ? roomNumber.value : 'not found');
            console.log('Floor Number:', floorNumber ? floorNumber.value : 'not found');
            console.log('Total Beds:', totalBeds ? totalBeds.value : 'not found');
            console.log('Bed Capacity:', bedCapacity ? bedCapacity.value : 'not found');
            console.log('Price:', pricePerMonth ? pricePerMonth.value : 'not found');
            
            if (!roomNumber || !roomNumber.value.trim()) {
                console.log('Room number validation failed');
                // Remove alert and just show inline error
                roomNumber.style.borderColor = 'red';
                roomNumber.focus();
                return false;
            }
            
            if (!floorNumber || !floorNumber.value || floorNumber.value < 1) {
                console.log('Floor number validation failed');
                floorNumber.style.borderColor = 'red';
                floorNumber.focus();
                return false;
            }
            
            if (!totalBeds || !totalBeds.value || totalBeds.value < 1) {
                console.log('Total beds validation failed');
                totalBeds.style.borderColor = 'red';
                totalBeds.focus();
                return false;
            }
            
            if (!bedCapacity || !bedCapacity.value) {
                console.log('Bed capacity validation failed');
                bedCapacity.style.borderColor = 'red';
                bedCapacity.focus();
                return false;
            }
            
            if (!pricePerMonth || !pricePerMonth.value || pricePerMonth.value < 0) {
                console.log('Price validation failed');
                pricePerMonth.style.borderColor = 'red';
                pricePerMonth.focus();
                return false;
            }
            
            console.log('All validations passed');
            return true;
        }
        
        function validateAllocateForm() {
            const studentId = document.querySelector('select[name="student_id"]').value;
            const bedNumber = document.getElementById('bedNumber').value;
            const roomId = document.getElementById('allocateRoomId').value;
            
            if (!studentId) {
                alert('Please select a student.');
                return false;
            }
            
            if (!bedNumber || bedNumber < 1) {
                alert('Please enter a valid bed number.');
                return false;
            }
            
            const bedNumberInput = document.getElementById('bedNumber');
            const maxBeds = parseInt(bedNumberInput.max);
            
            if (parseInt(bedNumber) > maxBeds) {
                alert(`Bed number cannot exceed ${maxBeds} for this room.`);
                return false;
            }
            
            return true;
        }
        
        function allocateStudent(roomId) {
            document.getElementById('allocateRoomId').value = roomId;
            
            // Get room details and update bed number field
            const rooms = <?php echo json_encode($rooms); ?>;
            const room = rooms.find(r => r.id == roomId);
            
            if (room) {
                const bedNumberInput = document.getElementById('bedNumber');
                const bedNumberInfo = document.getElementById('bedNumberInfo');
                
                // Set max beds based on room capacity
                bedNumberInput.max = room.total_beds;
                bedNumberInput.min = 1;
                bedNumberInput.value = '';
                
                // Update info text
                bedNumberInfo.textContent = `Room ${room.room_number} has ${room.total_beds} bed(s) (Bed 1 to ${room.total_beds})`;
                
                // Get occupied beds to show available ones
                fetch(`get_occupied_beds.php?room_id=${roomId}`)
                    .then(response => response.json())
                    .then(data => {
                        const occupiedBeds = data.occupied_beds || [];
                        let availableBedsText = `Available beds: `;
                        const availableBeds = [];
                        
                        for (let i = 1; i <= room.total_beds; i++) {
                            if (!occupiedBeds.includes(i)) {
                                availableBeds.push(i);
                            }
                        }
                        
                        if (availableBeds.length > 0) {
                            availableBedsText += availableBeds.join(', ');
                        } else {
                            availableBedsText = 'No beds available';
                        }
                        
                        bedNumberInfo.textContent = `${availableBedsText} (Room ${room.room_number} - ${room.total_beds} beds)`;
                    })
                    .catch(error => {
                        console.error('Error fetching occupied beds:', error);
                    });
            }
            
            const modal = new bootstrap.Modal(document.getElementById('allocateModal'));
            modal.show();
        }
        
        function editRoom(roomId) {
            // Load room data and show edit modal
            ajaxRequest('get_room_details.php', 'POST', 'room_id=' + roomId, function(response) {
                // Parse response and populate edit form
                const data = JSON.parse(response);
                
                // Populate edit modal fields
                document.getElementById('editRoomId').value = data.id;
                document.getElementById('editRoomNumber').value = data.room_number;
                document.getElementById('editFloorNumber').value = data.floor_number;
                document.getElementById('editBedCapacity').value = data.bed_capacity;
                document.getElementById('editTotalBeds').value = data.total_beds;
                document.getElementById('editPricePerMonth').value = data.price_per_month;
                
                const modal = new bootstrap.Modal(document.getElementById('editRoomModal'));
                modal.show();
            });
        }
        
        function deleteRoom(roomId) {
            confirmDialog('Are you sure you want to delete this room?', function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_room">
                    <input type="hidden" name="room_id" value="${roomId}">
                `;
                document.body.appendChild(form);
                form.submit();
            });
        }
        
        function updateMaxBeds() {
            const bedCapacity = document.getElementById('bedCapacity').value;
            const totalBeds = document.getElementById('totalBeds');
            const bedCapacityInfo = document.getElementById('bedCapacityInfo');
            
            // Set max beds based on capacity
            let maxBeds = 4;
            let infoText = 'Number of beds available in the room';
            
            switch(bedCapacity) {
                case 'single':
                    maxBeds = 4;
                    infoText = 'Single bed rooms (max 4 beds per room)';
                    break;
                case 'double':
                    maxBeds = 2;
                    infoText = 'Double bed rooms (max 2 beds per room)';
                    break;
                case 'triple':
                    maxBeds = 3;
                    infoText = 'Triple bed rooms (max 3 beds per room)';
                    break;
            }
            
            totalBeds.max = maxBeds;
            bedCapacityInfo.textContent = infoText;
            
            // Adjust current value if it exceeds new max
            if (parseInt(totalBeds.value) > maxBeds) {
                totalBeds.value = maxBeds;
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateMaxBeds();
            
            // Ensure modal inputs are focusable
            const addRoomModal = document.getElementById('addRoomModal');
            if (addRoomModal) {
                addRoomModal.addEventListener('shown.bs.modal', function () {
                    // Focus first input when modal is shown
                    const firstInput = addRoomModal.querySelector('input[name="room_number"]');
                    if (firstInput) {
                        setTimeout(() => {
                            firstInput.focus();
                            firstInput.click();
                        }, 100);
                    }
                    
                    // Ensure all inputs are enabled and focusable
                    const inputs = addRoomModal.querySelectorAll('input, select, button');
                    inputs.forEach(input => {
                        input.style.pointerEvents = 'auto';
                        input.style.userSelect = 'auto';
                        input.removeAttribute('disabled');
                    });
                });
            }
            
            // Clear validation errors when user types
            const formInputs = document.querySelectorAll('input[name="room_number"], input[name="floor_number"], input[name="total_beds"], input[name="price_per_month"], select[name="bed_capacity"]');
            formInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.style.borderColor = '';
                });
                input.addEventListener('change', function() {
                    this.style.borderColor = '';
                });
            });
        });
    </script>
</body>
</html>
