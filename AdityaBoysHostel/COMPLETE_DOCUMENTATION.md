# Aditya Boys Hostel Management System - Complete Documentation

## Table of Contents

1. [Introduction](#introduction)
2. [System Overview](#system-overview)
3. [Technical Architecture](#technical-architecture)
4. [Database Design](#database-design)
5. [User Management](#user-management)
6. [Room Management](#room-management)
7. [Fee Management](#fee-management)
8. [Payment System](#payment-system)
9. [Complaint Management](#complaint-management)
10. [Notification System](#notification-system)
11. [Security Features](#security-features)
12. [Installation Guide](#installation-guide)
13. [User Manuals](#user-manuals)
14. [API Documentation](#api-documentation)
15. [Troubleshooting](#troubleshooting)
16. [Maintenance Guide](#maintenance-guide)
17. [Best Practices](#best-practices)
18. [Appendices](#appendices)

---

## 1. Introduction

### 1.1 Project Overview

The Aditya Boys Hostel Management System is a comprehensive web-based application designed to streamline and automate the management of hostel operations. This system provides an integrated solution for managing students, rooms, fees, payments, complaints, and communications within a hostel environment.

### 1.2 System Purpose

The primary purpose of this system is to:
- Automate administrative tasks and reduce manual workload
- Provide efficient room allocation and management
- Streamline fee collection and payment processing
- Facilitate communication between administration and students
- Maintain comprehensive records and generate reports
- Enhance security and data management

### 1.3 Key Features

#### Core Features
- **Student Management**: Registration, profile management, and approval workflow
- **Room Management**: Room allocation, availability tracking, and maintenance scheduling
- **Fee Management**: Fee structure configuration, billing, and payment tracking
- **Payment Processing**: Multiple payment methods with QR code integration
- **Complaint System**: Complaint submission, tracking, and resolution
- **Notification System**: Real-time notifications and communication

#### Advanced Features
- **Multi-role Authentication**: Secure access control for different user types
- **Real-time Dashboard**: Comprehensive analytics and reporting
- **Mobile Responsive**: Works seamlessly across all devices
- **Email Integration**: Automated email notifications and communications
- **Data Export**: Export reports in various formats (Excel, PDF)
- **Audit Logging**: Complete activity tracking for security

### 1.4 Target Audience

This documentation is intended for:
- System Administrators
- Database Administrators
- IT Support Staff
- Hostel Management Personnel
- Developers and Technical Staff
- End Users (Administrators and Students)

---

## 2. System Overview

### 2.1 System Architecture

The Aditya Boys Hostel Management System follows a three-tier architecture pattern:

#### 2.1.1 Presentation Layer (Frontend)
- **Technology Stack**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Framework**: Bootstrap 5.3.2 for responsive design
- **UI Components**: Custom components with consistent styling
- **JavaScript**: jQuery and vanilla JavaScript for interactions
- **Icons**: Font Awesome 6.4 for iconography

#### 2.1.2 Application Layer (Backend)
- **Core Technology**: PHP 8.x with object-oriented programming
- **Session Management**: Secure session handling with timeout
- **Authentication**: Multi-role authentication system
- **Request Routing**: URL rewriting with .htaccess
- **Error Handling**: Comprehensive error management system

#### 2.1.3 Data Layer (Database)
- **Database Engine**: MySQL 8.x with InnoDB storage engine
- **Character Set**: UTF8MB4 for full Unicode support
- **Transactions**: ACID compliant transaction support
- **Indexing**: Optimized indexes for performance
- **Foreign Keys**: Referential integrity enforcement

### 2.2 System Components

#### 2.2.1 Core Modules

1. **Authentication Module**
   - Multi-role login system
   - Session management
   - Password recovery
   - Security features

2. **User Management Module**
   - Student registration and approval
   - Admin and staff management
   - Profile management
   - Access control

3. **Room Management Module**
   - Room creation and configuration
   - Bed allocation system
   - Availability tracking
   - Maintenance scheduling

4. **Fee Management Module**
   - Fee structure configuration
   - Automated billing
   - Payment tracking
   - Financial reporting

5. **Payment Processing Module**
   - Multiple payment methods
   - QR code integration
   - Payment verification
   - Transaction logging

6. **Complaint Management Module**
   - Complaint submission
   - Category-based routing
   - Status tracking
   - Resolution workflow

7. **Notification Module**
   - Real-time notifications
   - Email integration
   - SMS capabilities
   - Alert management

#### 2.2.2 Supporting Components

1. **File Management System**
   - Secure file uploads
   - Document storage
   - Access control
   - Backup management

2. **Reporting System**
   - Dynamic report generation
   - Data visualization
   - Export capabilities
   - Scheduled reports

3. **Audit System**
   - Activity logging
   - Security monitoring
   - Compliance tracking
   - Forensic analysis

### 2.3 User Roles and Permissions

#### 2.3.1 Super Admin
- Complete system access and configuration
- User account management (all roles)
- Database backup and restore
- System settings and security policies
- Audit log management
- Financial reporting and analysis

#### 2.3.2 Admin
- Student registration and approval
- Room allocation and management
- Fee management and payment verification
- Complaint handling and resolution
- Report generation and analysis
- Notification management

#### 2.3.3 Staff
- Room maintenance tasks
- Basic student information viewing
- Complaint logging and status updates
- Visitor management
- Attendance marking
- Limited reporting capabilities

#### 2.3.4 Student
- Profile management
- Fee payments
- Complaint submission
- Room information viewing
- Notification access
- Personal data management

---

## 3. Technical Architecture

### 3.1 System Design Principles

#### 3.1.1 Scalability
- Modular architecture for easy expansion
- Database optimization for large datasets
- Caching mechanisms for performance
- Load balancing support

#### 3.1.2 Security
- Multi-layered security approach
- Encryption at rest and in transit
- Role-based access control
- Comprehensive audit logging

#### 3.1.3 Performance
- Optimized database queries
- Efficient caching strategies
- Minimal server load
- Fast response times

#### 3.1.4 Maintainability
- Clean code architecture
- Comprehensive documentation
- Modular design patterns
- Standardized coding practices

### 3.2 Database Architecture

#### 3.2.1 Database Design Principles
- Normalization to reduce redundancy
- Proper indexing for performance
- Foreign key constraints for integrity
- Optimized data types for storage

#### 3.2.2 Core Database Tables

1. **students**
   - Primary student information
   - Personal and contact details
   - Room allocation information
   - Status tracking

2. **admins**
   - Administrator accounts
   - Role-based permissions
   - Login credentials
   - Activity tracking

3. **rooms**
   - Room configuration
   - Capacity and amenities
   - Availability status
   - Maintenance records

4. **fees**
   - Fee structure definition
   - Billing information
   - Payment status
   - Due date management

5. **payments**
   - Payment transactions
   - Verification workflow
   - Payment methods
   - Transaction details

6. **complaints**
   - Complaint details
   - Category and priority
   - Status tracking
   - Resolution information

7. **notifications**
   - System notifications
   - User messages
   - Read/unread status
   - Priority levels

### 3.3 Security Architecture

#### 3.3.1 Authentication System
- Secure password hashing (bcrypt/Argon2)
- Session management with timeout
- Multi-factor authentication support
- Rate limiting for brute force protection

#### 3.3.2 Authorization System
- Role-based access control (RBAC)
- Permission matrix implementation
- Resource-level access control
- Dynamic permission assignment

#### 3.3.3 Data Protection
- Input validation and sanitization
- SQL injection prevention
- XSS protection with output encoding
- CSRF protection with tokens

#### 3.3.4 File Security
- Secure file upload validation
- File type and size restrictions
- Virus scanning capabilities
- Access control lists

### 3.4 Performance Architecture

#### 3.4.1 Database Optimization
- Strategic indexing for fast queries
- Query optimization techniques
- Connection pooling
- Caching layer implementation

#### 3.4.2 Application Optimization
- OPcache for PHP bytecode caching
- Session storage optimization
- Memory management
- CPU usage optimization

#### 3.4.3 Frontend Optimization
- Lazy loading for images
- Debounced search functionality
- Minified CSS and JavaScript
- Browser caching headers

---

## 4. Database Design

### 4.1 Database Schema Overview

The database consists of 13 core tables designed for optimal performance and data integrity. The schema follows normalization principles to reduce redundancy and ensure data consistency.

### 4.2 Core Tables

#### 4.2.1 Students Table

**Purpose**: Stores student information and profile data.

**Structure**:
```sql
CREATE TABLE students (
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
    KEY idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Unique email constraint for login
- Status tracking for registration workflow
- Room allocation tracking
- Soft delete capability with is_active flag

#### 4.2.2 Admins Table

**Purpose**: Stores administrator and staff account information.

**Structure**:
```sql
CREATE TABLE admins (
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
    UNIQUE KEY email (email),
    KEY idx_role (role),
    KEY idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Role-based access control
- Last login tracking
- Profile management
- Activity monitoring

#### 4.2.3 Rooms Table

**Purpose**: Manages hostel room information and allocation details.

**Structure**:
```sql
CREATE TABLE rooms (
    id int(11) NOT NULL AUTO_INCREMENT,
    room_number varchar(50) NOT NULL,
    floor_number int(11) DEFAULT NULL,
    total_beds int(11) DEFAULT NULL,
    occupied_beds int(11) DEFAULT 0,
    room_type enum('single','double','triple','four_bed','dormitory') DEFAULT NULL,
    price_per_month decimal(10,2) DEFAULT NULL,
    status enum('available','full','maintenance') DEFAULT 'available',
    has_ac tinyint(1) DEFAULT 0,
    has_attached_bathroom tinyint(1) DEFAULT 0,
    has_wifi tinyint(1) DEFAULT 0,
    has_study_table tinyint(1) DEFAULT 0,
    has_almirah tinyint(1) DEFAULT 0,
    description text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY room_number (room_number),
    KEY idx_room_type (room_type),
    KEY idx_status (status),
    KEY idx_floor_number (floor_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Room capacity and occupancy tracking
- Amenity details
- Availability status
- Pricing information

#### 4.2.4 Fees Table

**Purpose**: Tracks fee structure and payment obligations.

**Structure**:
```sql
CREATE TABLE fees (
    id int(11) NOT NULL AUTO_INCREMENT,
    student_id int(11) NOT NULL,
    month varchar(20) NOT NULL,
    year int(11) NOT NULL,
    amount decimal(10,2) NOT NULL,
    status enum('pending','paid','partial') DEFAULT 'pending',
    paid_amount decimal(10,2) DEFAULT 0.00,
    payment_date date DEFAULT NULL,
    payment_method varchar(50) DEFAULT NULL,
    transaction_id varchar(100) DEFAULT NULL,
    due_date date DEFAULT NULL,
    late_fee decimal(10,2) DEFAULT 0.00,
    total_amount decimal(10,2) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_student_id (student_id),
    KEY idx_status (status),
    KEY idx_month_year (month, year),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Monthly fee tracking
- Late fee calculation
- Payment status management
- Due date tracking

#### 4.2.5 Payments Table

**Purpose**: Records actual payment transactions and verification status.

**Structure**:
```sql
CREATE TABLE payments (
    id int(11) NOT NULL AUTO_INCREMENT,
    student_id int(11) NOT NULL,
    fee_id int(11) DEFAULT NULL,
    transaction_id varchar(100) DEFAULT NULL,
    payment_method enum('upi','bank_transfer','cash','cheque','google_pay','paytm','phonepe') DEFAULT NULL,
    payment_proof varchar(255) DEFAULT NULL,
    amount decimal(10,2) NOT NULL,
    status enum('pending','approved','rejected') DEFAULT 'pending',
    approved_at timestamp NULL DEFAULT NULL,
    approved_by int(11) DEFAULT NULL,
    rejection_reason text DEFAULT NULL,
    admin_notes text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_student_id (student_id),
    KEY idx_fee_id (fee_id),
    KEY idx_status (status),
    KEY idx_payment_method (payment_method),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Payment verification workflow
- Multiple payment method support
- Transaction tracking
- Admin approval process

#### 4.2.6 Complaints Table

**Purpose**: Manages student complaints and resolution tracking.

**Structure**:
```sql
CREATE TABLE complaints (
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
    KEY idx_category (category),
    KEY idx_priority (priority),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Category-based organization
- Priority level assignment
- Status tracking
- Resolution workflow

#### 4.2.7 Notifications Table

**Purpose**: Stores system notifications for users.

**Structure**:
```sql
CREATE TABLE notifications (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    user_type enum('student','admin') NOT NULL,
    title varchar(255) NOT NULL,
    message text NOT NULL,
    is_read tinyint(1) DEFAULT 0,
    priority enum('low','medium','high','urgent') DEFAULT 'medium',
    action_url varchar(255) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_user_type (user_type),
    KEY idx_is_read (is_read),
    KEY idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**Key Features**:
- Multi-user notification support
- Read/unread tracking
- Priority levels
- Action URL support

### 4.3 Database Relationships

#### 4.3.1 Entity Relationship Overview

The database follows these key relationships:

1. **Students to Fees**: One-to-Many (One student can have multiple fee records)
2. **Students to Payments**: One-to-Many (One student can make multiple payments)
3. **Students to Complaints**: One-to-Many (One student can submit multiple complaints)
4. **Students to Notifications**: One-to-Many (One student can receive multiple notifications)
5. **Rooms to Students**: One-to-Many (One room can accommodate multiple students)
6. **Fees to Payments**: One-to-Many (One fee can have multiple payment attempts)
7. **Admins to Complaints**: One-to-Many (One admin can resolve multiple complaints)

#### 4.3.2 Foreign Key Constraints

```sql
-- Student-Fee Relationship
ALTER TABLE fees ADD CONSTRAINT fk_fees_student 
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE;

-- Student-Payment Relationship
ALTER TABLE payments ADD CONSTRAINT fk_payments_student 
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE;

-- Fee-Payment Relationship
ALTER TABLE payments ADD CONSTRAINT fk_payments_fee 
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE SET NULL;

-- Student-Complaint Relationship
ALTER TABLE complaints ADD CONSTRAINT fk_complaints_student 
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE;

-- Student-Room Relationship
ALTER TABLE students ADD CONSTRAINT fk_students_room 
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL;
```

### 4.4 Database Optimization

#### 4.4.1 Indexing Strategy

**Primary Indexes**:
- All primary keys automatically indexed
- Unique constraints on email fields

**Secondary Indexes**:
```sql
-- Performance Indexes
CREATE INDEX idx_student_fee_status ON fees(student_id, status);
CREATE INDEX idx_payment_approval ON payments(status, approved_at);
CREATE INDEX idx_complaint_status_priority ON complaints(status, priority);
CREATE INDEX idx_notification_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_student_room_status ON students(room_id, room_status);
CREATE INDEX idx_student_active_approved ON students(is_active, status);
```

**Composite Indexes**:
- Multi-column indexes for common query patterns
- Optimized for JOIN operations
- Covering indexes for frequent queries

#### 4.4.2 Query Optimization

**Optimized Queries**:
```sql
-- Student Fee Summary
SELECT s.id, s.full_name, s.email, 
       COUNT(f.id) as total_fees,
       SUM(CASE WHEN f.status = 'paid' THEN 1 ELSE 0 END) as paid_fees,
       SUM(f.amount) as total_amount,
       SUM(CASE WHEN f.status = 'paid' THEN f.paid_amount ELSE 0 END) as paid_amount
FROM students s
LEFT JOIN fees f ON s.id = f.student_id
WHERE s.status = 'approved' AND s.is_active = 1
GROUP BY s.id, s.full_name, s.email;

-- Room Occupancy Report
SELECT r.room_number, r.total_beds, r.occupied_beds,
       (r.total_beds - r.occupied_beds) as available_beds,
       ROUND((r.occupied_beds / r.total_beds) * 100, 2) as occupancy_percentage
FROM rooms r
ORDER BY r.room_number;
```

---

## 5. User Management

### 5.1 User Registration System

#### 5.1.1 Student Registration Process

**Registration Workflow**:
1. Student fills registration form
2. System validates input data
3. Profile photo upload (optional)
4. Application submitted for admin approval
5. Admin reviews and approves/rejects
6. Student receives notification of decision

**Registration Form Fields**:
- Personal Information (Name, Email, Mobile)
- Parent/Guardian Information
- Address Details
- Academic Information
- Medical Information (optional)
- Profile Photo Upload

**Validation Rules**:
```php
// Email Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Please enter a valid email address";
}

// Mobile Validation
if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    $errors['mobile'] = "Please enter a valid 10-digit mobile number";
}

// Password Strength
if (strlen($password) < 8) {
    $errors['password'] = "Password must be at least 8 characters long";
}
```

#### 5.1.2 Admin Account Management

**Admin Types**:
1. **Super Admin**: Complete system control
2. **Admin**: Day-to-day operations
3. **Staff**: Limited operational access

**Admin Creation Process**:
1. Super Admin creates admin account
2. Assign role and permissions
3. Set initial password
4. Send account details via email
5. Admin changes password on first login

### 5.2 Authentication System

#### 5.2.1 Login Process

**Authentication Flow**:
1. User submits credentials
2. System validates against database
3. Password verification using secure hashing
4. Session creation with security parameters
5. Role-based permission assignment
6. Redirect to appropriate dashboard

**Security Features**:
- Password hashing with bcrypt
- Session timeout after inactivity
- IP address validation
- User agent verification
- Rate limiting for failed attempts

#### 5.2.2 Session Management

**Session Configuration**:
```php
// Secure Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hour
```

**Session Validation**:
```php
function validateSession() {
    // Check session exists
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check session timeout
    $login_time = $_SESSION['login_time'] ?? 0;
    if (time() - $login_time > 3600) {
        session_destroy();
        return false;
    }
    
    // Check IP consistency
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        return false;
    }
    
    return true;
}
```

#### 5.2.3 Password Security

**Password Policy**:
- Minimum 8 characters
- Must include uppercase, lowercase, numbers, special characters
- Cannot reuse last 5 passwords
- Expires every 90 days

**Password Hashing**:
```php
function hashPassword($password) {
    $options = [
        'cost' => 12,
        'memory' => '1MB',
        'time' => 4,
        'threads' => 2
    ];
    return password_hash($password, PASSWORD_ARGON2ID, $options);
}
```

### 5.3 Profile Management

#### 5.3.1 Student Profile

**Profile Sections**:
1. **Personal Information**: Name, contact details, address
2. **Academic Information**: Course, year, roll number
3. **Parent/Guardian Information**: Contact details
4. **Medical Information**: Blood group, medical conditions
5. **Room Information**: Current allocation details
6. **Payment History**: Fee payment records

**Profile Update Process**:
1. Student logs into system
2. Navigates to profile section
3. Updates required fields
4. Uploads new documents if needed
5. Saves changes
6. System validates and updates database

#### 5.3.2 Admin Profile

**Admin Profile Features**:
- Personal information management
- Password change functionality
- Profile photo upload
- Activity history viewing
- Permission display (read-only)

### 5.4 User Status Management

#### 5.4.1 Student Status Types

**Status Values**:
- **pending**: New registration awaiting approval
- **approved**: Student approved and active
- **rejected**: Registration rejected
- **inactive**: Student temporarily inactive
- **graduated**: Student completed course

**Status Transitions**:
```php
function updateStudentStatus($student_id, $new_status, $admin_id) {
    // Validate status transition
    $current_status = getStudentStatus($student_id);
    
    if (!isValidStatusTransition($current_status, $new_status)) {
        throw new Exception("Invalid status transition");
    }
    
    // Update status
    $stmt = $conn->prepare("UPDATE students SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_status, $student_id);
    $stmt->execute();
    
    // Log the change
    logStatusChange($student_id, $current_status, $new_status, $admin_id);
    
    // Send notification to student
    sendStatusChangeNotification($student_id, $new_status);
}
```

#### 5.4.2 Admin Status Management

**Admin Account States**:
- **Active**: Account is functional
- **Inactive**: Account temporarily disabled
- **Suspended**: Account suspended for security reasons

**Account Deactivation**:
```php
function deactivateAdmin($admin_id, $reason, $deactivated_by) {
    $stmt = $conn->prepare("UPDATE admins SET is_active = 0, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    
    // Log deactivation
    logAccountDeactivation($admin_id, $reason, $deactivated_by);
    
    // Terminate active sessions
    terminateAdminSessions($admin_id);
}
```

---

## 6. Room Management

### 6.1 Room Configuration

#### 6.1.1 Room Creation

**Room Properties**:
- Room number (unique identifier)
- Floor number
- Room type (single, double, triple, four_bed, dormitory)
- Total bed capacity
- Monthly rent amount
- Available amenities
- Current status

**Room Creation Process**:
1. Admin accesses room management section
2. Clicks "Add New Room"
3. Fills room configuration form
4. Selects available amenities
5. Sets monthly rent
6. Saves room details
7. System validates and creates room record

**Room Types and Features**:
```php
$room_types = [
    'single' => [
        'capacity' => 1,
        'base_price' => 5000,
        'amenities' => ['bed', 'study_table', 'almirah']
    ],
    'double' => [
        'capacity' => 2,
        'base_price' => 4000,
        'amenities' => ['bed', 'study_table', 'almirah', 'attached_bathroom']
    ],
    'triple' => [
        'capacity' => 3,
        'base_price' => 3500,
        'amenities' => ['bed', 'study_table', 'almirah', 'attached_bathroom', 'wifi']
    ],
    'four_bed' => [
        'capacity' => 4,
        'base_price' => 3000,
        'amenities' => ['bed', 'study_table', 'almirah', 'attached_bathroom', 'wifi', 'ac']
    ],
    'dormitory' => [
        'capacity' => 8,
        'base_price' => 2000,
        'amenities' => ['bed', 'almirah', 'common_bathroom', 'wifi']
    ]
];
```

#### 6.1.2 Amenity Management

**Available Amenities**:
- Air Conditioning (AC)
- Attached Bathroom
- WiFi Internet
- Study Table
- Almirah/Wardrobe
- Hot Water
- Refrigerator
- TV

**Amenity Pricing**:
```php
$amenity_prices = [
    'ac' => 1000,
    'attached_bathroom' => 500,
    'wifi' => 200,
    'study_table' => 100,
    'almirah' => 150,
    'hot_water' => 300,
    'refrigerator' => 400,
    'tv' => 250
];
```

### 6.2 Room Allocation System

#### 6.2.1 Allocation Algorithm

**Allocation Criteria**:
1. Room availability
2. Student preferences
3. Payment status
4. Room type requirements
5. Floor preferences

**Allocation Process**:
```php
function allocateRoomToStudent($student_id, $preferences = []) {
    // Get student details
    $student = getStudentDetails($student_id);
    
    // Find available rooms based on preferences
    $available_rooms = findAvailableRooms($preferences);
    
    if (empty($available_rooms)) {
        throw new Exception("No rooms available matching preferences");
    }
    
    // Select best room based on algorithm
    $selected_room = selectBestRoom($available_rooms, $student);
    
    // Allocate room
    $stmt = $conn->prepare("UPDATE students SET room_id = ?, bed_number = ?, room_status = 'allocated', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $selected_room['id'], $selected_room['bed_number'], $student_id);
    $stmt->execute();
    
    // Update room occupancy
    updateRoomOccupancy($selected_room['id']);
    
    // Send notification
    sendRoomAllocationNotification($student_id, $selected_room);
    
    return $selected_room;
}
```

#### 6.2.2 Bed Management

**Bed Assignment**:
- Automatic bed number assignment
- Bed tracking per room
- Bed status management
- Bed change requests

**Bed Numbering System**:
```php
function generateBedNumber($room_type, $bed_index) {
    $prefixes = [
        'single' => 'S',
        'double' => 'D',
        'triple' => 'T',
        'four_bed' => 'F',
        'dormitory' => 'DORM'
    ];
    
    return $prefixes[$room_type] . ($bed_index + 1);
}
```

### 6.3 Room Availability Management

#### 6.3.1 Real-time Availability

**Availability Tracking**:
```php
function getRoomAvailability($room_id) {
    $stmt = $conn->prepare("
        SELECT r.*, 
               (r.total_beds - r.occupied_beds) as available_beds,
               ROUND((r.occupied_beds / r.total_beds) * 100, 2) as occupancy_percentage
        FROM rooms r 
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
```

**Status Updates**:
```php
function updateRoomStatus($room_id) {
    $room = getRoomDetails($room_id);
    
    if ($room['occupied_beds'] == 0) {
        $status = 'available';
    } elseif ($room['occupied_beds'] >= $room['total_beds']) {
        $status = 'full';
    } else {
        $status = 'available';
    }
    
    $stmt = $conn->prepare("UPDATE rooms SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $room_id);
    $stmt->execute();
}
```

#### 6.3.2 Maintenance Management

**Maintenance Workflow**:
1. Room marked for maintenance
2. Students notified in advance
3. Temporary relocation if needed
4. Maintenance work completed
5. Room returned to service

**Maintenance Scheduling**:
```php
function scheduleMaintenance($room_id, $start_date, $end_date, $description) {
    // Mark room as under maintenance
    $stmt = $conn->prepare("UPDATE rooms SET status = 'maintenance', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    
    // Notify affected students
    $affected_students = getStudentsInRoom($room_id);
    foreach ($affected_students as $student) {
        sendMaintenanceNotification($student['id'], $room_id, $start_date, $end_date);
    }
    
    // Log maintenance schedule
    logMaintenanceSchedule($room_id, $start_date, $end_date, $description);
}
```

### 6.4 Room Change Management

#### 6.4.1 Room Change Requests

**Request Process**:
1. Student submits room change request
2. Admin reviews request and availability
3. Admin approves/rejects request
4. If approved, process room change
5. Update records and notify parties

**Room Change Algorithm**:
```php
function processRoomChange($student_id, $new_room_id, $reason) {
    // Get current room details
    $current_room = getStudentCurrentRoom($student_id);
    $new_room = getRoomDetails($new_room_id);
    
    // Check availability
    if ($new_room['occupied_beds'] >= $new_room['total_beds']) {
        throw new Exception("New room is not available");
    }
    
    // Update student room allocation
    $stmt = $conn->prepare("UPDATE students SET room_id = ?, bed_number = ?, room_status = 'allocated', updated_at = NOW() WHERE id = ?");
    $new_bed_number = getNextAvailableBed($new_room_id);
    $stmt->bind_param("isi", $new_room_id, $new_bed_number, $student_id);
    $stmt->execute();
    
    // Update room occupancies
    decrementRoomOccupancy($current_room['id']);
    incrementRoomOccupancy($new_room_id);
    
    // Log the change
    logRoomChange($student_id, $current_room['id'], $new_room_id, $reason);
    
    // Send notifications
    sendRoomChangeNotification($student_id, $current_room, $new_room);
}
```

#### 6.4.2 Room Change History

**History Tracking**:
```sql
CREATE TABLE room_change_history (
    id int(11) NOT NULL AUTO_INCREMENT,
    student_id int(11) NOT NULL,
    old_room_id int(11) NOT NULL,
    new_room_id int(11) NOT NULL,
    change_reason text DEFAULT NULL,
    changed_by int(11) NOT NULL,
    change_date timestamp NOT NULL DEFAULT current_timestamp(),
    
    PRIMARY KEY (id),
    KEY idx_student_id (student_id),
    KEY idx_change_date (change_date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 7. Fee Management

### 7.1 Fee Structure Configuration

#### 7.1.1 Fee Types

**Fee Categories**:
1. **Accommodation Fee**: Monthly room rent
2. **Mess Fee**: Food services
3. **Electricity Fee**: Power consumption
4. **Water Fee**: Water usage
5. **Maintenance Fee**: Building maintenance
6. **Security Fee**: Security services
7. **Internet Fee**: WiFi services
8. **Other Fees**: Miscellaneous charges

**Fee Structure Setup**:
```php
function createFeeStructure($fee_data) {
    $stmt = $conn->prepare("
        INSERT INTO fee_structures 
        (fee_type, amount, room_type_applicable, frequency, description, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("sdsssi", 
        $fee_data['fee_type'],
        $fee_data['amount'],
        $fee_data['room_type'],
        $fee_data['frequency'],
        $fee_data['description'],
        $fee_data['is_active']
    );
    
    $stmt->execute();
    return $conn->insert_id;
}
```

#### 7.1.2 Dynamic Fee Calculation

**Fee Calculation Logic**:
```php
function calculateMonthlyFee($student_id, $month, $year) {
    $student = getStudentDetails($student_id);
    $room = getRoomDetails($student['room_id']);
    
    $base_fee = $room['price_per_month'];
    $total_fee = $base_fee;
    
    // Add amenity charges
    if ($room['has_ac']) $total_fee += getAmenityFee('ac');
    if ($room['has_wifi']) $total_fee += getAmenityFee('wifi');
    if ($room['has_attached_bathroom']) $total_fee += getAmenityFee('attached_bathroom');
    
    // Apply discounts if any
    $discount = getApplicableDiscount($student_id, $total_fee);
    $total_fee -= $discount;
    
    // Add late fees if applicable
    $late_fee = calculateLateFee($student_id, $month, $year);
    $total_fee += $late_fee;
    
    return [
        'base_fee' => $base_fee,
        'amenity_fees' => $total_fee - $base_fee - $late_fee - $discount,
        'discount' => $discount,
        'late_fee' => $late_fee,
        'total_fee' => $total_fee
    ];
}
```

### 7.2 Fee Generation System

#### 7.2.1 Automated Fee Generation

**Monthly Fee Generation**:
```php
function generateMonthlyFees($month, $year) {
    // Get all active students
    $students = getActiveStudents();
    
    foreach ($students as $student) {
        // Check if fee already generated
        if (!isFeeGenerated($student['id'], $month, $year)) {
            $fee_details = calculateMonthlyFee($student['id'], $month, $year);
            
            // Create fee record
            $stmt = $conn->prepare("
                INSERT INTO fees 
                (student_id, month, year, amount, status, due_date, total_amount, created_at)
                VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW())
            ");
            
            $due_date = date('Y-m-d', strtotime("last day of $month $year"));
            $stmt->bind_param("isdsdd", 
                $student['id'],
                $month,
                $year,
                $fee_details['total_fee'],
                $due_date,
                $fee_details['total_fee']
            );
            
            $stmt->execute();
            
            // Send notification to student
            sendFeeGenerationNotification($student['id'], $month, $year, $fee_details);
        }
    }
    
    return count($students) . " fee records processed";
}
```

#### 7.2.2 Fee Due Management

**Due Date Calculation**:
```php
function calculateDueDate($month, $year, $grace_days = 5) {
    // Due date is 5th of next month with 5 days grace
    $next_month = date('Y-m-d', strtotime("first day of next month $year-$month"));
    $due_date = date('Y-m-d', strtotime("$next_month +5 days"));
    
    return $due_date;
}
```

**Late Fee Calculation**:
```php
function calculateLateFee($student_id, $month, $year) {
    $fee = getFeeRecord($student_id, $month, $year);
    
    if ($fee['status'] === 'paid') {
        return 0;
    }
    
    $due_date = new DateTime($fee['due_date']);
    $current_date = new DateTime();
    
    if ($current_date <= $due_date) {
        return 0;
    }
    
    $days_late = $current_date->diff($due_date)->days;
    $late_fee_rate = 0.02; // 2% per day
    $late_fee = $fee['amount'] * $late_fee_rate * $days_late;
    
    return min($late_fee, $fee['amount'] * 0.5); // Cap at 50% of fee amount
}
```

### 7.3 Fee Payment Tracking

#### 7.3.1 Payment Status Management

**Payment Status Values**:
- **pending**: Fee generated, not paid
- **partial**: Partial payment made
- **paid**: Full payment received

**Status Update Logic**:
```php
function updateFeePaymentStatus($fee_id) {
    // Get total payments for this fee
    $stmt = $conn->prepare("
        SELECT SUM(amount) as total_paid, COUNT(*) as payment_count
        FROM payments 
        WHERE fee_id = ? AND status = 'approved'
    ");
    $stmt->bind_param("i", $fee_id);
    $stmt->execute();
    $payment_info = $stmt->get_result()->fetch_assoc();
    
    // Get fee details
    $fee = getFeeDetails($fee_id);
    
    // Update status based on payments
    if ($payment_info['total_paid'] >= $fee['total_amount']) {
        $new_status = 'paid';
        $new_paid_amount = $fee['total_amount'];
    } elseif ($payment_info['total_paid'] > 0) {
        $new_status = 'partial';
        $new_paid_amount = $payment_info['total_paid'];
    } else {
        $new_status = 'pending';
        $new_paid_amount = 0;
    }
    
    // Update fee record
    $stmt = $conn->prepare("
        UPDATE fees 
        SET status = ?, paid_amount = ?, payment_date = NOW(), updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("sdi", $new_status, $new_paid_amount, $fee_id);
    $stmt->execute();
    
    return $new_status;
}
```

#### 7.3.2 Payment History

**Payment History Query**:
```php
function getStudentPaymentHistory($student_id, $limit = 12) {
    $stmt = $conn->prepare("
        SELECT f.*, 
               p.payment_method,
               p.transaction_id,
               p.approved_at,
               p.status as payment_status
        FROM fees f
        LEFT JOIN payments p ON f.id = p.fee_id AND p.status = 'approved'
        WHERE f.student_id = ?
        ORDER BY f.year DESC, f.month DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $student_id, $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

### 7.4 Fee Reporting

#### 7.4.1 Collection Reports

**Monthly Collection Report**:
```php
function generateMonthlyCollectionReport($month, $year) {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_students,
            SUM(CASE WHEN f.status = 'paid' THEN 1 ELSE 0 END) as paid_students,
            SUM(CASE WHEN f.status = 'pending' THEN 1 ELSE 0 END) as pending_students,
            SUM(f.amount) as total_amount_due,
            SUM(CASE WHEN f.status = 'paid' THEN f.paid_amount ELSE 0 END) as total_collected,
            SUM(CASE WHEN f.status != 'paid' THEN f.total_amount ELSE 0 END) as outstanding_amount,
            ROUND((SUM(CASE WHEN f.status = 'paid' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as collection_percentage
        FROM fees f
        JOIN students s ON f.student_id = s.id
        WHERE f.month = ? AND f.year = ? AND s.status = 'approved'
    ");
    $stmt->bind_param("si", $month, $year);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
```

#### 7.4.2 Outstanding Fees Report

**Outstanding Fees Analysis**:
```php
function getOutstandingFeesReport($age_days = 30) {
    $stmt = $conn->prepare("
        SELECT 
            f.id,
            f.student_id,
            s.full_name,
            s.email,
            s.mobile,
            f.month,
            f.year,
            f.total_amount,
            f.due_date,
            DATEDIFF(NOW(), f.due_date) as days_overdue,
            f.total_amount - COALESCE(f.paid_amount, 0) as outstanding_amount,
            CASE 
                WHEN DATEDIFF(NOW(), f.due_date) <= 0 THEN 'current'
                WHEN DATEDIFF(NOW(), f.due_date) <= 30 THEN 'overdue_30'
                WHEN DATEDIFF(NOW(), f.due_date) <= 60 THEN 'overdue_60'
                ELSE 'overdue_90'
            END as overdue_category
        FROM fees f
        JOIN students s ON f.student_id = s.id
        WHERE f.status != 'paid' 
        AND s.status = 'approved'
        AND s.is_active = 1
        ORDER BY f.due_date ASC
    ");
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

---

## 8. Payment System

### 8.1 Payment Methods

#### 8.1.1 Supported Payment Methods

**Digital Payment Methods**:
1. **UPI (Unified Payments Interface)**
   - Google Pay
   - PhonePe
   - Paytm
   - BHIM
   - Other UPI apps

2. **Bank Transfer**
   - NEFT
   - RTGS
   - IMPS

3. **Traditional Methods**
   - Cash
   - Cheque
   - Demand Draft

**Payment Method Configuration**:
```php
$payment_methods = [
    'upi' => [
        'name' => 'UPI Payment',
        'icon' => 'fa-mobile',
        'requires_qr' => true,
        'requires_bank_details' => false,
        'display_order' => 1
    ],
    'google_pay' => [
        'name' => 'Google Pay',
        'icon' => 'fa-google',
        'requires_qr' => true,
        'requires_bank_details' => false,
        'display_order' => 2
    ],
    'phonepe' => [
        'name' => 'PhonePe',
        'icon' => 'fa-phone',
        'requires_qr' => true,
        'requires_bank_details' => false,
        'display_order' => 3
    ],
    'paytm' => [
        'name' => 'Paytm',
        'icon' => 'fa-wallet',
        'requires_qr' => true,
        'requires_bank_details' => false,
        'display_order' => 4
    ],
    'bank_transfer' => [
        'name' => 'Bank Transfer',
        'icon' => 'fa-university',
        'requires_qr' => false,
        'requires_bank_details' => true,
        'display_order' => 5
    ]
];
```

#### 8.1.2 Payment Method Management

**Payment Information Storage**:
```sql
CREATE TABLE payment_info (
    id int(11) NOT NULL AUTO_INCREMENT,
    payment_method varchar(50) NOT NULL,
    upi_id varchar(100) DEFAULT NULL,
    phone_number varchar(20) DEFAULT NULL,
    bank_name varchar(100) DEFAULT NULL,
    account_number varchar(50) DEFAULT NULL,
    ifsc_code varchar(20) DEFAULT NULL,
    account_holder_name varchar(100) DEFAULT NULL,
    qr_code_path varchar(255) DEFAULT NULL,
    is_active tinyint(1) DEFAULT 1,
    display_order int(11) DEFAULT 0,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    PRIMARY KEY (id),
    UNIQUE KEY payment_method (payment_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 8.2 QR Code System

#### 8.2.1 QR Code Generation

**QR Code Implementation**:
```php
function generateQRCode($payment_data) {
    // QR code data format
    $qr_data = [
        'upi' => $payment_data['upi_id'],
        'pn' => 'Aditya Boys Hostel',
        'am' => $payment_data['amount'],
        'cu' => 'INR',
        'tn' => $payment_data['transaction_id']
    ];
    
    // Create UPI payment string
    $upi_string = 'upi://pay?';
    $upi_string .= 'pa=' . urlencode($qr_data['upi']);
    $upi_string .= '&pn=' . urlencode($qr_data['pn']);
    $upi_string .= '&am=' . urlencode($qr_data['am']);
    $upi_string .= '&cu=' . urlencode($qr_data['cu']);
    $upi_string .= '&tn=' . urlencode($qr_data['tn']);
    
    // Generate QR code image
    $qr_code_path = 'QR code/' . uniqid() . '_qr.png';
    QRCode::png($upi_string, $qr_code_path, QR_ECLEVEL_M, 8);
    
    return $qr_code_path;
}
```

#### 8.2.2 QR Code Management

**QR Code Update Process**:
```php
function updateQRCode($payment_method, $qr_file) {
    // Validate QR code file
    if (!isValidQRCode($qr_file)) {
        throw new Exception("Invalid QR code file");
    }
    
    // Move QR code to secure location
    $qr_path = 'QR code/' . uniqid() . '_' . basename($qr_file['name']);
    move_uploaded_file($qr_file['tmp_name'], $qr_path);
    
    // Update all UPI-based payment methods with new QR code
    $upi_methods = ['upi', 'google_pay', 'phonepe', 'paytm'];
    
    foreach ($upi_methods as $method) {
        $stmt = $conn->prepare("
            UPDATE payment_info 
            SET qr_code_path = ?, updated_at = NOW() 
            WHERE payment_method = ?
        ");
        $stmt->bind_param("ss", $qr_path, $method);
        $stmt->execute();
    }
    
    return $qr_path;
}
```

### 8.3 Payment Processing

#### 8.3.1 Payment Submission

**Payment Form Processing**:
```php
function processPaymentSubmission($payment_data) {
    // Validate payment data
    $errors = validatePaymentData($payment_data);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Handle file upload
    $payment_proof_path = null;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $payment_proof_path = uploadPaymentProof($_FILES['payment_proof']);
    }
    
    // Create payment record
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (student_id, fee_id, transaction_id, payment_method, payment_proof, amount, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->bind_param("iisssd", 
        $payment_data['student_id'],
        $payment_data['fee_id'],
        $payment_data['transaction_id'],
        $payment_data['payment_method'],
        $payment_proof_path,
        $payment_data['amount']
    );
    
    $stmt->execute();
    $payment_id = $conn->insert_id;
    
    // Send notification to admin
    sendPaymentSubmissionNotification($payment_id);
    
    return ['success' => true, 'payment_id' => $payment_id];
}
```

#### 8.3.2 Payment Verification

**Admin Verification Process**:
```php
function verifyPayment($payment_id, $admin_id, $status, $notes = '') {
    // Get payment details
    $payment = getPaymentDetails($payment_id);
    
    if ($status === 'approved') {
        // Update payment status
        $stmt = $conn->prepare("
            UPDATE payments 
            SET status = 'approved', approved_by = ?, approved_at = NOW(), admin_notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("isi", $admin_id, $notes, $payment_id);
        $stmt->execute();
        
        // Update fee payment status
        updateFeePaymentStatus($payment['fee_id']);
        
        // Send confirmation to student
        sendPaymentConfirmationNotification($payment['student_id'], $payment);
        
    } elseif ($status === 'rejected') {
        // Update payment status
        $stmt = $conn->prepare("
            UPDATE payments 
            SET status = 'rejected', approved_by = ?, rejection_reason = ?, admin_notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("issi", $admin_id, $notes, $notes, $payment_id);
        $stmt->execute();
        
        // Send rejection notification to student
        sendPaymentRejectionNotification($payment['student_id'], $payment, $notes);
    }
    
    return true;
}
```

### 8.4 Payment Analytics

#### 8.4.1 Payment Statistics

**Daily Payment Statistics**:
```php
function getDailyPaymentStats($date) {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_transactions,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_transactions,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_transactions,
            SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as total_collected,
            AVG(CASE WHEN status = 'approved' THEN amount END) as average_payment,
            payment_method,
            COUNT(*) as method_count
        FROM payments 
        WHERE DATE(created_at) = ?
        GROUP BY payment_method
        ORDER BY total_collected DESC
    ");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

#### 8.4.2 Payment Method Analysis

**Payment Method Popularity**:
```php
function getPaymentMethodAnalysis($start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT 
            payment_method,
            COUNT(*) as total_transactions,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_transactions,
            SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as total_amount,
            ROUND(AVG(CASE WHEN status = 'approved' THEN amount END), 2) as average_amount,
            ROUND((SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate
        FROM payments 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY payment_method
        ORDER BY total_amount DESC
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

---

## 9. Complaint Management

### 9.1 Complaint Registration

#### 9.1.1 Complaint Categories

**Complaint Categories**:
1. **Maintenance**: Electrical, plumbing, structural issues
2. **Cleaning**: Room cleaning, common areas, sanitation
3. **Food**: Mess food quality, timing, hygiene
4. **Security**: Security personnel, access control
5. **Other**: Miscellaneous issues

**Priority Levels**:
- **Low**: Minor issues, no immediate attention needed
- **Medium**: Important issues, should be addressed soon
- **High**: Urgent issues, requires immediate attention
- **Urgent**: Critical issues, emergency response required

#### 9.1.2 Complaint Submission Process

**Complaint Form Fields**:
```php
$complaint_form_fields = [
    'title' => [
        'type' => 'text',
        'label' => 'Complaint Title',
        'required' => true,
        'max_length' => 255
    ],
    'category' => [
        'type' => 'select',
        'label' => 'Category',
        'required' => true,
        'options' => ['maintenance', 'cleaning', 'food', 'security', 'other']
    ],
    'priority' => [
        'type' => 'select',
        'label' => 'Priority',
        'required' => true,
        'options' => ['low', 'medium', 'high', 'urgent']
    ],
    'description' => [
        'type' => 'textarea',
        'label' => 'Description',
        'required' => true,
        'min_length' => 20,
        'max_length' => 2000
    ],
    'attachment' => [
        'type' => 'file',
        'label' => 'Supporting Document (Optional)',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'max_size' => 5 * 1024 * 1024 // 5MB
    ]
];
```

**Complaint Registration Logic**:
```php
function registerComplaint($student_id, $complaint_data) {
    // Validate complaint data
    $errors = validateComplaintData($complaint_data);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Handle file attachment
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $attachment_path = uploadComplaintAttachment($_FILES['attachment']);
    }
    
    // Create complaint record
    $stmt = $conn->prepare("
        INSERT INTO complaints 
        (student_id, title, description, category, priority, attachment_path, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->bind_param("isssss", 
        $student_id,
        $complaint_data['title'],
        $complaint_data['description'],
        $complaint_data['category'],
        $complaint_data['priority'],
        $attachment_path
    );
    
    $stmt->execute();
    $complaint_id = $conn->insert_id;
    
    // Send notification to admin
    sendComplaintNotification($complaint_id);
    
    // Send confirmation to student
    sendComplaintConfirmationNotification($student_id, $complaint_id);
    
    return ['success' => true, 'complaint_id' => $complaint_id];
}
```

### 9.2 Complaint Tracking

#### 9.2.1 Status Management

**Complaint Status Values**:
- **pending**: New complaint, not yet reviewed
- **in_progress**: Being addressed by staff
- **resolved**: Issue has been fixed
- **rejected**: Complaint not valid

**Status Update Logic**:
```php
function updateComplaintStatus($complaint_id, $new_status, $admin_id, $response = '') {
    // Get current complaint details
    $complaint = getComplaintDetails($complaint_id);
    
    // Validate status transition
    if (!isValidStatusTransition($complaint['status'], $new_status)) {
        throw new Exception("Invalid status transition");
    }
    
    // Update complaint status
    $stmt = $conn->prepare("
        UPDATE complaints 
        SET status = ?, admin_response = ?, resolved_by = ?, resolved_at = NOW(), updated_at = NOW()
        WHERE id = ?
    ");
    
    $resolved_by = ($new_status === 'resolved') ? $admin_id : null;
    $stmt->bind_param("ssii", $new_status, $response, $resolved_by, $complaint_id);
    $stmt->execute();
    
    // Send status update notification to student
    sendComplaintStatusNotification($complaint['student_id'], $complaint_id, $new_status, $response);
    
    return true;
}
```

#### 9.2.2 Complaint History

**Complaint Timeline**:
```php
function getComplaintTimeline($complaint_id) {
    $stmt = $conn->prepare("
        SELECT 
            'created' as action_type,
            created_at as timestamp,
            CONCAT('Complaint registered: ', title) as description,
            student_id as actor_id,
            'student' as actor_type
        FROM complaints WHERE id = ?
        
        UNION ALL
        
        SELECT 
            'status_change' as action_type,
            updated_at as timestamp,
            CONCAT('Status changed to: ', status, '. Response: ', COALESCE(admin_response, '')) as description,
            resolved_by as actor_id,
            'admin' as actor_type
        FROM complaints WHERE id = ? AND status != 'pending'
        
        ORDER BY timestamp ASC
    ");
    $stmt->bind_param("ii", $complaint_id, $complaint_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

### 9.3 Complaint Resolution

#### 9.3.1 Resolution Workflow

**Resolution Process**:
1. **Complaint Received**: New complaint registered
2. **Initial Review**: Admin reviews complaint details
3. **Assignment**: Complaint assigned to appropriate staff
4. **Action Taken**: Staff addresses the issue
5. **Verification**: Student confirms resolution
6. **Closure**: Complaint marked as resolved

**Resolution Time Tracking**:
```php
function calculateResolutionTime($complaint_id) {
    $stmt = $conn->prepare("
        SELECT 
            TIMESTAMPDIFF(HOUR, created_at, resolved_at) as resolution_hours,
            TIMESTAMPDIFF(DAY, created_at, resolved_at) as resolution_days,
            category,
            priority
        FROM complaints 
        WHERE id = ? AND status = 'resolved'
    ");
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
```

#### 9.3.2 Escalation System

**Escalation Rules**:
```php
function checkEscalationRules() {
    $stmt = $conn->prepare("
        SELECT c.*, s.full_name, s.email, s.mobile
        FROM complaints c
        JOIN students s ON c.student_id = s.id
        WHERE c.status = 'pending' 
        AND c.priority IN ('high', 'urgent')
        AND TIMESTAMPDIFF(HOUR, c.created_at, NOW()) > 24
    ");
    $stmt->execute();
    
    $escalation_complaints = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($escalation_complaints as $complaint) {
        // Send escalation notification
        sendEscalationNotification($complaint);
        
        // Update complaint priority if needed
        if ($complaint['priority'] === 'high' && TIMESTAMPDIFF(HOUR, $complaint['created_at'], NOW()) > 48) {
            updateComplaintPriority($complaint['id'], 'urgent');
        }
    }
}
```

### 9.4 Complaint Analytics

#### 9.4.1 Complaint Statistics

**Monthly Complaint Report**:
```php
function generateMonthlyComplaintReport($month, $year) {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_complaints,
            SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_complaints,
            SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_complaints,
            SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium_complaints,
            SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low_complaints,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_complaints,
            AVG(CASE WHEN status = 'resolved' THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END) as avg_resolution_hours,
            category,
            COUNT(*) as category_count
        FROM complaints 
        WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?
        GROUP BY category
        ORDER BY category_count DESC
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

#### 9.4.2 Resolution Performance

**Resolution Performance Metrics**:
```php
function getResolutionPerformance($start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT 
            category,
            priority,
            COUNT(*) as total_complaints,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints,
            ROUND((SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as resolution_rate,
            AVG(CASE WHEN status = 'resolved' THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END) as avg_resolution_hours,
            MIN(CASE WHEN status = 'resolved' THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END) as min_resolution_hours,
            MAX(CASE WHEN status = 'resolved' THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END) as max_resolution_hours
        FROM complaints 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY category, priority
        ORDER BY category, priority DESC
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

---

## 10. Notification System

### 10.1 Notification Types

#### 10.1.1 System Notifications

**Notification Categories**:
1. **Fee Notifications**: Payment due, payment received, late fees
2. **Room Notifications**: Allocation, changes, maintenance
3. **Complaint Notifications**: Submission, status updates, resolution
4. **System Notifications**: Maintenance, updates, announcements
5. **Security Notifications**: Login alerts, password changes

**Priority Levels**:
- **Low**: General information
- **Medium**: Important updates
- **High**: Urgent attention required
- **Urgent**: Critical alerts

#### 10.1.2 Notification Creation

**Notification Generation**:
```php
function createNotification($user_id, $user_type, $title, $message, $priority = 'medium', $action_url = null) {
    $stmt = $conn->prepare("
        INSERT INTO notifications 
        (user_id, user_type, title, message, priority, action_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("isssss", $user_id, $user_type, $title, $message, $priority, $action_url);
    $stmt->execute();
    
    $notification_id = $conn->insert_id;
    
    // Send real-time notification if user is online
    sendRealTimeNotification($user_id, $user_type, $notification_id);
    
    return $notification_id;
}
```

**Bulk Notification Creation**:
```php
function createBulkNotification($recipients, $title, $message, $priority = 'medium') {
    $notifications_created = 0;
    
    foreach ($recipients as $recipient) {
        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, user_type, title, message, priority, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param("issss", 
            $recipient['user_id'],
            $recipient['user_type'],
            $title,
            $message,
            $priority
        );
        
        $stmt->execute();
        $notifications_created++;
    }
    
    return $notifications_created;
}
```

### 10.2 Notification Delivery

#### 10.2.1 Real-time Notifications

**WebSocket Integration**:
```javascript
// Client-side WebSocket connection
const socket = new WebSocket('ws://localhost:8080/notifications');

socket.onmessage = function(event) {
    const notification = JSON.parse(event.data);
    displayNotification(notification);
};

function displayNotification(notification) {
    // Update notification badge
    updateNotificationBadge();
    
    // Show notification popup
    showNotificationPopup(notification);
    
    // Play notification sound
    playNotificationSound();
}
```

**Server-side Notification Push**:
```php
function pushRealTimeNotification($user_id, $notification_data) {
    // Check if user is online
    if (isUserOnline($user_id)) {
        $notification = [
            'id' => $notification_data['id'],
            'title' => $notification_data['title'],
            'message' => $notification_data['message'],
            'priority' => $notification_data['priority'],
            'action_url' => $notification_data['action_url'],
            'timestamp' => $notification_data['created_at']
        ];
        
        // Send via WebSocket
        pushToWebSocket($user_id, $notification);
    }
}
```

#### 10.2.2 Email Notifications

**Email Template System**:
```php
function sendEmailNotification($to_email, $subject, $template, $data) {
    // Load email template
    $template_content = loadEmailTemplate($template);
    
    // Replace template variables
    $email_body = replaceTemplateVariables($template_content, $data);
    
    // Send email
    $mail = new PHPMailer(true);
    
    try {
        $mail->setFrom('noreply@adityahostel.com', 'Aditya Boys Hostel');
        $mail->addAddress($to_email);
        $mail->Subject = $subject;
        $mail->Body = $email_body;
        $mail->isHTML(true);
        
        $mail->send();
        
        // Log email
        logEmailNotification($to_email, $subject, $template, true);
        
    } catch (Exception $e) {
        // Log error
        logEmailNotification($to_email, $subject, $template, false, $mail->ErrorInfo);
    }
}
```

**Email Templates**:
```html
<!-- Fee Due Reminder Template -->
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #2c3e50; color: white; padding: 20px; text-align: center;">
        <h2>Aditya Boys Hostel</h2>
        <p>Fee Payment Reminder</p>
    </div>
    
    <div style="padding: 20px; background: #f9f9f9;">
        <p>Dear {{student_name}},</p>
        <p>This is a reminder that your fee for {{month}} {{year}} is due.</p>
        
        <div style="background: white; padding: 15px; border-left: 4px solid #e74c3c; margin: 20px 0;">
            <h3>Fee Details:</h3>
            <p><strong>Amount:</strong> ₹{{amount}}</p>
            <p><strong>Due Date:</strong> {{due_date}}</p>
            <p><strong>Late Fee:</strong> ₹{{late_fee}}</p>
        </div>
        
        <p>Please make your payment before the due date to avoid late fees.</p>
        <p><a href="{{payment_url}}" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Pay Now</a></p>
    </div>
    
    <div style="background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px;">
        <p>&copy; 2026 Aditya Boys Hostel. All rights reserved.</p>
    </div>
</div>
```

### 10.3 Notification Management

#### 10.3.1 Notification Display

**Notification List Retrieval**:
```php
function getUserNotifications($user_id, $user_type, $limit = 10, $unread_only = false) {
    $sql = "
        SELECT id, title, message, priority, is_read, action_url, created_at
        FROM notifications 
        WHERE user_id = ? AND user_type = ?
    ";
    
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $user_id, $user_type, $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

**Notification Badge Update**:
```php
function getUnreadNotificationCount($user_id, $user_type) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications 
        WHERE user_id = ? AND user_type = ? AND is_read = 0
    ");
    $stmt->bind_param("is", $user_id, $user_type);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    return $result['unread_count'];
}
```

#### 10.3.2 Notification Actions

**Mark as Read**:
```php
function markNotificationAsRead($notification_id, $user_id) {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    
    return $stmt->affected_rows > 0;
}
```

**Mark All as Read**:
```php
function markAllNotificationsAsRead($user_id, $user_type) {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = ? AND user_type = ? AND is_read = 0
    ");
    $stmt->bind_param("is", $user_id, $user_type);
    $stmt->execute();
    
    return $stmt->affected_rows;
}
```

### 10.4 Notification Analytics

#### 10.4.1 Notification Statistics

**Notification Performance Report**:
```php
function getNotificationStatistics($start_date, $end_date) {
    $stmt = $conn->prepare("
        SELECT 
            user_type,
            priority,
            COUNT(*) as total_notifications,
            SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_notifications,
            ROUND((SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as read_rate,
            AVG(CASE WHEN is_read = 1 THEN TIMESTAMPDIFF(MINUTE, created_at, 
                (SELECT updated_at FROM notifications n2 WHERE n2.id = notifications.id AND n2.is_read = 1)
            ) END) as avg_read_time_minutes
        FROM notifications 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY user_type, priority
        ORDER BY user_type, priority DESC
    ");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

#### 10.4.2 Notification Engagement

**Engagement Metrics**:
```php
function getNotificationEngagement($notification_id) {
    $stmt = $conn->prepare("
        SELECT 
            n.*,
            CASE WHEN n.is_read = 1 THEN 
                TIMESTAMPDIFF(MINUTE, n.created_at, n.updated_at)
            ELSE NULL END as read_time_minutes,
            CASE WHEN n.action_url IS NOT NULL AND n.is_read = 1 THEN 
                (SELECT COUNT(*) FROM notification_clicks WHERE notification_id = n.id)
            ELSE 0 END as click_count
        FROM notifications n
        WHERE n.id = ?
    ");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
```

---

## 10. Security Features

### 10.1 Authentication & Authorization

#### Password Security
```php
// Password hashing during registration
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

// Password verification during login
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Password strength validation
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}
```

#### Session Management
```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Session timeout management
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_destroy();
        header('Location: ../login.php?timeout=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

// Session regeneration for security
function regenerateSession() {
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = true;
    }
    
    if (isset($_SESSION['last_regeneration']) && (time() - $_SESSION['last_regeneration'] > 300)) {
        session_regenerate_id();
        $_SESSION['last_regeneration'] = time();
    }
}
```

#### Role-Based Access Control (RBAC)
```php
class AccessControl {
    private $user_role;
    private $permissions;
    
    public function __construct($user_role) {
        $this->user_role = $user_role;
        $this->permissions = $this->getRolePermissions($user_role);
    }
    
    private function getRolePermissions($role) {
        $role_permissions = [
            'super_admin' => [
                'user.create', 'user.read', 'user.update', 'user.delete',
                'room.create', 'room.read', 'room.update', 'room.delete',
                'fee.create', 'fee.read', 'fee.update', 'fee.delete',
                'payment.approve', 'payment.reject', 'payment.view',
                'complaint.create', 'complaint.read', 'complaint.update', 'complaint.delete',
                'system.settings', 'system.backup', 'system.logs'
            ],
            'admin' => [
                'user.read', 'user.update',
                'room.read', 'room.update',
                'fee.create', 'fee.read', 'fee.update',
                'payment.approve', 'payment.reject', 'payment.view',
                'complaint.create', 'complaint.read', 'complaint.update'
            ],
            'student' => [
                'profile.read', 'profile.update',
                'fee.read', 'payment.create',
                'complaint.create', 'complaint.read',
                'notification.read'
            ]
        ];
        
        return $role_permissions[$role] ?? [];
    }
    
    public function hasPermission($permission) {
        return in_array($permission, $this->permissions);
    }
    
    public function checkPermission($permission) {
        if (!$this->hasPermission($permission)) {
            header('HTTP/1.0 403 Forbidden');
            die('Access Denied');
        }
    }
}

// Usage example
$access_control = new AccessControl($_SESSION['user_role']);
$access_control->checkPermission('user.create');
```

### 10.2 Input Validation & Sanitization

#### Data Validation
```php
class InputValidator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && 
               strlen($email) <= 255;
    }
    
    public static function validatePhone($phone) {
        return preg_match('/^[0-9]{10}$/', $phone);
    }
    
    public static function validateName($name) {
        return strlen(trim($name)) >= 2 && 
               strlen(trim($name)) <= 100 &&
               preg_match('/^[a-zA-Z\s\-\.]+$/', $name);
    }
    
    public static function validateAmount($amount) {
        return is_numeric($amount) && 
               $amount > 0 && 
               $amount <= 999999.99;
    }
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

// Validation in practice
$errors = [];

if (!InputValidator::validateEmail($_POST['email'])) {
    $errors[] = "Invalid email address";
}

if (!InputValidator::validatePhone($_POST['phone'])) {
    $errors[] = "Phone number must be 10 digits";
}

if (!InputValidator::validateName($_POST['full_name'])) {
    $errors[] = "Invalid name format";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: registration.php');
    exit();
}
```

#### File Upload Security
```php
class SecureFileUpload {
    private $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private $max_size = 5242880; // 5MB
    private $upload_dir;
    
    public function __construct($upload_dir) {
        $this->upload_dir = $upload_dir;
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    public function uploadFile($file_key, $new_name = null) {
        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        $file = $_FILES[$file_key];
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $this->allowed_types)) {
            throw new Exception("File type not allowed");
        }
        
        // Validate file size
        if ($file['size'] > $this->max_size) {
            throw new Exception("File size exceeds maximum limit");
        }
        
        // Generate secure filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $new_name ?: uniqid('file_', true) . '.' . $extension;
        $filepath = $this->upload_dir . '/' . $filename;
        
        // Move file securely
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to move uploaded file");
        }
        
        // Set secure permissions
        chmod($filepath, 0644);
        
        return $filename;
    }
}
```

### 10.3 SQL Injection Prevention

#### Prepared Statements
```php
// Secure database operations using prepared statements
class Database {
    private $conn;
    
    public function __construct($host, $username, $password, $database) {
        $this->conn = new mysqli($host, $username, $password, $database);
        
        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed");
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function query($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Query preparation failed");
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed");
        }
        
        return $stmt->get_result();
    }
    
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}

// Example usage
$db = new Database('localhost', 'username', 'password', 'hostel_db');

// Secure user registration
function registerUser($db, $name, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO students (full_name, email, password, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $params = [$name, $email, $hashed_password];
    $types = 'sss';
    
    try {
        $db->query($sql, $params, $types);
        return $db->conn->insert_id;
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}
```

### 10.4 XSS & CSRF Protection

#### XSS Prevention
```php
// Output encoding
function escapeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Content Security Policy header
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");

// X-XSS-Protection header
header("X-XSS-Protection: 1; mode=block");

// Secure template rendering
function renderTemplate($template, $data = []) {
    extract($data);
    
    ob_start();
    include $template;
    $content = ob_get_clean();
    
    // Escape all dynamic content
    return escapeOutput($content);
}
```

#### CSRF Protection
```php
class CSRFProtection {
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token, $max_age = 3600) {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        if (time() - $_SESSION['csrf_token_time'] > $max_age) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

// Form implementation
<form method="POST" action="process.php">
    <?php echo CSRFProtection::getTokenField(); ?>
    <!-- Other form fields -->
    <button type="submit">Submit</button>
</form>

// Processing form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRFProtection::validateToken($_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    // Process form data
}
```

### 10.5 Security Headers & Configuration

#### Security Headers
```php
// Set security headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header("X-Frame-Options: DENY");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Enable XSS protection
    header("X-XSS-Protection: 1; mode=block");
    
    // Strict Transport Security
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self';");
    
    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Permissions Policy
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

// Call at the beginning of every page
setSecurityHeaders();
```

#### Error Handling & Logging
```php
class SecurityLogger {
    private static $log_file = 'security.log';
    
    public static function logSecurityEvent($event_type, $details, $severity = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $user_id = $_SESSION['user_id'] ?? 'anonymous';
        
        $log_entry = sprintf(
            "[%s] [%s] IP: %s | User: %s | Event: %s | Details: %s | UA: %s\n",
            $timestamp,
            $severity,
            $ip,
            $user_id,
            $event_type,
            $details,
            $user_agent
        );
        
        file_put_contents(self::$log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // For critical events, also send email to admin
        if ($severity === 'CRITICAL') {
            self::sendSecurityAlert($event_type, $details);
        }
    }
    
    private static function sendSecurityAlert($event_type, $details) {
        $to = 'admin@hostel.com';
        $subject = 'Security Alert: ' . $event_type;
        $message = "A security event has been detected:\n\n";
        $message .= "Event: " . $event_type . "\n";
        $message .= "Details: " . $details . "\n";
        $message .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
        
        mail($to, $subject, $message);
    }
}

// Usage examples
SecurityLogger::logSecurityEvent('LOGIN_FAILED', 'Invalid credentials for email: user@example.com', 'WARNING');
SecurityLogger::logSecurityEvent('SQL_INJECTION_ATTEMPT', 'Suspicious SQL pattern detected in input', 'CRITICAL');
```

---

## 11. Installation Guide

### 11.1 System Requirements

#### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Version**: PHP 8.0 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: Minimum 2GB RAM (4GB recommended)
- **Storage**: Minimum 10GB free space
- **SSL Certificate**: Required for production

#### PHP Extensions Required
```php
// Required extensions check
function checkPHPExtensions() {
    $required_extensions = [
        'mysqli' => 'Database connectivity',
        'gd' => 'Image processing',
        'curl' => 'HTTP requests',
        'json' => 'JSON handling',
        'mbstring' => 'Multi-byte string handling',
        'openssl' => 'SSL/TLS support',
        'session' => 'Session management',
        'fileinfo' => 'File information',
        'zip' => 'Archive handling',
        'xml' => 'XML processing'
    ];
    
    $missing = [];
    
    foreach ($required_extensions as $ext => $description) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext . ' (' . $description . ')';
        }
    }
    
    if (!empty($missing)) {
        echo "<h3>Missing PHP Extensions:</h3>";
        echo "<ul>";
        foreach ($missing as $ext) {
            echo "<li>$ext</li>";
        }
        echo "</ul>";
        echo "<p>Please install the missing extensions before proceeding.</p>";
        return false;
    }
    
    echo "<p style='color: green;'>✓ All required PHP extensions are installed.</p>";
    return true;
}
```

### 11.2 Database Setup

#### Database Creation Script
```sql
-- Create database
CREATE DATABASE IF NOT EXISTS aditya_hostel 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create database user
CREATE USER 'hostel_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON aditya_hostel.* TO 'hostel_user'@'localhost';
FLUSH PRIVILEGES;

-- Use the database
USE aditya_hostel;

-- Create all tables (complete schema provided in Section 4)
-- [Insert all table creation scripts from Section 4.1]
```

#### Database Configuration
```php
// config/database.php
<?php
class DatabaseConfig {
    private static $instance = null;
    private $connection;
    
    private $host = 'localhost';
    private $username = 'hostel_user';
    private $password = 'secure_password_here';
    private $database = 'aditya_hostel';
    
    private function __construct() {
        try {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please check configuration.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = [], $types = '') {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }
        
        return $stmt->get_result();
    }
}
```

### 11.3 File Structure Setup

#### Directory Structure
```
AdityaBoysHostel/
├── admin/                   # Admin panel files
│   ├── dashboard.php
│   ├── manage_students.php
│   ├── manage_rooms.php
│   ├── manage_fees.php
│   ├── manage_complaints.php
│   ├── payment_info.php
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── sidebar.php
│   │   └── auth.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
├── student/                 # Student panel files
│   ├── dashboard.php
│   ├── profile.php
│   ├── fees.php
│   ├── complaints.php
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── auth.php
│   └── assets/
├── assets/                  # Shared assets
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── fontawesome.min.css
│   │   └── style.css
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   ├── jquery.min.js
│   │   └── main.js
│   ├── images/
│   └── fonts/
├── config/                  # Configuration files
│   ├── database.php
│   ├── config.php
│   └── constants.php
├── includes/                # Shared includes
│   ├── functions.php
│   ├── session.php
│   └── security.php
├── uploads/                 # Upload directories
│   ├── payment_proofs/
│   ├── profile_photos/
│   ├── documents/
│   └── QR code/
├── api/                     # API endpoints
│   ├── get_payment_info.php
│   ├── process_payment.php
│   └── notifications.php
├── logs/                    # Log files
│   ├── error.log
│   ├── security.log
│   └── access.log
├── backups/                 # Database backups
├── install.php              # Installation script
├── index.php               # Landing page
├── login.php               # Login page
├── logout.php              # Logout script
├── README.md               # Documentation
└── .htaccess               # Apache configuration
```

#### .htaccess Configuration
```apache
# Enable rewrite engine
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';"
</IfModule>

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "(config|database|\.log)">
    Order allow,deny
    Deny from all
</FilesMatch>

# URL rewriting
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?page=$1 [QSA,L]

# PHP settings
<IfModule mod_php8.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log logs/error.log
    php_value max_execution_time 300
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value memory_limit 256M
</IfModule>
```

### 11.4 Installation Process

#### Step-by-Step Installation
```php
<?php
// install.php - Installation script
session_start();

$installation_steps = [
    'requirements' => 'Check System Requirements',
    'database' => 'Setup Database',
    'config' => 'Configure System',
    'admin' => 'Create Admin Account',
    'complete' => 'Installation Complete'
];

$current_step = $_GET['step'] ?? 'requirements';

function checkRequirements() {
    $checks = [];
    
    // PHP version
    $checks['php_version'] = [
        'name' => 'PHP Version (8.0+)',
        'status' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'current' => PHP_VERSION,
        'required' => '8.0.0'
    ];
    
    // Required extensions
    $extensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring'];
    foreach ($extensions as $ext) {
        $checks['extension_' . $ext] = [
            'name' => 'PHP Extension: ' . $ext,
            'status' => extension_loaded($ext),
            'current' => extension_loaded($ext) ? 'Loaded' : 'Not Loaded',
            'required' => 'Loaded'
        ];
    }
    
    // Directory permissions
    $directories = ['uploads', 'logs', 'backups'];
    foreach ($directories as $dir) {
        $checks['dir_' . $dir] = [
            'name' => 'Directory Writable: ' . $dir,
            'status' => is_writable($dir),
            'current' => is_writable($dir) ? 'Writable' : 'Not Writable',
            'required' => 'Writable'
        ];
    }
    
    return $checks;
}

function setupDatabase($host, $username, $password, $database) {
    try {
        $conn = new mysqli($host, $username, $password);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database
        $conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Select database
        $conn->select_db($database);
        
        // Read and execute SQL schema
        $schema = file_get_contents('database/schema.sql');
        $statements = explode(';', $schema);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                if (!$conn->query($statement)) {
                    throw new Exception("SQL Error: " . $conn->error);
                }
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Database setup error: " . $e->getMessage());
        return false;
    }
}

function createConfigFile($config_data) {
    $config_template = '<?php
// Database Configuration
define("DB_HOST", "' . $config_data['host'] . '");
define("DB_USER", "' . $config_data['username'] . '");
define("DB_PASS", "' . $config_data['password'] . '");
define("DB_NAME", "' . $config_data['database'] . '");

// System Configuration
define("SITE_URL", "' . $config_data['site_url'] . '");
define("SITE_NAME", "' . $config_data['site_name'] . '");
define("ADMIN_EMAIL", "' . $config_data['admin_email'] . '");

// Security Configuration
define("ENCRYPTION_KEY", "' . bin2hex(random_bytes(32)) . '");
define("SESSION_TIMEOUT", 1800);
define("MAX_LOGIN_ATTEMPTS", 5);
';
    
    return file_put_contents('config/config.php', $config_template);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($current_step) {
        case 'database':
            $db_setup = setupDatabase(
                $_POST['db_host'],
                $_POST['db_username'],
                $_POST['db_password'],
                $_POST['db_name']
            );
            
            if ($db_setup) {
                $_SESSION['db_config'] = [
                    'host' => $_POST['db_host'],
                    'username' => $_POST['db_username'],
                    'password' => $_POST['db_password'],
                    'database' => $_POST['db_name']
                ];
                header('Location: install.php?step=config');
                exit();
            }
            break;
            
        case 'config':
            $config_data = array_merge($_SESSION['db_config'], $_POST);
            if (createConfigFile($config_data)) {
                $_SESSION['config'] = $config_data;
                header('Location: install.php?step=admin');
                exit();
            }
            break;
            
        case 'admin':
            // Create admin account
            // [Admin creation logic]
            header('Location: install.php?step=complete');
            exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aditya Boys Hostel - Installation</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Installation Wizard</h3>
                        <!-- Progress bar -->
                    </div>
                    <div class="card-body">
                        <?php include "steps/{$current_step}.php"; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

---

## 12. User Manual

### 12.1 Admin User Manual

#### Dashboard Navigation
The admin dashboard provides comprehensive control over all hostel operations:

**Main Navigation Menu:**
- **Dashboard**: Overview with statistics and recent activities
- **Students**: Manage student registrations, profiles, and room allocations
- **Rooms**: Manage room types, availability, and maintenance
- **Fees**: Configure fee structures and manage payments
- **Complaints**: Track and resolve student complaints
- **Payments**: Review and approve payment submissions
- **Reports**: Generate various administrative reports
- **Settings**: Configure system settings and preferences

#### Student Management
1. **Adding New Students:**
   - Navigate to Students → Add Student
   - Fill in personal details (name, email, phone, address)
   - Set academic information (course, year, roll number)
   - Assign room or mark as waiting list
   - Set initial fee status

2. **Managing Existing Students:**
   - View all students in the Students list
   - Use search and filter options to find specific students
   - Edit student information by clicking the edit button
   - Update room assignments as needed
   - Manage student status (active, inactive, graduated)

3. **Room Allocation:**
   - Automatic allocation based on preferences and availability
   - Manual override for special cases
   - Room change requests processing
   - Generate room allocation reports

#### Fee Management
1. **Setting Up Fee Structure:**
   - Navigate to Fees → Fee Structure
   - Define fee types (tuition, hostel, mess, etc.)
   - Set amounts and due dates
   - Configure late fee penalties
   - Set up installment plans if needed

2. **Managing Fee Payments:**
   - View pending payments dashboard
   - Review payment proofs submitted by students
   - Approve or reject payments with reasons
   - Generate payment receipts
   - Send payment reminders

#### Complaint Management
1. **Complaint Categories:**
   - Maintenance issues (plumbing, electrical, furniture)
   - Security concerns
   - Food and mess-related issues
   - Administrative problems
   - Other miscellaneous issues

2. **Complaint Resolution Process:**
   - Review new complaints in the dashboard
   - Assign priority levels (low, medium, high, urgent)
   - Assign to appropriate staff member
   - Track resolution progress
   - Update status and communicate with students

### 12.2 Student User Manual

#### Dashboard Overview
The student dashboard provides easy access to all essential services:

**Quick Access Features:**
- **Profile Management**: View and update personal information
- **Fee Status**: Check current fee balance and payment history
- **Quick Pay**: Make instant payments using various methods
- **Complaints**: Register and track complaints
- **Notifications**: View important announcements and updates
- **Room Information**: View room details and roommate information

#### Making Payments
1. **Available Payment Methods:**
   - UPI Payment (Google Pay, PhonePe, Paytm)
   - Bank Transfer
   - Cash (at hostel office)
   - Cheque

2. **Payment Process:**
   - Click "Quick Pay" on the dashboard
   - Select the fee you want to pay
   - Choose your preferred payment method
   - Complete payment using the provided QR code or bank details
   - Upload payment proof (screenshot or receipt)
   - Submit for admin approval

3. **Payment Tracking:**
   - View payment status in the Fees section
   - Receive email notifications for payment confirmations
   - Download payment receipts
   - View payment history

#### Complaint Registration
1. **Submitting a Complaint:**
   - Navigate to Complaints → Register Complaint
   - Select appropriate category
   - Provide detailed description of the issue
   - Upload supporting documents or photos if needed
   - Set priority level if applicable
   - Submit for review

2. **Tracking Complaint Status:**
   - View all complaints in the Complaints section
   - Check current status (pending, in-progress, resolved)
   - View admin responses and updates
   - Add additional information if requested

#### Profile Management
1. **Personal Information:**
   - Update contact details (phone, email)
   - Modify address information
   - Update emergency contact details
   - Change profile photo

2. **Academic Information:**
   - View current course and year details
   - Update roll number if changed
   - View academic progress

### 12.3 Troubleshooting Guide

#### Common Issues and Solutions

**Login Problems:**
- **Issue**: Cannot login with correct credentials
- **Solution**: Check if account is active, reset password if needed
- **Contact**: System administrator

**Payment Issues:**
- **Issue**: Payment not reflecting after successful transaction
- **Solution**: Wait for admin approval (usually within 24 hours)
- **Action**: Upload payment proof if not already done

**Room Allocation Issues:**
- **Issue**: Room not allocated despite payment
- **Solution**: Contact admin office with payment details
- **Documents**: Keep payment receipt ready

**Complaint Not Resolved:**
- **Issue**: Complaint status not updated
- **Solution**: Send follow-up message through complaint system
- **Escalation**: Contact warden or admin office directly

**Technical Issues:**
- **Issue**: Website not loading properly
- **Solution**: Clear browser cache, try different browser
- **Report**: Report technical issues to IT support

#### Contact Information
- **Admin Office**: 080-12345678
- **Warden Office**: 080-12345679
- **IT Support**: support@adityahostel.com
- **Emergency**: 080-12345680

---

## 13. API Documentation

### 13.1 REST API Endpoints

#### Authentication Endpoints
```php
// POST /api/login
// Description: User authentication
// Request Body: JSON
{
    "email": "user@example.com",
    "password": "password123",
    "user_type": "student|admin"
}

// Response
{
    "success": true,
    "token": "jwt_token_here",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "student"
    },
    "expires_in": 3600
}

// POST /api/logout
// Description: User logout
// Headers: Authorization: Bearer {token}

// Response
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### Student Management API
```php
// GET /api/students
// Description: Get list of students
// Query Parameters: page, limit, search, status
// Headers: Authorization: Bearer {token}

// Response
{
    "success": true,
    "data": [
        {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com",
            "phone": "1234567890",
            "room_number": "A-101",
            "status": "active",
            "created_at": "2024-01-01T10:00:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 10,
        "total_records": 100
    }
}

// POST /api/students
// Description: Create new student
// Request Body: JSON
{
    "full_name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "0987654321",
    "course": "Computer Science",
    "year": 2,
    "roll_number": "CS2024001"
}

// PUT /api/students/{id}
// Description: Update student information
// Request Body: JSON (partial update allowed)

// DELETE /api/students/{id}
// Description: Delete student account
```

#### Payment API
```php
// GET /api/payments
// Description: Get payment history
// Query Parameters: student_id, status, date_from, date_to

// Response
{
    "success": true,
    "data": [
        {
            "id": 1,
            "student_id": 1,
            "fee_id": 1,
            "amount": 5000.00,
            "payment_method": "upi",
            "status": "approved",
            "transaction_id": "TXN123456789",
            "created_at": "2024-01-01T10:00:00Z",
            "approved_at": "2024-01-01T12:00:00Z"
        }
    ]
}

// POST /api/payments
// Description: Submit new payment
// Request Body: multipart/form-data
{
    "fee_id": 1,
    "amount": 5000.00,
    "payment_method": "upi",
    "transaction_id": "TXN123456789",
    "payment_proof": [file]
}

// PUT /api/payments/{id}/approve
// Description: Approve payment
// Request Body: JSON
{
    "notes": "Payment verified and approved"
}

// PUT /api/payments/{id}/reject
// Description: Reject payment
// Request Body: JSON
{
    "reason": "Invalid payment proof"
}
```

#### Complaint API
```php
// GET /api/complaints
// Description: Get complaints list
// Query Parameters: student_id, status, priority, category

// POST /api/complaints
// Description: Register new complaint
// Request Body: JSON
{
    "category": "maintenance",
    "subject": "Water leakage in bathroom",
    "description": "There is water leakage from the bathroom tap...",
    "priority": "medium",
    "attachments": ["file1.jpg", "file2.jpg"]
}

// PUT /api/complaints/{id}
// Description: Update complaint status
// Request Body: JSON
{
    "status": "resolved",
    "resolution_notes": "Issue has been fixed by maintenance team"
}
```

### 13.2 WebSocket API

#### Real-time Notifications
```javascript
// WebSocket Connection
const ws = new WebSocket('wss://hostel.example.com/ws');

// Authentication
ws.onopen = function() {
    // Send authentication token
    ws.send(JSON.stringify({
        type: 'auth',
        token: 'jwt_token_here'
    }));
};

// Receive notifications
ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    switch(data.type) {
        case 'notification':
            handleNotification(data.payload);
            break;
        case 'payment_update':
            handlePaymentUpdate(data.payload);
            break;
        case 'complaint_update':
            handleComplaintUpdate(data.payload);
            break;
    }
};

// Send messages
function sendMessage(type, payload) {
    ws.send(JSON.stringify({
        type: type,
        payload: payload
    }));
}

// Example: Mark notification as read
function markNotificationRead(notificationId) {
    sendMessage('mark_read', { notification_id: notificationId });
}
```

#### Notification Types
```javascript
// Payment notification
{
    "type": "payment_update",
    "payload": {
        "payment_id": 123,
        "status": "approved",
        "message": "Your payment has been approved"
    }
}

// Complaint notification
{
    "type": "complaint_update",
    "payload": {
        "complaint_id": 456,
        "status": "resolved",
        "message": "Your complaint has been resolved"
    }
}

// System notification
{
    "type": "system",
    "payload": {
        "title": "Maintenance Notice",
        "message": "Water supply will be interrupted tomorrow from 10 AM to 2 PM",
        "priority": "high"
    }
}
```

### 13.3 API Error Handling

#### Standard Error Response Format
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "email": "Invalid email format",
            "phone": "Phone number must be 10 digits"
        }
    },
    "timestamp": "2024-01-01T10:00:00Z"
}
```

#### Error Codes
```php
class APIErrorCodes {
    const VALIDATION_ERROR = 'VALIDATION_ERROR';
    const AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
    const AUTHORIZATION_DENIED = 'AUTHORIZATION_DENIED';
    const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    const DUPLICATE_RESOURCE = 'DUPLICATE_RESOURCE';
    const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    const SERVER_ERROR = 'SERVER_ERROR';
    const DATABASE_ERROR = 'DATABASE_ERROR';
    const FILE_UPLOAD_ERROR = 'FILE_UPLOAD_ERROR';
    const PAYMENT_FAILED = 'PAYMENT_FAILED';
}

// Error handling middleware
function handleAPIError($code, $message, $details = null, $httpCode = 400) {
    http_response_code($httpCode);
    
    $response = [
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
            'timestamp' => date('c')
        ]
    ];
    
    if ($details !== null) {
        $response['error']['details'] = $details;
    }
    
    echo json_encode($response);
    exit();
}
```

---

## 14. Maintenance & Support

### 14.1 Database Maintenance

#### Regular Backup Procedures
```bash
#!/bin/bash
# backup_database.sh - Automated database backup script

# Configuration
DB_HOST="localhost"
DB_USER="hostel_user"
DB_PASS="secure_password"
DB_NAME="aditya_hostel"
BACKUP_DIR="/var/backups/hostel_db"
RETENTION_DAYS=30

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Generate backup filename with timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/hostel_backup_$TIMESTAMP.sql"

# Create database backup
mysqldump --host=$DB_HOST --user=$DB_USER --password=$DB_PASS \
          --single-transaction --routines --triggers \
          $DB_NAME > $BACKUP_FILE

# Compress backup file
gzip $BACKUP_FILE

# Remove backups older than retention period
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete

# Log backup operation
echo "$(date): Database backup completed - $BACKUP_FILE.gz" >> /var/log/hostel_backup.log

# Send email notification (optional)
# echo "Database backup completed successfully" | mail -s "Hostel DB Backup" admin@example.com
```

#### Database Optimization
```sql
-- Monthly optimization script
-- Run this script monthly to maintain database performance

-- Optimize all tables
SET @tables = (SELECT GROUP_CONCAT(TABLE_NAME) 
               FROM INFORMATION_SCHEMA.TABLES 
               WHERE TABLE_SCHEMA = 'aditya_hostel');

SET @sql = CONCAT('OPTIMIZE TABLE ', @tables);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update table statistics
ANALYZE TABLE students, rooms, fees, payments, complaints, notifications, admins;

-- Check for fragmented tables
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    ROUND((data_free / 1024 / 1024), 2) AS 'Free Space (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'aditya_hostel' 
    AND data_free > 0
ORDER BY data_free DESC;

-- Clean up old audit logs (older than 1 year)
DELETE FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Clean up old notifications (older than 6 months)
DELETE FROM notifications 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    AND is_read = 1;
```

#### Database Health Monitoring
```php
<?php
// Database health check script
class DatabaseHealthMonitor {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function runHealthCheck() {
        $results = [];
        
        // Check database connection
        $results['connection'] = $this->checkConnection();
        
        // Check table sizes
        $results['table_sizes'] = $this->getTableSizes();
        
        // Check index usage
        $results['index_usage'] = $this->getIndexUsage();
        
        // Check slow queries
        $results['slow_queries'] = $this->getSlowQueries();
        
        // Check for errors
        $results['error_count'] = $this->getErrorCount();
        
        return $results;
    }
    
    private function checkConnection() {
        try {
            $this->db->query("SELECT 1");
            return ['status' => 'healthy', 'message' => 'Connection OK'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function getTableSizes() {
        $sql = "SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                ORDER BY size_mb DESC";
        
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getIndexUsage() {
        $sql = "SELECT 
                    table_name,
                    index_name,
                    cardinality,
                    CASE 
                        WHEN cardinality = 0 THEN 'Unused'
                        WHEN cardinality < 10 THEN 'Low usage'
                        ELSE 'Active'
                    END as usage_status
                FROM information_schema.STATISTICS 
                WHERE table_schema = DATABASE()
                ORDER BY cardinality DESC";
        
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getSlowQueries() {
        $sql = "SELECT 
                    start_time,
                    query_time,
                    lock_time,
                    rows_sent,
                    rows_examined,
                    sql_text
                FROM mysql.slow_log 
                WHERE start_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY query_time DESC
                LIMIT 10";
        
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getErrorCount() {
        $sql = "SELECT COUNT(*) as error_count 
                FROM mysql.error_log 
                WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $result = $this->db->query($sql)->fetch_assoc();
        return $result['error_count'];
    }
}

// Usage
$monitor = new DatabaseHealthMonitor($db);
$health_report = $monitor->runHealthCheck();

// Send alert if issues found
if ($health_report['connection']['status'] !== 'healthy' || 
    $health_report['error_count'] > 10) {
    // Send alert to administrators
    sendHealthAlert($health_report);
}
```

### 14.2 System Maintenance

#### Log Rotation
```bash
#!/bin/bash
# log_rotation.sh - Log rotation script

# Configuration
LOG_DIR="/var/log/hostel"
RETENTION_DAYS=30
WEB_LOG_DIR="/var/log/apache2"

# Rotate application logs
for logfile in $LOG_DIR/*.log; do
    if [ -f "$logfile" ]; then
        # Compress old log
        gzip "$logfile"
        
        # Move to archive
        mv "$logfile.gz" "$LOG_DIR/archive/$(basename $logfile .log)_$(date +%Y%m%d).gz"
    fi
done

# Remove old archived logs
find $LOG_DIR/archive -name "*.gz" -mtime +$RETENTION_DAYS -delete

# Rotate Apache logs
for logfile in $WEB_LOG_DIR/hostel_*.log; do
    if [ -f "$logfile" ]; then
        gzip "$logfile"
        mv "$logfile.gz" "$WEB_LOG_DIR/archive/"
    fi
done

# Restart Apache to release log files
systemctl reload apache2

echo "$(date): Log rotation completed" >> $LOG_DIR/maintenance.log
```

#### File System Cleanup
```php
<?php
// File cleanup script
class FileCleanupManager {
    private $cleanup_rules = [
        'uploads/payment_proofs' => [
            'max_age_days' => 365,
            'max_size_mb' => 1000
        ],
        'uploads/temp' => [
            'max_age_days' => 1,
            'max_size_mb' => 100
        ],
        'logs' => [
            'max_age_days' => 30,
            'max_size_mb' => 500
        ]
    ];
    
    public function runCleanup() {
        $results = [];
        
        foreach ($this->cleanup_rules as $directory => $rules) {
            $results[$directory] = $this->cleanupDirectory($directory, $rules);
        }
        
        return $results;
    }
    
    private function cleanupDirectory($directory, $rules) {
        $cleaned_files = 0;
        $freed_space = 0;
        
        if (!is_dir($directory)) {
            return ['status' => 'skipped', 'reason' => 'Directory not found'];
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $file_age = (time() - $file->getMTime()) / 86400; // days
                
                if ($file_age > $rules['max_age_days']) {
                    $file_size = $file->getSize() / 1024 / 1024; // MB
                    
                    if (unlink($file->getRealPath())) {
                        $cleaned_files++;
                        $freed_space += $file_size;
                    }
                }
            }
        }
        
        return [
            'status' => 'completed',
            'files_cleaned' => $cleaned_files,
            'space_freed_mb' => round($freed_space, 2)
        ];
    }
}

// Schedule to run daily
$cleanup = new FileCleanupManager();
$results = $cleanup->runCleanup();

// Log results
$log_entry = sprintf(
    "[%s] File cleanup completed: %s\n",
    date('Y-m-d H:i:s'),
    json_encode($results)
);
file_put_contents('logs/cleanup.log', $log_entry, FILE_APPEND);
```

#### Performance Monitoring
```php
<?php
// Performance monitoring script
class PerformanceMonitor {
    private $metrics = [];
    
    public function collectMetrics() {
        $this->metrics['system'] = $this->getSystemMetrics();
        $this->metrics['database'] = $this->getDatabaseMetrics();
        $this->metrics['application'] = $this->getApplicationMetrics();
        
        return $this->metrics;
    }
    
    private function getSystemMetrics() {
        return [
            'cpu_usage' => sys_getloadavg()[0],
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'network_connections' => $this->getNetworkConnections()
        ];
    }
    
    private function getMemoryUsage() {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match_all('/(\w+):\s+(\d+)\s+kB/', $meminfo, $matches);
        
        $memory = array_combine($matches[1], $matches[2]);
        $total = $memory['MemTotal'];
        $available = $memory['MemAvailable'];
        
        return [
            'total_kb' => $total,
            'available_kb' => $available,
            'usage_percent' => round((($total - $available) / $total) * 100, 2)
        ];
    }
    
    private function getDiskUsage() {
        $df = disk_free_space('/');
        $dt = disk_total_space('/');
        
        return [
            'total_gb' => round($dt / 1024 / 1024 / 1024, 2),
            'free_gb' => round($df / 1024 / 1024 / 1024, 2),
            'usage_percent' => round((($dt - $df) / $dt) * 100, 2)
        ];
    }
    
    private function getNetworkConnections() {
        $connections = shell_exec('netstat -an | grep ESTABLISHED | wc -l');
        return trim($connections);
    }
    
    private function getDatabaseMetrics() {
        global $db;
        
        // Active connections
        $result = $db->query("SHOW STATUS LIKE 'Threads_connected'")->fetch_assoc();
        $active_connections = $result['Value'];
        
        // Slow queries
        $result = $db->query("SHOW STATUS LIKE 'Slow_queries'")->fetch_assoc();
        $slow_queries = $result['Value'];
        
        // Query cache hit rate
        $result = $db->query("SHOW STATUS LIKE 'Qcache_hits'")->fetch_assoc();
        $cache_hits = $result['Value'];
        
        $result = $db->query("SHOW STATUS LIKE 'Qcache_inserts'")->fetch_assoc();
        $cache_inserts = $result['Value'];
        
        $hit_rate = $cache_hits + $cache_inserts > 0 
            ? round(($cache_hits / ($cache_hits + $cache_inserts)) * 100, 2) 
            : 0;
        
        return [
            'active_connections' => $active_connections,
            'slow_queries' => $slow_queries,
            'query_cache_hit_rate' => $hit_rate
        ];
    }
    
    private function getApplicationMetrics() {
        return [
            'active_sessions' => $this->countActiveSessions(),
            'file_uploads_today' => $this->countFileUploadsToday(),
            'payments_today' => $this->countPaymentsToday(),
            'complaints_today' => $this->countComplaintsToday()
        ];
    }
    
    private function countActiveSessions() {
        $session_files = glob(session_save_path() . '/sess_*');
        return count($session_files);
    }
    
    private function countFileUploadsToday() {
        global $db;
        $result = $db->query(
            "SELECT COUNT(*) as count FROM payments 
             WHERE DATE(created_at) = CURDATE()"
        )->fetch_assoc();
        return $result['count'];
    }
    
    private function countPaymentsToday() {
        global $db;
        $result = $db->query(
            "SELECT COUNT(*) as count FROM payments 
             WHERE DATE(created_at) = CURDATE()"
        )->fetch_assoc();
        return $result['count'];
    }
    
    private function countComplaintsToday() {
        global $db;
        $result = $db->query(
            "SELECT COUNT(*) as count FROM complaints 
             WHERE DATE(created_at) = CURDATE()"
        )->fetch_assoc();
        return $result['count'];
    }
}
```

### 14.3 Troubleshooting Common Issues

#### Performance Issues
```php
// Performance diagnostic script
class PerformanceDiagnostics {
    public function runDiagnostics() {
        $issues = [];
        
        // Check database query performance
        $slow_queries = $this->findSlowQueries();
        if (!empty($slow_queries)) {
            $issues[] = [
                'type' => 'slow_queries',
                'severity' => 'high',
                'description' => 'Found slow database queries',
                'details' => $slow_queries
            ];
        }
        
        // Check memory usage
        $memory_usage = $this->checkMemoryUsage();
        if ($memory_usage > 90) {
            $issues[] = [
                'type' => 'high_memory',
                'severity' => 'critical',
                'description' => 'High memory usage detected',
                'details' => ['usage_percent' => $memory_usage]
            ];
        }
        
        // Check disk space
        $disk_usage = $this->checkDiskUsage();
        if ($disk_usage > 85) {
            $issues[] = [
                'type' => 'low_disk_space',
                'severity' => 'high',
                'description' => 'Low disk space',
                'details' => ['usage_percent' => $disk_usage]
            ];
        }
        
        // Check file permissions
        $permission_issues = $this->checkFilePermissions();
        if (!empty($permission_issues)) {
            $issues[] = [
                'type' => 'file_permissions',
                'severity' => 'medium',
                'description' => 'File permission issues found',
                'details' => $permission_issues
            ];
        }
        
        return $issues;
    }
    
    private function findSlowQueries() {
        global $db;
        
        $sql = "SELECT 
                    query_time,
                    lock_time,
                    rows_sent,
                    rows_examined,
                    sql_text
                FROM mysql.slow_log 
                WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY query_time DESC
                LIMIT 5";
        
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    private function checkMemoryUsage() {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match_all('/(\w+):\s+(\d+)\s+kB/', $meminfo, $matches);
        
        $memory = array_combine($matches[1], $matches[2]);
        $total = $memory['MemTotal'];
        $available = $memory['MemAvailable'];
        
        return round((($total - $available) / $total) * 100, 2);
    }
    
    private function checkDiskUsage() {
        $df = disk_free_space('/');
        $dt = disk_total_space('/');
        
        return round((($dt - $df) / $dt) * 100, 2);
    }
    
    private function checkFilePermissions() {
        $issues = [];
        $critical_dirs = ['uploads', 'logs', 'config'];
        
        foreach ($critical_dirs as $dir) {
            if (!is_writable($dir)) {
                $issues[] = "$dir is not writable";
            }
        }
        
        return $issues;
    }
}
```

#### Common Error Solutions
```php
// Error resolution guide
class ErrorResolver {
    private $solutions = [
        'database_connection_failed' => [
            'check_database_server' => 'Ensure MySQL/MariaDB server is running',
            'verify_credentials' => 'Check database username and password',
            'test_network' => 'Verify network connectivity to database server',
            'check_permissions' => 'Ensure database user has necessary permissions'
        ],
        'file_upload_failed' => [
            'check_permissions' => 'Verify upload directory is writable',
            'check_disk_space' => 'Ensure sufficient disk space',
            'check_php_limits' => 'Verify upload_max_filesize and post_max_size',
            'check_file_type' => 'Ensure file type is allowed'
        ],
        'session_expired' => [
            'check_session_config' => 'Verify session.save_path is writable',
            'check_php_time' => 'Ensure server time is correct',
            'check_cookies' => 'Verify browser accepts cookies',
            'clear_browser_cache' => 'Clear browser cache and cookies'
        ],
        'email_not_sending' => [
            'check_smtp_config' => 'Verify SMTP settings are correct',
            'test_smtp_connection' => 'Test connection to SMTP server',
            'check_email_limits' => 'Verify not exceeding email sending limits',
            'check_spam_filters' => 'Check if emails are marked as spam'
        ]
    ];
    
    public function getSolution($error_type) {
        return $this->solutions[$error_type] ?? 
               ['general' => 'Check system logs and contact administrator'];
    }
    
    public function autoFix($error_type) {
        switch ($error_type) {
            case 'file_upload_failed':
                return $this->fixFilePermissions();
            case 'session_expired':
                return $this->fixSessionPath();
            default:
                return false;
        }
    }
    
    private function fixFilePermissions() {
        $dirs = ['uploads', 'logs'];
        foreach ($dirs as $dir) {
            if (!is_writable($dir)) {
                chmod($dir, 0755);
                return true;
            }
        }
        return false;
    }
    
    private function fixSessionPath() {
        $session_path = session_save_path();
        if (!is_writable($session_path)) {
            chmod($session_path, 0755);
            return true;
        }
        return false;
    }
}
```

---

## 15. Appendices

### 15.1 Configuration Reference

#### Complete Configuration File
```php
<?php
// config/config.php - Complete system configuration

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'hostel_user');
define('DB_PASS', 'secure_password_here');
define('DB_NAME', 'aditya_hostel');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://hostel.example.com');
define('SITE_NAME', 'Aditya Boys Hostel');
define('SITE_EMAIL', 'info@adityahostel.com');
define('ADMIN_EMAIL', 'admin@adityahostel.com');

// Security Configuration
define('ENCRYPTION_KEY', 'your_32_character_encryption_key_here');
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('PASSWORD_MIN_LENGTH', 8);
define('ENABLE_2FA', false);

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 10485760); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Email Configuration
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'email@example.com');
define('SMTP_PASSWORD', 'smtp_password');
define('SMTP_ENCRYPTION', 'tls');

// Payment Configuration
define('PAYMENT_GATEWAY_ENABLED', false);
define('UPI_TIMEOUT', 900); // 15 minutes
define('PAYMENT_REMINDER_DAYS', [7, 3, 1]);

// Notification Configuration
define('NOTIFICATION_RETENTION_DAYS', 180);
define('EMAIL_NOTIFICATIONS_ENABLED', true);
define('SMS_NOTIFICATIONS_ENABLED', false);

// Backup Configuration
define('BACKUP_ENABLED', true);
define('BACKUP_SCHEDULE', '0 2 * * *'); // Daily at 2 AM
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_PATH', '/var/backups/hostel/');

// Logging Configuration
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR, CRITICAL
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_MAX_SIZE', 10485760); // 10MB
define('LOG_ROTATION_ENABLED', true);

// Performance Configuration
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600); // 1 hour
define('PAGE_CACHE_ENABLED', true);
define('DB_QUERY_CACHE_ENABLED', true);

// Development Configuration
define('DEBUG_MODE', false);
define('ERROR_REPORTING', false);
define('MAINTENANCE_MODE', false);

// API Configuration
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TOKEN_EXPIRY', 3600); // 1 hour
define('API_VERSION', 'v1');

// Feature Flags
define('FEATURE_MOBILE_APP', false);
define('FEATURE_PARENT_PORTAL', false);
define('FEATURE_ANALYTICS', true);
define('FEATURE_BULK_OPERATIONS', true);

// Third-party Integration
define('GOOGLE_ANALYTICS_ID', '');
define('FACEBOOK_PIXEL_ID', '');
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Legal Configuration
define('PRIVACY_POLICY_URL', SITE_URL . '/privacy-policy');
define('TERMS_CONDITIONS_URL', SITE_URL . '/terms-conditions');
define('COOKIE_CONSENT_ENABLED', true);
?>
```

### 15.2 Database Schema Reference

#### Complete Table Relationships
```sql
-- Entity Relationship Diagram (Text representation)

-- Students (Core Entity)
students (id) 1:N → payments (student_id)
students (id) 1:N → complaints (student_id)
students (id) 1:N → fees (student_id)
students (id) 1:1 → rooms (id) [via room_id]

-- Rooms (Core Entity)
rooms (id) 1:N → students (room_id)
rooms (id) 1:N → complaints (room_id)

-- Fees (Core Entity)
fees (id) 1:N → payments (fee_id)

-- Payments (Transaction Entity)
payments (id) 1:1 → fees (id) [via fee_id]
payments (id) 1:N → notifications (related_id)

-- Complaints (Support Entity)
complaints (id) 1:N → notifications (related_id)

-- Notifications (Communication Entity)
notifications (id) N:1 → students (student_id)
notifications (id) N:1 → admins (admin_id)

-- Audit Logs (Security Entity)
audit_logs (id) N:1 → students (user_id)
audit_logs (id) N:1 → admins (user_id)

-- Email Logs (Communication Entity)
email_logs (id) N:1 → students (student_id)
```

#### Index Optimization Guide
```sql
-- Recommended indexes for optimal performance

-- Primary indexes (already created by primary keys)
-- students.id, rooms.id, fees.id, payments.id, complaints.id, notifications.id

-- Foreign key indexes
CREATE INDEX idx_students_room_id ON students(room_id);
CREATE INDEX idx_payments_student_id ON payments(student_id);
CREATE INDEX idx_payments_fee_id ON payments(fee_id);
CREATE INDEX idx_complaints_student_id ON complaints(student_id);
CREATE INDEX idx_complaints_room_id ON complaints(room_id);
CREATE INDEX idx_fees_student_id ON fees(student_id);
CREATE INDEX idx_notifications_student_id ON notifications(student_id);
CREATE INDEX idx_notifications_admin_id ON notifications(admin_id);

-- Search and filter indexes
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_students_status ON students(status);
CREATE INDEX idx_students_created_at ON students(created_at);

CREATE INDEX idx_rooms_type ON rooms(type);
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_rooms_floor ON rooms(floor);

CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_payment_method ON payments(payment_method);
CREATE INDEX idx_payments_created_at ON payments(created_at);

CREATE INDEX idx_fees_status ON fees(status);
CREATE INDEX idx_fees_due_date ON fees(due_date);
CREATE INDEX idx_fees_type ON fees(type);

CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_complaints_priority ON complaints(priority);
CREATE INDEX idx_complaints_category ON complaints(category);
CREATE INDEX idx_complaints_created_at ON complaints(created_at);

CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);

-- Composite indexes for common queries
CREATE INDEX idx_students_status_room ON students(status, room_id);
CREATE INDEX idx_payments_student_status ON payments(student_id, status);
CREATE INDEX idx_fees_student_status ON fees(student_id, status);
CREATE INDEX idx_complaints_student_status ON complaints(student_id, status);
CREATE INDEX idx_notifications_student_read ON notifications(student_id, is_read);

-- Full-text search indexes (if needed)
CREATE FULLTEXT INDEX idx_students_search ON students(full_name, email);
CREATE FULLTEXT INDEX idx_complaints_search ON complaints(subject, description);
```

### 15.3 API Reference Card

#### Quick API Reference
```markdown
# Aditya Boys Hostel API Quick Reference

## Base URL
`https://hostel.example.com/api/v1`

## Authentication
Header: `Authorization: Bearer {jwt_token}`

## Common Response Format
```json
{
    "success": true|false,
    "data": {...}|[],
    "error": {...},
    "pagination": {...}
}
```

## Endpoints Summary

### Authentication
- POST `/auth/login` - User login
- POST `/auth/logout` - User logout
- POST `/auth/refresh` - Refresh token
- POST `/auth/forgot-password` - Forgot password
- POST `/auth/reset-password` - Reset password

### Students
- GET `/students` - List students
- GET `/students/{id}` - Get student details
- POST `/students` - Create student
- PUT `/students/{id}` - Update student
- DELETE `/students/{id}` - Delete student

### Rooms
- GET `/rooms` - List rooms
- GET `/rooms/{id}` - Get room details
- POST `/rooms` - Create room
- PUT `/rooms/{id}` - Update room
- DELETE `/rooms/{id}` - Delete room

### Fees
- GET `/fees` - List fees
- GET `/fees/{id}` - Get fee details
- POST `/fees` - Create fee
- PUT `/fees/{id}` - Update fee
- DELETE `/fees/{id}` - Delete fee

### Payments
- GET `/payments` - List payments
- GET `/payments/{id}` - Get payment details
- POST `/payments` - Submit payment
- PUT `/payments/{id}/approve` - Approve payment
- PUT `/payments/{id}/reject` - Reject payment

### Complaints
- GET `/complaints` - List complaints
- GET `/complaints/{id}` - Get complaint details
- POST `/complaints` - Create complaint
- PUT `/complaints/{id}` - Update complaint
- DELETE `/complaints/{id}` - Delete complaint

### Notifications
- GET `/notifications` - List notifications
- GET `/notifications/{id}` - Get notification
- PUT `/notifications/{id}/read` - Mark as read
- DELETE `/notifications/{id}` - Delete notification

### Reports
- GET `/reports/students` - Student reports
- GET `/reports/payments` - Payment reports
- GET `/reports/complaints` - Complaint reports
- GET `/reports/occupancy` - Occupancy reports
```

### 15.4 Security Checklist

#### Production Security Checklist
```markdown
# Security Deployment Checklist

## Database Security
- [ ] Change default database passwords
- [ ] Restrict database user privileges
- [ ] Enable database query logging
- [ ] Set up regular database backups
- [ ] Test backup restoration procedures
- [ ] Enable database encryption (if available)

## File System Security
- [ ] Set appropriate file permissions (755 for directories, 644 for files)
- [ ] Protect configuration files (600 permissions)
- [ ] Disable directory listing
- [ ] Set up secure file upload restrictions
- [ ] Implement file type validation
- [ ] Regularly scan uploaded files for malware

## Web Server Security
- [ ] Install SSL/TLS certificate
- [ ] Enable HTTPS redirect
- [ ] Set security headers (HSTS, CSP, X-Frame-Options)
- [ ] Disable unnecessary HTTP methods
- [ ] Implement rate limiting
- [ ] Set up web application firewall (WAF)

## Application Security
- [ ] Enable input validation and sanitization
- [ ] Implement CSRF protection
- [ ] Use parameterized queries for database operations
- [ ] Enable XSS protection
- [ ] Implement secure session management
- [ ] Set up secure password policies
- [ ] Enable account lockout after failed attempts
- [ ] Implement two-factor authentication (if possible)

## Monitoring & Logging
- [ ] Enable comprehensive error logging
- [ ] Set up security event logging
- [ ] Implement log monitoring and alerting
- [ ] Set up intrusion detection system
- [ ] Regular security scans and penetration testing
- [ ] Monitor for unusual database activity

## Backup & Recovery
- [ ] Automated daily database backups
- [ ] Off-site backup storage
- [ ] Regular backup verification
- [ ] Documented recovery procedures
- [ ] Disaster recovery plan
- [ ] Regular recovery testing

## Compliance & Legal
- [ ] Privacy policy implementation
- [ ] GDPR compliance (if applicable)
- [ ] Data retention policy
- [ ] Cookie consent implementation
- [ ] Terms of service agreement
- [ ] Regular security audits
```

### 15.5 Performance Optimization Guide

#### Database Performance Tuning
```sql
-- MySQL Performance Optimization Settings

-- Memory allocation
SET GLOBAL innodb_buffer_pool_size = 2147483648; -- 2GB
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB
SET GLOBAL innodb_flush_log_at_trx_commit = 2;
SET GLOBAL innodb_flush_method = O_DIRECT;

-- Query cache (if using MySQL < 8.0)
SET GLOBAL query_cache_type = ON;
SET GLOBAL query_cache_size = 67108864; -- 64MB

-- Connection settings
SET GLOBAL max_connections = 200;
SET GLOBAL max_user_connections = 50;
SET GLOBAL wait_timeout = 600;
SET GLOBAL interactive_timeout = 600;

-- Slow query logging
SET GLOBAL slow_query_log = ON;
SET GLOBAL long_query_time = 2;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow.log';

-- Binary logging (for replication)
SET GLOBAL log_bin = ON;
SET GLOBAL binlog_format = ROW;
SET GLOBAL expire_logs_days = 7;
```

#### Application Caching Strategy
```php
// Caching implementation
class CacheManager {
    private $cache_dir;
    private $default_ttl;
    
    public function __construct($cache_dir = 'cache/', $default_ttl = 3600) {
        $this->cache_dir = $cache_dir;
        $this->default_ttl = $default_ttl;
        
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
    }
    
    public function get($key) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $file = $this->getCacheFile($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        file_put_contents($file, serialize($data));
    }
    
    public function delete($key) {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function clear() {
        $files = glob($this->cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    private function getCacheFile($key) {
        return $this->cache_dir . md5($key) . '.cache';
    }
}

// Usage examples
$cache = new CacheManager();

// Cache database query results
$students = $cache->get('all_students');
if ($students === null) {
    $students = $db->query("SELECT * FROM students")->fetch_all(MYSQLI_ASSOC);
    $cache->set('all_students', $students, 1800); // 30 minutes
}

// Cache configuration data
$config = $cache->get('system_config');
if ($config === null) {
    $config = loadSystemConfig();
    $cache->set('system_config', $config, 3600); // 1 hour
}
```

---

## 16. Code Examples & Implementation

### 16.1 Complete Working Examples

#### Student Registration Form Implementation
```php
<?php
// student/register.php - Complete student registration implementation

require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $course = sanitizeInput($_POST['course'] ?? '');
    $year = (int)($_POST['year'] ?? 1);
    $roll_number = sanitizeInput($_POST['roll_number'] ?? '');
    
    // Validation
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone number required";
    if (empty($password) || strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($course)) $errors[] = "Course is required";
    if (empty($roll_number)) $errors[] = "Roll number is required";
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
    }
    
    // Check if roll number already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
        $stmt->bind_param("s", $roll_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Roll number already registered";
        }
    }
    
    // Create student account if no errors
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate unique student ID
            $student_id = generateStudentId();
            
            // Insert student record
            $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, email, phone, password, course, year, roll_number, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("ssssssis", $student_id, $full_name, $email, $phone, $hashed_password, $course, $year, $roll_number);
            
            if ($stmt->execute()) {
                // Send welcome email
                sendWelcomeEmail($email, $full_name, $student_id);
                
                // Log registration
                logActivity('student_registered', "New student registered: $email", $student_id);
                
                $success = true;
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Helper functions
function generateStudentId() {
    global $conn;
    
    do {
        $year = date('Y');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $student_id = "STU$year$random";
        
        $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $student_id;
}

function sendWelcomeEmail($email, $name, $student_id) {
    $subject = "Welcome to Aditya Boys Hostel";
    $message = "
    <html>
    <head><title>Welcome to Aditya Boys Hostel</title></head>
    <body>
        <h2>Welcome, $name!</h2>
        <p>Thank you for registering at Aditya Boys Hostel.</p>
        <p>Your Student ID: <strong>$student_id</strong></p>
        <p>Your account is currently pending approval by the administration.</p>
        <p>You will receive an email once your account is activated.</p>
        <br>
        <p>Best regards,<br>Aditya Boys Hostel Management</p>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@adityahostel.com" . "\r\n";
    
    mail($email, $subject, $message, $headers);
}

function logActivity($action, $description, $user_id = null) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (action, description, user_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $action, $description, $user_id, $ip_address, $user_agent);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Student Registration</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Registration Successful!</strong><br>
                                Your account has been created and is pending approval.<br>
                                You will receive an email once your account is activated.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Please fix the following errors:</strong><br>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       pattern="[0-9]{10}" maxlength="10" required>
                                <div class="form-text">Enter 10-digit mobile number</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="8" required>
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="8" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="course" class="form-label">Course *</label>
                                <select class="form-select" id="course" name="course" required>
                                    <option value="">Select Course</option>
                                    <option value="Computer Science" <?php echo ($_POST['course'] ?? '') === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Information Technology" <?php echo ($_POST['course'] ?? '') === 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                    <option value="Electronics" <?php echo ($_POST['course'] ?? '') === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                    <option value="Mechanical" <?php echo ($_POST['course'] ?? '') === 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                                    <option value="Civil" <?php echo ($_POST['course'] ?? '') === 'Civil' ? 'selected' : ''; ?>>Civil</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="year" class="form-label">Year *</label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="1" <?php echo ($_POST['year'] ?? '') == '1' ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2" <?php echo ($_POST['year'] ?? '') == '2' ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3" <?php echo ($_POST['year'] ?? '') == '3' ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4" <?php echo ($_POST['year'] ?? '') == '4' ? 'selected' : ''; ?>>4th Year</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="roll_number" class="form-label">Roll Number *</label>
                                    <input type="text" class="form-control" id="roll_number" name="roll_number" 
                                           value="<?php echo htmlspecialchars($_POST['roll_number'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Register Account
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                            <p><a href="../index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Phone number validation
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>
```

#### Payment Processing Implementation
```php
<?php
// student/process_payment.php - Complete payment processing implementation

require_once '../config/database.php';
require_once '../includes/functions.php';

// Start session and check authentication
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$errors = [];
$success = false;
$payment_id = null;

// Process payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fee_id = (int)($_POST['fee_id'] ?? 0);
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $transaction_id = sanitizeInput($_POST['transaction_id'] ?? '');
    
    // Validate input
    if ($fee_id <= 0) $errors[] = "Invalid fee selected";
    if (empty($payment_method)) $errors[] = "Payment method is required";
    if ($amount <= 0) $errors[] = "Invalid payment amount";
    if (empty($transaction_id)) $errors[] = "Transaction ID is required";
    
    // Validate fee exists and belongs to student
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT f.*, s.full_name FROM fees f JOIN students s ON f.student_id = s.id WHERE f.id = ? AND f.student_id = ? AND f.status = 'unpaid'");
        $stmt->bind_param("ii", $fee_id, $_SESSION['student_id']);
        $stmt->execute();
        $fee = $stmt->get_result()->fetch_assoc();
        
        if (!$fee) {
            $errors[] = "Invalid fee or fee already paid";
        } else {
            // Validate amount matches fee amount
            if ($amount != $fee['amount']) {
                $errors[] = "Payment amount does not match fee amount";
            }
        }
    }
    
    // Handle file upload
    $payment_proof_path = '';
    if (empty($errors) && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and PDF allowed";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size too large. Maximum 5MB allowed";
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'payment_' . $_SESSION['student_id'] . '_' . time() . '.' . $extension;
            $upload_dir = '../uploads/payment_proofs/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $payment_proof_path = $filename;
            } else {
                $errors[] = "Failed to upload payment proof";
            }
        }
    } else {
        $errors[] = "Payment proof is required";
    }
    
    // Process payment if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Insert payment record
            $stmt = $conn->prepare("INSERT INTO payments (fee_id, student_id, amount, payment_method, transaction_id, payment_proof_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("iidsss", $fee_id, $_SESSION['student_id'], $amount, $payment_method, $transaction_id, $payment_proof_path);
            
            if ($stmt->execute()) {
                $payment_id = $conn->insert_id;
                
                // Update fee status
                $stmt = $conn->prepare("UPDATE fees SET status = 'payment_submitted', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $fee_id);
                $stmt->execute();
                
                // Create notification for admin
                $notification_message = "New payment submitted by {$fee['full_name']} for {$fee['type']} - Amount: ₹" . number_format($amount, 2);
                $stmt = $conn->prepare("INSERT INTO notifications (type, message, related_id, student_id, is_read, created_at) VALUES ('payment', ?, ?, ?, 0, NOW())");
                $stmt->bind_param("ssi", $notification_message, $payment_id, $_SESSION['student_id']);
                $stmt->execute();
                
                // Send confirmation email to student
                sendPaymentConfirmationEmail($_SESSION['email'], $fee['full_name'], $amount, $transaction_id, $payment_method);
                
                // Send notification to admin
                sendAdminPaymentNotification($fee['full_name'], $amount, $payment_method);
                
                // Log payment submission
                logActivity('payment_submitted', "Payment submitted: $transaction_id", $_SESSION['student_id']);
                
                // Commit transaction
                $conn->commit();
                $success = true;
            } else {
                throw new Exception("Failed to process payment");
            }
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $errors[] = "Payment processing failed: " . $e->getMessage();
        }
    }
}

// Helper functions
function sendPaymentConfirmationEmail($email, $name, $amount, $transaction_id, $payment_method) {
    $subject = "Payment Confirmation - Aditya Boys Hostel";
    $message = "
    <html>
    <head><title>Payment Confirmation</title></head>
    <body>
        <h2>Payment Confirmation</h2>
        <p>Dear $name,</p>
        <p>Your payment has been successfully submitted and is pending approval.</p>
        <table border='1' cellpadding='10' style='border-collapse: collapse;'>
            <tr><td><strong>Amount:</strong></td><td>₹" . number_format($amount, 2) . "</td></tr>
            <tr><td><strong>Transaction ID:</strong></td><td>$transaction_id</td></tr>
            <tr><td><strong>Payment Method:</strong></td><td>" . ucfirst($payment_method) . "</td></tr>
            <tr><td><strong>Status:</strong></td><td>Pending Approval</td></tr>
        </table>
        <p>You will receive another email once your payment is approved.</p>
        <br>
        <p>Best regards,<br>Aditya Boys Hostel Management</p>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@adityahostel.com" . "\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendAdminPaymentNotification($student_name, $amount, $payment_method) {
    $admin_email = 'admin@adityahostel.com';
    $subject = "New Payment Submission - Aditya Boys Hostel";
    $message = "
    <html>
    <head><title>New Payment Submission</title></head>
    <body>
        <h2>New Payment Submission</h2>
        <p>A new payment has been submitted by <strong>$student_name</strong>.</p>
        <table border='1' cellpadding='10' style='border-collapse: collapse;'>
            <tr><td><strong>Student:</strong></td><td>$student_name</td></tr>
            <tr><td><strong>Amount:</strong></td><td>₹" . number_format($amount, 2) . "</td></tr>
            <tr><td><strong>Payment Method:</strong></td><td>" . ucfirst($payment_method) . "</td></tr>
            <tr><td><strong>Time:</strong></td><td>" . date('Y-m-d H:i:s') . "</td></tr>
        </table>
        <p>Please login to the admin panel to review and approve this payment.</p>
        <br>
        <p>Best regards,<br>Aditya Boys Hostel System</p>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@adityahostel.com" . "\r\n";
    
    mail($admin_email, $subject, $message, $headers);
}

function logActivity($action, $description, $user_id = null) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (action, description, user_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $action, $description, $user_id, $ip_address, $user_agent);
    $stmt->execute();
}

// Get student information for display
$stmt = $conn->prepare("SELECT full_name, email FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Payment Confirmation</h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <h4>Payment Submitted Successfully!</h4>
                                <p class="mb-2">Thank you, <strong><?php echo htmlspecialchars($student['full_name']); ?></strong></p>
                                <p>Your payment of <strong>₹<?php echo number_format($amount, 2); ?></strong> has been submitted and is pending approval.</p>
                                <p class="mb-0">Payment ID: <strong>#<?php echo str_pad($payment_id, 6, '0', STR_PAD_LEFT); ?></strong></p>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Payment Details</h6>
                                            <p class="mb-1"><strong>Amount:</strong> ₹<?php echo number_format($amount, 2); ?></p>
                                            <p class="mb-1"><strong>Method:</strong> <?php echo ucfirst($payment_method); ?></p>
                                            <p class="mb-1"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction_id); ?></p>
                                            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-warning">Pending Approval</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-envelope me-2"></i>Notifications</h6>
                                            <p class="mb-1">✓ Confirmation email sent to your registered email</p>
                                            <p class="mb-1">✓ Admin notified for approval</p>
                                            <p class="mb-0">✓ You'll receive updates on approval</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <h4>Payment Failed</h4>
                                <p>There was an error processing your payment:</p>
                                <ul class="text-start">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                            <a href="payments.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-history me-2"></i>View Payment History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

#### Admin Dashboard Analytics Implementation
```php
<?php
// admin/dashboard_analytics.php - Complete analytics implementation

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get date ranges for analytics
$today = date('Y-m-d');
$this_month = date('Y-m-01');
$last_month = date('Y-m-01', strtotime('-1 month'));
$this_year = date('Y-01-01');

// Initialize analytics data
$analytics = [];

// Student Statistics
$analytics['students'] = [
    'total' => 0,
    'active' => 0,
    'pending' => 0,
    'new_this_month' => 0,
    'by_course' => [],
    'by_year' => []
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM students");
$stmt->execute();
$analytics['students']['total'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as active FROM students WHERE status = 'active'");
$stmt->execute();
$analytics['students']['active'] = $stmt->get_result()->fetch_assoc()['active'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM students WHERE status = 'pending'");
$stmt->execute();
$analytics['students']['pending'] = $stmt->get_result()->fetch_assoc()['pending'];

$stmt = $conn->prepare("SELECT COUNT(*) as new_month FROM students WHERE created_at >= ?");
$stmt->bind_param("s", $this_month);
$stmt->execute();
$analytics['students']['new_this_month'] = $stmt->get_result()->fetch_assoc()['new_month'];

// Students by course
$stmt = $conn->prepare("SELECT course, COUNT(*) as count FROM students WHERE status = 'active' GROUP BY course ORDER BY count DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['students']['by_course'][] = $row;
}

// Students by year
$stmt = $conn->prepare("SELECT year, COUNT(*) as count FROM students WHERE status = 'active' GROUP BY year ORDER BY year");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['students']['by_year'][] = $row;
}

// Room Statistics
$analytics['rooms'] = [
    'total' => 0,
    'occupied' => 0,
    'available' => 0,
    'maintenance' => 0,
    'occupancy_rate' => 0,
    'by_type' => [],
    'by_floor' => []
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM rooms");
$stmt->execute();
$analytics['rooms']['total'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as occupied FROM rooms WHERE status = 'occupied'");
$stmt->execute();
$analytics['rooms']['occupied'] = $stmt->get_result()->fetch_assoc()['occupied'];

$stmt = $conn->prepare("SELECT COUNT(*) as available FROM rooms WHERE status = 'available'");
$stmt->execute();
$analytics['rooms']['available'] = $stmt->get_result()->fetch_assoc()['available'];

$stmt = $conn->prepare("SELECT COUNT(*) as maintenance FROM rooms WHERE status = 'maintenance'");
$stmt->execute();
$analytics['rooms']['maintenance'] = $stmt->get_result()->fetch_assoc()['maintenance'];

$analytics['rooms']['occupancy_rate'] = $analytics['rooms']['total'] > 0 
    ? round(($analytics['rooms']['occupied'] / $analytics['rooms']['total']) * 100, 1) 
    : 0;

// Rooms by type
$stmt = $conn->prepare("SELECT type, COUNT(*) as count FROM rooms GROUP BY type ORDER BY count DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['rooms']['by_type'][] = $row;
}

// Rooms by floor
$stmt = $conn->prepare("SELECT floor, COUNT(*) as count FROM rooms GROUP BY floor ORDER BY floor");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['rooms']['by_floor'][] = $row;
}

// Payment Statistics
$analytics['payments'] = [
    'total_collected' => 0,
    'pending' => 0,
    'this_month' => 0,
    'last_month' => 0,
    'overdue' => 0,
    'by_method' => [],
    'monthly_trend' => []
];

$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'approved'");
$stmt->execute();
$analytics['payments']['total_collected'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM payments WHERE status = 'pending'");
$stmt->execute();
$analytics['payments']['pending'] = $stmt->get_result()->fetch_assoc()['pending'];

$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as this_month FROM payments WHERE status = 'approved' AND created_at >= ?");
$stmt->bind_param("s", $this_month);
$stmt->execute();
$analytics['payments']['this_month'] = $stmt->get_result()->fetch_assoc()['this_month'];

$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as last_month FROM payments WHERE status = 'approved' AND created_at >= ? AND created_at < ?");
$stmt->bind_param("ss", $last_month, $this_month);
$stmt->execute();
$analytics['payments']['last_month'] = $stmt->get_result()->fetch_assoc()['last_month'];

$stmt = $conn->prepare("SELECT COUNT(*) as overdue FROM fees WHERE status = 'unpaid' AND due_date < ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$analytics['payments']['overdue'] = $stmt->get_result()->fetch_assoc()['overdue'];

// Payments by method
$stmt = $conn->prepare("SELECT payment_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'approved' GROUP BY payment_method ORDER BY total DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['payments']['by_method'][] = $row;
}

// Monthly payment trend (last 6 months)
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           COALESCE(SUM(amount), 0) as total,
           COUNT(*) as count
    FROM payments 
    WHERE status = 'approved' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['payments']['monthly_trend'][] = $row;
}

// Complaint Statistics
$analytics['complaints'] = [
    'total' => 0,
    'pending' => 0,
    'resolved' => 0,
    'in_progress' => 0,
    'this_month' => 0,
    'by_category' => [],
    'by_priority' => []
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM complaints");
$stmt->execute();
$analytics['complaints']['total'] = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM complaints WHERE status = 'pending'");
$stmt->execute();
$analytics['complaints']['pending'] = $stmt->get_result()->fetch_assoc()['pending'];

$stmt = $conn->prepare("SELECT COUNT(*) as resolved FROM complaints WHERE status = 'resolved'");
$stmt->execute();
$analytics['complaints']['resolved'] = $stmt->get_result()->fetch_assoc()['resolved'];

$stmt = $conn->prepare("SELECT COUNT(*) as in_progress FROM complaints WHERE status = 'in_progress'");
$stmt->execute();
$analytics['complaints']['in_progress'] = $stmt->get_result()->fetch_assoc()['in_progress'];

$stmt = $conn->prepare("SELECT COUNT(*) as this_month FROM complaints WHERE created_at >= ?");
$stmt->bind_param("s", $this_month);
$stmt->execute();
$analytics['complaints']['this_month'] = $stmt->get_result()->fetch_assoc()['this_month'];

// Complaints by category
$stmt = $conn->prepare("SELECT category, COUNT(*) as count FROM complaints GROUP BY category ORDER BY count DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['complaints']['by_category'][] = $row;
}

// Complaints by priority
$stmt = $conn->prepare("SELECT priority, COUNT(*) as count FROM complaints GROUP BY priority ORDER BY FIELD(priority, 'high', 'medium', 'low')");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['complaints']['by_priority'][] = $row;
}

// Recent Activities
$analytics['recent_activities'] = [];
$stmt = $conn->prepare("
    SELECT al.*, s.full_name as student_name, a.full_name as admin_name
    FROM audit_logs al
    LEFT JOIN students s ON al.user_id = s.id AND al.user_type = 'student'
    LEFT JOIN admins a ON al.user_id = a.id AND al.user_type = 'admin'
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $analytics['recent_activities'][] = $row;
}

// Generate JSON data for charts
$chart_data = [
    'students_by_course' => json_encode(array_column($analytics['students']['by_course'], 'count')),
    'students_by_course_labels' => json_encode(array_column($analytics['students']['by_course'], 'course')),
    'rooms_by_floor' => json_encode(array_column($analytics['rooms']['by_floor'], 'count')),
    'rooms_by_floor_labels' => json_encode(array_column($analytics['rooms']['by_floor'], 'floor')),
    'payments_monthly' => json_encode(array_column($analytics['payments']['monthly_trend'], 'total')),
    'payments_monthly_labels' => json_encode(array_map(function($date) {
        return date('M Y', strtotime($date . '-01'));
    }, array_column($analytics['payments']['monthly_trend'], 'month'))),
    'complaints_by_category' => json_encode(array_column($analytics['complaints']['by_category'], 'count')),
    'complaints_by_category_labels' => json_encode(array_column($analytics['complaints']['by_category'], 'category'))
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Aditya Boys Hostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="sidebar-heading">Analytics</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#overview">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#students">Students</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#rooms">Rooms</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#payments">Payments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#complaints">Complaints</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Analytics Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                                <i class="fas fa-sync me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Overview Cards -->
                <section id="overview">
                    <h4 class="mb-3">Overview</h4>
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Students</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $analytics['students']['total']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Occupancy Rate</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $analytics['rooms']['occupancy_rate']; ?>%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Collected</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?php echo number_format($analytics['payments']['total_collected'], 0); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Complaints</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $analytics['complaints']['pending']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Students Section -->
                <section id="students" class="mt-4">
                    <h4 class="mb-3">Student Analytics</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Students by Course</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="studentsByCourseChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Students by Year</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="studentsByYearChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Rooms Section -->
                <section id="rooms" class="mt-4">
                    <h4 class="mb-3">Room Analytics</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-success">Rooms by Floor</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="roomsByFloorChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-success">Room Status</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="text-success">
                                                <i class="fas fa-check-circle fa-2x"></i>
                                                <h5><?php echo $analytics['rooms']['available']; ?></h5>
                                                <small>Available</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-primary">
                                                <i class="fas fa-user fa-2x"></i>
                                                <h5><?php echo $analytics['rooms']['occupied']; ?></h5>
                                                <small>Occupied</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-warning">
                                                <i class="fas fa-tools fa-2x"></i>
                                                <h5><?php echo $analytics['rooms']['maintenance']; ?></h5>
                                                <small>Maintenance</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Payments Section -->
                <section id="payments" class="mt-4">
                    <h4 class="mb-3">Payment Analytics</h4>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-info">Monthly Payment Trend</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="paymentTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-info">Payment Summary</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>This Month:</strong> ₹<?php echo number_format($analytics['payments']['this_month'], 0); ?></p>
                                    <p><strong>Last Month:</strong> ₹<?php echo number_format($analytics['payments']['last_month'], 0); ?></p>
                                    <p><strong>Pending:</strong> <?php echo $analytics['payments']['pending']; ?></p>
                                    <p><strong>Overdue:</strong> <?php echo $analytics['payments']['overdue']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Complaints Section -->
                <section id="complaints" class="mt-4">
                    <h4 class="mb-3">Complaint Analytics</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-warning">Complaints by Category</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="complaintsByCategoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-warning">Recent Activities</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($analytics['recent_activities'], 0, 5) as $activity): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($activity['created_at'])); ?></small>
                                                        <div><?php echo htmlspecialchars($activity['description']); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart configurations
        Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

        // Students by Course Chart
        const studentsByCourseCtx = document.getElementById('studentsByCourseChart').getContext('2d');
        new Chart(studentsByCourseCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $chart_data['students_by_course_labels']; ?>,
                datasets: [{
                    data: <?php echo $chart_data['students_by_course']; ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#c0392b'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Students by Year Chart
        const studentsByYearCtx = document.getElementById('studentsByYearChart').getContext('2d');
        new Chart(studentsByYearCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($analytics['students']['by_year'], 'year')); ?>,
                datasets: [{
                    label: 'Students',
                    data: <?php echo json_encode(array_column($analytics['students']['by_year'], 'count')); ?>,
                    backgroundColor: '#4e73df',
                    hoverBackgroundColor: '#2e59d9',
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Rooms by Floor Chart
        const roomsByFloorCtx = document.getElementById('roomsByFloorChart').getContext('2d');
        new Chart(roomsByFloorCtx, {
            type: 'pie',
            data: {
                labels: <?php echo $chart_data['rooms_by_floor_labels']; ?>,
                datasets: [{
                    data: <?php echo $chart_data['rooms_by_floor']; ?>,
                    backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#17a673', '#2c9faf', '#f4b619', '#c0392b'],
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Payment Trend Chart
        const paymentTrendCtx = document.getElementById('paymentTrendChart').getContext('2d');
        new Chart(paymentTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo $chart_data['payments_monthly_labels']; ?>,
                datasets: [{
                    label: 'Payments (₹)',
                    data: <?php echo $chart_data['payments_monthly']; ?>,
                    borderColor: '#36b9cc',
                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
                    fill: true,
                    tension: 0.4
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Complaints by Category Chart
        const complaintsByCategoryCtx = document.getElementById('complaintsByCategoryChart').getContext('2d');
        new Chart(complaintsByCategoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $chart_data['complaints_by_category_labels']; ?>,
                datasets: [{
                    label: 'Complaints',
                    data: <?php echo $chart_data['complaints_by_category']; ?>,
                    backgroundColor: '#f6c23e',
                    hoverBackgroundColor: '#f4b619',
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Utility functions
        function exportData() {
            // Implement export functionality
            alert('Export functionality would be implemented here');
        }

        function refreshData() {
            location.reload();
        }

        // Smooth scroll for navigation
        document.querySelectorAll('.nav-link').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
```

---

## Conclusion

This comprehensive documentation provides a complete guide to the Aditya Boys Hostel Management System, covering all aspects from installation and configuration to advanced features and maintenance procedures. The system is designed to be robust, secure, and scalable, meeting the needs of modern hostel management.

### Key Features Implemented:
- **Multi-tier Architecture**: Frontend, Backend, and Database layers
- **Role-Based Access Control**: Admin and Student roles with specific permissions
- **Comprehensive Payment System**: Multiple payment methods with QR code integration
- **Real-time Notifications**: WebSocket-based instant messaging
- **Robust Security**: Input validation, SQL injection prevention, XSS/CSRF protection
- **Mobile Responsive Design**: Works seamlessly across all devices
- **Advanced Reporting**: Detailed analytics and reporting capabilities
- **Automated Maintenance**: Backup systems, log rotation, and performance monitoring

### Future Enhancements:
- Mobile application for iOS and Android
- Parent portal for student monitoring
- Integration with biometric attendance systems
- AI-powered predictive analytics for occupancy planning
- Advanced expense management and budgeting tools
- Integration with university ERP systems

This documentation serves as a complete reference for developers, administrators, and users of the Aditya Boys Hostel Management System.

---

**Document Version**: 2.0  
**Last Updated**: January 2024  
**Total Pages**: ~100 pages  
**Document ID**: ADITYA-HOSTEL-DOC-2024-001
