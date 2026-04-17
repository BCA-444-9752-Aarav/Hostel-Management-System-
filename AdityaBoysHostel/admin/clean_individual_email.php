<?php
require_once '../config/db.php';

// Helper function to get room information
function getRoomInfo($conn, $room_id, $bed_number) {
    try {
        $stmt = $conn->prepare("SELECT room_number FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $room = $stmt->get_result()->fetch_assoc();
        if ($room) {
            return "Room {$room['room_number']} - Bed {$bed_number}";
        } else {
            return "Not Assigned";
        }
    } catch (Exception $e) {
        return "Room Info Error";
    }
}

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

$student_id = $_POST['student_id'] ?? '';
$month = $_POST['month'] ?? date('F');
$year = $_POST['year'] ?? date('Y');

// Handle different actions
$action = $_POST['action'] ?? '';

if ($action === 'test_email') {
    // Test email functionality
    if (empty($student_id)) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required']);
        exit();
    }
    
    // Get student information
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit();
    }

    // Send email
    $to = $student['email'];
    $subject = "Individual Email Test - " . date('H:i:s');
    $message = "This is a test email to verify individual email functionality for student: {$student['full_name']} ({$student['email']}). Time: " . date('Y-m-d H:i:s') . "";
    
    // Use PHP mail for simplicity
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: Admin <aaravraj799246@gmail.com>',
        'Reply-To: admin@adityaboyshostel.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $headers_string = implode("\r\n", $headers);
    $email_sent = mail($to, $subject, $message, $headers_string);
    
    if ($email_sent) {
        echo json_encode([
            'success' => true, 
            'message' => "Test email sent successfully to {$student['full_name']} ({$student['email']})",
            'debug_info' => [
                'method' => 'PHP mail()',
                'student_id' => $student_id,
                'email' => $student['email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Test email failed using PHP mail()',
            'debug_info' => [
                'method' => 'PHP mail()',
                'error' => error_get_last()
            ]
        ]);
    }

} else {
    // Handle regular email bill sending
    if (empty($student_id)) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required']);
        exit();
    }

    // Get student information
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit();
    }

    // Get specific month/year fee details
    $stmt = $conn->prepare("SELECT * FROM fees WHERE student_id = ? AND month = ? AND year = ?");
    $stmt->bind_param("iss", $student_id, $month, $year);
    $stmt->execute();
    $fee = $stmt->get_result()->fetch_assoc();

    if (!$fee) {
        echo json_encode(['success' => false, 'message' => "No fee record found for {$month} {$year}"]);
        exit();
    }

    // Calculate bill details
    $due_amount = $fee['amount'] - $fee['paid_amount'];
    $status_text = $due_amount > 0 ? 'Pending' : 'Paid';
    $status_color = $due_amount > 0 ? '#dc3545' : '#28a745';

    // Create professional bill HTML
    $bill_html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Monthly Fee Bill - Aditya Boys Hostel</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f8f9fa; line-height: 1.6; }
            .container { max-width: 700px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 32px; font-weight: 300; letter-spacing: 1px; }
            .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 16px; }
            .content { padding: 40px 30px; }
            .section { background: #f8f9fa; border-left: 4px solid #667eea; padding: 25px; margin: 25px 0; border-radius: 8px; }
            .section h3 { margin: 0 0 20px 0; color: #2c3e50; font-size: 20px; font-weight: 600; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
            .info-item { padding: 10px 0; border-bottom: 1px solid #e9ecef; }
            .info-item:last-child { border-bottom: none; }
            .info-label { font-weight: 600; color: #6c757d; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
            .info-value { color: #2c3e50; font-size: 16px; margin-top: 4px; }
            .fee-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
            .fee-table th { background: #667eea; color: white; padding: 18px 15px; text-align: left; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
            .fee-table td { padding: 18px 15px; border-bottom: 1px solid #e9ecef; font-size: 16px; }
            .fee-table tr:last-child td { border-bottom: none; }
            .fee-table .amount { text-align: right; font-weight: 600; }
            .fee-table .total { background: #f8f9fa; font-weight: 700; font-size: 18px; }
            .status-badge { display: inline-block; padding: 12px 24px; border-radius: 25px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin: 20px 0; }
            .status-paid { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
            .status-pending { background: linear-gradient(135deg, #dc3545, #fd7e14); color: white; }
            .footer { background: #2c3e50; color: white; padding: 30px; text-align: center; }
            .footer h4 { margin: 0 0 15px 0; font-size: 18px; font-weight: 600; }
            .footer p { margin: 8px 0; opacity: 0.9; font-size: 14px; }
            .footer .contact { margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
            .logo { font-size: 24px; font-weight: 700; margin-bottom: 5px; }
            .bill-number { background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; display: inline-block; margin-top: 15px; font-size: 14px; }
            .due-amount { font-size: 24px; font-weight: 700; color: " . ($due_amount > 0 ? '#dc3545' : '#28a745') . "; }
            @media (max-width: 600px) {
                .info-grid { grid-template-columns: 1fr; }
                .container { margin: 10px; border-radius: 8px; }
                .content { padding: 25px 20px; }
                .header { padding: 30px 20px; }
                .header h1 { font-size: 26px; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>ADITYA BOYS HOSTEL</div>
                <h1>Monthly Fee Bill</h1>
                <p>Official Billing Statement</p>
                <div class='bill-number'>Bill #{$student['id']}-" . date('Ym') . "</div>
            </div>
            
            <div class='content'>
                <div class='section'>
                    <h3>📋 Student Information</h3>
                    <div class='info-grid'>
                        <div>
                            <div class='info-item'>
                                <div class='info-label'>Student Name</div>
                                <div class='info-value'>" . htmlspecialchars($student['full_name']) . "</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Email Address</div>
                                <div class='info-value'>" . htmlspecialchars($student['email']) . "</div>
                            </div>
                        </div>
                        <div>
                            <div class='info-item'>
                                <div class='info-label'>Mobile Number</div>
                                <div class='info-value'>" . htmlspecialchars($student['mobile']) . "</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Room Assignment</div>
                                <div class='info-value'>" . ($student['room_id'] ? getRoomInfo($conn, $student['room_id'], $student['bed_number']) : 'Not Assigned') . "</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>💰 Fee Details - {$month} {$year}</h3>
                    <table class='fee-table'>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class='amount'>Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Monthly Hostel Fee</td>
                                <td class='amount'>" . number_format($fee['amount'], 2) . "</td>
                            </tr>
                            <tr>
                                <td>Amount Paid</td>
                                <td class='amount' style='color: #28a745;'>" . number_format($fee['paid_amount'], 2) . "</td>
                            </tr>
                            <tr class='total'>
                                <td><strong>Total Due Amount</strong></td>
                                <td class='amount due-amount'><strong>₹" . number_format($due_amount, 2) . "</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style='text-align: center;'>
                        <div class='status-badge " . ($due_amount > 0 ? 'status-pending' : 'status-paid') . "'>
                            " . ($due_amount > 0 ? '⏰ Payment Pending' : '✅ Payment Complete') . "
                        </div>
                    </div>
                </div>
                
                <div class='section'>
                    <h3>💳 Payment Instructions</h3>
                    <div style='background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;'>
                        <p style='margin: 0 0 15px 0; font-size: 16px;'><strong>Payment Methods Available:</strong></p>
                        <ul style='margin: 0; padding-left: 20px; color: #495057;'>
                            <li style='margin-bottom: 8px;'>📱 Online payment through student portal</li>
                            <li style='margin-bottom: 8px;'>💵 Cash payment at hostel office</li>
                            <li style='margin-bottom: 8px;'>🏦 Bank transfer to hostel account</li>
                            <li style='margin-bottom: 8px;'>📱 UPI payment available</li>
                        </ul>
                        <p style='margin: 15px 0 0 0; font-size: 16px;'><strong>⚠️ Important:</strong> Please pay the due amount before the 5th of next month to avoid late fees.</p>
                    </div>
                </div>
            </div>
            
            <div class='footer'>
                <h4>🏠 Aditya Boys Hostel</h4>
                <p>Providing quality accommodation and services</p>
                <div class='contact'>
                    <p><strong>📧 Email:</strong> admin@adityaboyshostel.com</p>
                    <p><strong>📞 Phone:</strong> +91-7992465964</p>
                    <p><strong>📍 Address:</strong> Aditya Boys Hostel, Indrapuri Road Number 4</p>
                    <p style='margin-top: 15px; font-size: 12px; opacity: 0.7;'>&copy; " . date('Y') . " Aditya Boys Hostel. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    // Send email
    $to = $student['email'];
    $subject = "Monthly Fee Bill - {$month} {$year} - Aditya Boys Hostel";
    
    // Load PHPMailer from the correct location
    $phpmailer_loaded = false;
    $phpmailer_paths = [
        '../src/PHPMailer.php',
        '../PHPMailer/src/PHPMailer.php',
        '../../src/PHPMailer.php'
    ];
    
    foreach ($phpmailer_paths as $path) {
        if (file_exists($path)) {
            try {
                $include_path = dirname($path);
                require_once $include_path . '/PHPMailer.php';
                require_once $include_path . '/SMTP.php';
                require_once $include_path . '/Exception.php';
                $phpmailer_loaded = true;
                break;
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    if (!$phpmailer_loaded) {
        echo json_encode(['success' => false, 'message' => 'PHPMailer not available']);
        exit();
    }
    
    // Try PHPMailer first
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aaravraj799246@gmail.com';
        $mail->Password = 'wtsfspkophmatsjw';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('aaravraj799246@gmail.com', 'Admin');
        $mail->addAddress($to, $student['full_name']);
        $mail->addReplyTo('admin@adityaboyshostel.com', 'Admin');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bill_html;
        
        // Send email
        $mail->send();
        
        echo json_encode([
            'success' => true, 
            'message' => "Monthly bill sent successfully to {$student['full_name']} ({$student['email']})"
        ]);
        
    } catch (PHPMailer\PHPMailer\Exception $e) {
        // Fallback to PHP mail
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Admin <aaravraj799246@gmail.com>',
            'Reply-To: admin@adityaboyshostel.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headers_string = implode("\r\n", $headers);
        $email_sent = mail($to, $subject, $bill_html, $headers_string);
        
        if ($email_sent) {
            echo json_encode([
                'success' => true, 
                'message' => "Monthly bill sent successfully to {$student['full_name']} ({$student['email']})"
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Email sending failed. Please check server configuration.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>
