-- =====================================================
-- Aditya Boys Hostel - Complete Database Schema
-- Version: 2.5
-- Updated: March 26, 2026
-- Description: Complete database structure for hostel management system
-- =====================================================

-- 1. Students Table
-- =====================================================
CREATE TABLE IF NOT EXISTS students (
    id int(11) NOT NULL AUTO_INCREMENT,
    full_name varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    mobile varchar(20) DEFAULT NULL,
    parent_mobile varchar(20) DEFAULT NULL,
    address text DEFAULT NULL,
    profile_photo varchar(255) DEFAULT NULL,
    status enum('pending','approved','rejected','inactive','graduated') DEFAULT 'pending',
    room_id int(11) DEFAULT NULL,
    bed_number varchar(10) DEFAULT NULL,
    room_status enum('allocated','not_allocated','vacated') DEFAULT 'not_allocated',
    is_active tinyint(1) DEFAULT 1,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    KEY idx_status (status),
    KEY idx_room_id (room_id),
    KEY idx_is_active (is_active),
    KEY idx_students_created_at (created_at),
    KEY idx_students_email (email),
    KEY idx_students_room_status (room_status)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Admins Table
-- =====================================================
CREATE TABLE IF NOT EXISTS admins (
    id int(11) NOT NULL AUTO_INCREMENT,
    username varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    role enum('super_admin','admin','staff') DEFAULT 'admin',
    full_name varchar(255) NOT NULL,
    mobile varchar(20) DEFAULT NULL,
    profile_photo varchar(255) DEFAULT NULL,
    is_active tinyint(1) DEFAULT 1,
    last_login timestamp NULL DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY username (username),
    UNIQUE KEY email (email),
    KEY idx_role (role),
    KEY idx_is_active (is_active),
    KEY idx_admins_created_at (created_at),
    KEY idx_admins_email (email)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Rooms Table
-- =====================================================
CREATE TABLE IF NOT EXISTS rooms (
    id int(11) NOT NULL AUTO_INCREMENT,
    room_number varchar(20) NOT NULL,
    floor_number int(11) DEFAULT 1,
    total_beds int(11) DEFAULT 4,
    occupied_beds int(11) DEFAULT 0,
    room_type enum('single','double','triple','four_bed','dormitory') DEFAULT 'four_bed',
    price_per_month decimal(10,2) DEFAULT 0.00,
    status enum('available','full','maintenance') DEFAULT 'available',
    has_ac tinyint(1) DEFAULT 0,
    has_attached_bathroom tinyint(1) DEFAULT 0,
    has_wifi tinyint(1) DEFAULT 1,
    has_study_table tinyint(1) DEFAULT 1,
    has_almirah tinyint(1) DEFAULT 1,
    description text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY room_number (room_number),
    KEY idx_floor_number (floor_number),
    KEY idx_status (status),
    KEY idx_room_type (room_type),
    KEY idx_rooms_room_number (room_number)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Fees Table
-- =====================================================
CREATE TABLE IF NOT EXISTS fees (
    id int(11) NOT NULL AUTO_INCREMENT,
    student_id int(11) NOT NULL,
    month varchar(20) NOT NULL,
    year int(11) NOT NULL,
    amount decimal(10,2) NOT NULL,
    status enum('pending','paid','partial') DEFAULT 'pending',
    paid_amount decimal(10,2) DEFAULT 0.00,
    payment_date date DEFAULT NULL,
    payment_method varchar(50) DEFAULT NULL,
    transaction_id varchar(255) DEFAULT NULL,
    due_date date NOT NULL,
    late_fee decimal(10,2) DEFAULT 0.00,
    total_amount decimal(10,2) GENERATED ALWAYS AS (amount + late_fee) STORED,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_fee (student_id, month, year),
    KEY idx_student_id (student_id),
    KEY idx_status (status),
    KEY idx_month_year (month, year),
    KEY idx_fees_due_date (due_date),
    CONSTRAINT fees_ibfk_1 FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Payments Table
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
    id int(11) NOT NULL AUTO_INCREMENT,
    student_id int(11) NOT NULL,
    fee_id int(11) DEFAULT NULL,
    transaction_id varchar(255) DEFAULT NULL,
    payment_method enum('upi','bank_transfer','cash','cheque','google_pay','paytm','phonepe','bhim','amazon_pay') NOT NULL,
    payment_proof varchar(255) DEFAULT NULL,
    amount decimal(10,2) NOT NULL,
    status enum('pending','approved','rejected') DEFAULT 'pending',
    approved_at timestamp NULL DEFAULT NULL,
    approved_by varchar(255) DEFAULT NULL,
    rejection_reason text DEFAULT NULL,
    rejected_reason text DEFAULT NULL,
    admin_notes text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_student_id (student_id),
    KEY idx_fee_id (fee_id),
    KEY idx_status (status),
    KEY idx_transaction_id (transaction_id),
    KEY idx_payments_created_at (created_at),
    KEY idx_payments_payment_method (payment_method),
    KEY idx_payments_approved_at (approved_at),
    CONSTRAINT payments_ibfk_1 FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT payments_ibfk_2 FOREIGN KEY (fee_id) REFERENCES fees (id) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Payment Info Table
-- =====================================================
CREATE TABLE IF NOT EXISTS payment_info (
    id int(11) NOT NULL AUTO_INCREMENT,
    payment_method varchar(50) NOT NULL,
    upi_id varchar(255) DEFAULT NULL,
    phone_number varchar(20) DEFAULT NULL,
    bank_name varchar(255) DEFAULT NULL,
    account_number varchar(255) DEFAULT NULL,
    ifsc_code varchar(50) DEFAULT NULL,
    account_holder_name varchar(255) DEFAULT NULL,
    qr_code_path varchar(255) DEFAULT NULL,
    is_active tinyint(1) DEFAULT 1,
    display_order int(11) DEFAULT 0,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_payment_method (payment_method),
    KEY idx_is_active (is_active)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Complaints Table
-- =====================================================
CREATE TABLE IF NOT EXISTS complaints (
    id int(11) NOT NULL AUTO_INCREMENT,
    student_id int(11) NOT NULL,
    title varchar(255) NOT NULL,
    description text NOT NULL,
    category enum('maintenance','cleaning','food','security','other') DEFAULT 'other',
    priority enum('low','medium','high','urgent') DEFAULT 'medium',
    status enum('pending','in_progress','resolved','rejected') DEFAULT 'pending',
    admin_response text DEFAULT NULL,
    resolved_by int(11) DEFAULT NULL,
    resolved_at timestamp NULL DEFAULT NULL,
    attachment_path varchar(255) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_student_id (student_id),
    KEY idx_status (status),
    KEY idx_priority (priority),
    KEY idx_category (category),
    KEY idx_complaints_created_at (created_at),
    CONSTRAINT complaints_ibfk_1 FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Notifications Table
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_type enum('student','admin') NOT NULL,
    user_id int(11) DEFAULT NULL,
    title varchar(255) NOT NULL,
    message text NOT NULL,
    type enum('info','success','warning','error') DEFAULT 'info',
    is_read tinyint(1) DEFAULT 0,
    priority enum('low','medium','high') DEFAULT 'medium',
    action_url varchar(255) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_user_type_id (user_type, user_id),
    KEY idx_is_read (is_read),
    KEY idx_created_at (created_at),
    KEY idx_type_priority (type, priority),
    KEY idx_user_type_read (user_type, is_read),
    KEY idx_notifications_composite (user_type, user_id, is_read, created_at)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Email Logs Table
-- =====================================================
CREATE TABLE IF NOT EXISTS email_logs (
    id int(11) NOT NULL AUTO_INCREMENT,
    to_email varchar(255) NOT NULL,
    subject varchar(255) NOT NULL,
    message text NOT NULL,
    status enum('sent','failed','pending') DEFAULT 'pending',
    error_message text DEFAULT NULL,
    retry_count int(11) DEFAULT 0,
    sent_at timestamp NULL DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_to_email (to_email),
    KEY idx_status (status),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 10. Audit Logs Table
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_type enum('student','admin') NOT NULL,
    user_id int(11) DEFAULT NULL,
    action varchar(100) NOT NULL,
    table_name varchar(100) DEFAULT NULL,
    record_id int(11) DEFAULT NULL,
    old_values json DEFAULT NULL,
    new_values json DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    user_agent text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_user_type_id (user_type, user_id),
    KEY idx_action (action),
    KEY idx_table_name (table_name),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 11. Sessions Table
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id int(11) NOT NULL AUTO_INCREMENT,
    session_id varchar(255) NOT NULL,
    user_type enum('student','admin') NOT NULL,
    user_id int(11) DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    user_agent text DEFAULT NULL,
    payload text DEFAULT NULL,
    expires_at timestamp NOT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY session_id (session_id),
    KEY idx_user_type_id (user_type, user_id),
    KEY idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 12. Hostel Settings Table (FIXED - was missing in previous schema)
-- =====================================================
CREATE TABLE IF NOT EXISTS hostel_settings (
    id int(11) NOT NULL AUTO_INCREMENT,
    setting_key varchar(100) NOT NULL,
    setting_value text DEFAULT NULL,
    description text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 13. Admin Notifications Table
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_notifications (
    id int(11) NOT NULL AUTO_INCREMENT,
    admin_id int(11) DEFAULT NULL,
    title varchar(255) NOT NULL,
    message text NOT NULL,
    type enum('info','success','warning','error') DEFAULT 'info',
    is_read tinyint(1) DEFAULT 0,
    priority enum('low','medium','high') DEFAULT 'medium',
    action_url varchar(255) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_admin_id (admin_id),
    KEY idx_is_read (is_read),
    KEY idx_created_at (created_at),
    CONSTRAINT admin_notifications_ibfk_1 FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- Database Views for Enhanced Reporting
-- =====================================================

-- Student Payment Summary View
CREATE OR REPLACE VIEW student_payment_summary AS
SELECT 
    s.id,
    s.full_name,
    s.email,
    s.mobile,
    s.status as student_status,
    s.room_status,
    r.room_number,
    r.floor_number,
    COUNT(p.id) as total_payments,
    COALESCE(SUM(CASE WHEN p.status = 'approved' THEN p.amount ELSE 0 END), 0) as total_paid,
    MAX(p.created_at) as last_payment_date
FROM students s
LEFT JOIN rooms r ON s.room_id = r.id
LEFT JOIN payments p ON s.id = p.student_id
GROUP BY s.id, s.full_name, s.email, s.mobile, s.status, s.room_status, r.room_number, r.floor_number;

-- Monthly Payment Statistics View
CREATE OR REPLACE VIEW monthly_payment_stats AS
SELECT 
    MONTH(p.created_at) as month,
    YEAR(p.created_at) as year,
    COUNT(*) as payment_count,
    SUM(p.amount) as total_amount,
    AVG(p.amount) as average_amount,
    COUNT(CASE WHEN p.status = 'approved' THEN 1 END) as approved_count,
    SUM(CASE WHEN p.status = 'approved' THEN p.amount ELSE 0 END) as approved_total
FROM payments p
GROUP BY MONTH(p.created_at), YEAR(p.created_at)
ORDER BY YEAR(p.created_at), MONTH(p.created_at);

-- Room Occupancy Summary View
CREATE OR REPLACE VIEW room_occupancy_summary AS
SELECT 
    r.id,
    r.room_number,
    r.floor_number,
    r.total_beds,
    r.occupied_beds,
    r.price_per_month,
    r.status,
    CASE 
        WHEN r.occupied_beds = 0 THEN 'Available'
        WHEN r.occupied_beds < r.total_beds THEN 'Partially Occupied'
        WHEN r.occupied_beds = r.total_beds THEN 'Full'
        ELSE 'Overcrowded'
    END as occupancy_status,
    ROUND((r.occupied_beds / r.total_beds) * 100, 2) as occupancy_percentage
FROM rooms r
ORDER BY r.floor_number, r.room_number;

-- Student Complaints Summary View
CREATE OR REPLACE VIEW student_complaints_summary AS
SELECT 
    s.id as student_id,
    s.full_name,
    s.email,
    COUNT(c.id) as total_complaints,
    COUNT(CASE WHEN c.status = 'resolved' THEN 1 END) as resolved_complaints,
    COUNT(CASE WHEN c.status = 'pending' THEN 1 END) as pending_complaints,
    COUNT(CASE WHEN c.priority = 'high' THEN 1 END) as high_priority_complaints,
    MAX(c.created_at) as last_complaint_date
FROM students s
LEFT JOIN complaints c ON s.id = c.student_id
GROUP BY s.id, s.full_name, s.email
ORDER BY s.full_name;

-- =====================================================
-- Default Data Insertion
-- =====================================================

-- Default Admin Account
INSERT IGNORE INTO admins (username, password, full_name, email, mobile, is_active) 
VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', 'aaravraj799246@gmail.com', '9876543210', 1);

-- Default Payment Methods
INSERT IGNORE INTO payment_info (payment_method, upi_id, phone_number, qr_code_path, is_active, display_order) VALUES
('upi', 'aaravraj799246@okaxis', '7992465964', 'QR code/upi_qr.png', 1, 1),
('google_pay', 'aaravraj799246@okaxis', '7992465964', 'QR code/upi_qr.png', 1, 2),
('phonepe', 'aaravraj799246@okaxis', '7992465964', 'QR code/upi_qr.png', 1, 3),
('paytm', 'aaravraj799246@okaxis', '7992465964', 'QR code/upi_qr.png', 1, 4),
('bank_transfer', NULL, NULL, NULL, 1, 5);

-- Default Hostel Settings
INSERT IGNORE INTO hostel_settings (setting_key, setting_value, description) VALUES
('hostel_name', 'Aditya Boys Hostel', 'Name of the hostel'),
('hostel_address', '123 College Road, Pune', 'Address of the hostel'),
('hostel_phone', '+91-20-1234-5678', 'Contact phone number'),
('hostel_email', 'info@adityahostel.com', 'Contact email'),
('monthly_fee', '5000.00', 'Default monthly fee amount'),
('late_fee_percentage', '10.00', 'Late fee percentage'),
('currency', 'INR', 'Currency code');

-- =====================================================
-- Schema Complete
-- =====================================================

-- =====================================================
-- Recent Updates & Changelog
-- =====================================================

-- Version 2.5 (March 26, 2026)
-- - Added performance indexes to notifications table:
--   * idx_type_priority (type, priority)
--   * idx_user_type_read (user_type, is_read) 
--   * idx_notifications_composite (user_type, user_id, is_read, created_at)
-- - Enhanced notification management system
-- - Improved dark mode compatibility for student notifications
-- - Fixed notification counting and display issues

-- Version 2.4 (March 22, 2026)
-- - Added comprehensive notification system
-- - Enhanced payment management with QR codes
-- - Improved room allocation system
-- - Added bulk email functionality

-- =====================================================
-- Schema Statistics
-- =====================================================

-- Total Tables: 13
-- Foreign Keys: 6
-- Indexes: 38 (3 new indexes added in v2.5)
-- Views: 4
-- Default Data: Admin account, payment methods, hostel settings
-- Schema Version: v2.5
-- Last Updated: March 26, 2026

-- =====================================================
-- Performance Notes
-- =====================================================
-- - All frequently queried columns are properly indexed
-- - Composite indexes optimize common query patterns
-- - Foreign key constraints ensure data integrity
-- - Optimized for both read and write operations
-- =====================================================
