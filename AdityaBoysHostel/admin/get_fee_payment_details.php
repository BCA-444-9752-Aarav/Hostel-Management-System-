<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fee_id = $_GET['fee_id'] ?? 0;
    
    if (empty($fee_id)) {
        echo json_encode(['success' => false, 'message' => 'Fee ID is required']);
        exit();
    }
    
    try {
        // Get fee and payment details with student information
        $stmt = $conn->prepare("
            SELECT 
                f.id as fee_id,
                f.student_id,
                f.month,
                f.year,
                f.amount,
                f.paid_amount,
                f.status as fee_status,
                f.payment_date,
                f.payment_method,
                f.transaction_id,
                s.full_name as student_name,
                s.email as student_email,
                r.room_number,
                p.status as payment_status,
                p.payment_proof,
                p.approved_at,
                p.approved_by,
                p.created_at as payment_created_at
            FROM fees f
            JOIN students s ON f.student_id = s.id
            LEFT JOIN rooms r ON s.room_id = r.id
            LEFT JOIN payments p ON f.id = p.fee_id AND p.status = 'Approved'
            WHERE f.id = ?
        ");
        
        $stmt->bind_param("i", $fee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();
            
            // Format dates
            if ($payment['payment_date']) {
                $payment['payment_date'] = date('d M, Y', strtotime($payment['payment_date']));
            }
            if ($payment['approved_at']) {
                $payment['approved_at'] = date('d M, Y H:i', strtotime($payment['approved_at']));
            }
            if ($payment['payment_created_at']) {
                $payment['payment_created_at'] = date('d M, Y H:i', strtotime($payment['payment_created_at']));
            }
            
            // Format payment method name
            $payment_methods = [
                'cash' => 'Cash',
                'bank_transfer' => 'Bank Transfer',
                'upi' => 'UPI',
                'cheque' => 'Cheque',
                'google_pay' => 'Google Pay',
                'paytm' => 'Paytm',
                'phonepe' => 'PhonePe',
                'bhim' => 'BHIM UPI',
                'amazon_pay' => 'Amazon Pay'
            ];
            $payment['payment_method'] = $payment_methods[$payment['payment_method']] ?? ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? ''));
            
            echo json_encode([
                'success' => true,
                'payment' => $payment
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment details not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
