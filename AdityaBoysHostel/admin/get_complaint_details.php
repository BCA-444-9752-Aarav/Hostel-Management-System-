<?php
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized access');
}

$complaint_id = $_POST['complaint_id'];

$stmt = $conn->prepare("SELECT c.*, s.full_name, s.email, s.mobile FROM complaints c JOIN students s ON c.student_id = s.id WHERE c.id = ?");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $complaint = $result->fetch_assoc();
    ?>
    <style>
        .complaint-details {
            color: #ffffff !important;
            background-color: transparent !important;
        }
        .complaint-details h6 {
            color: #ffffff !important;
            font-weight: 600 !important;
            background-color: transparent !important;
        }
        .complaint-details .card-title {
            color: #ffffff !important;
            font-weight: 600 !important;
            background-color: transparent !important;
        }
        .complaint-details .card-text {
            color: #ffffff !important;
            line-height: 1.6 !important;
            background-color: transparent !important;
        }
        .complaint-details table td {
            color: #ffffff !important;
            background-color: transparent !important;
        }
        .complaint-details .card-body {
            background-color: #21262d !important;
            border: 1px solid #30363d !important;
            border-radius: 6px !important;
            padding: 1rem !important;
        }
        .complaint-details .card {
            background-color: #21262d !important;
            border: 1px solid #30363d !important;
            border-radius: 6px !important;
        }
        .complaint-details table {
            background-color: transparent !important;
            border: 1px solid #30363d !important;
            border-radius: 6px !important;
        }
        .complaint-details .table td {
            background-color: transparent !important;
            border-bottom: 1px solid #30363d !important;
            padding: 0.5rem !important;
        }
        .complaint-details strong {
            color: #c9d1d9 !important;
        }
        .complaint-details .table-borderless td {
            border-bottom: 1px solid #30363d !important;
        }
        .complaint-details .badge {
            font-weight: 500 !important;
        }
        .complaint-details .badge-danger {
            background-color: #f85149 !important;
            color: #ffffff !important;
        }
        .complaint-details .badge-warning {
            background-color: #f7ba2a !important;
            color: #000000 !important;
        }
        .complaint-details .badge-success {
            background-color: #3fb950 !important;
            color: #ffffff !important;
        }
    </style>
    <div class="row complaint-details">
        <div class="col-md-6">
            <h6 class="mb-3">Student Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td><?php echo htmlspecialchars($complaint['full_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($complaint['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Mobile:</strong></td>
                    <td><?php echo htmlspecialchars($complaint['mobile']); ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="mb-3">Complaint Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>Category:</strong></td>
                    <td><?php echo ucfirst($complaint['category']); ?></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        <?php
                        $status_colors = [
                            'pending' => 'badge-danger',
                            'in_progress' => 'badge-warning',
                            'resolved' => 'badge-success'
                        ];
                        ?>
                        <span class="badge <?php echo $status_colors[$complaint['status']]; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Created:</strong></td>
                    <td><?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></td>
                </tr>
                <?php if ($complaint['status'] === 'resolved'): ?>
                <tr>
                    <td><strong>Resolved:</strong></td>
                    <td><?php echo date('M d, Y H:i', strtotime($complaint['updated_at'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <div class="mt-3 complaint-details">
        <h6 class="mb-3">Complaint Details</h6>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title"><?php echo htmlspecialchars($complaint['title']); ?></h6>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
            </div>
        </div>
    </div>
    
    <?php if ($complaint['admin_response']): ?>
    <div class="mt-3 complaint-details">
        <h6 class="mb-3">Admin Response</h6>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="card-text"><?php echo nl2br(htmlspecialchars($complaint['admin_response'])); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php
} else {
    echo '<p class="text-danger" style="color: #dc3545 !important;">Complaint not found.</p>';
}
?>
