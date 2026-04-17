<?php
require_once '../config/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get payment information from database
$payment_info = null;
try {
    // Get only the specific payment methods requested: upi, bank_transfer, google_pay, paytm, phonepe
    $allowed_methods = ['upi', 'bank_transfer', 'google_pay', 'paytm', 'phonepe'];
    
    // First try with is_active column and allowed methods
    $stmt = $conn->prepare("SELECT * FROM payment_info WHERE is_active = TRUE AND payment_method IN ('" . implode("','", $allowed_methods) . "') ORDER BY display_order ASC, id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get all matching payment methods
    $payment_methods = [];
    while ($row = $result->fetch_assoc()) {
        $payment_methods[] = $row;
    }
    
    // If no results, try without is_active condition but with allowed methods
    if (empty($payment_methods)) {
        $stmt = $conn->prepare("SELECT * FROM payment_info WHERE payment_method IN ('" . implode("','", $allowed_methods) . "') ORDER BY display_order ASC, id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $payment_methods[] = $row;
        }
    }
    
    // If still no results, create default payment info for allowed methods
    if (empty($payment_methods)) {
        $default_methods = [
            [
                'payment_method' => 'upi',
                'upi_id' => 'aaravraj799246@okaxis',
                'phone_number' => '7992465964',
                'qr_code_path' => 'QR code/WhatsApp Image 2026-03-09 at 8.36.02 PM.jpeg',
                'bank_name' => null,
                'account_number' => null,
                'ifsc_code' => null,
                'account_holder_name' => null,
                'is_active' => true,
                'display_order' => 1
            ],
            [
                'payment_method' => 'google_pay',
                'upi_id' => 'aaravraj799246@okaxis',
                'phone_number' => '7992465964',
                'qr_code_path' => 'QR code/WhatsApp Image 2026-03-09 at 8.36.02 PM.jpeg',
                'bank_name' => null,
                'account_number' => null,
                'ifsc_code' => null,
                'account_holder_name' => null,
                'is_active' => true,
                'display_order' => 2
            ],
            [
                'payment_method' => 'phonepe',
                'upi_id' => 'aaravraj799246@okaxis',
                'phone_number' => '7992465964',
                'qr_code_path' => 'QR code/WhatsApp Image 2026-03-09 at 8.36.02 PM.jpeg',
                'bank_name' => null,
                'account_number' => null,
                'ifsc_code' => null,
                'account_holder_name' => null,
                'is_active' => true,
                'display_order' => 3
            ],
            [
                'payment_method' => 'paytm',
                'upi_id' => 'aaravraj799246@okaxis',
                'phone_number' => '7992465964',
                'qr_code_path' => 'QR code/WhatsApp Image 2026-03-09 at 8.36.02 PM.jpeg',
                'bank_name' => null,
                'account_number' => null,
                'ifsc_code' => null,
                'account_holder_name' => null,
                'is_active' => true,
                'display_order' => 4
            ],
            [
                'payment_method' => 'bank_transfer',
                'upi_id' => null,
                'phone_number' => '7992465964',
                'qr_code_path' => null,
                'bank_name' => 'State Bank of India',
                'account_number' => '123456789012345',
                'ifsc_code' => 'SBIN0001234',
                'account_holder_name' => 'Aditya Boys Hostel',
                'is_active' => true,
                'display_order' => 5
            ]
        ];
        $payment_methods = $default_methods;
    }
    
    // Debug logging
    error_log("Payment info query result: " . count($payment_methods) . " records found");
    foreach ($payment_methods as $method) {
        error_log("Payment method: " . json_encode($method));
    }
    
} catch (Exception $e) {
    error_log("Error in get_payment_info.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

if (!empty($payment_methods)) {
    echo json_encode(['success' => true, 'payment_info' => $payment_methods]);
} else {
    // Return default payment info as fallback
    $default_methods = [
        [
            'payment_method' => 'upi',
            'upi_id' => 'aaravraj799246@okaxis',
            'phone_number' => '7992465964',
            'qr_code_path' => 'QR code/WhatsApp Image 2026-03-09 at 8.36.02 PM.jpeg',
            'bank_name' => 'Not Available',
            'account_number' => 'Not Available',
            'ifsc_code' => 'Not Available'
        ]
    ];
    echo json_encode(['success' => true, 'payment_info' => $default_methods]);
}
?>
