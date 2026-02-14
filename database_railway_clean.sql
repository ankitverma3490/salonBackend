-- Salon Booking Platform Database Schema
-- MySQL/MariaDB Version

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS admin_activity_logs;
DROP TABLE IF EXISTS platform_payments;
DROP TABLE IF EXISTS salon_subscriptions;
DROP TABLE IF EXISTS platform_offers;
DROP TABLE IF EXISTS platform_banners;
DROP TABLE IF EXISTS platform_settings;
DROP TABLE IF EXISTS subscription_plans;
DROP TABLE IF EXISTS platform_admins;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS staff_profiles;
DROP TABLE IF EXISTS salons;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS platform_orders;
DROP TABLE IF EXISTS newsletter_subscribers;

-- Users table (authentication)
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY ,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Profiles table
CREATE TABLE profiles (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36) NOT NULL UNIQUE,
    full_name VARCHAR(255),
    phone VARCHAR(20),
    avatar_url TEXT,
    user_type ENUM('customer', 'salon_owner', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salons table
CREATE TABLE salons (
    id VARCHAR(36) PRIMARY KEY ,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(255),
    gst_number VARCHAR(50),
    logo_url TEXT,
    cover_image_url TEXT,
    business_hours JSON,
    tax_settings JSON,
    notification_settings JSON,
    is_active BOOLEAN DEFAULT TRUE,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_at TIMESTAMP NULL,
    approved_by VARCHAR(36),
    rejection_reason TEXT,
    blocked_at TIMESTAMP NULL,
    blocked_by VARCHAR(36),
    block_reason TEXT,
    subscription_plan_id VARCHAR(36),
    subscription_status ENUM('trial', 'active', 'past_due', 'cancelled', 'expired') DEFAULT 'trial',
    subscription_start_date TIMESTAMP NULL,
    subscription_end_date TIMESTAMP NULL,
    trial_ends_at TIMESTAMP DEFAULT (DATE_ADD(NOW(), INTERVAL 14 DAY)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_approval_status (approval_status),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Roles table
CREATE TABLE user_roles (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    role ENUM('owner', 'manager', 'staff', 'super_admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_salon (user_id, salon_id),
    INDEX idx_user_id (user_id),
    INDEX idx_salon_id (salon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services table
CREATE TABLE services (
    id VARCHAR(36) PRIMARY KEY ,
    salon_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_minutes INT NOT NULL,
    category VARCHAR(100),
    image_url TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    INDEX idx_salon_id (salon_id),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff Profiles table
CREATE TABLE staff_profiles (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36),
    salon_id VARCHAR(36) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    avatar_url TEXT,
    specializations JSON,
    commission_percentage DECIMAL(5,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    INDEX idx_salon_id (salon_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE bookings (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    service_id VARCHAR(36) NOT NULL,
    staff_id VARCHAR(36),
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    price_paid DECIMAL(10,2),
    coins_used INT DEFAULT 0,
    coin_currency_value DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    coupon_code VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_salon_id (salon_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status),
    INDEX idx_staff_id (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Admins table
CREATE TABLE platform_admins (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription Plans table
CREATE TABLE subscription_plans (
    id VARCHAR(36) PRIMARY KEY ,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_yearly DECIMAL(10,2),
    max_staff INT DEFAULT 5,
    max_services INT DEFAULT 20,
    max_bookings_per_month INT,
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salon Subscriptions table
CREATE TABLE salon_subscriptions (
    id VARCHAR(36) PRIMARY KEY ,
    salon_id VARCHAR(36) NOT NULL,
    plan_id VARCHAR(36) NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'upgraded', 'downgraded') DEFAULT 'active',
    amount DECIMAL(10,2) NOT NULL,
    billing_cycle ENUM('monthly', 'yearly') NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    payment_method VARCHAR(100),
    payment_reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    INDEX idx_salon_id (salon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Settings table
CREATE TABLE platform_settings (
    id VARCHAR(36) PRIMARY KEY ,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value JSON NOT NULL,
    description TEXT,
    updated_by VARCHAR(36),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Banners table
CREATE TABLE platform_banners (
    id VARCHAR(36) PRIMARY KEY ,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    image_url TEXT,
    link_url TEXT,
    link_text VARCHAR(255),
    position ENUM('home_hero', 'home_secondary', 'sidebar', 'popup') DEFAULT 'home_hero',
    is_active BOOLEAN DEFAULT TRUE,
    start_date TIMESTAMP NULL,
    end_date TIMESTAMP NULL,
    sort_order INT DEFAULT 0,
    created_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Offers table
CREATE TABLE platform_offers (
    id VARCHAR(36) PRIMARY KEY ,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    code VARCHAR(50) UNIQUE,
    discount_type ENUM('percentage', 'fixed', 'free_trial_days') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    applicable_to ENUM('all', 'new_salons', 'existing_salons', 'specific_plans') DEFAULT 'all',
    applicable_plan_ids JSON,
    max_uses INT,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    start_date TIMESTAMP NULL,
    end_date TIMESTAMP NULL,
    created_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Activity Logs table
CREATE TABLE admin_activity_logs (
    id VARCHAR(36) PRIMARY KEY ,
    admin_id VARCHAR(36) NOT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(36),
    details JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_entity_type (entity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Payments table
CREATE TABLE platform_payments (
    id VARCHAR(36) PRIMARY KEY ,
    salon_id VARCHAR(36) NOT NULL,
    subscription_id VARCHAR(36),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(100),
    payment_gateway VARCHAR(100),
    transaction_id VARCHAR(255),
    invoice_number VARCHAR(100),
    invoice_url TEXT,
    notes TEXT,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES salon_subscriptions(id),
    INDEX idx_salon_id (salon_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Platform Orders table
CREATE TABLE platform_orders (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36) NULL,
    guest_name VARCHAR(255) NULL,
    guest_email VARCHAR(255) NULL,
    items JSON,
    shipping_address JSON,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('placed', 'dispatched', 'delivered', 'cancelled') DEFAULT 'placed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscribers table
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Salon Profiles (Clinical/Health Profile)
CREATE TABLE customer_salon_profiles (
    id VARCHAR(36) PRIMARY KEY ,
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    date_of_birth DATE,
    skin_type VARCHAR(50),
    skin_issues TEXT, -- Stored as comma-separated or JSON string
    allergy_records TEXT,
    medical_conditions TEXT,
    notes TEXT,
    concern_photo_url VARCHAR(255),
    concern_photo_public_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_salon_profile (user_id, salon_id),
    INDEX idx_user_salon (user_id, salon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add loyalty_points to customer_salon_profiles
ALTER TABLE customer_salon_profiles ADD COLUMN loyalty_points INT DEFAULT 0 AFTER concern_photo_public_id;

-- loyalty_programs table
CREATE TABLE loyalty_programs (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    salon_id VARCHAR(36) NOT NULL,
    program_name VARCHAR(255) DEFAULT 'Loyalty Program',
    is_active TINYINT(1) DEFAULT 0,
    points_per_currency_unit DECIMAL(10, 2) DEFAULT 1.00,
    min_points_redemption INT DEFAULT 100,
    signup_bonus_points INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_salon (salon_id),
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- loyalty_rewards table
CREATE TABLE loyalty_rewards (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    salon_id VARCHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    points_required INT NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- loyalty_transactions table
CREATE TABLE loyalty_transactions (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    salon_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    points INT NOT NULL,
    transaction_type ENUM('earned', 'redeemed', 'adjusted', 'bonus', 'refunded') NOT NULL,
    reference_id VARCHAR(36),
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- coin_transactions table
CREATE TABLE coin_transactions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_type ENUM('earned', 'spent', 'refunded', 'admin_adjustment') NOT NULL,
    description TEXT,
    reference_id VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Treatment Records (Clinical Session Notes)
CREATE TABLE treatment_records (
    id VARCHAR(36) PRIMARY KEY ,
    booking_id VARCHAR(36),
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    service_name_manual VARCHAR(255),
    record_date DATE,
    treatment_details TEXT,
    products_used TEXT,
    skin_reaction TEXT,
    improvement_notes TEXT,
    recommended_next_treatment TEXT,
    post_treatment_instructions TEXT,
    follow_up_reminder_date DATE,
    marketing_notes TEXT,
    before_photo_url TEXT,
    before_photo_public_id VARCHAR(255),
    after_photo_url TEXT,
    after_photo_public_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_salon_records (user_id, salon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT INTO subscription_plans (name, slug, description, price_monthly, price_yearly, max_staff, max_services, max_bookings_per_month, features, sort_order, is_featured)
VALUES
    ('Free Trial', 'free-trial', '14-day trial with basic features', 0, 0, 2, 10, 50, '["Basic booking", "Email notifications", "1 staff member"]', 0, FALSE),
    ('Starter', 'starter', 'Perfect for small salons', 9, 89, 3, 20, 200, '["Unlimited bookings", "Email & SMS notifications", "3 staff members", "Basic reports"]', 1, FALSE),
    ('Professional', 'professional', 'For growing businesses', 29, 289, 10, 50, NULL, '["Everything in Starter", "10 staff members", "Advanced reports", "Customer loyalty", "Inventory management"]', 2, TRUE),
    ('Enterprise', 'enterprise', 'For large salon chains', 89, 889, NULL, NULL, NULL, '["Everything in Professional", "Unlimited staff", "Multi-location", "API access", "Priority support", "Custom branding"]', 3, FALSE);

-- Insert default platform settings
INSERT INTO platform_settings (setting_key, setting_value, description)
VALUES
    ('platform_name', '"NoamSkin"', 'Platform brand name'),
    ('platform_commission', '10', 'Platform commission percentage'),
    ('trial_days', '14', 'Default trial period in days'),
    ('support_email', '"support@noamskin.com"', 'Support email address'),
    ('currency', '"USD"', 'Default currency'),
    ('auto_approve_salons', 'false', 'Automatically approve new salon registrations');