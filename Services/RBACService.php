<?php

class RBACService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Check if a user has a specific permission in a specific salon
     */
    public function hasPermission($userId, $salonId, $permissionName)
    {
        // 1. Get user role in the salon
        $stmt = $this->db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userId, $salonId]);
        $userRole = $stmt->fetch();

        if (!$userRole) {
            return false;
        }

        $role = $userRole['role'];

        // 2. Check if the role has the permission
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role = ? AND p.name = ?
        ");
        $stmt->execute([$role, $permissionName]);
        $hasRolePermission = $stmt->fetchColumn() > 0;

        // 3. Check for specific overrides if staff
        if ($role === 'staff') {
            $stmt = $this->db->prepare("
                SELECT is_allowed 
                FROM staff_specific_permissions ssp
                JOIN staff_profiles sp ON ssp.staff_id = sp.id
                JOIN permissions p ON ssp.permission_id = p.id
                WHERE sp.user_id = ? AND sp.salon_id = ? AND p.name = ?
            ");
            $stmt->execute([$userId, $salonId, $permissionName]);
            $override = $stmt->fetch();

            if ($override !== false) {
                return (bool) $override['is_allowed'];
            }
        }

        return $hasRolePermission;
    }

    /**
     * Get all permissions for a user in a salon
     */
    public function getUserPermissions($userId, $salonId)
    {
        $stmt = $this->db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userId, $salonId]);
        $userRole = $stmt->fetch();

        if (!$userRole)
            return [];

        $role = $userRole['role'];

        // Get role permissions
        $stmt = $this->db->prepare("
            SELECT p.name 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role = ?
        ");
        $stmt->execute([$role]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // TODO: Apply staff-specific overrides if needed

        return $permissions;
    }
}
