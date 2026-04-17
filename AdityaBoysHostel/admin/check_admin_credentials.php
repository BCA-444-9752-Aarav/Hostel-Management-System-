<?php
require_once 'config/db.php';

echo "<h2>🔍 Admin Login Credentials Check</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto;'>";

// Check if admins table exists
echo "<h3>📋 Checking Admins Table:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Admins table exists</p>";
    
    // Get all admin accounts
    echo "<h3>👤 Admin Accounts Found:</h3>";
    $stmt = $conn->prepare("SELECT id, full_name, email, role, is_active, created_at FROM admins ORDER BY id");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th>";
        echo "</tr>";
        
        while ($admin = $result->fetch_assoc()) {
            $active_status = $admin['is_active'] ? '✅ Yes' : '❌ No';
            $row_style = $admin['is_active'] ? 'background-color: #e8f5e8;' : 'background-color: #ffe8e8;';
            echo "<tr style='$row_style'>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['full_name']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($admin['email']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
            echo "<td>" . $active_status . "</td>";
            echo "<td>" . $admin['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🔑 Login Information:</h3>";
        echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>";
        echo "<p><strong>Default Login Credentials:</strong></p>";
        echo "<p>📧 <strong>Email:</strong> aaravraj799246@gmail.com</p>";
        echo "<p>🔒 <strong>Password:</strong> 787062</p>";
        echo "<p style='color: #6c757d; font-size: 14px;'>Note: If these don't work, you may need to reset the password using the script below.</p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No admin accounts found in the database.</p>";
        echo "<h3>🔧 Creating Default Admin Account:</h3>";
        
        // Create default admin account
        $default_email = 'aaravraj799246@gmail.com';
        $default_password = '787062';
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role, is_active) VALUES (?, ?, ?, 'Super Admin', TRUE)");
        $stmt->bind_param("sss", 'System Administrator', $default_email, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Default admin account created successfully!</p>";
            echo "<p><strong>Login Credentials:</strong></p>";
            echo "<p>📧 Email: aaravraj799246@gmail.com</p>";
            echo "<p>🔒 Password: 787062</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin account: " . $stmt->error . "</p>";
        }
    }
    
} else {
    echo "<p style='color: red;'>❌ Admins table does not exist</p>";
    echo "<h3>🔧 Creating Admins Table:</h3>";
    
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
        
        // Create default admin account
        $default_email = 'aaravraj799246@gmail.com';
        $default_password = '787062';
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role, is_active) VALUES (?, ?, ?, 'Super Admin', TRUE)");
        $stmt->bind_param("sss", 'System Administrator', $default_email, $hashed_password);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Default admin account created successfully!</p>";
            echo "<h3>🔑 Login Credentials:</h3>";
            echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
            echo "<p><strong>Use these credentials to login:</strong></p>";
            echo "<p>📧 <strong>Email:</strong> aaravraj799246@gmail.com</p>";
            echo "<p>🔒 <strong>Password:</strong> 787062</p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin account: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to create admins table: " . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🏠 Go to Login Page</a></p>";

echo "</div>";
?>
