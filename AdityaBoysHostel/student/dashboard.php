<?php
require_once '../config/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get student information
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_complaint'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    
    // Valid categories from database schema
    $valid_categories = ['maintenance', 'cleaning', 'food', 'security', 'other'];
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($category)) {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($category, $valid_categories)) {
        $error = "Invalid category selected.";
    } elseif (strlen($title) < 5) {
        $error = "Complaint title must be at least 5 characters long.";
    } elseif (strlen($description) < 20) {
        $error = "Complaint description must be at least 20 characters long.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO complaints (student_id, title, description, category) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $_SESSION['student_id'], $title, $description, $category);
            
            if ($stmt->execute()) {
                // Create admin notification for new complaint
                try {
                    // Get the actual admin ID from database (use the first active admin)
                    $admin_stmt = $conn->prepare("SELECT id FROM admins WHERE is_active = TRUE LIMIT 1");
                    $admin_stmt->execute();
                    $admin_result = $admin_stmt->get_result();
                    $admin_row = $admin_result->fetch_assoc();
                    $admin_id = $admin_row['id'];
                    
                    if ($admin_id) {
                        $notification_title = "New Complaint Submitted";
                        $notification_message = "Complaint: {$title} by {$student['full_name']} ({$student['email']})";
                        
                        $stmt = $conn->prepare("
                            INSERT INTO notifications (user_type, user_id, title, message, type, is_read) 
                            VALUES ('admin', ?, ?, ?, 'info', FALSE)
                        ");
                        $stmt->bind_param("iss", $admin_id, $notification_title, $notification_message);
                        $stmt->execute();
                        
                        // Create student notification for complaint submission confirmation
                        $student_notification_title = "Complaint Submitted Successfully";
                        $student_notification_message = "Your complaint '{$title}' has been submitted and will be reviewed by the administration.";
                        
                        $stmt = $conn->prepare("
                            INSERT INTO notifications (user_type, user_id, title, message, type, is_read) 
                            VALUES ('student', ?, ?, ?, 'success', FALSE)
                        ");
                        $stmt->bind_param("iss", $_SESSION['student_id'], $student_notification_title, $student_notification_message);
                        $stmt->execute();
                    }
                } catch (Exception $e) {
                    error_log("Failed to create complaint notification: " . $e->getMessage());
                }
                
                $success = "Complaint submitted successfully!";
                error_log("Complaint inserted successfully for student ID: " . $_SESSION['student_id']);
            } else {
                $error = "Error submitting complaint. Please try again.";
                error_log("Database error: " . $stmt->error);
            }
        } catch (Exception $e) {
            $error = "Error submitting complaint. Please try again.";
            error_log("Exception: " . $e->getMessage());
        }
    }
}

// Get student's complaints
$complaints = [];
$stmt = $conn->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}

// Get student's fees
$fees = [];
$stmt = $conn->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY year DESC, month DESC");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fees[] = $row;
}

// Get notifications (for dropdown - limited to 5)
$notifications = [];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_type = 'student' AND user_id = ? AND is_read = FALSE ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Get total unread notifications count (for badge)
$unread_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_type = 'student' AND user_id = ? AND is_read = FALSE");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$count_row = $result->fetch_assoc();
$unread_count = $count_row['count'];

// Get room information if allocated
$room_info = null;
if ($student['room_id']) {
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $student['room_id']);
    $stmt->execute();
    $room_info = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        html {
            scroll-behavior: smooth;
        }
        
        /* Enhanced Quick Pay Button Styling */
        .quick-pay-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 1.5rem 2rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            min-height: 80px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .quick-pay-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .quick-pay-btn:hover::before {
            left: 100%;
        }
        
        .quick-pay-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #1ea085 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.6);
        }
        
        .quick-pay-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        
        .quick-pay-btn i {
            font-size: 1.4rem;
            animation: pulse 2s infinite;
        }
        
        .quick-pay-text {
            position: relative;
            z-index: 1;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        #paymentModal .form-check:hover {
            border-color: #007bff !important;
            background: #e3f2fd !important;
            transform: none !important;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15) !important;
        }
        
        #paymentModal .form-check-input:checked + .form-check-label {
            color: #007bff !important;
            font-weight: 600 !important;
        }
        
        #paymentModal .form-check-input:checked ~ .form-check {
            border-color: #007bff !important;
            background: #e3f2fd !important;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2) !important;
        }
        
        #paymentModal .form-check-label {
            color: #333333 !important;
            font-weight: 500 !important;
            margin: 0 !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            font-size: 0.9rem !important;
        }
        
        #paymentModal .form-check-input {
            opacity: 0 !important;
            position: absolute !important;
        }
        
        /* QR Code Section Optimization */
        #paymentModal .qr-section {
            text-align: center !important;
            padding: 0.75rem !important;
            background: #ffffff !important;
            border-radius: 8px !important;
            border: 1px solid #e9ecef !important;
            margin-bottom: 0.5rem !important;
        }
        
        #paymentModal #paymentQRCodeMain {
            max-width: 120px !important;
            max-height: 120px !important;
            width: 100% !important;
            height: auto !important;
            border-radius: 8px !important;
            border: 2px solid #007bff !important;
            object-fit: cover !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        #paymentModal .qr-text {
            font-size: 0.7rem !important;
            color: #333333 !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0 !important;
            line-height: 1.2 !important;
        }
        
        /* Enhanced QR Code Display for Main Payment Form */
        #paymentQRCodeDisplay {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            min-height: 200px !important;
        }
        
        #paymentQRCodeImage {
            max-width: 200px !important;
            max-height: 200px !important;
            width: 100% !important;
            height: auto !important;
            border-radius: 12px !important;
            border: 3px solid #28a745 !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2) !important;
            object-fit: contain !important;
            transition: all 0.3s ease !important;
        }
        
        #paymentQRCodeImage:hover {
            transform: scale(1.05) !important;
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3) !important;
        }
        
        /* Fee Cards Optimization */
        #paymentModal .fee-card {
            background: #f8f9fa !important;
            border: 2px solid #e9ecef !important;
            border-radius: 6px !important;
            padding: 0.75rem !important;
            margin-bottom: 0.5rem !important;
            cursor: pointer !important;
            transition: none !important;
            min-height: 80px !important;
            display: flex !important;
            align-items: center !important;
            transform: none !important;
            height: fit-content !important;
        }
        
        #paymentModal .fee-card:hover {
            border-color: #007bff !important;
            background: #e3f2fd !important;
            transform: none !important;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15) !important;
        }
        
        #paymentModal .fee-card.selected {
            border-color: #007bff !important;
            background: #007bff !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3) !important;
        }
        
        #paymentModal .fee-card .card-title {
            font-size: 0.75rem !important;
            margin-bottom: 0.25rem !important;
            line-height: 1.1 !important;
        }
        
        #paymentModal .fee-amount {
            font-size: 0.7rem !important;
            margin-bottom: 0.25rem !important;
            color: #333333 !important;
        }
        
        #paymentModal .fee-due {
            font-size: 0.65rem !important;
            color: #333333 !important;
        }
        
        #paymentModal .fee-card.selected .fee-amount,
        #paymentModal .fee-card.selected .fee-due {
            color: white !important;
        }
        
        /* Enhanced Payment Form Container */
        #paymentModal .payment-form-section .d-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
            margin-top: 1.5rem !important;
            position: relative !important;
            z-index: 50 !important;
        }
        
        #paymentModal .payment-form-section .d-grid .btn {
            justify-self: center !important;
            position: relative !important;
            z-index: 102 !important;
        }
        
        #paymentModal .btn-primary:hover {
            transform: none !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4) !important;
        }
        
        #paymentModal .btn-outline-secondary {
            background: transparent !important;
            border: 1px solid #6c757d !important;
            color: #6c757d !important;
            font-size: 0.8rem !important;
            padding: 0.25rem 0.5rem !important;
            transform: none !important;
            transition: none !important;
        }
        
        #paymentModal .btn-outline-secondary:hover {
            background: #6c757d !important;
            color: #ffffff !important;
        }
        
        #paymentModal .alert {
            color: #333333 !important;
            font-size: 0.85rem !important;
        }
        
        #paymentModal .alert-info {
            background-color: #d1ecf1 !important;
            border-color: #bee5eb !important;
        }
        
        #paymentModal .table {
            margin-bottom: 0 !important;
            font-size: 0.85rem !important;
        }
        
        #paymentModal .table th {
            background: #f8f9fa !important;
            padding: 0.5rem !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #333333 !important;
            border: 1px solid #dee2e6 !important;
        }
        
        #paymentModal .table td {
            background: #ffffff !important;
            padding: 0.5rem !important;
            font-size: 0.85rem !important;
            color: #333333 !important;
            border: 1px solid #dee2e6 !important;
        }
        
        #paymentModal p,
        #paymentModal strong,
        #paymentModal small {
            color: #333333 !important;
            font-size: 0.85rem !important;
        }
        
        #paymentModal .text-muted {
            color: #6c757d !important;
            font-size: 0.8rem !important;
        }
        
        #paymentModal .card-body {
            padding: 2rem !important;
            background: #f8f9fa !important;
            color: #333333 !important;
        }
        
        #paymentModal .card-title {
            color: #333333 !important;
            font-weight: 600 !important;
            margin-bottom: 1.25rem !important;
            font-size: 1.1rem !important;
        }
        
        #paymentModal .form-label {
            color: #333333 !important;
            font-weight: 500 !important;
            margin-bottom: 0.75rem !important;
            font-size: 0.95rem !important;
        }
        
        #paymentModal .form-control {
            background: #ffffff !important;
            border: 1px solid #ced4da !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            font-size: 0.95rem !important;
            padding: 0.75rem 1rem !important;
        }
        
        #paymentModal .form-control:focus {
            background: #ffffff !important;
            border-color: #007bff !important;
            color: #333333 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            outline: none !important;
        }
        
        #paymentModal .form-select {
            background: #ffffff !important;
            border: 1px solid #ced4da !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            font-size: 0.95rem !important;
            padding: 0.75rem 1rem !important;
        }
        
        #paymentModal .form-select:focus {
            background: #ffffff !important;
            border-color: #007bff !important;
            color: #333333 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            outline: none !important;
        }
        
        #paymentModal .form-select option {
            background: #ffffff !important;
            color: #333333 !important;
            font-size: 0.95rem !important;
        }
        
        #paymentModal .form-control::placeholder {
            color: #6c757d !important;
            background: transparent !important;
        }
        
        #paymentModal .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border: none !important;
            transition: all 0.3s ease !important;
            color: white !important;
            font-size: 0.95rem !important;
            padding: 0.75rem 1.5rem !important;
        }
        
        #paymentModal .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4) !important;
        }
        
        #paymentModal .text-white {
            color: #333333 !important;
        }
        
        #paymentModal p, #paymentModal strong {
            color: #333333 !important;
            font-size: 0.95rem !important;
        }
        
        #paymentModal .alert {
            color: #333333 !important;
            font-size: 0.9rem !important;
        }
        
        /* Ensure modal is fully interactive */
        #paymentModal {
            z-index: 1055 !important;
        }
        
        #paymentModal .modal-backdrop {
            z-index: 1050 !important;
        }
        
        /* Responsive Design for Payment Modal - Optimized for Payment Info Display */
        @media (max-width: 1400px) {
            #paymentModal .modal-dialog {
                max-width: 92vw !important;
                width: 92vw !important;
                margin: 1.5vh auto !important;
            }
            
            #paymentModal .modal-body {
                padding: 1.25rem !important;
                max-height: calc(85vh - 70px) !important;
            }
        }
        
        @media (max-width: 1200px) {
            #paymentModal .modal-dialog {
                max-width: 94vw !important;
                width: 94vw !important;
                margin: 1vh auto !important;
            }
            
            #paymentModal .modal-body {
                padding: 1rem !important;
                max-height: calc(85vh - 65px) !important;
            }
        }
        
        @media (max-width: 992px) {
            #paymentModal .modal-dialog {
                max-width: 96vw !important;
                width: 96vw !important;
                margin: 0.5vh auto !important;
            }
            
            #paymentModal .modal-header {
                padding: 0.75rem 1rem !important;
            }
            
            #paymentModal .modal-body {
                padding: 0.875rem !important;
                max-height: calc(87vh - 60px) !important;
            }
        }
        
        @media (max-width: 768px) {
            #paymentModal .modal-dialog {
                max-width: 98vw !important;
                width: 98vw !important;
                margin: 0.25vh auto !important;
            }
            
            #paymentModal .modal-header {
                padding: 0.625rem 0.875rem !important;
            }
            
            #paymentModal .modal-body {
                padding: 0.75rem !important;
                max-height: calc(88vh - 55px) !important;
            }
            
            #paymentModal .payment-info-section .table th,
            #paymentModal .payment-info-section .table td {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.8rem !important;
            }
        }
        
        @media (max-width: 576px) {
            #paymentModal .modal-dialog {
                max-width: 99vw !important;
                width: 99vw !important;
                margin: 0.125vh auto !important;
            }
            
            #paymentModal .modal-header {
                padding: 0.5rem 0.75rem !important;
            }
            
            #paymentModal .modal-body {
                padding: 0.625rem !important;
                max-height: calc(89vh - 50px) !important;
            }
            
            #paymentModal .payment-info-section .table th,
            #paymentModal .payment-info-section .table td {
                padding: 0.375rem 0.625rem !important;
                font-size: 0.75rem !important;
            }
            
            #paymentModal .payment-info-section h6 {
                font-size: 0.9rem !important;
            }
        }
        
        /* Dark Mode Support for Payment Modal */
        [data-theme="dark"] #paymentModal .modal-content {
            background: #1a1a1a !important;
            border: 1px solid #333333 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .modal-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%) !important;
            color: white !important;
            border-bottom: 1px solid #333333 !important;
        }
        
        [data-theme="dark"] #paymentModal .modal-body {
            background: #1a1a1a !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .card {
            background: #2d3748 !important;
            border: 1px solid #4a5568 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .card-body {
            background: #2d3748 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .card-title {
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .form-label {
            color: #e2e8f0 !important;
        }
        
        [data-theme="dark"] #paymentModal .form-control {
            background: #2d3748 !important;
            border: 1px solid #4a5568 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .form-control:focus {
            background: #2d3748 !important;
            border-color: #4299e1 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25) !important;
        }
        
        [data-theme="dark"] #paymentModal .form-select {
            background: #2d3748 !important;
            border: 1px solid #4a5568 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .form-select:focus {
            background: #2d3748 !important;
            border-color: #4299e1 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25) !important;
        }
        
        [data-theme="dark"] #paymentModal .form-select option {
            background: #2d3748 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .form-control::placeholder {
            color: #a0aec0 !important;
        }
        
        [data-theme="dark"] #paymentModal p, 
        [data-theme="dark"] #paymentModal strong {
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .alert {
            background: #2d3748 !important;
            border: 1px solid #4a5568 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #paymentModal .btn-primary {
            background: linear-gradient(135deg, #4299e1 0%, #2b6cb0 100%) !important;
            color: white !important;
        }
        
        [data-theme="dark"] #paymentModal .btn-outline-secondary {
            border-color: #4a5568 !important;
            color: #e2e8f0 !important;
        }
        
        [data-theme="dark"] #paymentModal .btn-outline-secondary:hover {
            background: #4a5568 !important;
            color: #ffffff !important;
        }
        
        /* Complaint Modal Styling */
        #complaintModal .modal-dialog {
            max-width: 600px !important;
            width: 90vw !important;
            margin: 1.75rem auto !important;
        }
        
        #complaintModal .modal-content {
            background: #ffffff !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
            color: #333333 !important;
        }
        
        #complaintModal .modal-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            color: white !important;
            border-bottom: 1px solid #dee2e6 !important;
            padding: 1rem 1.5rem !important;
        }
        
        #complaintModal .modal-body {
            padding: 2rem !important;
            max-height: 80vh !important;
            overflow-y: auto !important;
            background: #ffffff !important;
            color: #333333 !important;
        }
        
        #complaintModal .form-label {
            color: #333333 !important;
            font-weight: 500 !important;
            margin-bottom: 0.5rem !important;
        }
        
        #complaintModal .form-control {
            background: #ffffff !important;
            border: 1px solid #ced4da !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
        }
        
        #complaintModal .form-control:focus {
            background: #ffffff !important;
            border-color: #007bff !important;
            color: #333333 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            outline: none !important;
        }
        
        #complaintModal .form-select {
            background: #ffffff !important;
            border: 1px solid #ced4da !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
        }
        
        #complaintModal .form-select:focus {
            background: #ffffff !important;
            border-color: #007bff !important;
            color: #333333 !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            outline: none !important;
        }
        
        #complaintModal .form-select option {
            background: #ffffff !important;
            color: #333333 !important;
        }
        
        #complaintModal .form-control::placeholder {
            color: #6c757d !important;
            background: transparent !important;
        }
        
        #complaintModal .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border: none !important;
            transition: all 0.3s ease !important;
            color: white !important;
        }
        
        #complaintModal .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4) !important;
        }
        
        /* Complaint Modal Dark Mode Support */
        [data-theme="dark"] #complaintModal .modal-content {
            background: #1a1a1a !important;
            border: 1px solid #333333 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #complaintModal .modal-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%) !important;
            color: white !important;
            border-bottom: 1px solid #333333 !important;
        }
        
        [data-theme="dark"] #complaintModal .modal-body {
            background: #1a1a1a !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #complaintModal .form-label {
            color: #e2e8f0 !important;
        }
        
        [data-theme="dark"] #complaintModal .form-control {
            background: #2d3748 !important;
            border: 1px solid #4a5568 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #complaintModal .form-control:focus {
            background: #2d3748 !important;
            border-color: #4299e1 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25) !important;
        }
        
        [data-theme="dark"] #complaintModal .form-select {
            background: #2d3748 !important;
            border: 1px solid #4a5568 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #complaintModal .form-select:focus {
            background: #2d3748 !important;
            border-color: #4299e1 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25) !important;
        }
        
        [data-theme="dark"] #complaintModal .form-select option {
            background: #2d3748 !important;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] #complaintModal .form-control::placeholder {
            color: #a0aec0 !important;
        }
        
        [data-theme="dark"] #complaintModal .btn-primary {
            background: linear-gradient(135deg, #4299e1 0%, #2b6cb0 100%) !important;
            color: white !important;
        }
        
        /* Responsive adjustments for complaint modal */
        @media (max-width: 768px) {
            #complaintModal .modal-dialog {
                max-width: 95vw !important;
                margin: 0.5rem auto !important;
            }
            
            #complaintModal .modal-body {
                padding: 1rem !important;
            }
        }
        
        /* Force complaint modal inputs to be fully interactive */
        #complaintModal .form-control,
        #complaintModal .form-select,
        #complaintModal textarea {
            pointer-events: auto !important;
            user-select: text !important;
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            z-index: 1050 !important;
            position: relative !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        #complaintModal .form-control:focus,
        #complaintModal .form-select:focus,
        #complaintModal textarea:focus {
            pointer-events: auto !important;
            user-select: text !important;
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            z-index: 1051 !important;
        }
        
        #complaintModal .modal-body {
            pointer-events: auto !important;
            z-index: auto !important;
            position: relative !important;
        }
        
        #complaintModal .modal-content {
            pointer-events: auto !important;
            z-index: 1051 !important;
            position: relative !important;
        }
        
        #complaintModal .modal-dialog {
            pointer-events: auto !important;
            z-index: 1051 !important;
            position: relative !important;
        }
        
        /* Fix any overlay issues */
        #complaintModal.modal.show {
            pointer-events: auto !important;
        }
        
        #complaintModal .modal-backdrop {
            z-index: 1049 !important;
        }
        
        /* CRITICAL FIX: Force visible text in all payment modal form inputs */
        #paymentModal .form-control,
        #paymentModal .form-select,
        #paymentModal textarea {
            background-color: #ffffff !important;
            color: #333333 !important;
            border: 1px solid #ced4da !important;
            -webkit-text-fill-color: #333333 !important;
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            z-index: 1052 !important;
            position: relative !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        #paymentModal .form-control:focus,
        #paymentModal .form-select:focus,
        #paymentModal textarea:focus {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            -webkit-text-fill-color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        #paymentModal .form-control::placeholder,
        #paymentModal .form-select::placeholder,
        #paymentModal textarea::placeholder {
            color: #6c757d !important;
            -webkit-text-fill-color: #6c757d !important;
            opacity: 1 !important;
        }
        
        /* Override any dark theme styles for payment modal */
        [data-theme="dark"] #paymentModal .form-control,
        [data-theme="dark"] #paymentModal .form-select,
        [data-theme="dark"] #paymentModal textarea {
            background-color: #ffffff !important;
            color: #333333 !important;
            -webkit-text-fill-color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        [data-theme="dark"] #paymentModal .form-control:focus,
        [data-theme="dark"] #paymentModal .form-select:focus,
        [data-theme="dark"] #paymentModal textarea:focus {
            background-color: #ffffff !important;
            color: #333333 !important;
            -webkit-text-fill-color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Fix payment info display fields (readonly inputs) */
        #paymentModal input[readonly] {
            background-color: #f8f9fa !important;
            color: #333333 !important;
            -webkit-text-fill-color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Ensure all text in payment modal is visible */
        #paymentModal .card-title,
        #paymentModal .card-text,
        #paymentModal .form-label,
        #paymentModal .text-muted,
        #paymentModal .text-dark,
        #paymentModal .text-primary,
        #paymentModal .text-success,
        #paymentModal .text-danger,
        #paymentModal .text-warning,
        #paymentModal .text-info,
        #paymentModal p,
        #paymentModal span,
        #paymentModal strong,
        #paymentModal small,
        #paymentModal h1,
        #paymentModal h2,
        #paymentModal h3,
        #paymentModal h4,
        #paymentModal h5,
        #paymentModal h6 {
            color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
            -webkit-text-fill-color: #333333 !important;
        }
        
        /* Fix radio button labels */
        #paymentModal .form-check-label {
            color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
            -webkit-text-fill-color: #333333 !important;
        }
        
        /* Fix table text */
        #paymentModal .table th,
        #paymentModal .table td {
            color: #333333 !important;
            opacity: 1 !important;
            visibility: visible !important;
            -webkit-text-fill-color: #333333 !important;
        }
        
        /* Remove any sliding animations that might hide text */
        #paymentModal .animate__animated,
        #paymentModal .animate__fadeInUp,
        #paymentModal .animate__fadeInDown {
            animation: none !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .animate__fadeInUp {
            animation-name: fadeInUp;
        }
        
        #complaintModal {
            z-index: 1050 !important;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.svg" alt="Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Aditya Boys Hostel</h3>
                <p class="text-white-50 mb-0">Student Portal</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="profile.php" class="sidebar-menu-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="fees.php" class="sidebar-menu-item">
                    <i class="fas fa-rupee-sign"></i> My Fees
                </a>
                <a href="complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comments"></i> My Complaints
                </a>
                <a href="notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-bell"></i> My Notifications
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
                <h1 class="top-bar-title">Dashboard</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['student_name']; ?></span>
                    <?php 
                    $top_avatar_path = '../uploads/' . ($student['profile_photo'] ?? '');
                    $top_default_avatar = '../assets/default_avatar.svg';
                    
                    // Check if profile photo exists and is not empty
                    if (!empty($student['profile_photo']) && file_exists($top_avatar_path)) {
                        echo '<img src="' . htmlspecialchars($top_avatar_path) . '" alt="Student" class="user-avatar">';
                    } else {
                        echo '<img src="' . $top_default_avatar . '" alt="Student" class="user-avatar">';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger animate__animated animate__fadeInDown" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Welcome Card -->
                <div class="dashboard-card animate__animated animate__fadeInUp mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <?php 
                            $profile_photo_path = '../uploads/' . ($student['profile_photo'] ?? '');
                            $default_avatar = '../assets/default_avatar.svg';
                            
                            // Check if profile photo exists and is not empty
                            if (!empty($student['profile_photo']) && file_exists($profile_photo_path)) {
                                echo '<img src="' . htmlspecialchars($profile_photo_path) . '" alt="Profile" class="rounded-circle" width="80" height="80">';
                            } else {
                                echo '<img src="' . $default_avatar . '" alt="Profile" class="rounded-circle" width="80" height="80">';
                            }
                            ?>
                        </div>
                        <div class="col-md-10">
                            <h3>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</h3>
                            <p class="text-muted mb-0">
                                <?php if ($room_info): ?>
                                    Room: <?php echo htmlspecialchars($room_info['room_number']); ?> - Bed <?php echo $student['bed_number']; ?>
                                <?php else: ?>
                                    Room not allocated yet
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-bed"></i>
                            </div>
                            <div class="dashboard-card-value">
                                <?php echo $room_info ? htmlspecialchars($room_info['room_number']) : 'N/A'; ?>
                            </div>
                            <div class="dashboard-card-label">Room Number</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <div class="dashboard-card-icon pink">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo count($fees); ?></div>
                            <div class="dashboard-card-label">Total Fees</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                            <div class="dashboard-card-icon green">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo count($complaints); ?></div>
                            <div class="dashboard-card-label">My Complaints</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="dashboard-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                            <div class="dashboard-card-icon blue">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="dashboard-card-value"><?php echo $unread_count; ?></div>
                            <div class="dashboard-card-label">Notifications</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp">
                            <h5 class="mb-3">Recent Complaints</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recent_complaints = array_slice($complaints, 0, 5);
                                        foreach ($recent_complaints as $complaint): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'badge-danger',
                                                    'in_progress' => 'badge-warning',
                                                    'resolved' => 'badge-success'
                                                ];
                                                $status_class = isset($status_colors[$complaint['status']]) ? $status_colors[$complaint['status']] : 'badge-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $complaint['status'] ?? 'pending')); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d', strtotime($complaint['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <?php if (empty($complaints)): ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted">No complaints yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                            <h5 class="mb-3">Fee Status</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recent_fees = array_slice($fees, 0, 5);
                                        foreach ($recent_fees as $fee): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fee['month']) . ' ' . $fee['year']; ?></td>
                                            <td>₹<?php echo number_format($fee['amount'], 0); ?></td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'paid' => 'badge-success',
                                                    'unpaid' => 'badge-danger',
                                                    'partial' => 'badge-warning',
                                                    'pending' => 'badge-secondary'
                                                ];
                                                $status_class = isset($status_colors[$fee['status']]) ? $status_colors[$fee['status']] : 'badge-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($fee['status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <?php if (empty($fees)): ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted">No fees records yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                            <h5 class="mb-3">Quick Actions</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="complaints.php" class="btn btn-warning w-100 h-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-comments me-2"></i> My Complaints
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-success w-100 h-100 d-flex align-items-center justify-content-center" onclick="showPaymentForm()">
                                        <i class="fas fa-credit-card me-2"></i> Quick Pay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
    <!-- Complaint Form Section - Direct on Page -->
    <div id="complaint-section" class="container mt-4">
        <div class="table-container animate__animated animate__fadeInUp">
            <h5 class="mb-3"><i class="fas fa-comment-dots me-2"></i>Submit Complaint</h5>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="complaintForm" action="dashboard.php" onsubmit="return validateComplaintForm()">
                                <input type="hidden" name="submit_complaint" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="complaintTitle" class="form-label">Title *</label>
                                            <input type="text" class="form-control" id="complaintTitle" name="title" required 
                                                   placeholder="Enter complaint title">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="complaintCategory" class="form-label">Category *</label>
                                            <select class="form-control" id="complaintCategory" name="category" required>
                                                <option value="">Select category...</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="cleaning">Cleaning</option>
                                                <option value="food">Food</option>
                                                <option value="security">Security</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="complaintDescription" class="form-label">Description *</label>
                                    <textarea class="form-control" id="complaintDescription" name="description" rows="5" required
                                              placeholder="Please provide detailed description of your complaint..."></textarea>
                                    <div class="form-text">Minimum 20 characters required</div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Complaint
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
                                    
                                   <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Simple Payment Form Section -->
    <div id="paymentFormSection" style="display: none;" class="container mt-4">
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-paper-plane me-2"></i>Send Payment Proof</h5>
            
            <!-- Payment Information Display -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scan QR Code</h6>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center align-items-center" style="min-height: 250px;">
                            <div id="paymentQRCodeDisplay" class="text-center">
                                <img id="paymentQRCodeImage" src="" alt="Payment QR Code" style="max-width: 200px; max-height: 200px;" class="img-fluid mx-auto d-block">
                            </div>
                            <small class="text-muted mt-3">Scan this QR code to make payment</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-university me-2"></i>Bank Details</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>UPI ID:</strong></td>
                                    <td id="paymentUPIDisplay">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td id="paymentPhoneDisplay">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Bank Name:</strong></td>
                                    <td id="paymentBankNameDisplay">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Account:</strong></td>
                                    <td id="paymentAccountDisplay">-</td>
                                </tr>
                                <tr>
                                    <td><strong>IFSC:</strong></td>
                                    <td id="paymentIFSCDisplay">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="paymentProofForm" method="POST" action="submit_payment_proof.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="feeSelection">Select Fee to Pay *</label>
                    <select class="form-control" id="feeSelection" name="fee_id" required onchange="updateAmount()">
                        <option value="">-- Select a fee --</option>
                        <?php if (!empty($fees)): ?>
                            <?php foreach ($fees as $fee): ?>
                                <?php $due_amount = $fee['amount'] - $fee['paid_amount']; ?>
                                <?php if ($due_amount > 0): ?>
                                    <option value="<?php echo $fee['id']; ?>" data-amount="<?php echo $due_amount; ?>" data-month="<?php echo htmlspecialchars($fee['month']); ?>" data-year="<?php echo htmlspecialchars($fee['year']); ?>">
                                        <?php echo htmlspecialchars($fee['month']); ?> <?php echo htmlspecialchars($fee['year']); ?> - Due: ₹<?php echo number_format($due_amount, 2); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="paymentAmount">Payment Amount *</label>
                    <input type="number" class="form-control" id="paymentAmount" name="amount" placeholder="Amount to pay" required readonly>
                    <small class="form-text">Amount will be automatically calculated based on selected fee</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="transactionId">Transaction ID *</label>
                            <input type="text" class="form-control" id="transactionId" name="transaction_id" placeholder="Enter transaction ID (max 12 characters)" maxlength="12" pattern="[A-Za-z0-9]{6,12}" title="Transaction ID should contain 6-12 characters, letters and numbers only" oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0, 12)" required>
                            <small class="form-text text-muted">Maximum 12 characters allowed</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_method">Payment Method *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required onchange="updatePaymentDetails()">
                                <option value="">-- Select Payment Method --</option>
                                <option value="upi" selected>UPI Payment</option>
                                <option value="google_pay">Google Pay</option>
                                <option value="phonepe">PhonePe</option>
                                <option value="paytm">Paytm</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                            <small class="form-text">Select your preferred payment method</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="paymentProof">Payment Screenshot</label>
                    <input type="file" class="form-control" id="paymentProof" name="payment_proof" accept="image/*">
                    <small class="form-text">Upload payment confirmation screenshot</small>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>Submit Payment Proof
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hidePaymentForm()">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPaymentForm() {
            document.getElementById('paymentFormSection').style.display = 'block';
            document.getElementById('paymentFormSection').scrollIntoView({ behavior: 'smooth' });
            
            // Set default payment method to UPI if not selected
            const paymentMethodSelect = document.getElementById('payment_method');
            if (!paymentMethodSelect.value) {
                paymentMethodSelect.value = 'upi';
            }
            
            // Load payment info to show QR code and bank details
            loadPaymentInfo();
            
            // Also call updatePaymentDetails to ensure display is updated
            setTimeout(() => {
                updatePaymentDetails();
            }, 500);
        }
        
        function hidePaymentForm() {
            document.getElementById('paymentFormSection').style.display = 'none';
        }
        
        function updateAmount() {
            const feeSelect = document.getElementById('feeSelection');
            const amountInput = document.getElementById('paymentAmount');
            
            if (feeSelect.value && feeSelect.selectedOptions[0]) {
                const selectedOption = feeSelect.selectedOptions[0];
                const amount = selectedOption.getAttribute('data-amount');
                amountInput.value = amount;
                amountInput.readOnly = true;
            } else {
                amountInput.value = '';
                amountInput.readOnly = false;
            }
        }
        
        function updatePaymentDetails() {
            const selectedMethod = document.getElementById('payment_method').value;
            
            // Load payment info to update details based on selected method
            loadPaymentInfo();
        }
        
        // Auto-refresh QR code every 30 seconds to check for updates
        function startQRAutoRefresh() {
            setInterval(() => {
                const selectedMethod = document.getElementById('payment_method').value;
                if (selectedMethod && (selectedMethod === 'upi' || selectedMethod === 'google_pay' || selectedMethod === 'phonepe' || selectedMethod === 'paytm')) {
                    loadPaymentInfo();
                }
            }, 30000); // Check every 30 seconds
        }
        
        function loadPaymentInfo() {
            // Add cache-busting timestamp to force fresh data
            const timestamp = new Date().getTime();
            
            fetch('../admin/get_payment_info.php?t=' + timestamp)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.payment_info) {
                        const paymentMethods = data.payment_info;
                        const selectedMethod = document.getElementById('payment_method').value;
                        
                        // Find the selected payment method
                        const selectedPaymentMethod = paymentMethods.find(method => method.payment_method === selectedMethod);
                        
                        // Find UPI method for QR code display (if UPI-based method is selected)
                        const upiMethod = (selectedMethod === 'upi' || selectedMethod === 'google_pay' || selectedMethod === 'phonepe' || selectedMethod === 'paytm') 
                            ? selectedPaymentMethod 
                            : paymentMethods.find(method => 
                                method.payment_method === 'upi' || 
                                method.payment_method === 'google_pay' || 
                                method.payment_method === 'phonepe' || 
                                method.payment_method === 'paytm'
                            );
                        
                        // Find bank transfer method
                        const bankMethod = selectedMethod === 'bank_transfer' 
                            ? selectedPaymentMethod 
                            : paymentMethods.find(method => method.payment_method === 'bank_transfer');
                        
                        // Update QR Code (show only for UPI-based methods)
                        const qrImage = document.getElementById('paymentQRCodeImage');
                        if (upiMethod && upiMethod.qr_code_path && (selectedMethod === 'upi' || selectedMethod === 'google_pay' || selectedMethod === 'phonepe' || selectedMethod === 'paytm')) {
                            // Add cache-busting timestamp to force image reload
                            const timestamp = new Date().getTime();
                            qrImage.src = '../' + upiMethod.qr_code_path + '?t=' + timestamp;
                            qrImage.style.display = 'block';
                        } else {
                            qrImage.style.display = 'none';
                        }
                        
                        // Update UPI Details (show only for UPI-based methods)
                        const upiDisplay = document.getElementById('paymentUPIDisplay');
                        const phoneDisplay = document.getElementById('paymentPhoneDisplay');
                        
                        if (selectedMethod === 'upi' || selectedMethod === 'google_pay' || selectedMethod === 'phonepe' || selectedMethod === 'paytm') {
                            if (selectedPaymentMethod) {
                                upiDisplay.textContent = selectedPaymentMethod.upi_id || 'Not Available';
                                phoneDisplay.textContent = selectedPaymentMethod.phone_number || 'Not Available';
                            } else {
                                upiDisplay.textContent = 'Not Available';
                                phoneDisplay.textContent = 'Not Available';
                            }
                        } else {
                            upiDisplay.textContent = 'Not Available';
                            phoneDisplay.textContent = 'Not Available';
                        }
                        
                        // Update Bank Details (show for all methods, but primary for bank transfer)
                        const bankNameDisplay = document.getElementById('paymentBankNameDisplay');
                        const accountDisplay = document.getElementById('paymentAccountDisplay');
                        const ifscDisplay = document.getElementById('paymentIFSCDisplay');
                        
                        // Always show bank details from bank_transfer method
                        if (bankMethod) {
                            bankNameDisplay.textContent = bankMethod.bank_name || 'Not Available';
                            accountDisplay.textContent = bankMethod.account_number || 'Not Available';
                            ifscDisplay.textContent = bankMethod.ifsc_code || 'Not Available';
                        } else {
                            bankNameDisplay.textContent = 'Not Available';
                            accountDisplay.textContent = 'Not Available';
                            ifscDisplay.textContent = 'Not Available';
                        }
                    } else {
                        // Hide QR code if not available
                        document.getElementById('paymentQRCodeImage').style.display = 'none';
                        
                        // Show default message
                        const defaultMsg = 'Contact admin for payment details';
                        document.getElementById('paymentUPIDisplay').textContent = defaultMsg;
                        document.getElementById('paymentPhoneDisplay').textContent = defaultMsg;
                        document.getElementById('paymentBankNameDisplay').textContent = defaultMsg;
                        document.getElementById('paymentAccountDisplay').textContent = defaultMsg;
                        document.getElementById('paymentIFSCDisplay').textContent = defaultMsg;
                    }
                })
                .catch(error => {
                    // Show error message
                    const errorMsg = 'Error loading payment info';
                    document.getElementById('paymentUPIDisplay').textContent = errorMsg;
                    document.getElementById('paymentPhoneDisplay').textContent = errorMsg;
                    document.getElementById('paymentBankNameDisplay').textContent = errorMsg;
                    document.getElementById('paymentAccountDisplay').textContent = errorMsg;
                    document.getElementById('paymentIFSCDisplay').textContent = errorMsg;
                });
        }
        
        function submitPaymentProof(event) {
            event.preventDefault();
            
            const form = document.getElementById('paymentProofForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Validate form
            const feeId = document.getElementById('feeSelection').value;
            const transactionId = document.getElementById('transactionId').value;
            const amount = document.getElementById('paymentAmount').value;
            
            if (!feeId) {
                alert('Please select a fee to pay.');
                return false;
            }
            
            if (!transactionId) {
                alert('Please enter transaction ID.');
                return false;
            }
            
            if (!amount) {
                alert('Payment amount is required.');
                return false;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Submit form with AJAX
            const formData = new FormData(form);
            
            fetch('submit_payment_proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    hidePaymentForm();
                    form.reset();
                    updateAmount();
                    
                    // Reload page after 2 seconds to show updated fees
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error submitting payment proof. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
            
            return false;
        }
    </script>

    <style>
        /* Ensure modal is always visible */
        #paymentModal {
            z-index: 1055 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0,0,0,0.5) !important;
        }
        
        #paymentModal.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        #paymentModal .modal-dialog {
            position: relative !important;
            z-index: 1060 !important;
            margin: 50px auto !important;
            background: white !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
        }
        
        #paymentModal .modal-content {
            background: white !important;
            border: none !important;
            position: relative !important;
            z-index: 1061 !important;
        }
        
        .modal-backdrop {
            z-index: 1050 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0,0,0,0.5) !important;
        }
        
        /* CRITICAL: Make form inputs work */
        #paymentModal .form-control,
        #paymentModal .form-select,
        #paymentModal .form-check-input,
        #paymentModal input[type="file"] {
            pointer-events: auto !important;
            user-select: auto !important;
            -webkit-user-select: auto !important;
            -moz-user-select: auto !important;
            -ms-user-select: auto !important;
            opacity: 1 !important;
            background-color: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #ced4da !important;
            z-index: 1070 !important;
            position: relative !important;
        }
        
        #paymentModal .form-control:focus,
        #paymentModal .form-select:focus,
        #paymentModal .form-check-input:focus {
            outline: none !important;
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25) !important;
            z-index: 1071 !important;
        }
        
        #paymentModal .form-check {
            pointer-events: auto !important;
            z-index: 1070 !important;
            position: relative !important;
        }
        
        #paymentModal .form-check-label {
            pointer-events: auto !important;
            cursor: pointer !important;
            z-index: 1070 !important;
            position: relative !important;
        }
        
        #paymentModal .btn {
            pointer-events: auto !important;
            z-index: 1070 !important;
            position: relative !important;
        }
        
        #paymentModal label {
            pointer-events: auto !important;
            z-index: 1070 !important;
            position: relative !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
        // Simple functions for modal control
        function closeQuickPayModal() {
            const modal = document.getElementById('paymentModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
        
        function openQuickPayModal() {
            console.log('🔘 Quick Pay button clicked!');
            
            const modal = document.getElementById('paymentModal');
            if (!modal) {
                alert('Payment modal not found!');
                return;
            }
            
            // Show modal with proper styles
            modal.style.display = 'block';
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.zIndex = '1055';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Add backdrop with lower z-index
            if (!document.querySelector('.modal-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.style.display = 'block';
                backdrop.style.zIndex = '1050';
                backdrop.style.opacity = '0.5';
                backdrop.onclick = closeQuickPayModal;
                document.body.appendChild(backdrop);
            }
            
            // CRITICAL: Force enable all form inputs
            setTimeout(() => {
                const inputs = modal.querySelectorAll('input, select, textarea, button, label');
                inputs.forEach(input => {
                    input.style.pointerEvents = 'auto';
                    input.style.userSelect = 'auto';
                    input.style.opacity = '1';
                    input.disabled = false;
                    input.readOnly = false;
                    console.log('✅ Enabled input:', input.tagName, input.id || input.name);
                });
                
                // Specifically enable file input
                const fileInput = document.getElementById('paymentProof');
                if (fileInput) {
                    fileInput.disabled = false;
                    fileInput.readOnly = false;
                    fileInput.style.pointerEvents = 'auto';
                    console.log('✅ File input enabled');
                }
            }, 100);
            
            // Setup image preview
            setupImagePreview();
            
            console.log('✅ Modal should be visible and inputs enabled now');
        }
        
        // Image preview function
        function setupImagePreview() {
            const fileInput = document.getElementById('paymentProof');
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (fileInput && preview && previewImg) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.style.display = 'none';
                    }
                });
            }
        }
        
        // SIMPLE WORKING SOLUTION - Bootstrap modal approach
        
        // Load payment info when modal is shown
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Page loaded - setting up payment modal');
            
            const paymentModal = document.getElementById('paymentModal');
            if (paymentModal) {
                // Bootstrap event listener
                paymentModal.addEventListener('show.bs.modal', function () {
                    console.log('💳 Payment modal is opening - loading payment info');
                    loadPaymentInfo();
                });
                
                console.log('✅ Payment modal event listener attached');
            } else {
                console.error('❌ Payment modal not found');
            }
            
            // Initialize payment info with defaults when page loads
            
            // Populate default payment info immediately
            const upiElem = document.getElementById('paymentUPIId');
            const phoneElem = document.getElementById('paymentPhone');
            const bankNameElem = document.getElementById('paymentBankName');
            const accountNumberElem = document.getElementById('paymentAccountNumber');
            const ifscCodeElem = document.getElementById('paymentIFSCCode');
            
            // Set default values immediately
            if (upiElem && !upiElem.value) upiElem.value = 'aaravraj799246@okaxis';
            if (phoneElem && !phoneElem.value) phoneElem.value = '7992465964';
            if (bankNameElem && !bankNameElem.value) bankNameElem.value = 'Loading...';
            if (accountNumberElem && !accountNumberElem.value) accountNumberElem.value = 'Loading...';
            if (ifscCodeElem && !ifscCodeElem.value) ifscCodeElem.value = 'Loading...';
            
            console.log('✅ Default payment info populated');
            
            // Load actual payment info after a short delay
            setTimeout(() => {
                loadPaymentInfo();
            }, 1000);
            
            // Other initialization
            setTimeout(enableAllInputs, 500);
        });
        
        // Auto-refresh payment info every 30 seconds to check for QR updates
        setInterval(function() {
            // Only refresh if modal is open
            const paymentModal = document.getElementById('paymentModal');
            if (paymentModal && paymentModal.classList.contains('show')) {
                console.log('Auto-refreshing payment info...');
                loadPaymentInfo();
            }
        }, 30000); // 30 seconds
        
        // Add animation classes to elements if they don't have them
        const animatedElements = document.querySelectorAll('.dashboard-card, .table-container');
        animatedElements.forEach((element, index) => {
            if (!element.classList.contains('animate__animated')) {
                element.classList.add('animate__animated', 'animate__fadeInUp');
                element.style.animationDelay = (index * 0.1) + 's';
            }
        });
        
        // Force all text in payment modal to be visible
        function forceTextVisibility() {
            const modal = document.getElementById('paymentModal');
            if (!modal) return;
            
            // Get all text elements in the modal
            const textElements = modal.querySelectorAll('*');
            textElements.forEach(element => {
                // Force visibility styles
                element.style.opacity = '1';
                element.style.visibility = 'visible';
                element.style.display = '';
                element.style.color = '#333333';
                element.style.webkitTextFillColor = '#333333';
                element.style.transform = 'none';
                element.style.transition = 'none';
                
                // Remove any animation classes that might hide text
                element.classList.remove('animate__animated', 'animate__fadeInUp', 'animate__fadeInDown', 'animate__fadeOut');
            });
            
            // Specifically target form elements
            const formElements = modal.querySelectorAll('input, select, textarea, label, span, strong, p, h1, h2, h3, h4, h5, h6');
            formElements.forEach(element => {
                element.style.opacity = '1';
                element.style.visibility = 'visible';
                element.style.color = '#333333';
                element.style.webkitTextFillColor = '#333333';
                element.style.backgroundColor = '#ffffff';
                element.style.transform = 'none';
                element.style.transition = 'none';
                
                if (element.tagName === 'INPUT' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') {
                    element.style.color = '#333333';
                    element.style.webkitTextFillColor = '#333333';
                    element.style.transform = 'none';
                    element.style.transition = 'none';
                    if (element.hasAttribute('readonly')) {
                        element.style.backgroundColor = '#f8f9fa';
                        element.style.color = '#333333';
                        element.style.webkitTextFillColor = '#333333';
                    }
                }
            });
            
            // Remove modal fade class to prevent sliding
            modal.classList.remove('fade');
            
            // Force modal content to be static
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.transform = 'none';
                modalContent.style.transition = 'none';
            }
            
            console.log('✅ Forced text visibility and removed sliding in payment modal');
        }
        
        // Function to setup scroll listener for payment modal
        function setupScrollListener() {
            const modalBody = document.querySelector('#paymentModal .modal-body');
            const paymentInfoCard = document.querySelector('#paymentModal .card:has(#paymentUPIId)');
            
            if (modalBody && paymentInfoCard) {
                modalBody.addEventListener('scroll', function() {
                    console.log('📜 Payment modal scrolled');
                    
                    // Ensure payment info stays visible
                    const scrollPosition = modalBody.scrollTop;
                    
                    // Add highlight effect when scrolling
                    if (scrollPosition > 50) {
                        paymentInfoCard.style.transform = 'scale(1.02)';
                        paymentInfoCard.style.transition = 'all 0.3s ease';
                    } else {
                        paymentInfoCard.style.transform = 'scale(1)';
                    }
                });
            }
        }
        
        function forceModalThemeUpdate() {
            // Force modal to respect current theme
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const paymentModal = document.getElementById('paymentModal');
            const complaintModal = document.getElementById('complaintModal');
            
            if (currentTheme === 'dark') {
                if (paymentModal) paymentModal.classList.add('dark-theme-modal');
                if (complaintModal) complaintModal.classList.add('dark-theme-modal');
            } else {
                if (paymentModal) paymentModal.classList.remove('dark-theme-modal');
                if (complaintModal) complaintModal.classList.remove('dark-theme-modal');
            }
            
            // Force style recalculation
            if (paymentModal) {
                paymentModal.style.display = 'none';
                paymentModal.offsetHeight; // Trigger reflow
                paymentModal.style.display = '';
            }
            if (complaintModal) {
                complaintModal.style.display = 'none';
                complaintModal.offsetHeight; // Trigger reflow
                complaintModal.style.display = '';
            }
        }
                .then(response => {
                    console.log('Payment info response:', response);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Payment info data:', data);
                    
                    const upiElem = document.getElementById('paymentUPIId');
                    const phoneElem = document.getElementById('paymentPhone');
                    const qrElem = document.getElementById('paymentQRCode');
                    const qrMainElem = document.getElementById('paymentQRCodeMain');
                    
                    console.log('Payment elements found:', {
                        upi: !!upiElem,
                        phone: !!phoneElem,
                        qr: !!qrElem,
                        qrMain: !!qrMainElem
                    });
                    
                    if (data.success && data.payment_info) {
                        const paymentMethods = data.payment_info;
                        console.log('Processing payment methods:', paymentMethods);
                        
                        // Helper function to safely set values
                        const safeSetValue = (element, value, defaultValue = '') => {
                            if (element) {
                                element.value = value || defaultValue;
                                console.log(`Set ${element.id} to:`, value || defaultValue);
                            }
                        };
                        
                        // Find UPI method for QR code and UPI details
                        const upiMethod = paymentMethods.find(method => 
                            method.payment_method === 'upi' || 
                            method.payment_method === 'google_pay' || 
                            method.payment_method === 'phonepe' || 
                            method.payment_method === 'paytm'
                        );
                        
                        // Find bank transfer method
                        const bankMethod = paymentMethods.find(method => method.payment_method === 'bank_transfer');
                        
                        // Set UPI ID with fallback
                        const upiId = upiMethod ? upiMethod.upi_id : 'aaravraj799246@okaxis';
                        safeSetValue(upiElem, upiId, 'aaravraj799246@okaxis');
                        
                        // Set Phone with fallback  
                        const phoneNumber = upiMethod ? upiMethod.phone_number : '7992465964';
                        safeSetValue(phoneElem, phoneNumber, '7992465964');
                        
                        // Populate bank details with proper fallbacks
                        const bankName = bankMethod ? bankMethod.bank_name : 'State Bank of India';
                        const accountNumber = bankMethod ? bankMethod.account_number : '123456789012345';
                        const ifscCode = bankMethod ? bankMethod.ifsc_code : 'SBIN0001234';
                        
                        safeSetValue(bankNameElem, bankName, 'State Bank of India');
                        safeSetValue(accountNumberElem, accountNumber, '123456789012345');
                        safeSetValue(ifscCodeElem, ifscCode, 'SBIN0001234');
                        
                        // Update both QR code displays with cache-busting
                        if (qrElem || qrMainElem) {
                            if (upiMethod && upiMethod.qr_code_path) {
                                const qrPath = '../' + upiMethod.qr_code_path + '?t=' + timestamp;
                                console.log('Setting QR code path with cache-buster:', qrPath);
                                
                                if (qrElem) {
                                    qrElem.src = qrPath;
                                    qrElem.onerror = function() {
                                        console.log('QR code load failed, using fallback');
                                        this.src = '../QR code/1773729851_WhatsAppImage20260309at8.36.02PM.jpeg?t=' + timestamp;
                                    };
                                    qrElem.onload = function() {
                                        console.log('✅ QR code loaded successfully');
                                    };
                                }
                                
                                if (qrMainElem) {
                                    qrMainElem.src = qrPath;
                                    qrMainElem.onerror = function() {
                                        console.log('Main QR code load failed, using fallback');
                                        this.src = '../QR code/1773729851_WhatsAppImage20260309at8.36.02PM.jpeg?t=' + timestamp;
                                    };
                                    qrMainElem.onload = function() {
                                        console.log('✅ Main QR code loaded successfully');
                                        // Show success message when QR updates
                                        showQRUpdateNotification();
                                    };
                                }
                            } else {
                                // Fallback if no QR code in database
                                console.log('No QR code in database, using fallback');
                                const fallbackPath = '../QR code/1773729851_WhatsAppImage20260309at8.36.02PM.jpeg?t=' + timestamp;
                                if (qrElem) qrElem.src = fallbackPath;
                                if (qrMainElem) qrMainElem.src = fallbackPath;
                            }
                        }
                        
                        console.log('✅ Payment information loaded from database');
                    } else {
                        console.warn('Payment info not found, using defaults');
                        // Use default values if API fails
                        const defaultUPI = 'aaravraj799246@okaxis';
                        const defaultPhone = '7992465964';
                        const defaultQR = '../QR code/1773729851_WhatsAppImage20260309at8.36.02PM.jpeg?t=' + timestamp;
                        const defaultBankName = 'State Bank of India';
                        const defaultAccountNumber = '123456789012345';
                        const defaultIFSCCode = 'SBIN0001234';
                        
                        if (upiElem) upiElem.value = defaultUPI;
                        if (phoneElem) phoneElem.value = defaultPhone;
                        if (qrElem) qrElem.src = defaultQR;
                        if (qrMainElem) qrMainElem.src = defaultQR;
                        
                        // Set default bank details
                        if (bankNameElem) bankNameElem.value = defaultBankName;
                        if (accountNumberElem) accountNumberElem.value = defaultAccountNumber;
                        if (ifscCodeElem) ifscCodeElem.value = defaultIFSCCode;
                    }
                })
                .catch(error => {
                    console.error('Error loading payment information:', error);
                    
                    const upiElem = document.getElementById('paymentUPIId');
                    const phoneElem = document.getElementById('paymentPhone');
                    const qrElem = document.getElementById('paymentQRCode');
                    const qrMainElem = document.getElementById('paymentQRCodeMain');
                    
                    // Set default values on error
                    const defaultUPI = 'aaravraj799246@okaxis';
                    const defaultPhone = '7992465964';
                    const defaultQR = '../QR code/1773729851_WhatsAppImage20260309at8.36.02PM.jpeg?t=' + timestamp;
                    const defaultBankName = 'State Bank of India';
                    const defaultAccountNumber = '123456789012345';
                    const defaultIFSCCode = 'SBIN0001234';
                    
                    if (upiElem) upiElem.value = defaultUPI;
                    if (phoneElem) phoneElem.value = defaultPhone;
                    if (qrElem) qrElem.src = defaultQR;
                    if (qrMainElem) qrMainElem.src = defaultQR;
                    
                    // Set default bank details
                    const bankNameElem = document.getElementById('paymentBankName');
                    const accountNumberElem = document.getElementById('paymentAccountNumber');
                    const ifscCodeElem = document.getElementById('paymentIFSCCode');
                    
                    if (bankNameElem) bankNameElem.value = defaultBankName;
                    if (accountNumberElem) accountNumberElem.value = defaultAccountNumber;
                    if (ifscCodeElem) ifscCodeElem.value = defaultIFSCCode;
                    
                    // Show user-friendly message
                    const paymentInfo = document.getElementById('paymentInfo');
                    if (paymentInfo) {
                        paymentInfo.innerHTML += '<div class="alert alert-warning mt-2">Using default payment information. Contact admin if issues persist.</div>';
                    }
                });
        }
        
        // Function to show QR code update notification
        function showQRUpdateNotification() {
            const paymentInfo = document.getElementById('paymentInfo');
            if (paymentInfo && !paymentInfo.querySelector('.qr-update-alert')) {
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show qr-update-alert animate__animated animate__fadeInDown';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>QR Code Updated!</strong> Latest QR code is now displayed.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                paymentInfo.appendChild(alert);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }
        }
        
        function updatePaymentDetails() {
            console.log('🔧 updatePaymentDetails called - updated for cards');
        }
        
        function selectFeeForPayment(feeId, month, year, amount, paid) {
            console.log('🔧 selectFeeForPayment called with:', { feeId, month, year, amount, paid });
            
            // Remove previous selection
            document.querySelectorAll('.fee-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Find and select the clicked card
            const cardElement = document.querySelector(`.fee-card[onclick*="selectFeeForPayment(${feeId}"]`);
            if (cardElement) {
                cardElement.classList.add('selected');
            }
            
            // Update hidden fields
            document.getElementById('paymentFeeId').value = feeId;
            document.getElementById('paymentDueAmount').value = amount - paid;
            
            // Update selected fee display
            document.getElementById('selectedFeeInfo').style.display = 'block';
            document.getElementById('selectedFeeMonth').textContent = `${month} ${year}`;
            document.getElementById('selectedFeeTotal').textContent = `₹${parseFloat(amount).toFixed(2)}`;
            document.getElementById('selectedFeePaid').textContent = `₹${parseFloat(paid).toFixed(2)}`;
            document.getElementById('selectedFeeDue').textContent = `₹${parseFloat(amount - paid).toFixed(2)}`;
            
            // Show payment instructions
            document.getElementById('paymentInstructions').style.display = 'block';
            
            // Ensure payment information is loaded when fee is selected
            loadPaymentInfo();
        }
        
        function copyUPIId() {
            const upiId = document.getElementById('paymentUPIId').value;
            navigator.clipboard.writeText(upiId).then(function() {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            });
        }
        
        function copyBankName() {
            const bankName = document.getElementById('paymentBankName').value;
            navigator.clipboard.writeText(bankName).then(function() {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            });
        }
        
        function copyAccountNumber() {
            const accountNumber = document.getElementById('paymentAccountNumber').value;
            navigator.clipboard.writeText(accountNumber).then(function() {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            });
        }
        
        function copyIFSCCode() {
            const ifscCode = document.getElementById('paymentIFSCCode').value;
            navigator.clipboard.writeText(ifscCode).then(function() {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            });
        }
        
        // Listen for theme changes and update modal if it's open
        document.addEventListener('themeChanged', function(e) {
            const modal = document.getElementById('paymentModal');
            if (modal) {
                forceModalThemeUpdate();
            }
        });
        
        // Helper function to enable all inputs
        function enableAllInputs() {
            const inputs = document.querySelectorAll('input, select, textarea, button');
            inputs.forEach(input => {
                input.style.pointerEvents = 'auto';
                input.style.userSelect = 'auto';
                input.disabled = false;
            });
        }
        
        // Complaint form validation
        function validateComplaintForm() {
            const title = document.getElementById('complaintTitle').value.trim();
            const category = document.getElementById('complaintCategory').value;
            const description = document.getElementById('complaintDescription').value.trim();
            
            // Valid categories
            const validCategories = ['maintenance', 'cleaning', 'food', 'security', 'other'];
            
            if (!title || !category || !description) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (!validCategories.includes(category)) {
                alert('Invalid category selected.');
                return false;
            }
            
            if (title.length < 5) {
                alert('Complaint title must be at least 5 characters long.');
                return false;
            }
            
            if (description.length < 20) {
                alert('Complaint description must be at least 20 characters long.');
                return false;
            }
            
            return true;
        }
        
        // Handle payment proof form submission
        document.getElementById('paymentProofForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const feeId = document.getElementById('paymentFeeId').value;
            const transactionId = document.getElementById('transactionId').value;
            const paymentMethod = document.getElementById('payment_method').value;
            
            if (!feeId) {
                alert('Please select a fee to pay.');
                return;
            }
            
            if (!transactionId || transactionId.trim().length < 6 || transactionId.trim().length > 12) {
                alert('Please enter a valid transaction ID (6-12 characters).');
                document.getElementById('transactionId').focus();
                return;
            }
            
            if (!paymentMethod) {
                alert('Please select a payment method.');
                document.getElementById('payment_method').focus();
                return;
            }
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            submitBtn.disabled = true;
            
            fetch('submit_payment_proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                    modal.hide();
                    
                    // Reset form
                    this.reset();
                    document.getElementById('selectedFeeInfo').style.display = 'none';
                    document.getElementById('paymentInstructions').style.display = 'none';
                    
                    // Show success message
                    alert(data.message);
                    
                    // Reload page to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting payment proof. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
        
        // Notification Modal Functions
        function openNotificationModal() {
            const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
            modal.show();
            loadAllNotifications();
        }
        
        function loadAllNotifications() {
            const notificationList = document.getElementById('notificationList');
            
            // Show loading state
            notificationList.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading notifications...</p>
                </div>
            `;
            
            // Fetch all notifications via AJAX
            fetch('get_all_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotifications(data.notifications);
                    } else {
                        notificationList.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error loading notifications: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    notificationList.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading notifications. Please try again.
                        </div>
                    `;
                });
        }
        
        function displayNotifications(notifications) {
            const notificationList = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No notifications</h6>
                        <p class="text-muted">You don't have any notifications at the moment.</p>
                    </div>
                `;
            } else {
                let notificationsHTML = '';
                
                notifications.forEach(notification => {
                    const unreadClass = notification.is_read ? 'notification-read' : 'notification-unread';
                    const icon = getNotificationIcon(notification.type);
                    const timeAgo = getTimeAgo(notification.created_at);
                    
                    // Add complaint information if available
                    let complaintInfo = '';
                    if (notification.complaint_title) {
                        complaintInfo = `
                            <div class="complaint-info mt-2">
                                <span class="badge bg-${getStatusColor(notification.complaint_status)}">${formatStatus(notification.complaint_status)}</span>
                                <small class="text-muted">Complaint: ${notification.complaint_title}</small>
                            </div>
                        `;
                    }
                    
                    // Add payment information if available
                    let paymentInfo = '';
                    if (notification.fee_title) {
                        paymentInfo = `
                            <div class="payment-info mt-2">
                                <span class="badge bg-${getPaymentStatusColor(notification.fee_status)}">${formatPaymentStatus(notification.fee_status)}</span>
                                <small class="text-muted">Payment: ${notification.fee_title} (₹${notification.fee_amount})</small>
                            </div>
                        `;
                    }
                    
                    notificationsHTML += `
                        <div class="notification-item ${unreadClass} mb-3 p-3 border rounded-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="notification-icon ${icon.class}">
                                        <i class="${icon.icon}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">${notification.title}</h6>
                                            <p class="mb-1 text-muted">${notification.message}</p>
                                            ${complaintInfo}
                                            ${paymentInfo}
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>${timeAgo}
                                            </small>
                                        </div>
                                        ${!notification.is_read ? `
                                            <button class="btn btn-sm btn-outline-primary" onclick="markAsRead(${notification.id})">
                                                <i class="fas fa-check me-1"></i>Mark as Read
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                notificationList.innerHTML = notificationsHTML;
            }
        }
        
        function getStatusColor(status) {
            const colors = {
                'pending' => 'warning',
                'in_progress' => 'info',
                'resolved' => 'success',
                'rejected' => 'danger',
                'paid' => 'success'
            };
            return colors[status] || 'secondary';
        }
        
        function getPaymentStatus(status) {
            const colors = {
                'pending' => 'warning',
                'paid' => 'success',
                'rejected' => 'danger'
            };
            return colors[status] || 'secondary';
        }
        
        function formatStatus(status) {
            return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ');
        }
        
        function formatPaymentStatus(status) {
            return status.charAt(0).toUpperCase() + status.slice(1);
        }
        
        function markAsRead(notificationId) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadAllNotifications();
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
        
        function markAllAsRead() {
            const notificationIds = document.querySelectorAll('.notification-item').length;
            
            if (notificationIds === 0) {
                return;
            }
            
            // Get all unread notification IDs
            const unreadNotifications = [];
            document.querySelectorAll('.notification-item.notification-unread').forEach(item => {
                const btn = item.querySelector('button[onclick*="markAsRead"]');
                if (btn) {
                    const onclick = btn.getAttribute('onclick');
                    const id = onclick.match(/markAsRead\((\d+)\)/);
                    if (id) {
                        unreadNotifications.push(parseInt(id[1]));
                    }
                }
            });
            
            if (unreadNotifications.length === 0) {
                return;
            }
            
            // Mark all as read
            const promises = unreadNotifications.map(id => 
                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'notification_id=' + id
                })
            );
            
            Promise.all(promises)
                .then(() => {
                    // Reload notifications
                    loadAllNotifications();
                    // Update badge
                    updateNotificationBadge(0);
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                });
        }
        
        function getNotificationIcon(type) {
            const icons = {
                'info' => { icon: 'fas fa-info-circle', class: 'text-info' },
                'success' => { icon: 'fas fa-check-circle', class: 'text-success' },
                'warning' => { icon: 'fas fa-exclamation-triangle', class: 'text-warning' },
                'error' => { icon: 'fas fa-exclamation-circle', class: 'text-danger' },
                'payment' => { icon: 'fas fa-credit-card', class: 'text-primary' },
                'complaint_response' => { icon: 'fas fa-comment-dots', class: 'text-warning' },
                'registration' => { icon: 'fas fa-user-plus', class: 'text-info' },
                'complaint' => { icon: 'fas fa-comment-dots', class: 'text-warning' },
                'fee_reminder' => { icon: 'fas fa-calendar-alt', class: 'text-info' },
                'payment_received' => { icon: 'fas fa-money-check', class: 'text-success' }
            };
            
            return icons[type] || icons['info'];
        }
        
        function getTimeAgo(datetime) {
            const time = new Date(datetime);
            const now = new Date();
            const diff = Math.floor((now - time) / 1000); // difference in seconds
            
            if (diff < 60) {
                return 'Just now';
            } else if (diff < 3600) {
                return Math.floor(diff / 60) + ' minutes ago';
            } else if (diff < 86400) {
                return Math.floor(diff / 3600) + ' hours ago';
            } else if (diff < 604800) {
                return Math.floor(diff / 86400) + ' days ago';
            } else {
                return time.toLocaleDateString();
            }
        }
        
        function updateNotificationBadge(count) {
            const badge = document.querySelector('.badge.rounded-pill.bg-danger');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        // Initialize auto-refresh for QR code updates
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing QR code auto-refresh...');
            startQRAutoRefresh();
        });
    </script>
</body>
</html>
