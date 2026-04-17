<?php
session_start();
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Please login as admin first: <a href='../index.php'>Login</a>");
}

echo "<h1>🔍 Dashboard Data Verification</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto;'>";

// Test Room Occupancy Data
echo "<h2>🏠 Room Occupancy Data Test</h2>";
echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$room_occupancy_data = [
    'total_rooms' => 0,
    'total_beds' => 0,
    'occupied_beds' => 0,
    'available_beds' => 0,
    'occupancy_rate' => 0
];

try {
    $result = $conn->query("SELECT COUNT(*) as total_rooms, SUM(total_beds) as total_beds, SUM(occupied_beds) as occupied_beds FROM rooms");
    if ($result && $result->num_rows > 0) {
        $room_stats = $result->fetch_assoc();
        $room_occupancy_data['total_rooms'] = $room_stats['total_rooms'];
        $room_occupancy_data['total_beds'] = $room_stats['total_beds'];
        $room_occupancy_data['occupied_beds'] = $room_stats['occupied_beds'];
        $room_occupancy_data['available_beds'] = $room_occupancy_data['total_beds'] - $room_occupancy_data['occupied_beds'];
        
        if ($room_occupancy_data['total_beds'] > 0) {
            $room_occupancy_data['occupancy_rate'] = round(($room_occupancy_data['occupied_beds'] / $room_occupancy_data['total_beds']) * 100, 1);
        }
    }
    
    echo "<p><strong>✅ Query Success:</strong> Real data fetched from rooms table</p>";
    echo "<p><strong>Total Rooms:</strong> " . $room_occupancy_data['total_rooms'] . "</p>";
    echo "<p><strong>Total Beds:</strong> " . $room_occupancy_data['total_beds'] . "</p>";
    echo "<p><strong>Occupied Beds:</strong> " . $room_occupancy_data['occupied_beds'] . "</p>";
    echo "<p><strong>Available Beds:</strong> " . $room_occupancy_data['available_beds'] . "</p>";
    echo "<p><strong>Occupancy Rate:</strong> " . $room_occupancy_data['occupancy_rate'] . "%</p>";
    
    if ($room_occupancy_data['total_beds'] == 0) {
        echo "<p style='color: orange;'>⚠️ No rooms found in database</p>";
        echo "<button onclick='createSampleRooms()' style='background-color: #007bff; color: white; padding: 5px 10px; border: none; cursor: pointer;'>Create Sample Rooms</button>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test Fee Collection Data
echo "<h2>💰 Fee Collection Data Test</h2>";
echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$fee_collection_data = [];
$current_date = new DateTime();

for ($i = 5; $i >= 0; $i--) {
    $month_date = clone $current_date;
    $month_date->modify("-$i months");
    $month_name = $month_date->format('F');
    $month_short = $month_date->format('M');
    $year = $month_date->format('Y');
    
    $monthly_total = 0;
    
    try {
        // Primary: Get from payments table
        $stmt = $conn->prepare("SELECT COALESCE(SUM(p.amount), 0) as total FROM payments p 
                                WHERE MONTH(p.created_at) = ? AND YEAR(p.created_at) = ? AND p.status = 'Approved'");
        $month_num = $month_date->format('n');
        $stmt->bind_param("ii", $month_num, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $monthly_total = $result->fetch_assoc()['total'];
        
        // Fallback: Get from fees table
        if ($monthly_total == 0) {
            $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM fees 
                                    WHERE month = ? AND year = ? AND status = 'paid'");
            $stmt->bind_param("si", $month_name, $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $monthly_total = $result->fetch_assoc()['total'];
        }
    } catch (Exception $e) {
        $monthly_total = 0;
    }
    
    $fee_collection_data[] = [
        'month' => $month_short,
        'full_month' => $month_name,
        'year' => $year,
        'total' => (float)$monthly_total
    ];
    
    echo "<p><strong>$month_short $year:</strong> ₹" . number_format($monthly_total) . "</p>";
}

$total_fees = array_sum(array_column($fee_collection_data, 'total'));
echo "<p><strong>Total (6 months):</strong> ₹" . number_format($total_fees) . "</p>";

if ($total_fees == 0) {
    echo "<p style='color: orange;'>⚠️ No fee data found</p>";
    echo "<button onclick='createSampleFees()' style='background-color: #007bff; color: white; padding: 5px 10px; border: none; cursor: pointer;'>Create Sample Fees</button>";
}

echo "</div>";

// Test Complaint Status Data
echo "<h2>📋 Complaint Status Data Test</h2>";
echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

$complaint_status_data = [
    'pending' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'total' => 0
];

try {
    $result = $conn->query("SELECT status, COUNT(*) as count FROM complaints GROUP BY status");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            $count = (int)$row['count'];
            
            if (in_array($status, ['pending', 'open'])) {
                $complaint_status_data['pending'] += $count;
            } elseif (in_array($status, ['in_progress', 'processing'])) {
                $complaint_status_data['in_progress'] += $count;
            } elseif (in_array($status, ['resolved', 'closed', 'completed'])) {
                $complaint_status_data['resolved'] += $count;
            }
        }
    }
    
    $complaint_status_data['total'] = $complaint_status_data['pending'] + $complaint_status_data['in_progress'] + $complaint_status_data['resolved'];
    
    echo "<p><strong>✅ Query Success:</strong> Real data fetched from complaints table</p>";
    echo "<p><strong>Pending:</strong> " . $complaint_status_data['pending'] . "</p>";
    echo "<p><strong>In Progress:</strong> " . $complaint_status_data['in_progress'] . "</p>";
    echo "<p><strong>Resolved:</strong> " . $complaint_status_data['resolved'] . "</p>";
    echo "<p><strong>Total:</strong> " . $complaint_status_data['total'] . "</p>";
    
    if ($complaint_status_data['total'] == 0) {
        echo "<p style='color: orange;'>⚠️ No complaints found</p>";
        echo "<button onclick='createSampleComplaints()' style='background-color: #007bff; color: white; padding: 5px 10px; border: none; cursor: pointer;'>Create Sample Complaints</button>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Summary
echo "<h2>📊 Data Summary for Dashboard</h2>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

echo "<h3>JSON Data for JavaScript:</h3>";
echo "<pre style='background-color: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
echo "// Room Occupancy Data\n";
echo json_encode($room_occupancy_data, JSON_PRETTY_PRINT);
echo "\n\n// Fee Collection Data\n";
echo json_encode($fee_collection_data, JSON_PRETTY_PRINT);
echo "\n\n// Complaint Status Data\n";
echo json_encode($complaint_status_data, JSON_PRETTY_PRINT);
echo "</pre>";

echo "</div>";

echo "<h3>🔗 Test Dashboard</h3>";
echo "<p><a href='dashboard.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 View Updated Dashboard</a></p>";

echo "</div>";

// JavaScript for creating sample data
echo "<script>
function createSampleRooms() {
    if(confirm('Create sample room data?')) {
        window.location.href = '?create_rooms=1';
    }
}

function createSampleFees() {
    if(confirm('Create sample fee data?')) {
        window.location.href = '?create_fees=1';
    }
}

function createSampleComplaints() {
    if(confirm('Create sample complaint data?')) {
        window.location.href = '?create_complaints=1';
    }
}
</script>";

// Handle sample data creation
if (isset($_GET['create_rooms'])) {
    $conn->query("DELETE FROM rooms");
    $sample_rooms = [
        ['A101', 2, 4],
        ['A102', 3, 4],
        ['A103', 1, 4],
        ['B101', 4, 4],
        ['B102', 2, 4]
    ];
    
    foreach ($sample_rooms as $room) {
        $stmt = $conn->prepare("INSERT INTO rooms (room_number, occupied_beds, total_beds) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $room[0], $room[1], $room[2]);
        $stmt->execute();
    }
    echo "<script>alert('Sample rooms created!'); window.location.href = 'verify_dashboard_data.php';</script>";
}

if (isset($_GET['create_fees'])) {
    $current_month = date('F');
    $current_year = date('Y');
    
    $stmt = $conn->prepare("INSERT INTO fees (student_id, month, year, amount, status, due_date) VALUES (?, ?, ?, ?, ?, ?)");
    $student_id = 1;
    $amount = 8000;
    $status = 'paid';
    $due_date = date('Y-m-d', strtotime('+7 days'));
    $stmt->bind_param("isisis", $student_id, $current_month, $current_year, $amount, $status, $due_date);
    $stmt->execute();
    
    // Create corresponding payment
    $fee_id = $conn->insert_id;
    $stmt = $conn->prepare("INSERT INTO payments (student_id, fee_id, amount, payment_method, status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
    $payment_method = 'upi';
    $payment_status = 'Approved';
    $transaction_id = 'TXN' . time();
    $stmt->bind_param("iidsss", $student_id, $fee_id, $amount, $payment_method, $payment_status, $transaction_id);
    $stmt->execute();
    
    echo "<script>alert('Sample fee data created!'); window.location.href = 'verify_dashboard_data.php';</script>";
}

if (isset($_GET['create_complaints'])) {
    $conn->query("DELETE FROM complaints");
    $sample_complaints = [
        ['pending', 'WiFi not working', 'Internet connectivity issues in room A101'],
        ['in_progress', 'Water leakage', 'Water leaking from bathroom ceiling'],
        ['resolved', 'AC not working', 'AC was repaired and is now working fine']
    ];
    
    $student_id = 1;
    foreach ($sample_complaints as $complaint) {
        $stmt = $conn->prepare("INSERT INTO complaints (student_id, title, description, category, status) VALUES (?, ?, ?, ?, ?)");
        $title = $complaint[1];
        $description = $complaint[2];
        $category = 'maintenance';
        $status = $complaint[0];
        $stmt->bind_param("issss", $student_id, $title, $description, $category, $status);
        $stmt->execute();
    }
    
    echo "<script>alert('Sample complaints created!'); window.location.href = 'verify_dashboard_data.php';</script>";
}
?>
