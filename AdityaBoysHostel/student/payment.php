<?php
require_once '../config/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get fee details from URL
$fee_id = $_GET['fee_id'] ?? 0;
$student_id = $_SESSION['student_id'];

// Get fee information
$fee = null;
if ($fee_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM fees WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $fee_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fee = $result->fetch_assoc();
    
    // Debug: Log fee information
    error_log("Payment page accessed - Fee ID: $fee_id, Student ID: $student_id, Fee found: " . ($fee ? 'Yes' : 'No'));
}

if (!$fee) {
    error_log("Fee not found or access denied - Fee ID: $fee_id, Student ID: $student_id");
    header('Location: fees.php');
    exit();
}

// Get payment information from database
$payment_info = null;
try {
    $stmt = $conn->prepare("SELECT * FROM payment_info WHERE is_active = TRUE ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $payment_info = $result->fetch_assoc();
} catch (Exception $e) {
    // If table doesn't exist, use default values
    $payment_info = [
        'upi_id' => 'aaravraj799246@okaxis',
        'phone_number' => '7992465964',
        'qr_code_path' => 'QR code/WhatsApp Image 2026-03-09 at 8.36.02 PM.jpeg'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .upi-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .qr-code {
            width: 200px;
            height: 200px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin: 20px auto;
            display: block;
        }
        .payment-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .proof-form {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        .upload-area.dragover {
            background: rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }
        .uploaded-file {
            margin-top: 15px;
            padding: 10px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 5px;
        }
        .btn-pay {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        .instructions {
            background: rgba(255, 193, 7, 0.1);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
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
                <p class="text-white-50 mb-0">Student Portal</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="profile.php" class="sidebar-menu-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="fees.php" class="sidebar-menu-item active">
                    <i class="fas fa-rupee-sign"></i> My Fees
                </a>
                <a href="complaints.php" class="sidebar-menu-item">
                    <i class="fas fa-comment-dots"></i> My Complaints
                </a>
                <a href="notifications.php" class="sidebar-menu-item">
                    <i class="fas fa-bell"></i> Notifications
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
                <h1 class="top-bar-title">Payment</h1>
                <div class="top-bar-user">
                    <span class="text-muted">Welcome, <?php echo $_SESSION['student_name']; ?></span>
                    <img src="../assets/default_avatar.svg" alt="Student" class="user-avatar">
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="payment-container animate__animated animate__fadeIn">
                    <!-- Fee Details -->
                    <div class="payment-header">
                        <h3><i class="fas fa-file-invoice me-2"></i>Fee Details</h3>
                        <div class="payment-info">
                            <p><strong>Month:</strong> <?php echo htmlspecialchars($fee['month']); ?> <?php echo $fee['year']; ?></p>
                            <p><strong>Amount:</strong> ₹<?php echo number_format($fee['amount'], 2); ?></p>
                            <p><strong>Due Date:</strong> 
                            <?php 
                            if (!empty($fee['due_date'])) {
                                echo date('d M, Y', strtotime($fee['due_date']));
                            } else {
                                echo 'Not specified';
                            }
                            ?>
                        </p>
                            <p><strong>Status:</strong> 
                                <span class="badge <?php echo $fee['status'] == 'paid' ? 'bg-success' : ($fee['status'] == 'partial' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($fee['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- UPI Payment Section -->
                    <div class="upi-section">
                        <h4><i class="fas fa-qrcode me-2"></i>UPI Payment</h4>
                        <div class="text-center">
                            <div class="qr-code" style="background: white; padding: 15px; border-radius: 10px; display: inline-block;">
                                <img src="../<?php echo htmlspecialchars($payment_info['qr_code_path']); ?>" 
                                     alt="UPI QR Code" 
                                     style="width: 180px; height: 180px; object-fit: contain; border-radius: 5px;">
                            </div>
                            <h5 class="mt-3">Hostel UPI ID</h5>
                            <p class="lead mb-2"><?php echo htmlspecialchars($payment_info['upi_id']); ?></p>
                            <p class="mb-2"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($payment_info['phone_number']); ?></p>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-light" onclick="copyUPIId()">
                                    <i class="fas fa-copy me-1"></i>Copy UPI ID
                                </button>
                                <a href="tel:<?php echo htmlspecialchars($payment_info['phone_number']); ?>" class="btn btn-sm btn-outline-light ms-2">
                                    <i class="fas fa-phone me-1"></i>Call
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Instructions -->
                    <div class="instructions">
                        <h5><i class="fas fa-info-circle me-2"></i>Payment Instructions</h5>
                        <ol>
                            <li>Open any UPI app (Google Pay, PhonePe, Paytm, etc.)</li>
                            <li>Scan the QR code or search for "<strong><?php echo htmlspecialchars($payment_info['upi_id']); ?></strong>"</li>
                            <li>Pay the amount: <strong>₹<?php echo number_format($fee['amount'], 2); ?></strong></li>
                            <li>After successful payment, note down the Transaction ID/UTR Number</li>
                            <li>Come back to this page and submit your payment proof below</li>
                        </ol>
                    </div>
                    
                    <!-- Payment Proof Form -->
                    <div class="proof-form">
                        <h5><i class="fas fa-file-upload me-2"></i>Submit Payment Proof</h5>
                        <form id="paymentProofForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="transaction_id" class="form-label">Transaction ID / UTR Number *</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" required 
                                       placeholder="Enter your 12-digit transaction ID">
                                <div class="form-text">Example: 123456789012</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="google_pay">Google Pay</option>
                                    <option value="paytm">Paytm</option>
                                    <option value="phonepe">PhonePe</option>
                                    <option value="bhim">BHIM UPI</option>
                                    <option value="amazon_pay">Amazon Pay</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label">Payment Screenshot (Recommended)</label>
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p>Click to upload or drag & drop your payment screenshot here</p>
                                    <p class="small text-muted">Supported formats: JPG, PNG, PDF (Max 5MB)</p>
                                    <input type="file" id="payment_proof" name="payment_proof" accept="image/*,.pdf" style="display: none;">
                                </div>
                                <div id="uploadedFile" class="uploaded-file" style="display: none;">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span id="fileName"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile()">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                            
                            <input type="hidden" name="fee_id" value="<?php echo $fee_id; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-pay">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Payment Proof
                                </button>
                                <a href="fees.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Fees
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Copy UPI ID to clipboard
        function copyUPIId() {
            const upiId = '<?php echo htmlspecialchars($payment_info['upi_id']); ?>';
            
            // Use fallback method for better compatibility
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(upiId).then(function() {
                    showCopySuccess('UPI ID');
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    fallbackCopyToClipboard(upiId, 'UPI ID');
                });
            } else {
                fallbackCopyToClipboard(upiId, 'UPI ID');
            }
        }
        
        // Fallback copy method
        function fallbackCopyToClipboard(text, type) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess(type);
                } else {
                    showCopyError(type, text);
                }
            } catch (err) {
                console.error('Fallback copy failed: ', err);
                showCopyError(type, text);
            }
            
            document.body.removeChild(textArea);
        }
        
        // Show copy success message
        function showCopySuccess(type) {
            // Find the button that was clicked
            const buttons = document.querySelectorAll('button');
            let clickedButton = null;
            
            buttons.forEach(btn => {
                if (btn.textContent.includes(type) || btn.textContent.includes('Copy')) {
                    if (btn.textContent.includes(type) || 
                        (type === 'UPI ID' && btn.textContent.includes('UPI'))) {
                        clickedButton = btn;
                    }
                }
            });
            
            if (clickedButton) {
                const originalText = clickedButton.innerHTML;
                clickedButton.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                clickedButton.classList.remove('btn-outline-light');
                clickedButton.classList.add('btn-success');
                
                setTimeout(() => {
                    clickedButton.innerHTML = originalText;
                    clickedButton.classList.remove('btn-success');
                    clickedButton.classList.add('btn-outline-light');
                }, 2000);
            } else {
                // Fallback alert
                alert(type + ' copied successfully!');
            }
        }
        
        // Show copy error message
        function showCopyError(type, text) {
            alert('Failed to copy ' + type + '. Please copy manually: ' + text);
        }
        
        // File upload functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('payment_proof');
        const uploadedFile = document.getElementById('uploadedFile');
        const fileName = document.getElementById('fileName');
        
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
        
        function handleFileSelect(file) {
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG, GIF, and PDF files are allowed');
                return;
            }
            
            // Display file info
            fileName.textContent = file.name;
            uploadedFile.style.display = 'block';
            uploadArea.style.display = 'none';
        }
        
        function removeFile() {
            fileInput.value = '';
            uploadedFile.style.display = 'none';
            uploadArea.style.display = 'block';
            fileName.textContent = '';
        }
        
        // Form submission
        document.getElementById('paymentProofForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            fetch('submit_payment_proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment proof submitted successfully! Admin will verify your payment within 24 hours.');
                    // Redirect back to fees page
                    setTimeout(() => {
                        window.location.href = 'fees.php';
                    }, 2000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                // Restore button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
