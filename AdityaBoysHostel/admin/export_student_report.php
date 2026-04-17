<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="student_payment_report_' . date('Y-m-d_H-i-s') . '.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Get filter and search parameters
$filter = $_POST['filter'] ?? 'all';
$search = $_POST['search'] ?? '';

// Build SQL query with joins to get fee information
$sql = "SELECT 
    s.id,
    s.full_name,
    s.email,
    s.mobile,
    s.parent_mobile,
    s.address,
    s.status,
    s.created_at as registration_date,
    r.room_number,
    r.floor_number,
    r.price_per_month as room_price,
    COALESCE(COUNT(f.id), 0) as total_fees_generated,
    COALESCE(SUM(CASE WHEN f.status = 'paid' THEN 1 ELSE 0 END), 0) as fees_paid,
    COALESCE(SUM(CASE WHEN f.status = 'unpaid' THEN 1 ELSE 0 END), 0) as fees_unpaid,
    COALESCE(SUM(CASE WHEN f.status = 'partial' THEN 1 ELSE 0 END), 0) as fees_partial,
    COALESCE(SUM(f.amount), 0) as total_amount_required,
    COALESCE(SUM(f.paid_amount), 0) as total_amount_paid,
    COALESCE(SUM(f.amount - f.paid_amount), 0) as due_amount,
    GROUP_CONCAT(CASE WHEN f.status = 'unpaid' THEN CONCAT(f.month, ' ', f.year) END SEPARATOR ', ') as unpaid_months,
    COALESCE(MAX(f.payment_date), NULL) as last_payment_date,
    COALESCE(MIN(CASE WHEN f.status = 'unpaid' THEN f.created_at END), NULL) as oldest_unpaid_date
FROM students s
LEFT JOIN rooms r ON s.room_id = r.id
LEFT JOIN fees f ON s.id = f.student_id";

$params = [];
$types = '';

// Apply filters
$where_clauses = [];

if ($filter != 'all') {
    $where_clauses[] = "s.status = ?";
    $params[] = $filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_clauses[] = "(s.full_name LIKE ? OR s.email LIKE ? OR s.mobile LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " GROUP BY s.id, s.full_name, s.email, s.mobile, s.parent_mobile, s.address, s.status, s.created_at, r.room_number, r.floor_number, r.price_per_month
ORDER BY s.full_name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Create file pointer
$output = fopen('php://output', 'w');

// CSV Headers with proper formatting
$headers = [
    'Student ID',
    'Full Name',
    'Email',
    'Mobile',
    'Parent Mobile',
    'Address',
    'Status',
    'Registration Date',
    'Room Number',
    'Floor',
    'Room Price',
    'Payment Status',
    'Total Fees Generated',
    'Fees Paid',
    'Fees Unpaid',
    'Fees Partial',
    'Total Amount Required',
    'Total Amount Paid',
    'Due Amount',
    'Unpaid Months',
    'Last Payment Date',
    'Days Since Last Payment'
];

// Write headers
fputcsv($output, $headers);

// Store all data for summary calculation
$all_data = [];
$total_students = 0;
$total_amount_due = 0;
$total_amount_paid = 0;
$total_amount_remaining = 0;
$fully_paid = 0;
$partially_paid = 0;
$unpaid = 0;

// Write data rows
while ($row = $result->fetch_assoc()) {
    // Determine payment status with clear indicators
    $payment_status = 'No Fees Generated';
    if ($row['total_fees_generated'] > 0) {
        if ($row['due_amount'] == 0) {
            $payment_status = 'FULLY PAID';
        } elseif ($row['total_amount_paid'] > 0) {
            $payment_status = 'PARTIALLY PAID';
        } else {
            $payment_status = 'NOT PAID';
        }
    }

    // Calculate days since last payment
    $days_since_last_payment = '';
    if ($row['last_payment_date']) {
        $last_payment = new DateTime($row['last_payment_date']);
        $today = new DateTime();
        $days_diff = $today->diff($last_payment)->days;
        $days_since_last_payment = $days_diff . ' days';
    } elseif ($row['total_fees_generated'] > 0) {
        $days_since_last_payment = 'Never paid';
    }

    // Format data for CSV with proper data types
    $csv_row = [
        (int)$row['id'],
        trim($row['full_name']),
        trim($row['email']),
        trim($row['mobile']),
        trim($row['parent_mobile']),
        trim($row['address']),
        ucfirst($row['status']),
        $row['registration_date'] ? date('Y-m-d', strtotime($row['registration_date'])) : '',
        $row['room_number'] ? trim($row['room_number']) : 'Not Assigned',
        $row['floor_number'] ? (int)$row['floor_number'] : '',
        $row['room_price'] ? number_format((float)$row['room_price'], 2, '.', '') : '0.00',
        $payment_status,
        (int)$row['total_fees_generated'],
        (int)$row['fees_paid'],
        (int)$row['fees_unpaid'],
        (int)$row['fees_partial'],
        number_format((float)$row['total_amount_required'], 2, '.', ''),
        number_format((float)$row['total_amount_paid'], 2, '.', ''),
        number_format((float)$row['due_amount'], 2, '.', ''),
        $row['unpaid_months'] ? trim($row['unpaid_months']) : 'None',
        $row['last_payment_date'] ? date('Y-m-d', strtotime($row['last_payment_date'])) : 'Never',
        $days_since_last_payment
    ];

    fputcsv($output, $csv_row);
    $all_data[] = $row;
    
    // Calculate totals
    $total_students++;
    $total_amount_due += (float)$row['total_amount_required'];
    $total_amount_paid += (float)$row['total_amount_paid'];
    $total_amount_remaining += (float)$row['due_amount'];
    
    if ($row['total_fees_generated'] > 0) {
        if ($row['due_amount'] == 0) {
            $fully_paid++;
        } elseif ($row['total_amount_paid'] > 0) {
            $partially_paid++;
        } else {
            $unpaid++;
        }
    }
}

// Add summary statistics
fputcsv($output, []); // Empty row for separation
fputcsv($output, ['PAYMENT SUMMARY STATISTICS']);
fputcsv($output, ['Total Students', $total_students]);
fputcsv($output, ['Total Amount Required', number_format($total_amount_due, 2, '.', '')]);
fputcsv($output, ['Total Amount Paid', number_format($total_amount_paid, 2, '.', '')]);
fputcsv($output, ['Total Due Amount', number_format($total_amount_remaining, 2, '.', '')]);
fputcsv($output, ['Payment Collection Rate', $total_amount_due > 0 ? number_format(($total_amount_paid / $total_amount_due) * 100, 2, '.', '') . '%' : '0%']);
fputcsv($output, []);
fputcsv($output, ['STUDENT PAYMENT STATUS BREAKDOWN']);
fputcsv($output, ['Fully Paid Students', $fully_paid]);
fputcsv($output, ['Partially Paid Students', $partially_paid]);
fputcsv($output, ['Not Paid Students', $unpaid]);
fputcsv($output, ['No Fees Generated', $total_students - $fully_paid - $partially_paid - $unpaid]);
fputcsv($output, []);
fputcsv($output, ['Report Generated', date('Y-m-d H:i:s')]);

// Close file pointer
fclose($output);

exit();
?>
