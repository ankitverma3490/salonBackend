-- Staff Management System Updates

-- 1. Staff Attendance Table
CREATE TABLE IF NOT EXISTS staff_attendance (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    staff_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    check_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    check_out TIMESTAMP NULL,
    status ENUM('present', 'late', 'on_leave', 'absent') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    INDEX idx_staff_day (staff_id, check_in)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Permissions Table
CREATE TABLE IF NOT EXISTS permissions (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    module VARCHAR(50) NOT NULL, -- e.g., 'bookings', 'staff', 'inventory'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Role Permissions Table (linking user_roles enum to permissions)
-- Since user_roles.role is an ENUM('owner', 'manager', 'staff', 'super_admin'),
-- we will use those strings as role identifiers.
CREATE TABLE IF NOT EXISTS role_permissions (
    role VARCHAR(50) NOT NULL,
    permission_id VARCHAR(36) NOT NULL,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Staff Specific Permissions (Override)
CREATE TABLE IF NOT EXISTS staff_specific_permissions (
    staff_id VARCHAR(36) NOT NULL,
    permission_id VARCHAR(36) NOT NULL,
    is_allowed BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (staff_id, permission_id),
    FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Seed Permissions
INSERT IGNORE INTO permissions (id, name, description, module) VALUES 
(UUID(), 'view_bookings', 'Can view salon bookings', 'bookings'),
(UUID(), 'manage_bookings', 'Can create, update, and cancel bookings', 'bookings'),
(UUID(), 'view_staff', 'Can view salon staff roster', 'staff'),
(UUID(), 'manage_staff', 'Can add, remove, or edit staff details', 'staff'),
(UUID(), 'view_reports', 'Can view business revenue and analytics', 'reports'),
(UUID(), 'manage_services', 'Can manage salon services and pricing', 'services'),
(UUID(), 'track_attendance', 'Can check-in/out and view own attendance', 'attendance'),
(UUID(), 'manage_attendance', 'Can view and edit everyone\'s attendance', 'attendance');

-- 6. Seed Default Role Permissions
-- Associate 'owner' with all permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'owner', id FROM permissions;

-- Associate 'manager' with most permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'manager', id FROM permissions WHERE name NOT IN ('view_reports');

-- Associate 'staff' with limited permissions
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'staff', id FROM permissions WHERE name IN ('view_bookings', 'track_attendance');

-- 7. Add strictly linked ownership to staff_profiles
ALTER TABLE staff_profiles ADD COLUMN IF NOT EXISTS created_by_id VARCHAR(36) AFTER user_id;
ALTER TABLE staff_profiles ADD CONSTRAINT fk_staff_creator FOREIGN KEY (created_by_id) REFERENCES users(id) ON DELETE SET NULL;

-- 8. Link Bookings to Staff
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS staff_id VARCHAR(36) AFTER service_id;
ALTER TABLE bookings ADD CONSTRAINT fk_booking_staff FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE SET NULL;

-- 9. Staff Leaves Table
CREATE TABLE IF NOT EXISTS staff_leaves (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    staff_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    leave_type ENUM('sick', 'casual', 'vacation', 'other') DEFAULT 'casual',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
