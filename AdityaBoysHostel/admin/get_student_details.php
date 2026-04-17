<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized access');
}

$student_id = $_POST['student_id'];

$stmt = $conn->prepare("SELECT s.*, r.room_number FROM students s LEFT JOIN rooms r ON s.room_id = r.id WHERE s.id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    ?>
    <div class="row">
        <div class="col-md-4 text-center">
            <img src="../uploads/<?php echo htmlspecialchars($student['profile_photo'] ?? 'default_avatar.svg'); ?>" 
                 alt="Profile" class="img-fluid rounded-circle mb-3" style="max-width: 200px;"
                 onerror="this.src='../assets/default_avatar.svg';">
            <h5><?php echo htmlspecialchars($student['full_name']); ?></h5>
            <span class="badge badge-<?php echo $student['status'] == 'approved' ? 'success' : ($student['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                <?php echo $student['status'] == 'approved' ? 'Active' : ucfirst($student['status']); ?>
            </span>
        </div>
        <div class="col-md-8">
            <table class="table table-borderless">
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Mobile:</strong></td>
                    <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                </tr>
                <tr>
                    <td><strong>Parent's Mobile:</strong></td>
                    <td><?php echo htmlspecialchars($student['parent_mobile']); ?></td>
                </tr>
                <tr>
                    <td><strong>Address:</strong></td>
                    <td><?php echo htmlspecialchars($student['address']); ?></td>
                </tr>
                <tr>
                    <td><strong>Room:</strong></td>
                    <td>
                        <?php 
                        if ($student['room_id']) {
                            echo htmlspecialchars($student['room_number']) . ' - Bed ' . $student['bed_number'];
                        } else {
                            echo 'Not Assigned';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Registered:</strong></td>
                    <td><?php echo date('M d, Y h:i A', strtotime($student['created_at'])); ?></td>
                </tr>
            </table>
            
            <!-- Active/Deactive Buttons -->
            <div class="mt-3 pt-3 border-top">
                <h6><i class="fas fa-toggle-on me-2"></i>Account Status</h6>
                <?php if ($student['status'] == 'approved'): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-success"><i class="fas fa-check-circle me-2"></i>Currently Active</span>
                        <button type="button" class="btn btn-sm btn-warning" onclick="deactivateStudent(<?php echo $student['id']; ?>)" title="Deactivate Student">
                            <i class="fas fa-pause me-1"></i>Deactivate
                        </button>
                    </div>
                <?php elseif ($student['status'] == 'inactive'): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-secondary"><i class="fas fa-pause-circle me-2"></i>Currently Inactive</span>
                        <button type="button" class="btn btn-sm btn-success" onclick="activateStudent(<?php echo $student['id']; ?>)" title="Activate Student">
                            <i class="fas fa-play me-1"></i>Activate
                        </button>
                    </div>
                <?php elseif ($student['status'] == 'pending'): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-warning"><i class="fas fa-clock me-2"></i>Pending Approval</span>
                        <button type="button" class="btn btn-sm btn-success" onclick="approveStudent(<?php echo $student['id']; ?>)" title="Approve Student">
                            <i class="fas fa-check me-1"></i>Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="rejectStudent(<?php echo $student['id']; ?>)" title="Reject Student">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
} else {
    echo '<p class="text-danger">Student not found.</p>';
}
?>
