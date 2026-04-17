-- =====================================================
-- Aditya Boys Hostel - Database Update Script
-- Version: 2.4 to 2.5
-- Updated: March 26, 2026
-- Description: Apply latest schema updates to existing database
-- =====================================================

-- Start transaction
START TRANSACTION;

-- Add new indexes to notifications table for better performance
-- These indexes will improve notification management queries

-- Index for type and priority filtering
ALTER TABLE notifications ADD INDEX idx_type_priority (type, priority);

-- Index for user type and read status filtering  
ALTER TABLE notifications ADD INDEX idx_user_type_read (user_type, is_read);

-- Composite index for common notification queries
ALTER TABLE notifications ADD INDEX idx_notifications_composite (user_type, user_id, is_read, created_at);

-- Update schema version in settings (if settings table exists)
INSERT IGNORE INTO hostel_settings (setting_key, setting_value, description) 
VALUES ('schema_version', '2.5', 'Current database schema version');

-- Update existing schema version record if it exists
UPDATE hostel_settings SET setting_value = '2.5' 
WHERE setting_key = 'schema_version';

-- Commit the transaction
COMMIT;

-- =====================================================
-- Update Complete
-- =====================================================
-- 
-- Changes Applied:
-- 1. Added idx_type_priority index to notifications table
-- 2. Added idx_user_type_read index to notifications table  
-- 3. Added idx_notifications_composite index to notifications table
-- 4. Updated schema version to 2.5
--
-- Performance Improvements:
-- - Faster notification filtering by type and priority
-- - Optimized queries for unread notifications per user type
-- - Improved performance for notification listing and statistics
--
-- =====================================================
