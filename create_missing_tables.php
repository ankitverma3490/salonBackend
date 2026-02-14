<?php
/**
 * Create Missing Tables in Railway Database
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Creating Missing Tables ===\n\n";

    $db->exec("SET FOREIGN_KEY_CHECKS=0");

    // List of missing tables to create
    $tables = [
        'platform_payments' => "CREATE TABLE IF NOT EXISTS platform_payments (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_type ENUM('subscription', 'addon', 'product') NOT NULL,
            payment_method VARCHAR(50),
            transaction_id VARCHAR(255),
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'subscription_plans' => "CREATE TABLE IF NOT EXISTS subscription_plans (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            billing_cycle ENUM('monthly', 'yearly') DEFAULT 'monthly',
            features JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'subscription_addons' => "CREATE TABLE IF NOT EXISTS subscription_addons (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            addon_type VARCHAR(50),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'salon_subscriptions' => "CREATE TABLE IF NOT EXISTS salon_subscriptions (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            plan_id VARCHAR(36),
            status ENUM('trial', 'active', 'past_due', 'cancelled', 'expired') DEFAULT 'trial',
            start_date TIMESTAMP NULL,
            end_date TIMESTAMP NULL,
            auto_renew BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'platform_offers' => "CREATE TABLE IF NOT EXISTS platform_offers (
            id VARCHAR(36) PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
            discount_value DECIMAL(10,2) NOT NULL,
            start_date TIMESTAMP NULL,
            end_date TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'platform_banners' => "CREATE TABLE IF NOT EXISTS platform_banners (
            id VARCHAR(36) PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            image_url TEXT,
            link_url TEXT,
            position INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'platform_settings' => "CREATE TABLE IF NOT EXISTS platform_settings (
            id VARCHAR(36) PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'platform_orders' => "CREATE TABLE IF NOT EXISTS platform_orders (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'newsletter_subscribers' => "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id VARCHAR(36) PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            is_active BOOLEAN DEFAULT TRUE,
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'platform_products' => "CREATE TABLE IF NOT EXISTS platform_products (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(100),
            image_url TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT,
            type VARCHAR(50),
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_is_read (is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'password_resets' => "CREATE TABLE IF NOT EXISTS password_resets (
            id VARCHAR(36) PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'permissions' => "CREATE TABLE IF NOT EXISTS permissions (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            category VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'role_permissions' => "CREATE TABLE IF NOT EXISTS role_permissions (
            id VARCHAR(36) PRIMARY KEY,
            role VARCHAR(50) NOT NULL,
            permission_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
            UNIQUE KEY unique_role_permission (role, permission_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'staff_profiles' => "CREATE TABLE IF NOT EXISTS staff_profiles (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            salon_id VARCHAR(36) NOT NULL,
            position VARCHAR(100),
            bio TEXT,
            specialties TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'staff_services' => "CREATE TABLE IF NOT EXISTS staff_services (
            id VARCHAR(36) PRIMARY KEY,
            staff_id VARCHAR(36) NOT NULL,
            service_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            UNIQUE KEY unique_staff_service (staff_id, service_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'staff_specific_permissions' => "CREATE TABLE IF NOT EXISTS staff_specific_permissions (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            salon_id VARCHAR(36) NOT NULL,
            permission_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_salon_permission (user_id, salon_id, permission_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'booking_reviews' => "CREATE TABLE IF NOT EXISTS booking_reviews (
            id VARCHAR(36) PRIMARY KEY,
            booking_id VARCHAR(36) NOT NULL,
            rating INT NOT NULL,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
            INDEX idx_booking_id (booking_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'messages' => "CREATE TABLE IF NOT EXISTS messages (
            id VARCHAR(36) PRIMARY KEY,
            sender_id VARCHAR(36) NOT NULL,
            recipient_id VARCHAR(36) NOT NULL,
            subject VARCHAR(255),
            message TEXT,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_sender (sender_id),
            INDEX idx_recipient (recipient_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'reminders' => "CREATE TABLE IF NOT EXISTS reminders (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            remind_at TIMESTAMP NOT NULL,
            is_sent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_remind_at (remind_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'contact_enquiries' => "CREATE TABLE IF NOT EXISTS contact_enquiries (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(255),
            message TEXT,
            status ENUM('new', 'in_progress', 'resolved') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'staff_attendance' => "CREATE TABLE IF NOT EXISTS staff_attendance (
            id VARCHAR(36) PRIMARY KEY,
            staff_id VARCHAR(36) NOT NULL,
            salon_id VARCHAR(36) NOT NULL,
            date DATE NOT NULL,
            check_in TIME,
            check_out TIME,
            status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_staff_id (staff_id),
            INDEX idx_date (date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'staff_leaves' => "CREATE TABLE IF NOT EXISTS staff_leaves (
            id VARCHAR(36) PRIMARY KEY,
            staff_id VARCHAR(36) NOT NULL,
            salon_id VARCHAR(36) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            leave_type VARCHAR(50),
            reason TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_staff_id (staff_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'salon_offers' => "CREATE TABLE IF NOT EXISTS salon_offers (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
            discount_value DECIMAL(10,2) NOT NULL,
            start_date TIMESTAMP NULL,
            end_date TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'salon_inventory' => "CREATE TABLE IF NOT EXISTS salon_inventory (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT DEFAULT 0,
            unit VARCHAR(50),
            reorder_level INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'salon_suppliers' => "CREATE TABLE IF NOT EXISTS salon_suppliers (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255),
            phone VARCHAR(20),
            email VARCHAR(255),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'salon_knowledge_base' => "CREATE TABLE IF NOT EXISTS salon_knowledge_base (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            category VARCHAR(100),
            is_published BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'customer_product_purchases' => "CREATE TABLE IF NOT EXISTS customer_product_purchases (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            product_id VARCHAR(36),
            quantity INT DEFAULT 1,
            total_amount DECIMAL(10,2) NOT NULL,
            purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'admin_activity_logs' => "CREATE TABLE IF NOT EXISTS admin_activity_logs (
            id VARCHAR(36) PRIMARY KEY,
            admin_id VARCHAR(36) NOT NULL,
            action VARCHAR(255) NOT NULL,
            entity_type VARCHAR(100),
            entity_id VARCHAR(36),
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_admin_id (admin_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    $created = 0;
    $skipped = 0;
    $errors = 0;

    foreach ($tables as $tableName => $sql) {
        echo "Creating {$tableName}... ";
        try {
            $db->exec($sql);

            // Check if table was created
            $stmt = $db->query("SHOW TABLES LIKE '{$tableName}'");
            if ($stmt->rowCount() > 0) {
                echo "✓\n";
                $created++;
            }
            else {
                echo "⊘ (already exists)\n";
                $skipped++;
            }
        }
        catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⊘ (already exists)\n";
                $skipped++;
            }
            else {
                echo "✗\n";
                echo "  Error: " . substr($e->getMessage(), 0, 100) . "...\n";
                $errors++;
            }
        }
    }

    $db->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "\n=== Summary ===\n";
    echo "Created: {$created}\n";
    echo "Skipped: {$skipped}\n";
    echo "Errors: {$errors}\n\n";

    // Verify total tables
    $stmt = $db->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Total tables in Railway: " . count($allTables) . "\n\n";

    echo "✓ Missing tables created!\n";


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
