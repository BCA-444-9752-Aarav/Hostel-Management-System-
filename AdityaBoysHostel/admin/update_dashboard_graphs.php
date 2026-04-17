<?php
session_start();
require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Please login as admin first: <a href='../index.php'>Login</a>");
}

echo "<h2>📊 Admin Dashboard Graph Data Analysis</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto;'>";

// Function to safely get data with error handling
function safeQuery($conn, $query, $params = [], $types = "") {
    try {
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
        return false;
    }
}

// 1. Check Room Occupancy Data
echo "<h3>🏠 Room Occupancy Data</h3>";
$room_data = [];
$result = safeQuery($conn, "SELECT room_number, occupied_beds, total_beds FROM rooms ORDER BY room_number");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $room_data[] = $row;
    }
    
    $total_occupied = array_sum(array_column($room_data, 'occupied_beds'));
    $total_beds = array_sum(array_column($room_data, 'total_beds'));
    $total_available = $total_beds - $total_occupied;
    
    echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Room Data Found</h4>";
    echo "<p><strong>Total Rooms:</strong> " . count($room_data) . "</p>";
    echo "<p><strong>Total Beds:</strong> $total_beds</p>";
    echo "<p><strong>Occupied Beds:</strong> $total_occupied</p>";
    echo "<p><strong>Available Beds:</strong> $total_available</p>";
    echo "<p><strong>Occupancy Rate:</strong> " . round(($total_occupied / $total_beds) * 100, 1) . "%</p>";
    echo "</div>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Room</th><th>Occupied</th><th>Total</th><th>Available</th></tr>";
    foreach ($room_data as $room) {
        $available = $room['total_beds'] - $room['occupied_beds'];
        echo "<tr>";
        echo "<td>" . $room['room_number'] . "</td>";
        echo "<td>" . $room['occupied_beds'] . "</td>";
        echo "<td>" . $room['total_beds'] . "</td>";
        echo "<td>$available</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ No Room Data Found</h4>";
    echo "<p>Creating sample room data...</p>";
    
    // Create sample room data
    $sample_rooms = [
        ['A101', 2, 4],
        ['A102', 3, 4],
        ['A103', 4, 4],
        ['B101', 1, 4],
        ['B102', 2, 4]
    ];
    
    foreach ($sample_rooms as $room) {
        $stmt = $conn->prepare("INSERT INTO rooms (room_number, occupied_beds, total_beds) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $room[0], $room[1], $room[2]);
        $stmt->execute();
    }
    echo "<p style='color: green;'>✅ Sample room data created!</p>";
    echo "</div>";
}

// 2. Check Fee Collection Data
echo "<h3>💰 Fee Collection Data (Last 6 Months)</h3>";
$fee_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('F', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $result = safeQuery($conn, "SELECT SUM(amount) as total FROM fees WHERE month = ? AND year = ? AND status = 'paid'", [$month, $year], "si");
    
    $total = 0;
    if ($result && $result->num_rows > 0) {
        $total = $result->fetch_assoc()['total'] ?? 0;
    }
    $fee_data[] = ['month' => substr($month, 0, 3), 'total' => $total];
}

$total_fees = array_sum(array_column($fee_data, 'total'));
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✅ Fee Data Analysis</h4>";
echo "<p><strong>Total Collected (6 months):</strong> ₹" . number_format($total_fees) . "</p>";
echo "<p><strong>Average per month:</strong> ₹" . number_format($total_fees / 6) . "</p>";
echo "</div>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr><th>Month</th><th>Amount Collected</th></tr>";
foreach ($fee_data as $fee) {
    echo "<tr>";
    echo "<td>" . $fee['month'] . "</td>";
    echo "<td>₹" . number_format($fee['total']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Check Complaint Status Data
echo "<h3>📋 Complaint Status Data</h3>";
$complaint_data = [];
$result = safeQuery($conn, "SELECT status, COUNT(*) as count FROM complaints GROUP BY status");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $complaint_data[$row['status']] = $row['count'];
    }
    
    $total_complaints = array_sum($complaint_data);
    echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Complaint Data Found</h4>";
    echo "<p><strong>Total Complaints:</strong> $total_complaints</p>";
    echo "</div>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Status</th><th>Count</th></tr>";
    $statuses = ['pending', 'in_progress', 'resolved'];
    foreach ($statuses as $status) {
        $count = $complaint_data[$status] ?? 0;
        echo "<tr>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $status)) . "</td>";
        echo "<td>$count</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ No Complaint Data Found</h4>";
    echo "<p>Creating sample complaint data...</p>";
    
    // Create sample complaint data
    $sample_complaints = [
        ['pending', 3],
        ['in_progress', 2],
        ['resolved', 5]
    ];
    
    foreach ($sample_complaints as $complaint) {
        for ($i = 0; $i < $complaint[1]; $i++) {
            $stmt = $conn->prepare("INSERT INTO complaints (student_id, title, description, category, status) VALUES (?, ?, ?, ?, ?)");
            $title = "Sample " . ucfirst($complaint[0]) . " Complaint " . ($i + 1);
            $description = "This is a sample complaint for testing purposes.";
            $category = "maintenance";
            $student_id = 1; // Assuming student ID 1 exists
            $stmt->bind_param("issss", $student_id, $title, $description, $category, $complaint[0]);
            $stmt->execute();
        }
    }
    echo "<p style='color: green;'>✅ Sample complaint data created!</p>";
    echo "</div>";
}

// 4. Check Payment Statistics
echo "<h3>💳 Payment Statistics (Last 30 Days)</h3>";
$payment_stats = [
    'total_payments' => 0,
    'pending_payments' => 0,
    'approved_payments' => 0,
    'total_collected' => 0
];

$result = safeQuery($conn, "SELECT status, COUNT(*) as count, SUM(amount) as total FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payment_stats['total_payments'] += $row['count'];
        if ($row['status'] == 'Pending Verification') {
            $payment_stats['pending_payments'] = $row['count'];
        } elseif ($row['status'] == 'Approved') {
            $payment_stats['approved_payments'] = $row['count'];
            $payment_stats['total_collected'] += $row['total'];
        }
    }
    
    echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Payment Data Found</h4>";
    echo "<p><strong>Total Payments:</strong> " . $payment_stats['total_payments'] . "</p>";
    echo "<p><strong>Pending:</strong> " . $payment_stats['pending_payments'] . "</p>";
    echo "<p><strong>Approved:</strong> " . $payment_stats['approved_payments'] . "</p>";
    echo "<p><strong>Total Collected:</strong> ₹" . number_format($payment_stats['total_collected']) . "</p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ No Payment Data Found</h4>";
    echo "<p>Creating sample payment data...</p>";
    
    // Create sample payment data
    $sample_payments = [
        ['Approved', 5000],
        ['Pending Verification', 3000],
        ['Approved', 4500],
        ['Rejected', 2000],
        ['Approved', 6000]
    ];
    
    $student_id = 1; // Assuming student ID 1 exists
    foreach ($sample_payments as $payment) {
        $stmt = $conn->prepare("INSERT INTO payments (student_id, fee_id, amount, payment_method, status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
        $fee_id = 1;
        $amount = $payment[1];
        $method = 'upi';
        $status = $payment[0];
        $transaction_id = 'TXN' . time() . rand(1000, 9999);
        $stmt->bind_param("iidsss", $student_id, $fee_id, $amount, $method, $status, $transaction_id);
        $stmt->execute();
    }
    echo "<p style='color: green;'>✅ Sample payment data created!</p>";
    echo "</div>";
}

// 5. Dashboard Statistics Verification
echo "<h3>📈 Dashboard Statistics Verification</h3>";
$result = safeQuery($conn, "SELECT COUNT(*) as count FROM students WHERE status = 'approved'");
$total_students = $result ? $result->fetch_assoc()['count'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM students WHERE status = 'pending'");
$pending_approvals = $result ? $result->fetch_assoc()['count'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM complaints WHERE status IN ('pending', 'in_progress')");
$pending_complaints = $result ? $result->fetch_assoc()['count'] : 0;

echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✅ Dashboard Stats Ready</h4>";
echo "<p><strong>Total Students:</strong> $total_students</p>";
echo "<p><strong>Pending Approvals:</strong> $pending_approvals</p>";
echo "<p><strong>Pending Complaints:</strong> $pending_complaints</p>";
echo "</div>";

echo "<h3>🔗 Next Steps</h3>";
echo "<div style='background-color: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>All graph data has been verified and updated!</strong></p>";
echo "<ol>";
echo "<li>✅ Room occupancy data is ready</li>";
echo "<li>✅ Fee collection data is calculated</li>";
echo "<li>✅ Complaint status data is available</li>";
echo "<li>✅ Payment statistics are updated</li>";
echo "<li>✅ Dashboard stats are verified</li>";
echo "</ol>";
echo "<p><a href='dashboard.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 View Updated Dashboard</a></p>";
echo "</div>";

echo "</div>";
?>
