-- Update all salons to have approved status
UPDATE salons 
SET approval_status = 'approved' 
WHERE approval_status IS NULL OR approval_status = '';

-- Verify the update
SELECT id, name, approval_status, is_active 
FROM salons;
