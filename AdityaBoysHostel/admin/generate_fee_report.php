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
$export_format = $_GET['export'] ?? 'pdf';

// Handle Excel Export
if ($export_format == 'excel') {
    include 'generate_fee_excel.php';
    exit();
}

// Get fees data with aggregated payments
$fees = [];
$sql = "SELECT 
    s.id as student_id,
    s.full_name, 
    s.email, 
    s.mobile, 
    r.room_number,
    GROUP_CONCAT(DISTINCT f.month) as months,
    GROUP_CONCAT(DISTINCT f.year) as years,
    SUM(f.amount) as total_amount,
    SUM(f.paid_amount) as total_paid,
    SUM(f.amount - f.paid_amount) as total_due,
    MIN(f.payment_date) as first_payment_date,
    MAX(f.payment_date) as last_payment_date,
    GROUP_CONCAT(
        CONCAT(
            '<div class=\"d-flex gap-2\">',
            GROUP_CONCAT(
                '<span class=\"badge ', 
                CASE 
                    WHEN SUM(f.paid_amount) = 0 THEN 'status-unpaid'
                    WHEN SUM(f.paid_amount) < SUM(f.amount) THEN 'status-partial'
                    ELSE 'status-paid'
                END, 
                '\">',
                CASE 
                    WHEN SUM(f.paid_amount) = 0 THEN 'Unpaid'
                    WHEN SUM(f.paid_amount) < SUM(f.amount) THEN 'Partial Paid'
                    ELSE 'Fully Paid'
                END,
                '</span>'
            ),
            ' - ',
            GROUP_CONCAT(DISTINCT f.year, ' ', f.month),
            ' (', SUM(f.amount), ' paid: ', SUM(f.paid_amount), ')'
        ),
        '</div>'
    ) as payment_details
FROM fees f 
JOIN students s ON f.student_id = s.id 
LEFT JOIN rooms r ON s.room_id = r.id 
WHERE 1=1
GROUP BY s.id, s.full_name, s.email, s.mobile, r.room_number, f.year, f.month";

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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Fee Report - Aditya Boys Hostel</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4 landscape;
        }
        
        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1A73E8;
            padding-bottom: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
        }
        
        .header h1 {
            color: #1A73E8;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            color: #495057;
            margin: 5px 0;
            font-size: 14px;
        }
        
        .export-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        
        .export-btn i {
            font-size: 16px;
        }
        
        .section-title {
            background-color: #1A73E8;
            color: white;
            padding: 12px;
            font-weight: bold;
            font-size: 16px;
            margin: 25px 0 15px 0;
            border-radius: 5px;
            text-align: center;
        }
        
        .table-container {
            margin-bottom: 30px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 11px;
        }
        
        th {
            background: linear-gradient(135deg, #1A73E8 0%, #1557b0 100%);
            color: white;
            font-weight: bold;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #1A73E8;
            white-space: nowrap;
            font-size: 11px;
        }
        
        td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            text-align: left;
            vertical-align: middle;
        }
        
        td.center {
            text-align: center;
        }
        
        td.right {
            text-align: right;
        }
        
        td.currency {
            text-align: right;
            font-weight: 500;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e3f2fd;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .status-partial {
            background-color: #fff3cd;
            color: #856404;
            font-weight: bold;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .summary-table th {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            font-weight: bold;
            padding: 15px 10px;
            text-align: center;
            border: 1px solid #28a745;
        }
        
        .summary-table td {
            padding: 12px 10px;
            text-align: center;
            font-weight: 500;
            border: 1px solid #dee2e6;
        }
        
        .summary-table td.currency {
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            border-top: 2px solid #1A73E8;
            color: #6c757d;
            font-size: 11px;
        }
        
        .no-print {
            display: none;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .section-title {
                page-break-before: auto;
                page-break-after: avoid;
            }
            
            .table-container {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Aditya Boys Hostel</h1>
        <p><strong>Fee Payment Report</strong></p>
    </div>
    
    <div class="section-title">STUDENT PAYMENT DETAILS</div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">S.No</th>
                    <th style="width: 18%;">Student Name</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Mobile</th>
                    <th style="width: 10%;">Room Number</th>
                    <th style="width: 20%;">Months</th>
                    <th style="width: 10%;">Years</th>
                    <th style="width: 8%;">Total Amount</th>
                    <th style="width: 8%;">Total Paid</th>
                    <th style="width: 8%;">Due Amount</th>
                    <th style="width: 15%;">Payment Details</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sno = 1;
                foreach ($fees as $fee): 
                    $due_amount = $fee['total_amount'] - $fee['total_paid'];
                    
                    // Determine status class
                    if ($fee['total_paid'] == 0) {
                        $status_class = 'status-unpaid';
                    } elseif ($fee['total_paid'] < $fee['total_amount']) {
                        $status_class = 'status-partial';
                    } else {
                        $status_class = 'status-paid';
                    }
                ?>
                    <tr>
                        <td class="center"><?php echo $sno++; ?></td>
                        <td><?php echo htmlspecialchars($fee['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($fee['email']); ?></td>
                        <td class="center"><?php echo htmlspecialchars($fee['mobile']); ?></td>
                        <td class="center"><?php echo htmlspecialchars($fee['room_number'] ?? 'Not Assigned'); ?></td>
                        <td><?php echo htmlspecialchars($fee['months']); ?></td>
                        <td><?php echo htmlspecialchars($fee['years']); ?></td>
                        <td class="currency">₹<?php echo number_format($fee['total_amount'], 2); ?></td>
                        <td class="currency">₹<?php echo number_format($fee['total_paid'], 2); ?></td>
                        <td class="currency">₹<?php echo number_format($due_amount, 2); ?></td>
                        <td>
                            <?php 
                            // Parse payment details to show each month separately
                            $payment_details = $fee['payment_details'];
                            $details = [];
                            
                            // Extract individual payment records from the aggregated data
                            preg_match_all('/\[([^\]]+)\]/', $payment_details, $matches);
                            if ($matches) {
                                foreach ($matches[1] as $index => $detail) {
                                    preg_match_all('/\(([^,]+), ([^,]+), ([^,]+), ([^,]+)\)/', $detail, $payment_matches);
                                    if ($payment_matches) {
                                        $details[] = [
                                            'month' => $matches[1][$index],
                                            'year' => $payment_matches[1][$index],
                                            'amount' => $payment_matches[1][$index],
                                            'paid' => $payment_matches[2][$index],
                                            'due' => $payment_matches[3][$index],
                                            'date' => $payment_matches[4][$index],
                                            'method' => $payment_matches[5][$index] ?? 'Not Paid',
                                            'transaction_id' => $payment_matches[6][$index] ?? 'N/A'
                                        ];
                                    }
                                }
                            }
                            }
                            
                            // Display each payment with proper formatting
                            $detail_output = [];
                            foreach ($details as $detail) {
                                $status_class = '';
                                if ($detail['due'] > 0) {
                                    $status_class = 'status-unpaid';
                                } elseif ($detail['due'] > 0) {
                                    $status_class = 'status-partial';
                                } else {
                                    $status_class = 'status-paid';
                                }
                                
                                $detail_output[] = sprintf(
                                    '<span class="badge %s">%s - ₹%0.00 (%s)</span>',
                                    ucfirst(strtolower($detail['month'])),
                                    $detail['year'],
                                    number_format($detail['amount'], 2),
                                    number_format($detail['paid'], 2),
                                    number_format($detail['due'], 2),
                                    date('d-M-Y', strtotime($detail['date']))
                                );
                            }
                            
                            echo implode('<br>', $detail_output);
                            ?>
                        </td>
                        <td><?php echo $fee['payment_details']; ?></td>
                    </tr>
                <?php 
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>
    
    <?php
    // Monthly summary calculation
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
        
        if ($fee['status'] == 'paid') $monthly_summary[$month_year]['fully_paid']++;
        elseif ($fee['status'] == 'partial') $monthly_summary[$month_year]['partial_paid']++;
        else $monthly_summary[$month_year]['unpaid']++;
    }
    ?>
    
    <div class="section-title">MONTHLY PAYMENT SUMMARY</div>
    
    <div class="table-container">
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Month</th>
                    <th style="width: 10%;">Total Students</th>
                    <th style="width: 12%;">Total Fee Amount</th>
                    <th style="width: 12%;">Total Collected</th>
                    <th style="width: 12%;">Total Due</th>
                    <th style="width: 10%;">Fully Paid</th>
                    <th style="width: 10%;">Partial Paid</th>
                    <th style="width: 10%;">Unpaid</th>
                    <th style="width: 9%;">Collection Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly_summary as $month => $data): ?>
                    <?php $collection_rate = $data['total_fee'] > 0 ? ($data['total_paid'] / $data['total_fee']) * 100 : 0; ?>
                    <tr>
                        <td><strong><?php echo $month; ?></strong></td>
                        <td class="center"><?php echo $data['students']; ?></td>
                        <td class="currency">₹<?php echo number_format($data['total_fee'], 2); ?></td>
                        <td class="currency">₹<?php echo number_format($data['total_paid'], 2); ?></td>
                        <td class="currency">₹<?php echo number_format($data['total_due'], 2); ?></td>
                        <td class="center"><?php echo $data['fully_paid']; ?></td>
                        <td class="center"><?php echo $data['partial_paid']; ?></td>
                        <td class="center"><?php echo $data['unpaid']; ?></td>
                        <td class="currency"><strong><?php echo number_format($collection_rate, 1); ?>%</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section-title">OVERALL SUMMARY</div>
    
    <div class="table-container">
        <table class="summary-table">
            <tbody>
                <tr>
                    <td style="width: 25%; background-color: #e3f2fd;"><strong>Total Records</strong></td>
                    <td style="width: 25%; background-color: #e3f2fd;" class="center"><strong><?php echo count($fees); ?></strong></td>
                    <td style="width: 25%; background-color: #e8f5e8;"><strong>Total Fee Amount</strong></td>
                    <td style="width: 25%; background-color: #e8f5e8;" class="currency"><strong>₹<?php echo number_format($total_amount, 2); ?></strong></td>
                </tr>
                <tr>
                    <td style="background-color: #fff3e0;"><strong>Total Collected</strong></td>
                    <td style="background-color: #fff3e0;" class="currency"><strong>₹<?php echo number_format($total_paid, 2); ?></strong></td>
                    <td style="background-color: #fce4ec;"><strong>Total Pending</strong></td>
                    <td style="background-color: #fce4ec;" class="currency"><strong>₹<?php echo number_format($total_pending, 2); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="background-color: #f3e5f5;"><strong>Overall Collection Rate</strong></td>
                    <td colspan="2" style="background-color: #f3e5f5;" class="currency"><strong><?php echo ($total_amount > 0 ? number_format(($total_paid / $total_amount) * 100, 1) : '0'); ?>%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p><strong>© <?php echo date('Y'); ?> Aditya Boys Hostel. All rights reserved.</strong></p>
        <p>This is a computer-generated financial report and does not require a signature.</p>
        <p>Report Generated: <?php echo date('d M Y H:i:s'); ?></p>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="padding: 12px 30px; font-size: 14px; margin: 5px;">
            <i class="fas fa-print"></i> Print Report
        </button>
        <button onclick="window.close()" class="btn btn-secondary" style="padding: 12px 30px; font-size: 14px; margin: 5px;">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>
</html>
