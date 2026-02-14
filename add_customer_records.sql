-- Customer Management Extensions

-- Customer Health Profiles (Salon specific)
CREATE TABLE IF NOT EXISTS customer_salon_profiles (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    date_of_birth DATE,
    skin_type VARCHAR(100),
    skin_issues TEXT,
    allergy_records TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_salon_profile (user_id, salon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detailed Treatment Records (Linked to bookings)
CREATE TABLE IF NOT EXISTS treatment_records (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    booking_id VARCHAR(36) NOT NULL UNIQUE,
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    treatment_details TEXT,
    products_used TEXT,
    skin_reaction TEXT,
    improvement_notes TEXT,
    recommended_next_treatment TEXT,
    post_treatment_instructions TEXT,
    follow_up_reminder_date DATE,
    marketing_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
