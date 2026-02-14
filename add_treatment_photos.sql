ALTER TABLE treatment_records ADD COLUMN IF NOT EXISTS before_photo_url VARCHAR(255) NULL;
ALTER TABLE treatment_records ADD COLUMN IF NOT EXISTS after_photo_url VARCHAR(255) NULL;
