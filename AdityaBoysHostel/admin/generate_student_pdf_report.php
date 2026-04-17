<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

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
    GROUP_CONCAT(CASE WHEN f.status = 'paid' THEN CONCAT(f.month, ' ', f.year) END SEPARATOR ', ') as paid_months,
    GROUP_CONCAT(CASE WHEN f.status = 'partial' THEN CONCAT(f.month, ' ', f.year) END SEPARATOR ', ') as partial_months,
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

// Calculate summary statistics
$total_students = 0;
$total_amount_required = 0;
$total_amount_paid = 0;
$total_due_amount = 0;
$fully_paid = 0;
$partially_paid = 0;
$not_paid = 0;
$no_fees = 0;

$all_students = [];
while ($row = $result->fetch_assoc()) {
    $all_students[] = $row;
    $total_students++;
    $total_amount_required += (float)$row['total_amount_required'];
    $total_amount_paid += (float)$row['total_amount_paid'];
    $total_due_amount += (float)$row['due_amount'];
    
    if ($row['total_fees_generated'] > 0) {
        if ($row['due_amount'] == 0) {
            $fully_paid++;
        } elseif ($row['total_amount_paid'] > 0) {
            $partially_paid++;
        } else {
            $not_paid++;
        }
    } else {
        $no_fees++;
    }
}

// Generate HTML for PDF
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 200px 200px;
            gap: 10px;
            margin: 10px 0;
        }
        .stat-item {
            padding: 5px;
        }
        .stat-label {
            font-weight: bold;
            color: #333;
        }
        .stat-value {
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-style: italic;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Aditya Boys Hostel - Student Payment Report</h1>
        <p>Generated on: ' . date('d-m-Y H:i:s') . '</p>';

// Add filter information if applied
if ($filter != 'all' || !empty($search)) {
    $html .= '<div class="section">
        <h3>Applied Filters:</h3>';
    if ($filter != 'all') {
        $html .= '<p><strong>Status:</strong> ' . ucfirst($filter) . '</p>';
    }
    if (!empty($search)) {
        $html .= '<p><strong>Search:</strong> ' . htmlspecialchars($search) . '</p>';
    }
    $html .= '</div>';
}

$html .= '
    </div>
    
    <div class="section">
        <h2>Summary Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">Total Students:</span>
                <span class="stat-value">' . $total_students . '</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Total Amount Required:</span>
                <span class="stat-value">₹' . number_format($total_amount_required, 2) . '</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Total Amount Paid:</span>
                <span class="stat-value">₹' . number_format($total_amount_paid, 2) . '</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Total Due Amount:</span>
                <span class="stat-value">₹' . number_format($total_due_amount, 2) . '</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Collection Rate:</span>
                <span class="stat-value">' . number_format($total_amount_required > 0 ? ($total_amount_paid / $total_amount_required) * 100 : 0, 2) . '%</span>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Payment Status Breakdown</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">Fully Paid:</span>
                <span class="stat-value">' . $fully_paid . ' students</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Partially Paid:</span>
                <span class="stat-value">' . $partially_paid . ' students</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Not Paid:</span>
                <span class="stat-value">' . $not_paid . ' students</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">No Fees Generated:</span>
                <span class="stat-value">' . $no_fees . ' students</span>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Student Payment Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Total Due</th>
                    <th>Amount Paid</th>
                    <th>Due Amount</th>
                    <th>Payment Months</th>
                </tr>
            </thead>
            <tbody>';

foreach ($all_students as $student) {
    // Determine payment status
    $payment_status = 'No Fees Generated';
    $status_class = '';
    if ($student['total_fees_generated'] > 0) {
        if ($student['due_amount'] == 0) {
            $payment_status = 'FULLY PAID';
            $status_class = 'status-paid';
        } elseif ($student['total_amount_paid'] > 0) {
            $payment_status = 'PARTIALLY PAID';
            $status_class = 'status-partial';
        } else {
            $payment_status = 'NOT PAID';
            $status_class = 'status-unpaid';
        }
    }

    // Convert status to Active/Deactive format
    $display_status = 'Deactive';
    if ($student['status'] === 'approved') {
        $display_status = 'Active';
    } elseif ($student['status'] === 'inactive') {
        $display_status = 'Deactive';
    } elseif ($student['status'] === 'pending') {
        $display_status = 'Pending';
    } elseif ($student['status'] === 'rejected') {
        $display_status = 'Rejected';
    }

    $room_info = $student['room_number'] ? $student['room_number'] : 'Not Assigned';
    
    // Build payment months display
    $payment_months = [];
    if (!empty($student['paid_months'])) {
        $payment_months[] = 'Paid: ' . $student['paid_months'];
    }
    if (!empty($student['unpaid_months'])) {
        $payment_months[] = 'Unpaid: ' . $student['unpaid_months'];
    }
    if (!empty($student['partial_months'])) {
        $payment_months[] = 'Partial: ' . $student['partial_months'];
    }
    
    $payment_months_display = !empty($payment_months) ? implode(' | ', $payment_months) : 'No Fees Generated';

    $html .= '
                <tr>
                    <td class="text-center">' . $student['id'] . '</td>
                    <td>' . htmlspecialchars($student['full_name']) . '</td>
                    <td class="text-center">' . $room_info . '</td>
                    <td class="text-center">' . $display_status . '</td>
                    <td class="text-center ' . $status_class . '">' . $payment_status . '</td>
                    <td class="text-right">₹' . number_format($student['total_amount_required'], 2) . '</td>
                    <td class="text-right">₹' . number_format($student['total_amount_paid'], 2) . '</td>
                    <td class="text-right">₹' . number_format($student['due_amount'], 2) . '</td>
                    <td>' . htmlspecialchars($payment_months_display) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>End of Report - Total Students: ' . $total_students . '</p>
        <p>Generated by Aditya Boys Hostel Management System</p>
    </div>
</body>
</html>';

// Output as HTML that can be printed to PDF
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Payment Report - Aditya Boys Hostel</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            font-size: 12px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        .header h1 { 
            color: #333; 
            margin: 0; 
            font-size: 18px; 
            font-weight: bold;
        }
        .header p { 
            margin: 5px 0; 
            color: #666; 
            font-size: 11px;
        }
        .section { 
            margin-bottom: 25px; 
        }
        .section h2 { 
            color: #333; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 5px; 
            font-size: 14px; 
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section h3 {
            color: #333; 
            font-size: 12px; 
            font-weight: bold;
            margin-bottom: 8px;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: 200px 200px; 
            gap: 10px; 
            margin: 10px 0; 
        }
        .stat-item { 
            padding: 8px; 
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .stat-label { 
            font-weight: bold; 
            color: #333; 
            display: block;
            margin-bottom: 2px;
        }
        .stat-value { 
            color: #000; 
            font-size: 13px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
            font-size: 10px; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 6px 4px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
            text-align: center; 
            font-size: 9px;
        }
        .text-center { 
            text-align: center; 
        }
        .text-right { 
            text-align: right; 
        }
        .status-paid { 
            background-color: #d4edda; 
            color: #155724; 
            font-weight: bold;
        }
        .status-partial { 
            background-color: #fff3cd; 
            color: #856404; 
            font-weight: bold;
        }
        .status-unpaid { 
            background-color: #f8d7da; 
            color: #721c24; 
            font-weight: bold;
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-style: italic; 
            color: #666; 
            border-top: 1px solid #ccc; 
            padding-top: 10px; 
            font-size: 10px;
        }
        .print-btn { 
            position: fixed; 
            top: 10px; 
            right: 10px; 
            padding: 12px 20px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .print-btn:hover {
            background: #0056b3;
        }
        .instructions {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 11px;
        }
        .instructions strong {
            color: #0066cc;
        }
        @media print { 
            .print-btn { display: none; } 
            .instructions { display: none; }
            body { margin: 10px; }
            .section { page-break-inside: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print to PDF</button>
    
    <div class="instructions">
        <strong>📋 Instructions:</strong> Click the "Print to PDF" button above, then choose "Save as PDF" in your browser\'s print dialog to save this report as a PDF file.
    </div>
    
    ' . $html . '
    
    <script>
        // Auto-print instructions
        setTimeout(function() {
            console.log("Report loaded. Click Print to PDF to save as PDF file.");
        }, 1000);
        
        // Add keyboard shortcut (Ctrl+P)
        document.addEventListener("keydown", function(e) {
            if (e.ctrlKey && e.key === "p") {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>';

exit();
?>
