-- Reminders and Follow-ups System
CREATE TABLE IF NOT EXISTS reminders (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id VARCHAR(36) NOT NULL, -- The customer who will receive the reminder
    salon_id VARCHAR(36) NOT NULL, -- The salon that scheduled the reminder
    booking_id VARCHAR(36), -- Optional: Link to the service that triggered this
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    status ENUM('pending', 'sent', 'cancelled') DEFAULT 'pending',
    reminder_type ENUM('manual', 'automated_followup') DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
