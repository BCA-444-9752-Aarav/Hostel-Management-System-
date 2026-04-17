<?php
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle payment info updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_payment_info') {
        try {
            $upi_id = $_POST['upi_id'] ?? '';
            $phone_number = $_POST['phone_number'] ?? '';
            $bank_name = $_POST['bank_name'] ?? '';
            $account_number = $_POST['account_number'] ?? '';
            $ifsc_code = $_POST['ifsc_code'] ?? '';
            
            // Update payment info
            // First, let's debug what we're working with
            error_log("Payment info update attempt - UPI: $upi_id, Phone: $phone_number, Bank: $bank_name");
            
            // Check if payment_info record exists
            $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM payment_info");
            $check_stmt->execute();
            $count_result = $check_stmt->get_result();
            $record_count = $count_result->fetch_assoc()['count'];
            
            error_log("Record count: $record_count");
            
            // Test if columns exist by trying a simple query first
            $test_query = "SELECT bank_name FROM payment_info LIMIT 1";
            $test_result = $conn->query($test_query);
            if ($test_result === false) {
                error_log("Column bank_name does not exist: " . $conn->error);
                $error = "Database error: bank_name column does not exist. Please run the setup script.";
            } else {
                error_log("Column bank_name exists");
                
                if ($record_count > 0) {
                    // Update existing records - Update bank details for ALL payment methods
                    error_log("Updating bank details for all payment methods");
                    
                    // Update all payment methods with the new bank details
                    $stmt = $conn->prepare("UPDATE payment_info SET bank_name = ?, account_number = ?, ifsc_code = ?, updated_at = NOW()");
                    if ($stmt) {
                        $stmt->bind_param("sss", $bank_name, $account_number, $ifsc_code);
                        $result = $stmt->execute();
                        
                        error_log("Bank details update execute result: " . ($result ? 'true' : 'false'));
                        error_log("Affected rows: " . $stmt->affected_rows);
                        error_log("Statement error: " . $stmt->error);
                        
                        // Also update UPI details for UPI-based methods
                        if (!empty($upi_id) || !empty($phone_number)) {
                            $upi_methods = ['upi', 'google_pay', 'phonepe', 'paytm'];
                            foreach ($upi_methods as $method) {
                                $upi_stmt = $conn->prepare("UPDATE payment_info SET upi_id = ?, phone_number = ?, updated_at = NOW() WHERE payment_method = ?");
                                if ($upi_stmt) {
                                    $upi_stmt->bind_param("sss", $upi_id, $phone_number, $method);
                                    $upi_result = $upi_stmt->execute();
                                    error_log("Updated UPI details for $method: " . ($upi_result ? 'true' : 'false'));
                                }
                            }
                        }
                        
                        if ($stmt->affected_rows > 0) {
                            $success = "Payment information updated successfully for all payment methods!";
                            error_log("Payment info updated successfully");
                        } else {
                            $error = "No changes made to payment information.";
                            error_log("No changes detected in payment info");
                        }
                    } else {
                        error_log("Prepare failed: " . $conn->error);
                        $error = "Database error: " . $conn->error;
                    }
                } else {
                    // Insert new records for all payment methods
                    error_log("Inserting new records for all payment methods");
                    
                    $payment_methods = [
                        ['upi', $upi_id, $phone_number],
                        ['google_pay', $upi_id, $phone_number],
                        ['phonepe', $upi_id, $phone_number],
                        ['paytm', $upi_id, $phone_number],
                        ['bank_transfer', null, $phone_number]
                    ];
                    
                    $total_inserted = 0;
                    foreach ($payment_methods as $index => $method_data) {
                        $stmt = $conn->prepare("INSERT INTO payment_info (payment_method, upi_id, phone_number, bank_name, account_number, ifsc_code, display_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                        if ($stmt) {
                            $display_order = $index + 1;
                            $stmt->bind_param("ssssssi", $method_data[0], $method_data[1], $method_data[2], $bank_name, $account_number, $ifsc_code, $display_order);
                            $result = $stmt->execute();
                            error_log("Inserted payment method " . $method_data[0] . ": " . ($result ? 'true' : 'false') . ", Affected rows: " . $stmt->affected_rows);
                            if ($stmt->affected_rows > 0) {
                                $total_inserted++;
                            }
                        }
                    }
                    
                    error_log("Total payment method insertions: $total_inserted");
                    
                    if ($total_inserted > 0) {
                        $success = "Payment information saved successfully for all payment methods!";
                        error_log("Payment info inserted successfully for $total_inserted methods");
                    } else {
                        $error = "Failed to save payment information.";
                        error_log("Payment info insert failed - no affected rows");
                    }
                }
            }
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($action == 'upload_qr') {
        error_log("QR upload attempt started");
        
        if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['qr_code'];
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '', $file['name']);
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            
            error_log("File details - Name: $file_name, Size: $file_size, Temp: $file_tmp");
            
            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                $error = "File size must be less than 5MB";
                error_log("File size validation failed: $file_size bytes");
            } else {
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($file_tmp);
                error_log("File type detected: $file_type");
                
                if (!in_array($file_type, $allowed_types)) {
                    $error = "Only JPG, PNG, and GIF files are allowed";
                    error_log("File type validation failed: $file_type");
                } else {
                    // Create uploads directory if it doesn't exist
                    $upload_dir = '../QR code/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                        error_log("Created directory: $upload_dir");
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                        error_log("File moved successfully to: " . $upload_dir . $file_name);
                        
                        // Check if payment_info record exists
                        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM payment_info");
                        $check_stmt->execute();
                        $count_result = $check_stmt->get_result();
                        $record_count = $count_result->fetch_assoc()['count'];
                        
                        $qr_path = 'QR code/' . $file_name;
                        error_log("QR path for database: $qr_path, Record count: $record_count");
                        
                        if ($record_count > 0) {
                            // Update existing records - Update QR code for ALL UPI-based methods
                            error_log("Updating QR code for all payment methods");
                            
                            // Update all UPI-based payment methods with the new QR code
                            $upi_methods = ['upi', 'google_pay', 'phonepe', 'paytm'];
                            $total_updated = 0;
                            
                            foreach ($upi_methods as $method) {
                                $stmt = $conn->prepare("UPDATE payment_info SET qr_code_path = ?, updated_at = NOW() WHERE payment_method = ?");
                                if ($stmt) {
                                    $stmt->bind_param("ss", $qr_path, $method);
                                    $result = $stmt->execute();
                                    error_log("Updated QR for $method: " . ($result ? 'true' : 'false') . ", Affected rows: " . $stmt->affected_rows);
                                    if ($stmt->affected_rows > 0) {
                                        $total_updated++;
                                    }
                                }
                            }
                            
                            error_log("Total QR code updates: $total_updated");
                            
                            if ($total_updated > 0) {
                                $success = "QR code updated successfully for all payment methods!";
                                error_log("QR update successful for $total_updated methods");
                                
                                // Only redirect if not already coming from a redirect to prevent loop
                                if (!isset($_GET['success'])) {
                                    header("Location: payment_info.php?success=qr_updated_all");
                                    exit();
                                }
                            } else {
                                $error = "Failed to update QR code for payment methods.";
                                error_log("QR update failed - no affected rows");
                            }
                        } else {
                            // Insert new records for all payment methods with the same QR code
                            error_log("Inserting new QR code records for all payment methods");
                            
                            $upi_methods = [
                                ['upi', 'aaravraj799246@okaxis'],
                                ['google_pay', 'aaravraj799246@okaxis'],
                                ['phonepe', 'aaravraj799246@okaxis'],
                                ['paytm', 'aaravraj799246@okaxis']
                            ];
                            
                            $total_inserted = 0;
                            foreach ($upi_methods as $method_data) {
                                $stmt = $conn->prepare("INSERT INTO payment_info (payment_method, upi_id, phone_number, qr_code_path, created_at, updated_at) VALUES (?, ?, '7992465964', ?, NOW(), NOW())");
                                if ($stmt) {
                                    $stmt->bind_param("sss", $method_data[0], $method_data[1], $qr_path);
                                    $result = $stmt->execute();
                                    error_log("Inserted QR for " . $method_data[0] . ": " . ($result ? 'true' : 'false') . ", Affected rows: " . $stmt->affected_rows);
                                    if ($stmt->affected_rows > 0) {
                                        $total_inserted++;
                                    }
                                }
                            }
                            
                            error_log("Total QR code insertions: $total_inserted");
                            
                            if ($total_inserted > 0) {
                                $success = "QR code uploaded successfully for all payment methods!";
                                error_log("QR insert successful for $total_inserted methods");
                                
                                // Only redirect if not already coming from a redirect to prevent loop
                                if (!isset($_GET['success'])) {
                                    header("Location: payment_info.php?success=qr_uploaded_all");
                                    exit();
                                }
                            } else {
                                $error = "Failed to insert QR code for payment methods.";
                                error_log("QR insert failed - no affected rows");
                            }
                        }
                    } else {
                        $error = "Failed to upload QR code file.";
                        error_log("File move failed");
                    }
                }
            }
        } else {
            $error = "Please select a QR code file";
            error_log("No file selected or upload error");
        }
    }
}

// Get current payment information (refresh after update)
$payment_info = null;
error_log("Fetching payment info after update");

// Get all payment info records and merge them
$stmt = $conn->prepare("SELECT * FROM payment_info ORDER BY display_order ASC, id DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Initialize empty payment info array
    $payment_info = [
        'upi_id' => null,
        'phone_number' => null,
        'bank_name' => null,
        'account_number' => null,
        'ifsc_code' => null,
        'qr_code_path' => null,
        'updated_at' => null
    ];
    
    // Merge all payment method records
    while ($row = $result->fetch_assoc()) {
        error_log("Processing payment method: " . $row['payment_method']);
        
        // Get UPI ID from any UPI-based method
        if (in_array($row['payment_method'], ['upi', 'google_pay', 'phonepe', 'paytm']) && !empty($row['upi_id'])) {
            $payment_info['upi_id'] = $row['upi_id'];
        }
        
        // Get phone number from any method
        if (!empty($row['phone_number'])) {
            $payment_info['phone_number'] = $row['phone_number'];
        }
        
        // Get bank details from bank_transfer or any method
        if (!empty($row['bank_name'])) {
            $payment_info['bank_name'] = $row['bank_name'];
        }
        if (!empty($row['account_number'])) {
            $payment_info['account_number'] = $row['account_number'];
        }
        if (!empty($row['ifsc_code'])) {
            $payment_info['ifsc_code'] = $row['ifsc_code'];
        }
        
        // Get QR code from any UPI method
        if (in_array($row['payment_method'], ['upi', 'google_pay', 'phonepe', 'paytm']) && !empty($row['qr_code_path'])) {
            $payment_info['qr_code_path'] = $row['qr_code_path'];
        }
        
        // Get latest updated timestamp
        if (!empty($row['updated_at']) && (empty($payment_info['updated_at']) || $row['updated_at'] > $payment_info['updated_at'])) {
            $payment_info['updated_at'] = $row['updated_at'];
        }
    }
    
    error_log("Final merged payment info: " . json_encode($payment_info));
    
} else {
    error_log("Payment info query failed: " . $conn->error);
    $error = "Database error: " . $conn->error;
}

// Handle success message from redirect
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'qr_updated') {
        $success = "QR code updated successfully! The new QR code is now active.";
        // Clear cache by adding timestamp to QR code URL
        $cache_buster = '?v=' . time();
    } elseif ($_GET['success'] == 'qr_updated_all') {
        $success = "QR code updated successfully for all payment methods! The new QR code is now active.";
        // Clear cache by adding timestamp to QR code URL
        $cache_buster = '?v=' . time();
    } elseif ($_GET['success'] == 'qr_uploaded_all') {
        $success = "QR code uploaded successfully for all payment methods! The QR code is now active.";
        // Clear cache by adding timestamp to QR code URL
        $cache_buster = '?v=' . time();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Information - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-info-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .info-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .qr-preview {
            width: 250px;
            height: 250px;
            border-radius: 15px;
            border: 3px solid #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            object-fit: cover;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: block;
            margin: 0 auto;
        }
        .qr-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }
        .qr-code-container {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .qr-code-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #fff;
        }
        
        /* Light Theme Styles */
        body:not(.dark-theme) .info-card {
            background: #ffffff;
            color: #333;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        body:not(.dark-theme) .qr-code-container {
            background: #f8f9fa;
            color: #333;
        }
        
        body:not(.dark-theme) .qr-code-title {
            color: #333;
        }
        
        body:not(.dark-theme) .qr-preview {
            border: 3px solid #e9ecef;
        }
        
        body:not(.dark-theme) .stat-card {
            color: white;
        }
        
        body:not(.dark-theme) .alert-success {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
        }
        
        body:not(.dark-theme) .alert-danger {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
        }
        
        body:not(.dark-theme) .alert-info {
            background-color: #d1ecf1 !important;
            border-color: #bee5eb !important;
            color: #0c5460 !important;
        }
        
        body:not(.dark-theme) .modal-content {
            background-color: #ffffff !important;
            color: #495057 !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }
        
        body:not(.dark-theme) .modal-header {
            background-color: #f8f9fa !important;
            border-bottom-color: #dee2e6 !important;
            color: #495057 !important;
        }
        
        body:not(.dark-theme) .modal-title {
            color: #495057 !important;
        }
        
        body:not(.dark-theme) .modal-body {
            background-color: #ffffff !important;
            color: #495057 !important;
        }
        
        body:not(.dark-theme) .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        
        body:not(.dark-theme) .modal {
            opacity: 1 !important;
        }
        
        body:not(.dark-theme) .modal .modal-dialog {
            opacity: 1 !important;
        }
        
        body:not(.dark-theme) .btn-close {
            color: #333 !important;
        }
        
        body:not(.dark-theme) .form-label {
            color: #333 !important;
        }
        
        body:not(.dark-theme) .form-control {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #ced4da !important;
        }
        
        body:not(.dark-theme) .form-control:focus {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Dark theme form controls */
        body.dark-theme .form-control {
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-color: #4a5568 !important;
        }
        
        body.dark-theme .form-control:focus {
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-color: #63b3ed !important;
            box-shadow: 0 0 0 0.2rem rgba(99, 179, 237, 0.25) !important;
        }
        
        /* Modal specific fixes */
        .modal .form-control {
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-color: #4a5568 !important;
        }
        
        .modal .form-control:focus {
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-color: #63b3ed !important;
            box-shadow: 0 0 0 0.2rem rgba(99, 179, 237, 0.25) !important;
        }
        
        body:not(.dark-theme) .modal .form-control {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #ced4da !important;
        }
        
        body:not(.dark-theme) .modal .form-control:focus {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Placeholder text styling */
        body.dark-theme .form-control::placeholder {
            color: #a0aec0 !important;
        }
        
        body:not(.dark-theme) .form-control::placeholder {
            color: #6c757d !important;
        }
        
        .modal .form-control::placeholder {
            color: #a0aec0 !important;
        }
        
        body:not(.dark-theme) .modal .form-control::placeholder {
            color: #6c757d !important;
        }
        
        body:not(.dark-theme) .form-text {
            color: #6c757d !important;
        }
        
        /* Responsive QR Code */
        @media (max-width: 768px) {
            .qr-preview {
                width: 200px;
                height: 200px;
            }
            .qr-code-container {
                padding: 15px;
            }
            .payment-info-container {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .qr-preview {
                width: 180px;
                height: 180px;
            }
            .qr-code-container {
                padding: 10px;
            }
        }
        
        /* Payment Details Table Fix */
        .payment-info-table {
            margin-top: 20px;
        }
        
        .payment-info-table th {
            width: 35%;
            text-align: left;
            font-weight: 600;
            vertical-align: middle;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: none;
        }
        
        .payment-info-table td {
            text-align: left;
            vertical-align: middle;
            padding-left: 20px;
            color: #fff;
            border: none;
        }
        
        .payment-info-table tbody tr {
            background-color: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .payment-info-table tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.08);
        }
        
        /* Light theme table styles */
        body:not(.dark-theme) .payment-info-table th {
            background-color: #f8f9fa;
            color: #333;
            border-bottom: 1px solid #dee2e6;
        }
        
        body:not(.dark-theme) .payment-info-table td {
            color: #333;
            border-bottom: 1px solid #dee2e6;
        }
        
        body:not(.dark-theme) .payment-info-table tbody tr {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
        }
        
        body:not(.dark-theme) .payment-info-table tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }
        
        /* Responsive Payment Details */
        @media (max-width: 768px) {
            .payment-info-table th {
                width: 40%;
                padding: 12px 15px;
                font-size: 0.9rem;
            }
            .payment-info-table td {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .payment-info-table th {
                width: 45%;
                padding: 10px 12px;
                font-size: 0.85rem;
            }
            .payment-info-table td {
                padding: 10px 12px;
                font-size: 0.85rem;
                padding-left: 15px;
            }
            .payment-info-table {
                margin-top: 15px;
            }
        }
        .payment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
            margin-bottom: 5px;
        }
        .stat-card > div:last-child {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        /* Special styling for UPI ID card */
        .stat-card:nth-child(3) .stat-number {
            font-size: 1.2rem;
        }
        
        /* Responsive design for statistics cards */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 1.3rem;
            }
            .stat-card:nth-child(3) .stat-number {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .stat-number {
                font-size: 1.1rem;
            }
            .stat-card:nth-child(3) .stat-number {
                font-size: 0.9rem;
            }
        }
        .btn-update {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        
        /* Fix QR upload modal */
        #updateQRModal .modal-dialog {
            max-width: 500px !important;
            width: 90vw !important;
        }
        
        #updateInfoModal .modal-dialog {
            max-width: 600px !important;
            width: 95vw !important;
        }
        
        #updateQRModal .modal-body {
            padding: 25px !important;
        }
        
        #updateInfoModal .modal-body {
            padding: 30px !important;
        }
        
        #updateQRModal .form-control {
            margin-bottom: 15px !important;
        }
        
        #updateQRModal input[type="file"] {
            padding: 12px !important;
            border: 2px dashed #ced4da !important;
            background: #f8f9fa !important;
        }
        
        #updateInfoModal input[type="file"]:focus {
            border-color: #80bdff !important;
            background: #ffffff !important;
        }
        
        #updateInfoModal .form-control {
            margin-bottom: 20px !important;
        }
        
        #updateInfoModal .form-control:focus {
            border-color: #80bdff !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Ensure QR code directory exists and is writable */
        .qr-upload-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .qr-upload-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .qr-upload-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Fix all form inputs globally */
        .form-control {
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            z-index: 1 !important;
            position: relative !important;
        }
        
        .modal .form-control {
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            z-index: 1052 !important;
            position: relative !important;
        }
        
        .modal-body {
            pointer-events: auto !important;
            z-index: 1051 !important;
            position: relative !important;
        }
        
        .modal-content {
            pointer-events: auto !important;
            z-index: 1052 !important;
            position: relative !important;
        }
        
        .modal-dialog {
            pointer-events: auto !important;
            z-index: 1052 !important;
            position: relative !important;
        }
        
        /* Remove any overlays that might block inputs */
        .modal-backdrop {
            pointer-events: none !important;
            background-color: rgba(0, 0, 0, 0.3) !important;
        }
        
        /* Ensure modals don't darken screen too much */
        .modal {
            backdrop-filter: blur(5px) !important;
        }
        
        body:not(.dark-theme) .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.3) !important;
        }
        
        body.dark-theme .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.4) !important;
        }
        
        button, input, select, textarea {
            pointer-events: auto !important;
        }
        
        /* CRITICAL FIX: Force visible text in all form inputs */
        #updateInfoModal .form-control,
        #updateQRModal .form-control,
        .modal .form-control {
            background-color: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #ced4da !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        #updateInfoModal .form-control:focus,
        #updateQRModal .form-control:focus,
        .modal .form-control:focus {
            background-color: #ffffff !important;
            color: #000000 !important;
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        #updateInfoModal .form-control::placeholder,
        #updateQRModal .form-control::placeholder,
        .modal .form-control::placeholder {
            color: #6c757d !important;
            -webkit-text-fill-color: #6c757d !important;
        }
        
        /* Override any dark theme styles for modals */
        body.dark-theme #updateInfoModal .form-control,
        body.dark-theme #updateQRModal .form-control,
        body.dark-theme .modal .form-control {
            background-color: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        body.dark-theme #updateInfoModal .form-control:focus,
        body.dark-theme #updateQRModal .form-control:focus,
        body.dark-theme .modal .form-control:focus {
            background-color: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Fix Bootstrap modal conflicts */
        .modal.show {
            pointer-events: auto !important;
        }
        
        .modal {
            pointer-events: auto !important;
        }
    </style>
</head>
<body class="dark-theme">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.svg" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Aditya Boys Hostel</h3>
                <p class="text-white-50 mb-0">Admin Portal</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_students.php" class="sidebar-menu-item">
                    <i class="fas fa-users"></i> Manage Students
                </a>
                <a href="manage_rooms.php" class="sidebar-menu-item">
                    <i class="fas fa-bed"></i> Room Management
                </a>
                <a href="manage_fees.php" class="sidebar-menu-item">
                    <i class="fas fa-rupee-sign"></i> Fee Management
                </a>
                <a href="payment_verification.php" class="sidebar-menu-item">
                    <i class="fas fa-credit-card"></i> Payment Verification
                </a>
                <a href="payment_history.php" class="sidebar-menu-item">
                    <i class="fas fa-history"></i> Payment History
                </a>
                <a href="payment_info.php" class="sidebar-menu-item active">
                    <i class="fas fa-info-circle"></i> Payment Information
                </a>
                <a href="manage_complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comment-dots"></i> Manage Complaints
                </a>
                <a href="manage_notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </a>
                <a href="../logout.php" class="sidebar-menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="top-bar-title">Payment Information</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Admin" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger animate__animated animate__fadeInDown" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Payment Statistics -->
                <div class="payment-stats">
                    <div class="stat-card animate__animated animate__fadeInUp">
                        <i class="fas fa-qrcode fa-2x mb-2"></i>
                        <div class="stat-number"><?php echo $payment_info ? 'Active' : 'Not Set'; ?></div>
                        <div>QR Code Status</div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                        <i class="fas fa-phone fa-2x mb-2"></i>
                        <div class="stat-number"><?php echo htmlspecialchars($payment_info['phone_number'] ?? 'Not Set'); ?></div>
                        <div>Contact Number</div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                        <i class="fas fa-wallet fa-2x mb-2"></i>
                        <div class="stat-number"><?php echo htmlspecialchars($payment_info['upi_id'] ?? 'Not Set'); ?></div>
                        <div>UPI ID</div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <div class="stat-number"><?php echo (!empty($payment_info) && !empty($payment_info['updated_at'])) ? date('M d, Y', strtotime($payment_info['updated_at'])) : 'Never'; ?></div>
                        <div>Last Updated</div>
                    </div>
                </div>
                
                <!-- Current Payment Information -->
                <div class="payment-info-container">
                    <h3 class="mb-4"><i class="fas fa-info-circle me-2"></i>Current Payment Information</h3>
                    
                    <div class="info-card animate__animated animate__fadeInUp">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="qr-code-container">
                                    <h5 class="qr-code-title"><i class="fas fa-qrcode me-2"></i>QR Code</h5>
                                    <?php if ($payment_info && $payment_info['qr_code_path']): ?>
                                        <?php 
                                        // Check if file exists and construct proper path
                                        $qr_path = $payment_info['qr_code_path'];
                                        $full_path = '../' . $qr_path;
                                        $display_path = '../' . $qr_path;
                                        
                                        // Add cache buster if recently updated
                                        if (isset($cache_buster)) {
                                            $display_path .= $cache_buster;
                                        }
                                        
                                        // Check file existence
                                        if (!file_exists($full_path)) {
                                            $display_path = '../assets/images/default-qr.png';
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($display_path); ?>" 
                                             alt="Current QR Code" 
                                             class="qr-preview mb-3"
                                             onerror="this.src='../assets/images/default-qr.png'">
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-qrcode fa-4x text-muted mb-3"></i>
                                            <p class="text-muted">No QR code uploaded</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <h5 class="mt-3"><i class="fas fa-mobile-alt me-2"></i>Payment Details</h5>
                                <table class="table table-striped payment-info-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">UPI ID:</th>
                                            <td><?php echo htmlspecialchars($payment_info['upi_id'] ?? 'Not Set'); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Phone Number:</th>
                                            <td><?php echo htmlspecialchars($payment_info['phone_number'] ?? 'Not Set'); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Bank Name:</th>
                                            <td><?php echo htmlspecialchars($payment_info['bank_name'] ?? 'Not Set'); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Account Number:</th>
                                            <td><?php echo htmlspecialchars($payment_info['account_number'] ?? 'Not Set'); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">IFSC Code:</th>
                                            <td><?php echo htmlspecialchars($payment_info['ifsc_code'] ?? 'Not Set'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <h5><i class="fas fa-edit me-2"></i>Quick Actions</h5>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-update" data-bs-toggle="modal" data-bs-target="#updateInfoModal">
                                        <i class="fas fa-edit me-1"></i>Update Payment Info
                                    </button>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateQRModal">
                                        <i class="fas fa-qrcode me-1"></i>Update QR Code
                                    </button>
                                    <a href="payment_verification.php" class="btn btn-info">
                                        <i class="fas fa-credit-card me-1"></i>View Payments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Payment Info Modal -->
    <div class="modal fade" id="updateInfoModal" tabindex="-1" aria-labelledby="updateInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateInfoModalLabel">Update Payment Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_payment_info">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="upi_id" class="form-label">UPI ID *</label>
                                    <input type="text" class="form-control" id="upi_id" name="upi_id" 
                                           value="<?php echo htmlspecialchars($payment_info['upi_id'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number *</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                           value="<?php echo htmlspecialchars($payment_info['phone_number'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                           value="<?php echo htmlspecialchars($payment_info['bank_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" 
                                           value="<?php echo htmlspecialchars($payment_info['account_number'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" 
                                           value="<?php echo htmlspecialchars($payment_info['ifsc_code'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" 
                                           value="<?php echo htmlspecialchars($payment_info['ifsc_code'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-update">
                            <i class="fas fa-save me-1"></i>Update Information
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update QR Code Modal -->
    <div class="modal fade" id="updateQRModal" tabindex="-1" aria-labelledby="updateQRModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateQRModalLabel">Update QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_qr">
                        
                        <div class="mb-3">
                            <label for="qr_code" class="form-label">QR Code Image *</label>
                            <input type="file" class="form-control" id="qr_code" name="qr_code" accept="image/*" required>
                            <div class="form-text">Supported formats: JPG, PNG, GIF (Max 5MB)</div>
                        </div>
                        
                        <?php if ($payment_info && $payment_info['qr_code_path']): ?>
                        <div class="alert alert-info text-center">
                            <strong>Current QR Code:</strong><br>
                            <?php 
                            $current_qr_path = '../' . $payment_info['qr_code_path'];
                            if (isset($cache_buster)) {
                                $current_qr_path .= $cache_buster;
                            }
                            ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars($current_qr_path); ?>" 
                                     alt="Current QR Code" 
                                     class="img-fluid"
                                     style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
                                     onerror="this.src='../assets/images/default-qr.png'">
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>Upload QR Code
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
    // Prevent reload loop and clean URL after showing success message
    document.addEventListener('DOMContentLoaded', function() {
        // Debug: Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
            return;
        }
        
        console.log('Bootstrap loaded successfully');
        
        // Check if URL has success parameter
        if (window.location.search.includes('success=qr_updated')) {
            // Remove success parameter from URL after 3 seconds
            setTimeout(function() {
                const url = new URL(window.location);
                url.searchParams.delete('success');
                window.history.replaceState({}, document.title, url);
            }, 3000);
        }
        
        // Force enable all form inputs
        function enableAllInputs() {
            const inputs = document.querySelectorAll('input, select, textarea, button');
            inputs.forEach(input => {
                input.style.pointerEvents = 'auto';
                input.style.userSelect = 'auto';
                input.style.webkitUserSelect = 'auto';
                input.style.mozUserSelect = 'auto';
                input.style.msUserSelect = 'auto';
                input.removeAttribute('disabled');
                input.removeAttribute('readonly');
            });
        }
        
        // Initialize modals manually for Quick Actions buttons
        const updateInfoBtn = document.querySelector('[data-bs-target="#updateInfoModal"]');
        const updateQRBtn = document.querySelector('[data-bs-target="#updateQRModal"]');
        
        console.log('Update Info Button:', updateInfoBtn);
        console.log('Update QR Button:', updateQRBtn);
        
        if (updateInfoBtn) {
            updateInfoBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Update Info button clicked');
                const modalElement = document.getElementById('updateInfoModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    // Enable inputs after modal is shown
                    setTimeout(enableAllInputs, 100);
                } else {
                    console.error('updateInfoModal not found');
                }
            });
        }
        
        if (updateQRBtn) {
            updateQRBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Update QR button clicked');
                const modalElement = document.getElementById('updateQRModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    // Enable inputs after modal is shown
                    setTimeout(enableAllInputs, 100);
                } else {
                    console.error('updateQRModal not found');
                }
            });
        }
        
        // Enable all inputs on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(enableAllInputs, 500);
        });
        
        // Enable inputs when any modal is shown
        document.addEventListener('shown.bs.modal', function() {
            setTimeout(enableAllInputs, 100);
        });
        
        // Initialize all Bootstrap modals
        const modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'));
        console.log('Modal triggers found:', modalTriggerList.length);
        
        modalTriggerList.forEach(function (modalTriggerEl) {
            modalTriggerEl.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-bs-target');
                console.log('Modal trigger clicked:', target);
                const modalElement = document.querySelector(target);
                if (modalElement) {
                    // Force modal brightness fix
                    setTimeout(() => {
                        const modalContent = modalElement.querySelector('.modal-content');
                        const modalBody = modalElement.querySelector('.modal-body');
                        const modalHeader = modalElement.querySelector('.modal-header');
                        
                        if (modalContent) {
                            modalElement.style.opacity = '1';
                            modalContent.style.opacity = '1';
                            modalContent.style.backgroundColor = '#ffffff';
                            modalContent.style.color = '#495057';
                        }
                        if (modalBody) {
                            modalBody.style.backgroundColor = '#ffffff';
                            modalBody.style.color = '#495057';
                        }
                        if (modalHeader) {
                            modalHeader.style.backgroundColor = '#f8f9fa';
                            modalHeader.style.color = '#495057';
                        }
                    }, 100);
                    
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.error('Modal element not found:', target);
                }
            });
        });
    </script>
</body>
</html>
