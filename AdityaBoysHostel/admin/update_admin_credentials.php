<?php
require_once 'config/db.php';

echo "<h2>🔧 Update Admin Credentials</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto;'>";

// New credentials
$new_email = 'aaravraj799246@gmail.com';
$new_password = '787062';
$new_full_name = 'Aarav Raj';

echo "<h3>📝 Updating Admin Account:</h3>";
echo "<p><strong>New Email:</strong> " . htmlspecialchars($new_email) . "</p>";
echo "<p><strong>New Password:</strong> " . htmlspecialchars($new_password) . "</p>";

// Check if admins table exists
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Admins table exists</p>";
    
    // Check if admin with this email already exists
    $stmt = $conn->prepare("SELECT id, full_name, email FROM admins WHERE email = ?");
    $stmt->bind_param("s", $new_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing admin
        $admin = $result->fetch_assoc();
        echo "<p style='color: orange;'>⚠️ Admin with this email already exists (ID: " . $admin['id'] . ")</p>";
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET full_name = ?, password = ?, is_active = TRUE WHERE email = ?");
        $stmt->bind_param("sss", $new_full_name, $hashed_password, $new_email);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Admin account updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update admin account: " . $stmt->error . "</p>";
        }
        
    } else {
        // Create new admin account
        echo "<p>🔧 Creating new admin account...</p>";
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role, is_active) VALUES (?, ?, ?, 'Super Admin', TRUE)");
        $stmt->bind_param("sss", $new_full_name, $new_email, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ New admin account created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin account: " . $stmt->error . "</p>";
        }
    }
    
    // Deactivate old default admin if it exists and is different
    if ($new_email !== 'admin@hostel.com') {
        $stmt = $conn->prepare("UPDATE admins SET is_active = FALSE WHERE email = 'admin@hostel.com'");
        $stmt->execute();
        echo "<p style='color: blue;'>ℹ️ Old default admin account deactivated</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Admins table does not exist. Creating it...</p>";
    
    // Create admins table
    $create_table_sql = "
    CREATE TABLE admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('Super Admin', 'Admin', 'Staff') DEFAULT 'Admin',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_email (email),
        INDEX idx_is_active (is_active),
        INDEX idx_role (role)
    )
    ";
    
    if ($conn->query($create_table_sql)) {
        echo "<p style='color: green;'>✅ Admins table created successfully!</p>";
        
        // Create new admin account
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role, is_active) VALUES (?, ?, ?, 'Super Admin', TRUE)");
        $stmt->bind_param("sss", $new_full_name, $new_email, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Admin account created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin account: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to create admins table: " . $conn->error . "</p>";
    }
}

// Verify the update
echo "<h3>✅ Verification:</h3>";
$stmt = $conn->prepare("SELECT id, full_name, email, role, is_active FROM admins WHERE email = ?");
$stmt->bind_param("s", $new_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
    echo "<h4>🎉 Admin Account Ready!</h4>";
    echo "<p><strong>ID:</strong> " . $admin['id'] . "</p>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($admin['full_name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($admin['role']) . "</p>";
    echo "<p><strong>Status:</strong> " . ($admin['is_active'] ? 'Active ✅' : 'Inactive ❌') . "</p>";
    echo "</div>";
    
    echo "<h3>🔑 Your New Login Credentials:</h3>";
    echo "<div style='background-color: #cce5ff; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>";
    echo "<p><strong>📧 Email:</strong> " . htmlspecialchars($new_email) . "</p>";
    echo "<p><strong>🔒 Password:</strong> " . htmlspecialchars($new_password) . "</p>";
    echo "</div>";
    
} else {
    echo "<p style='color: red;'>❌ Failed to verify admin account creation</p>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🏠 Go to Login Page</a></p>";

echo "</div>";
?>
