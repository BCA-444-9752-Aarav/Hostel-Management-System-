<?php
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
    $date = new DateTime('first day of previous month');
    $date->modify('-1 month');
    return $date->format('F');
}

try {
    // Add a small delay to handle database replication
    usleep(500000); // 0.5 second delay
    
    // Get current month and year
    $current_month = date('F');
    $current_year = date('Y');
    
    // Get students with unpaid fees for current month and previous month only
    $stmt = $conn->prepare("
        SELECT DISTINCT s.id, s.full_name, s.email, s.mobile, f.amount, f.paid_amount, f.id as fee_id, f.month, f.year
        FROM students s 
        INNER JOIN fees f ON s.id = f.student_id 
        WHERE s.status = 'approved' 
        AND (f.status = 'unpaid' OR (f.status = 'partial' AND f.paid_amount < f.amount))
        AND (f.month = ? OR f.month = ?)
        AND (f.year = ? OR f.year = ?)
        ORDER BY f.year DESC, f.month DESC, f.created_at DESC
    ");
    $stmt->bind_param("ssii", $current_month, getPreviousMonth(), $current_year, getPreviousMonth(), $current_year);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Debug: Log the count of unpaid fees found
    error_log("Email All Bills: Found " . count($students) . " students with unpaid fees");
    
    // Debug: Log the actual SQL query and results
    if (count($students) == 0) {
        // Check what fees actually exist
        $debug_stmt = $conn->prepare("SELECT id, student_id, month, year, status FROM fees ORDER BY created_at DESC LIMIT 5");
        $debug_stmt->execute();
        $all_fees = $debug_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        error_log("DEBUG: Last 5 fees in database: " . json_encode($all_fees));
        
        echo json_encode([
            'success' => false, 
            'message' => 'No students with unpaid fees found. Please generate fees first.',
            'debug' => [
                'query_students_found' => count($students),
                'total_fees_in_db' => count($all_fees),
                'recent_fees' => $all_fees,
                'sql_query' => 'SELECT DISTINCT s.id, s.full_name, s.email, s.mobile, f.amount, f.paid_amount, f.id as fee_id, f.month, f.year FROM students s INNER JOIN fees f ON s.id = f.student_id WHERE s.status = "approved" AND (f.status = "unpaid" OR (f.status = "partial" AND f.paid_amount < f.amount)) ORDER BY f.year DESC, f.month DESC, s.full_name'
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
            
            // Generate email content
            $due_amount = $fee_amount - $paid_amount;
            $status_class = $due_amount > 0 ? 'danger' : 'success';
            $status_text = $due_amount > 0 ? 'Pending' : 'Paid';
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Monthly Fee Bill - {$current_month} {$current_year}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
                    .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                    .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                    .header h1 { color: #007bff; margin: 0; }
                    .header p { color: #666; margin: 5px 0 0 0; }
                    .student-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                    .fee-details { margin-bottom: 20px; }
                    .fee-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    .fee-table th { background: #007bff; color: white; padding: 12px; text-align: left; }
                    .fee-table td { padding: 12px; border-bottom: 1px solid #ddd; }
                    .fee-table tr:nth-child(even) { background: #f8f9fa; }
                    .total { font-weight: bold; font-size: 18px; }
                    .status { padding: 10px; border-radius: 5px; color: white; text-align: center; font-weight: bold; }
                    .status.success { background: #28a745; }
                    .status.danger { background: #dc3545; }
                    .payment-methods { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🏠 Aditya Boys Hostel</h1>
                        <p>Monthly Fee Bill</p>
                    </div>
                    
                    <div class='student-info'>
                        <h3>Student Information</h3>
                        <p><strong>Name:</strong> {$student['full_name']}</p>
                        <p><strong>Email:</strong> {$student['email']}</p>
                        <p><strong>Mobile:</strong> {$student['mobile']}</p>
                    </div>
                    
                    <div class='fee-details'>
                        <h2 style='color: #333; margin-bottom: 20px;'>Monthly Fee Bill</h2>
                        <h3 style='color: #666; margin-bottom: 15px;'>Fee Details for {$fee_month} {$fee_year}</h3>
                        <table class='fee-table'>
                            <tr>
                                <th>Description</th>
                                <th>Amount (₹)</th>
                            </tr>
                            <tr>
                                <td>Monthly Fee</td>
                                <td>" . number_format($fee_amount, 2) . "</td>
                            </tr>
                            <tr>
                                <td>Paid Amount</td>
                                <td>-" . number_format($paid_amount, 2) . "</td>
                            </tr>
                            <tr class='total'>
                                <td>Due Amount</td>
                                <td>₹" . number_format($due_amount, 2) . "</td>
                            </tr>
                        </table>
                        
                        <div class='status {$status_class}'>
                            Payment Status: {$status_text}
                        </div>
                    </div>
                    
                    <div class='payment-methods'>
                        <h4>💳 Payment Methods</h4>
                        <p>Please use any of the following payment methods:</p>
                        <ul>
                            <li><strong>UPI:</strong> aaravraj799246@okaxis</li>
                            <li><strong>Phone:</strong> 7992465964</li>
                            <li><strong>Bank Transfer:</strong> Available on request</li>
                        </ul>
                    </div>
                    
                    <div class='footer'>
                        <p><strong>Payment Instructions:</strong></p>
                        <p>Please pay the due amount at the earliest. You can pay online through the student portal or at the hostel office.</p>
                        <p>For any queries, contact the hostel administration.</p>
                        <p>&copy; " . date('Y') . " Aditya Boys Hostel. All rights reserved.</p>
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
        'message' => "Successfully sent fee bills to {$sent_count} students with unpaid fees for {$current_month} and " . getPreviousMonth() . " {$current_year}",
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'total_count' => $total_count,
        'note' => 'Only students with unpaid or partially paid fees for current month and previous month received bills',
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
            'previous_month' => getPreviousMonth(),
            'current_year' => $current_year
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
