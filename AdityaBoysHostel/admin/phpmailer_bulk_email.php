<?php
session_start();
require_once '../config/db.php';

// Load PHPMailer
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
require_once '../PHPMailer/src/Exception.php';

header('Content-Type: application/json');

try {
    // Get all active students
    $stmt = $conn->prepare("SELECT id, full_name, email, mobile FROM students WHERE is_active = 1");
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $sent_count = 0;
    $failed_count = 0;
    $total_count = count($students);
    $error_details = [];
    
    // Get current month and year for billing
    $current_month = date('F');
    $current_year = date('Y');
    
    foreach ($students as $student) {
        try {
            // Get student's current fees
            $fee_stmt = $conn->prepare("SELECT * FROM fees WHERE student_id = ? AND month = ? AND year = ?");
            $fee_stmt->bind_param("iss", $student['id'], $current_month, $current_year);
            $fee_stmt->execute();
            $fee_result = $fee_stmt->get_result();
            $fee_data = $fee_result->fetch_assoc();
            
            if ($fee_data) {
                // Create PHPMailer instance
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'aaravraj799246@gmail.com';
                $mail->Password   = 'magh vsox feox jjbf'; // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom('aaravraj799246@gmail.com', 'Aditya Boys Hostel');
                $mail->addAddress($student['email'], $student['full_name']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = "Monthly Fee Bill - Aditya Boys Hostel - $current_month $current_year";
                
                $mail->Body = "
                <html>
                <head>
                    <title>Monthly Fee Bill - Aditya Boys Hostel</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; }
                        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                        .header h1 { margin: 0; font-size: 28px; }
                        .header h2 { margin: 10px 0 0 0; font-size: 18px; opacity: 0.9; }
                        .content { padding: 30px; }
                        .greeting { color: #333; margin-bottom: 20px; }
                        .bill-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
                        .bill-info h4 { color: #667eea; margin-top: 0; }
                        .fee-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e9ecef; }
                        .fee-row:last-child { border-bottom: none; }
                        .fee-label { font-weight: 600; color: #495057; }
                        .fee-value { color: #333; }
                        .total { font-weight: bold; font-size: 18px; color: #667eea; }
                        .payment-methods { background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; }
                        .payment-methods h4 { color: #1976d2; margin-top: 0; }
                        .payment-methods ul { margin: 10px 0; padding-left: 20px; }
                        .payment-methods li { margin: 5px 0; }
                        .footer { text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; color: #6c757d; }
                        .footer strong { color: #495057; }
                        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
                        .status-paid { background: #d4edda; color: #155724; }
                        .status-unpaid { background: #f8d7da; color: #721c24; }
                        .status-partial { background: #fff3cd; color: #856404; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🏠 Aditya Boys Hostel</h1>
                            <h2>Monthly Fee Bill</h2>
                        </div>
                        <div class='content'>
                            <p class='greeting'>Dear <strong>" . htmlspecialchars($student['full_name']) . "</strong>,</p>
                            <p>Your monthly fee bill for <strong>$current_month $current_year</strong> is ready. Please find the details below:</p>
                            
                            <div class='bill-info'>
                                <h4>💰 Fee Details</h4>
                                <div class='fee-row'>
                                    <span class='fee-label'>Monthly Fee:</span>
                                    <span class='fee-value'>₹" . number_format($fee_data['amount'], 2) . "</span>
                                </div>
                                <div class='fee-row'>
                                    <span class='fee-label'>Paid Amount:</span>
                                    <span class='fee-value'>₹" . number_format($fee_data['paid_amount'], 2) . "</span>
                                </div>
                                <div class='fee-row'>
                                    <span class='fee-label'>Balance Due:</span>
                                    <span class='total'>₹" . number_format($fee_data['amount'] - $fee_data['paid_amount'], 2) . "</span>
                                </div>
                                <div class='fee-row'>
                                    <span class='fee-label'>Status:</span>
                                    <span class='status-badge status-" . strtolower($fee_data['status']) . "'>" . ucfirst($fee_data['status']) . "</span>
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
                            
                            <p><strong>📅 Important:</strong> Please clear your dues before the end of the month to avoid late fees.</p>
                            
                            <div class='footer'>
                                <p><strong>Aditya Boys Hostel</strong><br>
                                📞 Contact: 7992465964<br>
                                📧 Email: aaravraj799246@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>";
                
                // Send email
                $mail->send();
                $sent_count++;
                
                // Add small delay to avoid spam filters
                sleep(1);
                
            } else {
                $failed_count++;
                $error_details[] = "No fee data found for student: " . $student['full_name'];
            }
            
        } catch (Exception $e) {
            $failed_count++;
            $error_details[] = "Failed to send to " . $student['email'] . ": " . $e->getMessage();
        }
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'total_count' => $total_count,
        'message' => "Bulk email process completed",
        'errors' => $error_details
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ]);
}
?>
