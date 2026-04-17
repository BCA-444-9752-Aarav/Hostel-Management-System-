# Aditya Boys Hostel Management System

A comprehensive web-based hostel management system built with PHP, MySQL, and Bootstrap 5.

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- MySQL 8.0+
- Apache 2.4+
- PHP Extensions: mysqli, gd, curl, openssl, mbstring

### Installation
1. Clone/Extract to web directory
2. Create database and import `database/complete_schema.sql`
3. Configure `config/db.php` with database credentials
4. Set permissions: `chmod 777 uploads/ "QR code/"`
5. Access: `http://localhost/AdityaBoysHostel/`

### Default Credentials
- **Admin**: Username: `admin`, Password: `admin`
- **Student**: Register via student registration form

## 📋 Features

### 👨‍💼 Admin Features
- Student registration and management
- Room allocation and management
- Fee structure and payment tracking
- Complaint handling and resolution
- Bulk notifications and emails
- Payment method configuration
- Comprehensive reporting

### 👨‍🎓 Student Features
- Personal dashboard
- Online fee payment (UPI, Bank Transfer, Google Pay, Paytm, PhonePe)
- Complaint submission and tracking
- Profile management
- Real-time notifications
- Payment history and receipts

## 🏗️ System Architecture

```
Frontend: Bootstrap 5 + Font Awesome + Animate.css
Backend: PHP 8.x with Session Management
Database: MySQL 8.x with 13 tables, 4 views
Email: PHPMailer integration
File Storage: Secure upload handling
```

## 📁 Project Structure

```
AdityaBoysHostel/
├── admin/              # Administrator panel
├── student/            # Student portal  
├── config/             # Database configuration
├── database/           # SQL schemas and updates
├── assets/             # CSS, JS, images
├── uploads/            # User uploads
├── QR code/            # QR code images
├── includes/           # Common files
├── PHPMailer/          # Email library
└── index.php           # Login page
```

## 🗃️ Database Overview

- **13 Core Tables**: students, admins, fees, payments, rooms, complaints, notifications, etc.
- **4 Database Views**: Payment summaries, occupancy reports, complaint analytics
- **38 Indexes**: Optimized for performance
- **Version**: 2.5 (March 26, 2026)

## 🔧 Key Configuration

### Database (config/db.php)
```php
$host = 'localhost';
$dbname = 'aditya_hostel';
$username = 'your_username';
$password = 'your_password';
```

### Payment Methods
- UPI Payments
- Bank Transfer
- Google Pay
- Paytm
- PhonePe

## 📱 Responsive Design

- **Desktop**: Full feature availability
- **Tablet**: Optimized touch interface
- **Mobile**: Responsive layout with essential features

## 🔐 Security Features

- Session-based authentication
- Role-based access control
- SQL injection prevention
- XSS protection
- Secure file uploads
- Password hashing

## 📞 Support

For detailed documentation, see `DOCUMENTATION.md`

### Common Issues
- **Database Connection**: Check config/db.php credentials
- **File Upload**: Set permissions on uploads/ directory
- **Email Issues**: Verify PHPMailer SMTP settings
- **Blank Pages**: Enable PHP error reporting

## 📊 System Stats

- **Current Version**: 2.5
- **Last Updated**: March 26, 2026
- **Tables**: 13
- **Views**: 4
- **Indexes**: 38
- **Payment Methods**: 5
- **User Roles**: 2 (Admin, Student)

---

**© 2026 Aditya Boys Hostel Management System**
