<?php
session_start();
require_once '../config/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Function to get previous month name
function getPreviousMonth() {
    $date = new DateTime('first day of this month');
    $date->modify('-1 month');
    return $date->format('F');
}

try {
    // Add a small delay to handle database replication
    usleep(500000); // 0.5 second delay
    
    // Get current month and year
    $current_month = date('F');
    $current_year = date('Y');
    $previous_month = getPreviousMonth();
    
    // Get next month name
    function getNextMonth() {
        $date = new DateTime('first day of this month');
        $date->modify('+1 month');
        return $date->format('F');
    }
    $next_month = getNextMonth();
    
    // Get students with unpaid fees for current month, previous month, and next month only
    $stmt = $conn->prepare("
        SELECT DISTINCT s.id, s.full_name, s.email, s.mobile, f.amount, f.paid_amount, f.id as fee_id, f.month, f.year
        FROM students s 
        INNER JOIN fees f ON s.id = f.student_id 
        WHERE s.status = 'approved' 
        AND (f.status = 'unpaid' OR f.status = 'partial' OR f.status = '' OR f.status IS NULL OR (f.status = 'partial' AND f.paid_amount < f.amount))
        AND (f.month = ? OR f.month = ? OR f.month = ?)
        AND (f.year = ? OR f.year = ? OR f.year = ?)
        ORDER BY f.year DESC, f.month DESC, f.created_at DESC
    ");
    $stmt->bind_param("sssiii", $current_month, $previous_month, $next_month, $current_year, $current_year, $current_year);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Debug: Log the count of unpaid fees found
    error_log("Send All Bills: Found " . count($students) . " students with unpaid fees");
    
    // Debug: Log the actual SQL query and results
    if (count($students) == 0) {
        // Check what fees actually exist
        $debug_stmt = $conn->prepare("SELECT id, student_id, month, year, status FROM fees ORDER BY created_at DESC LIMIT 5");
        $debug_stmt->execute();
        $all_fees = $debug_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        error_log("DEBUG: Last 5 fees in database: " . json_encode($all_fees));
        
        echo json_encode([
            'success' => false, 
            'message' => 'No students with unpaid fees found. Please generate fees first or check if all fees are already paid.',
            'debug' => [
                'query_students_found' => count($students),
                'total_fees_in_db' => count($all_fees),
                'recent_fees' => $all_fees,
                'current_month' => $current_month,
                'previous_month' => $previous_month,
                'next_month' => $next_month,
                'current_year' => $current_year,
                'sql_query' => 'SELECT DISTINCT s.id, s.full_name, s.email, s.mobile, f.amount, f.paid_amount, f.id as fee_id, f.month, f.year FROM students s INNER JOIN fees f ON s.id = f.student_id WHERE s.status = "approved" AND (f.status = "unpaid" OR f.status = "partial" OR f.status = "" OR f.status IS NULL OR (f.status = "partial" AND f.paid_amount < f.amount)) ORDER BY f.year DESC, f.month DESC, s.full_name'
            ]
        ]);
        exit();
    }
    
    $sent_count = 0;
    $failed_count = 0;
    $total_count = count($students);
    
    // Load PHPMailer
    require_once '../PHPMailer/src/PHPMailer.php';
    require_once '../PHPMailer/src/SMTP.php';
    require_once '../PHPMailer/src/Exception.php';
    
    // Process each student
    foreach ($students as $student) {
        try {
            // Use the fee data from our query result
            $fee_amount = $student['amount'];
            $paid_amount = $student['paid_amount'];
            $fee_id = $student['fee_id'];
            $fee_month = $student['month'];
            $fee_year = $student['year'];
            
            // Create PHPMailer instance
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'aaravraj799246@gmail.com';
            $mail->Password = 'magh vsox feox jjbf';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('aaravraj799246@gmail.com', 'Aditya Boys Hostel');
            $mail->addAddress($student['email'], $student['full_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Monthly Fee Bill - {$fee_month} {$fee_year} - Aditya Boys Hostel";
            
            // Generate professional bill email content
            $due_amount = $fee_amount - $paid_amount;
            $status_color = $due_amount > 0 ? '#dc3545' : '#28a745';
            $status_text = $due_amount > 0 ? 'UNPAID' : 'PAID';
            $status_bg = $due_amount > 0 ? '#f8d7da' : '#d4edda';
            
            // Calculate due date (15th of next month from fee month)
            $due_date = date('Y-m-15', strtotime("{$fee_year} {$fee_month} +1 month"));
            $due_date_formatted = date('d M Y', strtotime($due_date));
            
            // Calculate late fee (if unpaid and past due date)
            $late_fee = 0;
            if ($due_amount > 0 && date('Y-m-d') > $due_date) {
                $late_fee = 100;
            }
            $total_payable = $due_amount + $late_fee;
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Fee Bill - {$current_month} {$current_year}</title>
                <style>
                    body { 
                        font-family: 'Arial', sans-serif; 
                        margin: 0; 
                        padding: 20px; 
                        background: #f8f9fa; 
                        color: #333;
                    }
                    .bill-container { 
                        max-width: 750px; 
                        margin: 0 auto; 
                        background: white; 
                        border-radius: 12px; 
                        box-shadow: 0 6px 25px rgba(0,0,0,0.12); 
                        overflow: hidden;
                        border: 1px solid #e9ecef;
                    }
                    .bill-header { 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                        color: white; 
                        padding: 30px 25px; 
                        text-align: center; 
                        position: relative;
                    }
                    .bill-header h1 { 
                        margin: 0; 
                        font-size: 28px; 
                        font-weight: 700; 
                        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                    }
                    .bill-header h2 { 
                        margin: 8px 0 0 0; 
                        font-size: 16px; 
                        opacity: 0.9; 
                        font-weight: 400;
                    }
                    .bill-header .bill-number {
                        position: absolute;
                        top: 15px;
                        right: 20px;
                        background: rgba(255,255,255,0.2);
                        padding: 8px 15px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: 600;
                    }
                    .bill-meta {
                        background: #f8f9fa;
                        padding: 15px 25px;
                        border-bottom: 1px solid #e9ecef;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                    }
                    .meta-item {
                        text-align: center;
                        flex: 1;
                        min-width: 120px;
                    }
                    .meta-label {
                        font-size: 11px;
                        color: #6c757d;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        font-weight: 600;
                    }
                    .meta-value {
                        font-size: 14px;
                        font-weight: 700;
                        color: #495057;
                        margin-top: 3px;
                    }
                    .bill-body { 
                        padding: 25px; 
                    }
                    .section-title {
                        font-size: 16px;
                        font-weight: 700;
                        color: #495057;
                        margin-bottom: 15px;
                        padding-bottom: 8px;
                        border-bottom: 2px solid #667eea;
                        display: flex;
                        align-items: center;
                    }
                    .section-title span {
                        margin-right: 8px;
                        font-size: 18px;
                    }
                    .student-info { 
                        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
                        padding: 20px; 
                        border-radius: 10px; 
                        margin-bottom: 25px; 
                        border-left: 4px solid #667eea;
                        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
                    }
                    .student-details {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 20px;
                    }
                    .student-detail-item {
                        display: flex;
                        align-items: center;
                        background: white;
                        padding: 15px;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        transition: transform 0.3s ease;
                    }
                    .student-detail-item:hover {
                        transform: translateY(-2px);
                    }
                    .detail-icon {
                        width: 40px;
                        height: 40px;
                        background: #667eea;
                        color: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 15px;
                        font-size: 18px;
                        flex-shrink: 0;
                    }
                    .detail-content {
                        flex: 1;
                    }
                    .detail-label { 
                        font-size: 12px;
                        color: #6c757d;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        font-weight: 600;
                        margin-bottom: 3px;
                    }
                    .detail-value { 
                        font-size: 15px;
                        font-weight: 600;
                        color: #212529;
                    }
                    .fee-table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin: 25px 0; 
                        background: white;
                        border-radius: 10px;
                        overflow: hidden;
                        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
                    }
                    .fee-table th { 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                        color: white; 
                        padding: 12px 15px; 
                        text-align: left; 
                        font-weight: 600;
                        font-size: 12px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .fee-table td { 
                        padding: 12px 15px; 
                        border-bottom: 1px solid #e9ecef; 
                        font-size: 14px;
                        font-weight: 500;
                    }
                    .fee-table tr:nth-child(even) { 
                        background: #f8f9fa; 
                    }
                    .fee-table tr:hover { 
                        background: #e3f2fd; 
                        transition: background 0.3s ease;
                    }
                    .amount {
                        text-align: right;
                        font-weight: 600;
                        font-family: 'Courier New', monospace;
                    }
                    .total-row { 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; 
                        color: white; 
                        font-weight: 700; 
                        font-size: 16px;
                    }
                    .total-row td { 
                        padding: 15px; 
                        border-bottom: none;
                    }
                    .status-section {
                        text-align: center;
                        margin: 30px 0;
                    }
                    .status-badge { 
                        display: inline-block; 
                        padding: 10px 25px; 
                        border-radius: 25px; 
                        font-weight: 700; 
                        font-size: 14px;
                        text-transform: uppercase;
                        letter-spacing: 0.8px;
                        border: 2px solid {$status_color};
                        color: {$status_color};
                        background: {$status_bg};
                        box-shadow: 0 3px 12px rgba(0,0,0,0.15);
                    }
                    .payment-methods { 
                        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); 
                        padding: 25px; 
                        border-radius: 10px; 
                        margin: 25px 0; 
                        border-left: 4px solid #2196f3;
                        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
                    }
                    .payment-methods h4 { 
                        color: #1976d2; 
                        margin: 0 0 15px 0; 
                        font-size: 16px;
                        display: flex;
                        align-items: center;
                    }
                    .payment-methods h4 span {
                        margin-right: 8px;
                        font-size: 18px;
                    }
                    .payment-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                        gap: 12px;
                        margin-top: 15px;
                    }
                    .payment-item {
                        background: white;
                        padding: 15px;
                        border-radius: 8px;
                        text-align: center;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        transition: transform 0.3s ease;
                    }
                    .payment-item:hover {
                        transform: translateY(-2px);
                    }
                    .payment-icon {
                        font-size: 24px;
                        color: #1976d2;
                        margin-bottom: 8px;
                    }
                    .payment-name {
                        font-weight: 600;
                        color: #1976d2;
                        margin-bottom: 4px;
                        font-size: 13px;
                    }
                    .payment-detail {
                        font-size: 13px;
                        color: #495057;
                        font-weight: 500;
                    }
                    .contact-info { 
                        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); 
                        padding: 25px; 
                        border-radius: 10px; 
                        margin: 25px 0; 
                        border-left: 4px solid #ffc107;
                        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
                    }
                    .contact-info h4 { 
                        color: #856404; 
                        margin: 0 0 15px 0; 
                        font-size: 16px;
                        display: flex;
                        align-items: center;
                    }
                    .contact-info h4 span {
                        margin-right: 8px;
                        font-size: 18px;
                    }
                    .contact-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 15px;
                    }
                    .contact-item {
                        background: white;
                        padding: 15px;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        text-align: center;
                    }
                    .contact-item strong {
                        color: #856404;
                        display: block;
                        margin-bottom: 8px;
                        font-size: 13px;
                    }
                    .contact-item div {
                        font-size: 14px;
                        color: #495057;
                        margin-bottom: 5px;
                    }
                    .action-btn {
                        padding: 8px 15px;
                        border: none;
                        border-radius: 20px;
                        font-size: 11px;
                        font-weight: 600;
                        cursor: pointer;
                        text-decoration: none;
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        transition: all 0.3s ease;
                        font-family: 'Arial', sans-serif;
                        margin: 5px;
                    }
                    .call-btn {
                        background: #28a745;
                        color: white;
                    }
                    .call-btn:hover {
                        background: #218838;
                        transform: translateY(-1px);
                    }
                    .email-btn {
                        background: #007bff;
                        color: white;
                    }
                    .email-btn:hover {
                        background: #0056b3;
                        transform: translateY(-1px);
                    }
                    .bill-footer { 
                        margin-top: 30px; 
                        padding: 20px 25px; 
                        background: #f8f9fa; 
                        text-align: center; 
                        border-top: 2px solid #e9ecef;
                    }
                    .bill-footer h4 { 
                        color: #495057; 
                        margin: 0 0 10px 0; 
                        font-size: 14px;
                    }
                    .bill-footer p { 
                        margin: 5px 0; 
                        color: #6c757d; 
                        font-size: 12px;
                    }
                    .stamp {
                        display: inline-block;
                        padding: 12px 25px;
                        border: 2px solid {$status_color};
                        color: {$status_color};
                        font-weight: 700;
                        font-size: 16px;
                        border-radius: 8px;
                        transform: rotate(-5deg);
                        margin: 15px 0;
                        text-transform: uppercase;
                        letter-spacing: 0.8px;
                        box-shadow: 0 3px 12px rgba(0,0,0,0.15);
                    }
                    .late-fee {
                        color: #dc3545;
                        font-weight: 700;
                    }
                    @media print {
                        body { background: white; }
                        .bill-container { 
                            box-shadow: none; 
                            max-width: 100%;
                            margin: 0;
                            border-radius: 0;
                        }
                        .action-btn { display: none; }
                        .bill-header { padding: 20px; }
                        .bill-header h1 { font-size: 24px; }
                        .bill-body { padding: 15px; }
                        .section-title { font-size: 14px; }
                        .student-details { grid-template-columns: 1fr; }
                        .payment-grid { grid-template-columns: repeat(2, 1fr); }
                        .contact-grid { grid-template-columns: repeat(2, 1fr); }
                    }
                </style>
            </head>
            <body>
                <div class='bill-container'>
                    <div class='bill-header'>
                        <div class='bill-number'>Bill #{$fee_id}</div>
                        <h1>🏠 ADITYA BOYS HOSTEL</h1>
                        <h2>Monthly Fee Bill</h2>
                    </div>
                    
                    <div class='bill-meta'>
                        <div class='meta-item'>
                            <div class='meta-label'>Billing Month</div>
                            <div class='meta-value'>{$fee_month} {$fee_year}</div>
                        </div>
                        <div class='meta-item'>
                            <div class='meta-label'>Bill Generation Date</div>
                            <div class='meta-value'>" . date('d M Y') . "</div>
                        </div>
                        <div class='meta-item'>
                            <div class='meta-label'>Payment Due Date</div>
                            <div class='meta-value'>{$due_date_formatted}</div>
                        </div>
                        <div class='meta-item'>
                            <div class='meta-label'>Payment Status</div>
                            <div class='meta-value' style='color: {$status_color}; font-weight: 700;'>{$status_text}</div>
                        </div>
                    </div>
                    
                    <div class='bill-body'>
                        <div class='section-title'>
                            <span>👤</span> Student Details
                        </div>
                        <div class='student-info'>
                            <div class='student-details'>
                                <div class='student-detail-item'>
                                    <div class='detail-icon'>👨‍🎓</div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Student Name</div>
                                        <div class='detail-value'>{$student['full_name']}</div>
                                    </div>
                                </div>
                                <div class='student-detail-item'>
                                    <div class='detail-icon'>📧</div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Email Address</div>
                                        <div class='detail-value'>{$student['email']}</div>
                                    </div>
                                </div>
                                <div class='student-detail-item'>
                                    <div class='detail-icon'>📱</div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Mobile Number</div>
                                        <div class='detail-value'>{$student['mobile']}</div>
                                    </div>
                                </div>
                                <div class='student-detail-item'>
                                    <div class='detail-icon'>🏠</div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Room Number</div>
                                        <div class='detail-value'>Assigned Room</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class='section-title'>
                            <span>📊</span> Bill Breakdown
                        </div>
                        <table class='fee-table'>
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th style='text-align: center;'>Period</th>
                                    <th style='text-align: right;'>Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Monthly Hostel Fee</td>
                                    <td style='text-align: center;'>{$fee_month} {$fee_year}</td>
                                    <td class='amount'>" . number_format($fee_amount, 2) . "</td>
                                </tr>
                                <tr>
                                    <td>Amount Paid</td>
                                    <td style='text-align: center;'>-</td>
                                    <td class='amount' style='color: #28a745;'>-" . number_format($paid_amount, 2) . "</td>
                                </tr>";
            
            if ($late_fee > 0) {
                $mail->Body .= "
                                <tr>
                                    <td class='late-fee'>Late Fee (After Due Date)</td>
                                    <td style='text-align: center;'>Penalty</td>
                                    <td class='amount late-fee'>" . number_format($late_fee, 2) . "</td>
                                </tr>";
            }
            
            $mail->Body .= "
                                <tr class='total-row'>
                                    <td>Total Amount Payable</td>
                                    <td style='text-align: center;'>" . ($due_amount > 0 ? 'Due' : 'Paid') . "</td>
                                    <td class='amount'>" . number_format($total_payable, 2) . "</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class='status-section'>
                            <div class='status-badge'>Payment Status: {$status_text}</div>";
            
            if ($due_amount > 0) {
                $mail->Body .= "
                            <p style='color: #dc3545; font-weight: 600; margin-top: 15px;'>
                                ⚠️ Payment Due: {$due_date_formatted}<br>
                                Late Fee of ₹100 will be charged after due date
                            </p>";
            }
            
            $mail->Body .= "
                        </div>
                        
                        <div class='section-title'>
                            <span>💳</span> Payment Methods
                        </div>
                        <div class='payment-methods'>
                            <h4><span>💳</span> Complete Your Payment</h4>
                            <div class='payment-grid'>
                                <div class='payment-item'>
                                    <div class='payment-icon'>📱</div>
                                    <div class='payment-name'>PhonePe</div>
                                    <div class='payment-detail'>7992465964</div>
                                </div>
                                <div class='payment-item'>
                                    <div class='payment-icon'>💸</div>
                                    <div class='payment-name'>Google Pay</div>
                                    <div class='payment-detail'>7992465964</div>
                                </div>
                                <div class='payment-item'>
                                    <div class='payment-icon'>📲</div>
                                    <div class='payment-name'>UPI</div>
                                    <div class='payment-detail'>aaravraj799246@okaxis</div>
                                </div>
                                <div class='payment-item'>
                                    <div class='payment-icon'>🏦</div>
                                    <div class='payment-name'>Bank Transfer</div>
                                    <div class='payment-detail'>Available on Request</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class='section-title'>
                            <span>📞</span> Contact Information
                        </div>
                        <div class='contact-info'>
                            <h4><span>📞</span> Get in Touch</h4>
                            <div class='contact-grid'>
                                <div class='contact-item'>
                                    <strong>Office Phone</strong>
                                    7992465964
                                    <div>
                                        <a href='tel:7992465964' class='action-btn call-btn'>
                                            📞 Call
                                        </a>
                                    </div>
                                </div>
                                <div class='contact-item'>
                                    <strong>Office Email</strong>
                                    aaravraj799246@gmail.com
                                    <div>
                                        <a href='mailto:aaravraj799246@gmail.com' class='action-btn email-btn'>
                                            📧 Email
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style='text-align: center; margin: 40px 0;'>
                            <div class='stamp'>{$status_text}</div>
                        </div>
                    </div>
                    
                    <div class='bill-footer'>
                        <h4>📧 Important Information</h4>
                        <p><strong>Aditya Boys Hostel</strong> | 📞 7992465964 | 📧 aaravraj799246@gmail.com</p>
                        <p>📍 Aditya Boys Hostel, [Your Complete Address]</p>
                        <p style='margin-top: 15px; font-size: 12px; color: #adb5bd;'>
                            This is a computer-generated bill. No signature required. Please keep this bill for your records.
                        </p>
                        <p style='margin-top: 10px; font-size: 11px; color: #adb5bd;'>
                            Generated on: " . date('d M Y H:i:s') . " | Bill ID: #{$fee_id}
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            // Send email
            $mail->send();
            $sent_count++;
            
            // Small delay to prevent overwhelming the email server
            usleep(1000000); // 1 second delay
            
        } catch (Exception $e) {
            $failed_count++;
            error_log("Email sending failed for student {$student['full_name']} (ID: {$student['id']}): " . $e->getMessage());
        }
    }
    
    // Return comprehensive results
    echo json_encode([
        'success' => true,
        'message' => "Successfully sent fee bills to {$sent_count} students with unpaid fees for {$previous_month}, {$current_month}, and {$next_month} {$current_year}",
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'total_count' => $total_count,
        'note' => 'Only students with unpaid or partially paid fees for previous month, current month, and next month received bills',
        'processed_students' => array_map(function($student) {
            return [
                'id' => $student['id'],
                'name' => $student['full_name'],
                'email' => $student['email'],
                'month' => $student['month'],
                'year' => $student['year'],
                'amount' => $student['amount']
            ];
        }, $students),
        'debug_info' => [
            'phpmailer_loaded' => class_exists('PHPMailer\\PHPMailer\\PHPMailer'),
            'smtp_config' => 'smtp.gmail.com:587 with TLS',
            'total_students_found' => count($students),
            'current_month' => $current_month,
            'previous_month' => $previous_month,
            'next_month' => $next_month,
            'current_year' => $current_year
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
