<?php
/**
 * Create Core Tables and Transfer Data
 * Manually creates the essential tables and transfers data with PHP-generated UUIDs
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 600);

require_once __DIR__ . "/config.php";

// Local database config
$localHost = 'localhost';
$localPort = '3306';
$localDbName = 'salon_booking';
$localUser = 'root';
$localPass = '';

echo "=== Creating Core Tables in Railway ===\n\n";

try {
    // Connect to both databases
    $localDsn = "mysql:host={$localHost};port={$localPort};dbname={$localDbName};charset=utf8mb4";
    $localDb = new PDO($localDsn, $localUser, $localPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $railwayDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $railwayDb = new PDO($railwayDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "✓ Connected to both databases\n\n";

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");

    // Core table definitions - WITHOUT DEFAULT UUID
    $coreTables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(36) PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            email_verified BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'profiles' => "CREATE TABLE IF NOT EXISTS profiles (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL UNIQUE,
            full_name VARCHAR(255),
            phone VARCHAR(20),
            avatar_url TEXT,
            user_type ENUM('customer', 'salon_owner', 'admin') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'platform_admins' => "CREATE TABLE IF NOT EXISTS platform_admins (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'salons' => "CREATE TABLE IF NOT EXISTS salons (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            address TEXT,
            city VARCHAR(100),
            state VARCHAR(100),
            pincode VARCHAR(10),
            phone VARCHAR(20),
            email VARCHAR(255),
            logo_url TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'user_roles' => "CREATE TABLE IF NOT EXISTS user_roles (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            salon_id VARCHAR(36) NOT NULL,
            role ENUM('owner', 'manager', 'staff', 'super_admin') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_salon (user_id, salon_id),
            INDEX idx_user_id (user_id),
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'services' => "CREATE TABLE IF NOT EXISTS services (
            id VARCHAR(36) PRIMARY KEY,
            salon_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            duration INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            INDEX idx_salon_id (salon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'bookings' => "CREATE TABLE IF NOT EXISTS bookings (
            id VARCHAR(36) PRIMARY KEY,
            user_id VARCHAR(36) NOT NULL,
            salon_id VARCHAR(36) NOT NULL,
            service_id VARCHAR(36),
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_salon_id (salon_id),
            INDEX idx_booking_date (booking_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    // Create tables
    echo "Creating core tables...\n";
    foreach ($coreTables as $tableName => $sql) {
        echo "  Creating {$tableName}... ";
        try {
            $railwayDb->exec($sql);
            echo "✓\n";
        }
        catch (PDOException $e) {
            echo "✗ " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // Transfer data
    $tableOrder = ['users', 'profiles', 'platform_admins', 'salons', 'user_roles', 'services', 'bookings'];
    $totalRows = 0;

    echo "Transferring data...\n";
    foreach ($tableOrder as $table) {
        echo "  {$table}: ";

        // Get data from local
        try {
            $stmt = $localDb->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                echo "empty\n";
                continue;
            }

            // Clear Railway table
            $railwayDb->exec("TRUNCATE TABLE `{$table}`");

            // Insert data
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $insertSql = "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})";
            $insertStmt = $railwayDb->prepare($insertSql);

            $inserted = 0;
            foreach ($rows as $row) {
                try {
                    $insertStmt->execute(array_values($row));
                    $inserted++;
                }
                catch (PDOException $e) {
                // Skip duplicates
                }
            }

            echo "{$inserted}/" . count($rows) . " rows ✓\n";
            $totalRows += $inserted;

        }
        catch (PDOException $e) {
            echo "error: " . substr($e->getMessage(), 0, 60) . "...\n";
        }
    }

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "\n=== Complete! ===\n";
    echo "Total rows transferred: {$totalRows}\n\n";

    // Verify
    echo "Verification:\n";
    $stmt = $railwayDb->query("SELECT COUNT(*) FROM users");
    echo "  Users: " . $stmt->fetchColumn() . "\n";

    $stmt = $railwayDb->query("SELECT COUNT(*) FROM profiles WHERE user_type='admin'");
    echo "  Admins: " . $stmt->fetchColumn() . "\n";

    $stmt = $railwayDb->query("SELECT COUNT(*) FROM platform_admins");
    echo "  Platform Admins: " . $stmt->fetchColumn() . "\n";

    $stmt = $railwayDb->query("SELECT COUNT(*) FROM salons");
    echo "  Salons: " . $stmt->fetchColumn() . "\n";

    echo "\n✓ Core tables ready! Try logging in now.\n";


}
catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
