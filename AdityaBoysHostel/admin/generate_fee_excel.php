<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized access');
}

// Get filters
$month_filter = $_GET['month'] ?? 'all';
$year_filter = $_GET['year'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';

// Get fees data
$fees = [];
$sql = "SELECT f.*, s.full_name, s.email, s.mobile FROM fees f JOIN students s ON f.student_id = s.id WHERE 1=1";
$params = [];
$types = '';

if ($month_filter != 'all') {
    $sql .= " AND f.month = ?";
    $params[] = $month_filter;
    $types .= 's';
}

if ($year_filter != 'all') {
    $sql .= " AND f.year = ?";
    $params[] = $year_filter;
    $types .= 'i';
}

if ($status_filter != 'all') {
    $sql .= " AND f.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$sql .= " ORDER BY f.year DESC, f.month DESC, s.full_name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fees[] = $row;
}

// Calculate totals
$total_amount = 0;
$total_paid = 0;
$total_pending = 0;

foreach ($fees as $fee) {
    $total_amount += $fee['amount'];
    if ($fee['status'] == 'paid') {
        $total_paid += $fee['paid_amount'];
    } else {
        $total_pending += $fee['amount'];
    }
}

// Create Excel file using HTML table format (Excel can open HTML tables)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="fee_report_' . date('Y_m_d') . '.xls"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Fee Report</x:Name>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table {
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th {
            background-color: #1A73E8;
            color: white;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            padding: 8px;
        }
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            vertical-align: middle;
        }
        .currency {
            text-align: right;
            mso-number-format: "\\₹\\#,##0.00";
        }
        .center {
            text-align: center;
        }
        .header-row {
            background-color: #1A73E8;
            color: white;
            font-weight: bold;
        }
        .summary-header {
            background-color: #28A745;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table>
        <!-- Header Row -->
        <tr class="header-row">
            <th>S.No</th>
            <th>Student Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Month</th>
            <th>Year</th>
            <th>Monthly Fee</th>
            <th>Total Paid</th>
            <th>Due Amount</th>
            <th>Payment Status</th>
            <th>Payment Method</th>
            <th>Transaction ID</th>
            <th>Payment Date</th>
            <th>Last Payment Amount</th>
        </tr>';

// Data rows
$sno = 1;
foreach ($fees as $fee) {
    $due_amount = $fee['amount'] - $fee['paid_amount'];
    
    // Correct payment status logic
    if ($fee['paid_amount'] == 0) {
        $status_text = 'Unpaid';
    } elseif ($fee['paid_amount'] > 0 && $fee['paid_amount'] < $fee['amount']) {
        $status_text = 'Partial Paid';
    } else {
        $status_text = 'Fully Paid';
    }
    
    echo '<tr>';
    echo '<td class="center">' . $sno++ . '</td>';
    echo '<td>' . htmlspecialchars($fee['full_name']) . '</td>';
    echo '<td>' . htmlspecialchars($fee['email']) . '</td>';
    echo '<td class="center">' . htmlspecialchars($fee['mobile']) . '</td>';
    echo '<td class="center">' . htmlspecialchars($fee['month']) . '</td>';
    echo '<td class="center">' . $fee['year'] . '</td>';
    echo '<td class="currency">₹' . number_format($fee['amount'], 2) . '</td>';
    echo '<td class="currency">₹' . number_format($fee['paid_amount'], 2) . '</td>';
    echo '<td class="currency">₹' . number_format($due_amount, 2) . '</td>';
    echo '<td class="center">' . $status_text . '</td>';
    echo '<td class="center">' . ($fee['payment_method'] ?? 'Not Paid') . '</td>';
    echo '<td class="center">' . ($fee['transaction_id'] ?? 'N/A') . '</td>';
    echo '<td class="center">' . ($fee['payment_date'] ? date('d-M-Y', strtotime($fee['payment_date'])) : 'Not Paid') . '</td>';
    echo '<td class="currency">₹' . number_format($fee['paid_amount'], 2) . '</td>';
    echo '</tr>';
}

// Monthly summary
$monthly_summary = [];
foreach ($fees as $fee) {
    $month_year = $fee['month'] . ' ' . $fee['year'];
    if (!isset($monthly_summary[$month_year])) {
        $monthly_summary[$month_year] = [
            'students' => 0,
            'total_fee' => 0,
            'total_paid' => 0,
            'total_due' => 0,
            'fully_paid' => 0,
            'partial_paid' => 0,
            'unpaid' => 0
        ];
    }
    $monthly_summary[$month_year]['students']++;
    $monthly_summary[$month_year]['total_fee'] += $fee['amount'];
    $monthly_summary[$month_year]['total_paid'] += $fee['paid_amount'];
    $monthly_summary[$month_year]['total_due'] += ($fee['amount'] - $fee['paid_amount']);
    
    if ($fee['paid_amount'] == 0) {
        $monthly_summary[$month_year]['unpaid']++;
    } elseif ($fee['paid_amount'] > 0 && $fee['paid_amount'] < $fee['amount']) {
        $monthly_summary[$month_year]['partial_paid']++;
    } else {
        $monthly_summary[$month_year]['fully_paid']++;
    }
}

// Add monthly summary section
echo '<tr><td colspan="14" style="background-color: #f0f0f0; height: 20px;"></td></tr>';
echo '<tr><td colspan="14" class="summary-header"><strong>MONTHLY PAYMENT SUMMARY</strong></td></tr>';
echo '<tr class="summary-header">';
echo '<th>Month</th>';
echo '<th>Total Students</th>';
echo '<th>Total Fee</th>';
echo '<th>Total Collected</th>';
echo '<th>Total Due</th>';
echo '<th>Fully Paid</th>';
echo '<th>Partial Paid</th>';
echo '<th>Unpaid</th>';
echo '<th>Collection Rate (%)</th>';
echo '</tr>';

foreach ($monthly_summary as $month => $data) {
    $collection_rate = $data['total_fee'] > 0 ? ($data['total_paid'] / $data['total_fee']) * 100 : 0;
    echo '<tr>';
    echo '<td><strong>' . $month . '</strong></td>';
    echo '<td class="center">' . $data['students'] . '</td>';
    echo '<td class="currency">₹' . number_format($data['total_fee'], 2) . '</td>';
    echo '<td class="currency">₹' . number_format($data['total_paid'], 2) . '</td>';
    echo '<td class="currency">₹' . number_format($data['total_due'], 2) . '</td>';
    echo '<td class="center">' . $data['fully_paid'] . '</td>';
    echo '<td class="center">' . $data['partial_paid'] . '</td>';
    echo '<td class="center">' . $data['unpaid'] . '</td>';
    echo '<td class="currency"><strong>' . number_format($collection_rate, 1) . '%</strong></td>';
    echo '</tr>';
}

// Overall summary
echo '<tr><td colspan="14" style="background-color: #f0f0f0; height: 20px;"></td></tr>';
echo '<tr><td colspan="14" class="summary-header"><strong>OVERALL SUMMARY</strong></td></tr>';
echo '<tr>';
echo '<td colspan="2" class="summary-header"><strong>Total Students</strong></td>';
echo '<td colspan="2" class="center"><strong>' . count($fees) . '</strong></td>';
echo '<td colspan="2" class="summary-header"><strong>Total Fee</strong></td>';
echo '<td colspan="2" class="currency"><strong>₹' . number_format($total_amount, 2) . '</strong></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2" class="summary-header"><strong>Fully Paid Count</strong></td>';
echo '<td colspan="2" class="center"><strong>' . $fully_paid_count . '</strong></td>';
echo '<td colspan="2" class="summary-header"><strong>Partial Paid Count</strong></td>';
echo '<td colspan="2" class="center"><strong>' . $partial_paid_count . '</strong></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2" class="summary-header"><strong>Unpaid Count</strong></td>';
echo '<td colspan="2" class="center"><strong>' . $unpaid_count . '</strong></td>';
echo '<td colspan="2" class="summary-header"><strong>Total Collected</strong></td>';
echo '<td colspan="2" class="currency"><strong>₹' . number_format($total_paid, 2) . '</strong></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2" class="summary-header"><strong>Total Pending</strong></td>';
echo '<td colspan="2" class="currency"><strong>₹' . number_format($total_pending, 2) . '</strong></td>';
echo '<td colspan="2" class="summary-header"><strong>Collection Rate (%)</strong></td>';
echo '<td colspan="2" class="currency"><strong>' . ($total_amount > 0 ? number_format(($total_paid / $total_amount) * 100, 1) . '%' : '0%') . '</strong></td>';
echo '</tr>';

echo '</table>
</body>
</html>';

// Calculate counts for summary
$fully_paid_count = 0;
$partial_paid_count = 0;
$unpaid_count = 0;

foreach ($fees as $fee) {
    if ($fee['paid_amount'] == 0) {
        $unpaid_count++;
    } elseif ($fee['paid_amount'] > 0 && $fee['paid_amount'] < $fee['amount']) {
        $partial_paid_count++;
    } else {
        $fully_paid_count++;
    }
}
?>
