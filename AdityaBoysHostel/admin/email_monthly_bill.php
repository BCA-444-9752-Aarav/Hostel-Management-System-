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

$student_id = $_POST['student_id'] ?? '';
$month = $_POST['month'] ?? date('F');
$year = $_POST['year'] ?? date('Y');

if (empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit();
}

try {
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

    // Generate bill HTML
    $bill_html = generateBillHTML($student, $fee, $month, $year);
    
    // Send email using SMTP with fallback
    $to = $student['email'];
    $subject = "Monthly Fee Bill - {$month} {$year} - Aditya Boys Hostel";
    
    // Try PHPMailer first (if available)
    $phpmailer_available = false;
    
    // Check for PHPMailer in multiple locations
    $phpmailer_paths = [
        '../PHPMailer/src/PHPMailer.php',  // Current location
        '../src/PHPMailer.php',            // Alternative location (htdocs/src)
        '../../src/PHPMailer.php'           // Another alternative
    ];
    
    foreach ($phpmailer_paths as $path) {
        if (file_exists($path)) {
            try {
                // Determine the correct include path
                $include_path = dirname($path);
                require_once $include_path . '/PHPMailer.php';
                require_once $include_path . '/SMTP.php';
                require_once $include_path . '/Exception.php';
                $phpmailer_available = true;
                error_log("PHPMailer loaded from: " . $path);
                break;
            } catch (Exception $e) {
                $phpmailer_available = false;
                error_log("PHPMailer load error from $path: " . $e->getMessage());
                continue;
            }
        }
    }
    
    if (!$phpmailer_available) {
        error_log("PHPMailer not found in any location. Paths checked: " . implode(', ', $phpmailer_paths));
    }
    
    if ($phpmailer_available) {
        // Use PHPMailer SMTP
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'aaravraj799246@gmail.com';
            $mail->Password = 'wtsfspkophmatsjw';  // Gmail App Password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('aaravraj799246@gmail.com', 'Aditya Boys Hostel');
            $mail->addAddress($to, $student['full_name']);
            $mail->addReplyTo('admin@adityaboyshostel.com', 'Admin');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $bill_html;
            
            // Send email
            $mail->send();
            
            // Log the email sent
            $stmt = $conn->prepare("INSERT INTO email_logs (student_id, type, month, year, sent_at, status) VALUES (?, 'monthly_bill', ?, ?, NOW(), 'sent')");
            $stmt->bind_param("iss", $student_id, $current_month, $current_year);
            $stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => "Bill email sent successfully to {$student['full_name']} ({$student['email']})"
            ]);
            
        } catch (PHPMailer\PHPMailer\Exception $e) {
            // Log the email failure
            $stmt = $conn->prepare("INSERT INTO email_logs (student_id, type, month, year, sent_at, status, error_message) VALUES (?, 'monthly_bill', ?, ?, NOW(), 'failed', ?)");
            $error_msg = 'SMTP Error: ' . $e->getMessage();
            $stmt->bind_param("isss", $student_id, $current_month, $current_year, $error_msg);
            $stmt->execute();
            
            echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
    } else {
        // Fallback to basic PHP mail with proper headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Aditya Boys Hostel <aaravraj799246@gmail.com>',
            'Reply-To: admin@adityaboyshostel.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headers_string = implode("\r\n", $headers);
        
        // Attempt to send email
        $email_sent = mail($to, $subject, $bill_html, $headers_string);
        
        if ($email_sent) {
            // Log the email sent
            $stmt = $conn->prepare("INSERT INTO email_logs (student_id, type, month, year, sent_at, status) VALUES (?, 'monthly_bill', ?, ?, NOW(), 'sent')");
            $stmt->bind_param("iss", $student_id, $current_month, $current_year);
            $stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => "Bill email sent successfully to {$student['full_name']} ({$student['email']})"
            ]);
        } else {
            // Log the email failure
            $stmt = $conn->prepare("INSERT INTO email_logs (student_id, type, month, year, sent_at, status, error_message) VALUES (?, 'monthly_bill', ?, ?, NOW(), 'failed', 'PHP mail() function failed')");
            $stmt->bind_param("iss", $student_id, $current_month, $current_year);
            $stmt->execute();
            
            echo json_encode(['success' => false, 'message' => 'Email sending failed. Please check server mail configuration.']);
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function generateBillHTML($student, $fee, $month, $year) {
    $due_amount = $fee['amount'] - $fee['paid_amount'];
    $status_class = $due_amount > 0 ? 'danger' : 'success';
    $status_text = $due_amount > 0 ? 'Pending' : 'Paid';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Monthly Fee Bill - {$month} {$year}</title>
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
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Aditya Boys Hostel</h1>
                <p>Monthly Fee Bill</p>
            </div>
            
            <div class='student-info'>
                <h3>Student Information</h3>
                <p><strong>Name:</strong> {$student['full_name']}</p>
                <p><strong>Email:</strong> {$student['email']}</p>
                <p><strong>Mobile:</strong> {$student['mobile']}</p>
                <p><strong>Room:</strong> " . ($student['room_id'] ? "Room {$student['room_number']} - Bed {$student['bed_number']}" : 'Not Assigned') . "</p>
            </div>
            
            <div class='fee-details'>
                <h3>Fee Details for {$month} {$year}</h3>
                <table class='fee-table'>
                    <tr>
                        <th>Description</th>
                        <th>Amount (₹)</th>
                    </tr>
                    <tr>
                        <td>Monthly Fee</td>
                        <td>" . number_format($fee['amount'], 2) . "</td>
                    </tr>
                    <tr>
                        <td>Paid Amount</td>
                        <td>-" . number_format($fee['paid_amount'], 2) . "</td>
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
            
            <div class='footer'>
                <p><strong>Payment Instructions:</strong></p>
                <p>Please pay the due amount at the earliest. You can pay online through the student portal or at the hostel office.</p>
                <p>For any queries, contact the hostel administration.</p>
                <p>&copy; " . date('Y') . " Aditya Boys Hostel. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>
