-- Migration to allow nullable service_id for "Decide Later" bookings
ALTER TABLE bookings MODIFY service_id VARCHAR(36) NULL;

-- Ensure foreign key allows NULL (MySQL does this by default if COLLATE/ENGINE are compatible, but good to be explicit about the intent)
-- The original table definition had: FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
