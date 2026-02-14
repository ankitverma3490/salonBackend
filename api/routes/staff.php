<?php
// Staff routes (NEW TXT)

// GET /api/staff?salon_id=xxx - Get salon staff members
if ($method === 'GET' && empty($uriParts[1])) {
    $userData = protectRoute(['owner', 'manager', 'staff'], 'view_staff');
    $salonId = $_GET['salon_id'] ?? null;

    if (!$salonId) {
        sendResponse(['error' => 'salon_id is required'], 400);
    }

    $stmt = $db->prepare("
        SELECT s.*, ur.role,
               (SELECT GROUP_CONCAT(service_id) FROM staff_services ss WHERE ss.staff_id = s.id) as assigned_services
        FROM staff_profiles s
        LEFT JOIN user_roles ur ON s.user_id = ur.user_id AND s.salon_id = ur.salon_id
        WHERE s.salon_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$salonId]);
    $staff = $stmt->fetchAll();

    // Parse assigned_services from GROUP_CONCAT
    foreach ($staff as &$member) {
        $assigned = $member['assigned_services'] ?? '';
        $member['assigned_services'] = $assigned ? explode(',', $assigned) : [];
    }

    sendResponse(['staff' => $staff]);
}

// GET /api/staff/available-specialists?salon_id=xxx&service_id=yyy&date=zzz&time=ttt - Get available staff for service
if ($method === 'GET' && ($uriParts[1] ?? '') === 'available-specialists') {
    $salonId = $_GET['salon_id'] ?? null;
    $serviceId = $_GET['service_id'] ?? null; // Optional
    $date = $_GET['date'] ?? null;
    $time = $_GET['time'] ?? null;

    $query = "
        SELECT s.*, ur.role
        FROM staff_profiles s
        LEFT JOIN user_roles ur ON s.user_id = ur.user_id AND s.salon_id = ur.salon_id
        WHERE s.salon_id = ? AND s.is_active = 1
    ";
    $params = [$salonId];

    if ($serviceId) {
        $query .= " AND s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?)";
        $params[] = $serviceId;
    }

    if ($date && $time) {
        $query .= " AND s.id NOT IN (
            SELECT staff_id FROM bookings 
            WHERE booking_date = ? AND booking_time = ? AND status != 'cancelled' AND staff_id IS NOT NULL
        )";
        $params[] = $date;
        $params[] = $time;

        $query .= " AND s.id NOT IN (
            SELECT staff_id FROM staff_leaves 
            WHERE ? BETWEEN start_date AND end_date AND status = 'approved'
        )";
        $params[] = $date;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $staff = $stmt->fetchAll();

    sendResponse(['specialists' => $staff]);
}

// GET /api/staff/me - Get current user's staff profile or dashboard data
if ($method === 'GET' && ($uriParts[1] ?? '') === 'me') {
    $userData = protectRoute(['staff', 'manager', 'owner']);
    $salonId = $_GET['salon_id'] ?? null;

    if (!$salonId) {
        sendResponse(['error' => 'salon_id is required'], 400);
    }

    $stmt = $db->prepare("
        SELECT s.*, ur.role 
        FROM staff_profiles s
        LEFT JOIN user_roles ur ON s.user_id = ur.user_id AND s.salon_id = ur.salon_id
        WHERE s.user_id = ? AND s.salon_id = ?
    ");
    $stmt->execute([$userData['user_id'], $salonId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff profile not found'], 404);

    // If sub-route is dashboard-data
    if (($uriParts[2] ?? '') === 'dashboard-data') {
        $staffId = $staff['id'];

        // 1. Get Active Attendance Session (any start date)
        $stmt = $db->prepare("
            SELECT * FROM staff_attendance 
            WHERE staff_id = ? AND check_out IS NULL 
            ORDER BY check_in DESC LIMIT 1
        ");
        $stmt->execute([$staffId]);
        $attendance = $stmt->fetch();
        
        if ($attendance) {
            $attendance['check_in_raw'] = $attendance['check_in'];
            $attendance['check_in'] = date('c', strtotime($attendance['check_in']));
        }

        // 2. Get Today's Bookings
        $stmt = $db->prepare("
            SELECT b.*, s.name as service_name
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            WHERE b.staff_id = ? AND b.booking_date = CURDATE() AND b.status != 'cancelled'
            ORDER BY b.booking_time ASC
        ");
        $stmt->execute([$staffId]);
        $todayBookings = $stmt->fetchAll() ?: [];

        // 3. Get Basic Stats for Header (Current Month)
        $stmt = $db->prepare("
            SELECT 
                COUNT(id) as total_customers,
                COALESCE(SUM(COALESCE(price_paid, 0)), 0) as gross_revenue
            FROM bookings 
            WHERE staff_id = ? AND status = 'completed' AND MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE())
        ");
        $stmt->execute([$staffId]);
        $monthStats = $stmt->fetch();
        
        // 3b. Calculate Uptime (Total Hours this month)
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, check_in, IFNULL(check_out, CURRENT_TIMESTAMP))), 0) as total_minutes
            FROM staff_attendance
            WHERE staff_id = ? AND MONTH(check_in) = MONTH(CURDATE()) AND YEAR(check_in) = YEAR(CURDATE())
        ");
        $stmt->execute([$staffId]);
        $attendanceStats = $stmt->fetch();

        $commissionRate = (float)($staff['commission_percentage'] ?: 30);
        $earnings = (float)($monthStats['gross_revenue'] ?? 0) * ($commissionRate / 100);

        // 4. Unread Messages Count
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM messages 
            WHERE receiver_id = ? AND is_read = 0 AND salon_id = ?
        ");
        $stmt->execute([$userData['user_id'], $salonId]);
        $unreadMessagesCount = (int)$stmt->fetchColumn();

        sendResponse([
            'staff' => $staff,
            'attendance' => $attendance ?: null,
            'today_bookings' => $todayBookings,
            'unread_messages' => $unreadMessagesCount,
            'stats' => [
                'revenue' => (float)($monthStats['gross_revenue'] ?? 0),
                'earnings' => $earnings,
                'commission_rate' => $commissionRate,
                'total_customers' => (int)($monthStats['total_customers'] ?? 0),
                'total_hours' => round(($attendanceStats['total_minutes'] ?? 0) / 60, 1)
            ]
        ]);
    }

    sendResponse(['staff' => $staff]);
}

// GET /api/staff/:id - Get staff member by ID
if ($method === 'GET' && !empty($uriParts[1]) && empty($uriParts[2])) {
    $staffId = $uriParts[1];
    $userData = protectRoute(['owner', 'manager', 'staff']);

    $stmt = $db->prepare("
        SELECT s.*, ur.role,
               (SELECT GROUP_CONCAT(service_id) FROM staff_services ss WHERE ss.staff_id = s.id) as assigned_services
        FROM staff_profiles s
        LEFT JOIN user_roles ur ON s.user_id = ur.user_id AND s.salon_id = ur.salon_id
        WHERE s.id = ?
    ");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if ($staff) {
        $isSelf = ($staff['user_id'] === $userData['user_id']);
        $managementRoles = ['owner', 'manager', 'super_admin', 'admin'];
        $isManagement = in_array($userData['role'], $managementRoles);

        if (!$isSelf && !$isManagement) {
            sendResponse(['error' => 'Forbidden - You can only view your own profile'], 403);
        }
    }

    if (!$staff)
        sendResponse(['error' => 'Staff member not found'], 404);

    $assigned = $staff['assigned_services'] ?? '';
    $staff['assigned_services'] = $assigned ? explode(',', $assigned) : [];

    sendResponse(['staff' => $staff]);
}

// POST /api/staff - Create new staff member
if ($method === 'POST' && empty($uriParts[1])) {
    $userData = protectRoute(['owner', 'manager'], 'manage_staff');
    $data = getRequestBody();

    if (empty($data['salon_id']) || empty($data['display_name'])) {
        sendResponse(['error' => 'Missing required fields: salon_id, display_name'], 400);
    }

    if (!$membershipService->canAddStaff($data['salon_id'])) {
        sendResponse(['error' => 'Plan limit reached. Please upgrade your subscription.'], 403);
    }

    $db->beginTransaction();

    try {
        $staffUserId = null;

        // If email is provided, handle user creation or linking
        if (!empty($data['email'])) {
            // Check if user already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $staffUserId = $existingUser['id'];
            } else if (!empty($data['password'])) {
                // Create new user
                $staffUserId = Auth::generateUuid();
                $stmt = $db->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$staffUserId, $data['email'], Auth::hashPassword($data['password'])]);

                // Create profile
                $profileId = Auth::generateUuid();
                $stmt = $db->prepare("INSERT INTO profiles (id, user_id, full_name, user_type) VALUES (?, ?, ?, 'customer')");
                $stmt->execute([$profileId, $staffUserId, $data['display_name']]);
            }
        }

        $id = Auth::generateUuid();
        $stmt = $db->prepare("
            INSERT INTO staff_profiles 
            (id, user_id, salon_id, display_name, email, phone, specializations, commission_percentage, is_active, created_by_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $specializations = isset($data['specializations']) ? (is_array($data['specializations']) ? json_encode($data['specializations']) : $data['specializations']) : '[]';

        $stmt->execute([
            $id,
            $staffUserId,
            $data['salon_id'],
            $data['display_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $specializations,
            $data['commission_percentage'] ?? 0,
            isset($data['is_active']) ? (int) $data['is_active'] : 1,
            $userData['user_id'] // Strict ownership
        ]);

        // Create user_role for the salon
        if ($staffUserId) {
            $role = $data['role'] ?? 'staff';
            $roleId = Auth::generateUuid();
            $stmt = $db->prepare("
                INSERT INTO user_roles (id, user_id, salon_id, role)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE role = VALUES(role)
            ");
            $stmt->execute([$roleId, $staffUserId, $data['salon_id'], $role]);
        }

        $db->commit();

        $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE id = ?");
        $stmt->execute([$id]);
        $newStaff = $stmt->fetch();

        sendResponse(['staff' => $newStaff], 201);
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(['error' => 'Failed to create staff node: ' . $e->getMessage()], 500);
    }
}

// PUT /api/staff/leaves/:leave_id - Update leave status (approve/revoke)
if ($method === 'PUT' && ($uriParts[1] ?? '') === 'leaves' && !empty($uriParts[2])) {
    $leaveId = $uriParts[2];
    $userData = protectRoute(['owner', 'manager']);
    $data = getRequestBody();

    if (empty($data['status'])) {
        sendResponse(['error' => 'status is required'], 400);
    }

    $stmt = $db->prepare("SELECT salon_id FROM staff_leaves WHERE id = ?");
    $stmt->execute([$leaveId]);
    $leave = $stmt->fetch();

    if (!$leave) {
        sendResponse(['error' => 'Leave record not found'], 404);
    }

    // Verify user belongs to this salon
    $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $leave['salon_id']]);
    $userRole = $stmt->fetch();

    if (!$userRole || !in_array($userRole['role'], ['owner', 'manager'])) {
        sendResponse(['error' => 'Forbidden - You do not have management permissions for this salon'], 403);
    }

    $stmt = $db->prepare("UPDATE staff_leaves SET status = ? WHERE id = ?");
    $stmt->execute([$data['status'], $leaveId]);

    sendResponse(['message' => 'Leave status updated successfully']);
}

// PUT /api/staff/:id - Update staff member
if ($method === 'PUT' && !empty($uriParts[1]) && $uriParts[1] !== 'leaves') {
    $staffId = $uriParts[1];

    // Get staff record to find salon_id
    $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff member not found'], 404);

    $userData = protectRoute(['owner', 'manager'], 'manage_staff', $staff['salon_id']);

    $data = getRequestBody();

    // Check permissions
    $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $staff['salon_id']]);
    $userRole = $stmt->fetch();

    if (!$userRole || ($userRole['role'] !== 'owner' && $userRole['role'] !== 'manager')) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $fields = [];
    $params = [];

    $allowedFields = ['display_name', 'email', 'phone', 'specializations', 'commission_percentage', 'is_active', 'avatar_url'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $params[] = ($field === 'specializations' && is_array($data[$field])) ? json_encode($data[$field]) : $data[$field];
        }
    }

    if (!empty($fields)) {
        $params[] = $staffId;
        $sql = "UPDATE staff_profiles SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    // Update role if provided
    if (isset($data['role']) && !empty($staff['user_id'])) {
        $stmt = $db->prepare("UPDATE user_roles SET role = ? WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$data['role'], $staff['user_id'], $staff['salon_id']]);
    }

    // Update password or create user if provided
    if (!empty($data['password'])) {
        $passwordHash = Auth::hashPassword($data['password']);

        if (!empty($staff['user_id'])) {
            // Update existing user
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$passwordHash, $staff['user_id']]);
        } else if (!empty($staff['email'])) {
            // Check if user with this email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$staff['email']]);
            $existingUser = $stmt->fetch();

            $newUserId = $existingUser ? $existingUser['id'] : Auth::generateUuid();

            if (!$existingUser) {
                // Create user
                $stmt = $db->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$newUserId, $staff['email'], $passwordHash]);

                // Create profile
                $profileId = Auth::generateUuid();
                $stmt = $db->prepare("INSERT INTO profiles (id, user_id, full_name, user_type) VALUES (?, ?, ?, 'customer')");
                $stmt->execute([$profileId, $newUserId, $staff['display_name']]);
            } else {
                // Update password for existing user if they are being linked as staff
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$passwordHash, $newUserId]);
            }

            // Link to salon
            $roleId = Auth::generateUuid();
            $stmt = $db->prepare("INSERT INTO user_roles (id, user_id, salon_id, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE role = VALUES(role)");
            $stmt->execute([$roleId, $newUserId, $staff['salon_id'], $data['role'] ?? 'staff']);

            // Update staff profile with the new user_id
            $stmt = $db->prepare("UPDATE staff_profiles SET user_id = ? WHERE id = ?");
            $stmt->execute([$newUserId, $staffId]);
        }
    }

    $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $updatedStaff = $stmt->fetch();

    sendResponse(['staff' => $updatedStaff]);
}

// DELETE /api/staff/:id - Delete staff member
if ($method === 'DELETE' && !empty($uriParts[1])) {
    $staffId = $uriParts[1];

    // Get staff record to find salon_id
    $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff member not found'], 404);

    $userData = protectRoute(['owner'], 'manage_staff', $staff['salon_id']);

    // Check permissions
    $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $staff['salon_id']]);
    $userRole = $stmt->fetch();

    if (!$userRole || ($userRole['role'] !== 'owner' && $userRole['role'] !== 'manager')) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $stmt = $db->prepare("DELETE FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);

    sendResponse(['message' => 'Staff member deleted']);
}

// GET /api/staff/:id/profile-stats - Get detailed analytics for a staff member
if ($method === 'GET' && !empty($uriParts[1]) && ($uriParts[2] ?? '') === 'profile-stats') {
    $staffId = $uriParts[1];
    $userData = protectRoute(['owner', 'manager', 'staff']);

    // 1. Get Staff Info & Check Permissions
    $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff record not found'], 404);

    // Permission Check: Allow if self OR if owner/manager
    $isSelf = ($staff['user_id'] === $userData['user_id']);
    $managementRoles = ['owner', 'manager', 'super_admin', 'admin'];
    $isManagement = in_array($userData['role'], $managementRoles);

    if (!$isSelf && !$isManagement) {
        sendResponse(['error' => 'Forbidden - You can only view your own stats'], 403);
    }

    $salonId = $staff['salon_id'];
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');

    // 2. Calculate Earnings & Customers Handled
    $stmt = $db->prepare("
        SELECT 
            COUNT(b.id) as total_customers,
            COALESCE(SUM(COALESCE(b.price_paid, s.price)), 0) as gross_revenue,
            COALESCE(MAX(st.commission_percentage), 0) as commission_percentage
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN staff_profiles st ON b.staff_id = st.id
        WHERE b.staff_id = ? 
        AND b.status = 'completed'
        AND MONTH(b.booking_date) = ?
        AND YEAR(b.booking_date) = ?
    ");
    $stmt->execute([$staffId, $month, $year]);
    $monthStats = $stmt->fetch();
    
    if (!$monthStats) {
        $monthStats = ['total_customers' => 0, 'gross_revenue' => 0, 'commission_percentage' => 0];
    }

    $effectiveCommission = (int)$monthStats['commission_percentage'] > 0 ? (int)$monthStats['commission_percentage'] : 30;
    $totalEarnings = (float)$monthStats['gross_revenue'] * ($effectiveCommission / 100);

    // 3. Attendance Summary (Monthly)
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT DATE(check_in)) as days_worked,
            SUM(TIMESTAMPDIFF(MINUTE, check_in, IFNULL(check_out, CURRENT_TIMESTAMP))) as total_minutes
        FROM staff_attendance
        WHERE staff_id = ?
        AND MONTH(check_in) = ?
        AND YEAR(check_in) = ?
    ");
    $stmt->execute([$staffId, $month, $year]);
    $attendanceStats = $stmt->fetch();
    if (!$attendanceStats)
        $attendanceStats = ['days_worked' => 0, 'total_minutes' => 0];

    // 4. Leave Summary (Monthly)
    $stmt = $db->prepare("
        SELECT COUNT(*) as leave_days
        FROM staff_leaves
        WHERE staff_id = ?
        AND status = 'approved'
        AND ((MONTH(start_date) = ? AND YEAR(start_date) = ?) OR (MONTH(end_date) = ? AND YEAR(end_date) = ?))
    ");
    $stmt->execute([$staffId, $month, $year, $month, $year]);
    $leaveStats = $stmt->fetch();
    if (!$leaveStats)
        $leaveStats = ['leave_days' => 0];

    // 5. Recent Customers (Filtered by selected month/year)
    $stmt = $db->prepare("
        SELECT b.*, s.name as service_name, s.price as service_price, 
               COALESCE(b.price_paid, s.price) as effective_price,
               u.email, p.full_name
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN users u ON b.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE b.staff_id = ?
        AND MONTH(b.booking_date) = ?
        AND YEAR(b.booking_date) = ?
        ORDER BY b.booking_date DESC, b.booking_time DESC
        LIMIT 100
    ");
    $stmt->execute([$staffId, $month, $year]);
    $recentCustomers = $stmt->fetchAll();

    // 6. Daily Revenue for Chart
    $stmt = $db->prepare("
        SELECT DAY(b.booking_date) as day, SUM(COALESCE(b.price_paid, s.price)) as daily_revenue
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        WHERE b.staff_id = ?
        AND b.status = 'completed'
        AND MONTH(b.booking_date) = ?
        AND YEAR(b.booking_date) = ?
        GROUP BY DAY(b.booking_date)
        ORDER BY day ASC
    ");
    $stmt->execute([$staffId, $month, $year]);
    $dailyRevenue = $stmt->fetchAll();

    // 7. Attendance Logs (Full list for the month)
    $stmt = $db->prepare("
        SELECT DATE(check_in) as date, check_in, check_out
        FROM staff_attendance
        WHERE staff_id = ?
        AND MONTH(check_in) = ?
        AND YEAR(check_in) = ?
        ORDER BY check_in ASC
    ");
    $stmt->execute([$staffId, $month, $year]);
    $attendanceLogs = $stmt->fetchAll();

    sendResponse([
        'stats' => [
            'customers' => (int) $monthStats['total_customers'],
            'revenue' => (float) $monthStats['gross_revenue'],
            'earnings' => (float) $totalEarnings,
            'commission_rate' => (float) $effectiveCommission,
            'days_worked' => (int) $attendanceStats['days_worked'],
            'total_hours' => round($attendanceStats['total_minutes'] / 60, 1),
            'leave_days' => (int) $leaveStats['leave_days']
        ],
        'recent_customers' => $recentCustomers,
        'daily_revenue' => $dailyRevenue,
        'attendance_logs' => $attendanceLogs
    ]);
}

// GET /api/staff/:id/leaves - Get leave records
if ($method === 'GET' && !empty($uriParts[1]) && ($uriParts[2] ?? '') === 'leaves') {
    $staffId = $uriParts[1];
    $userData = protectRoute(['owner', 'manager', 'staff']);

    // Check ownership/permissions manually to allow self-view
    $stmt = $db->prepare("SELECT user_id, salon_id FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $targetStaff = $stmt->fetch();

    if ($targetStaff) {
        $isSelf = ($targetStaff['user_id'] === $userData['user_id']);
        $isManagement = in_array($userData['role'], ['owner', 'manager', 'super_admin', 'admin']);

        if (!$isSelf && !$isManagement) {
            sendResponse(['error' => 'Forbidden'], 403);
        }
    }

    $stmt = $db->prepare("SELECT * FROM staff_leaves WHERE staff_id = ? ORDER BY start_date DESC");
    $stmt->execute([$staffId]);
    $leaves = $stmt->fetchAll();

    sendResponse(['leaves' => $leaves]);
}

// POST /api/staff/:id/leaves - Create leave request
if ($method === 'POST' && !empty($uriParts[1]) && ($uriParts[2] ?? '') === 'leaves') {
    $staffId = $uriParts[1];
    $userData = protectRoute(['owner', 'manager', 'staff']);

    // Verify target staff exists
    $stmt = $db->prepare("SELECT user_id, salon_id FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $targetStaff = $stmt->fetch();

    if (!$targetStaff) {
        sendResponse(['error' => 'Staff profile not found'], 404);
    }

    // Permission Check: Allow if self OR if owner/manager
    $isSelf = ($targetStaff['user_id'] === $userData['user_id']);
    $isManagement = in_array($userData['role'], ['owner', 'manager', 'super_admin', 'admin']);

    if (!$isSelf && !$isManagement) {
        sendResponse(['error' => 'Forbidden - Insufficient permissions'], 403);
    }

    $data = getRequestBody();
    $id = Auth::generateUuid();

    $stmt = $db->prepare("
        INSERT INTO staff_leaves (id, staff_id, salon_id, start_date, end_date, leave_type, reason, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $id,
        $staffId,
        $data['salon_id'],
        $data['start_date'],
        $data['end_date'],
        $data['leave_type'] ?? 'casual',
        $data['reason'] ?? '',
        $isManagement ? 'approved' : 'pending'
    ]);

    sendResponse(['message' => 'Leave request logged', 'id' => $id]);
}

// POST /api/staff/attendance/check-in
if ($method === 'POST' && in_array('attendance', $uriParts) && in_array('check-in', $uriParts)) {
    $userData = protectRoute(['staff', 'manager', 'owner'], 'track_attendance');
    $data = getRequestBody();

    $salonId = $data['salon_id'] ?? null;
    if (!$salonId)
        sendResponse(['error' => 'salon_id required'], 400);

    // Get staff profile for this user in this salon
    $stmt = $db->prepare("SELECT id FROM staff_profiles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $salonId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff profile not found for this node'], 404);

    // Check if already checked in today
    $stmt = $db->prepare("SELECT id FROM staff_attendance WHERE staff_id = ? AND DATE(check_in) = CURDATE() AND check_out IS NULL");
    $stmt->execute([$staff['id']]);
    if ($stmt->fetch())
        sendResponse(['error' => 'Already checked in for current cycle'], 400);

    $id = Auth::generateUuid();
    $stmt = $db->prepare("INSERT INTO staff_attendance (id, staff_id, salon_id, check_in) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    $stmt->execute([$id, $staff['id'], $salonId]);

    sendResponse(['message' => 'Node check-in successful', 'attendance_id' => $id]);
}

// POST /api/staff/attendance/check-out
if ($method === 'POST' && in_array('attendance', $uriParts) && in_array('check-out', $uriParts)) {
    $userData = protectRoute(['staff', 'manager', 'owner'], 'track_attendance');
    $data = getRequestBody();

    $salonId = $data['salon_id'] ?? null;
    if (!$salonId)
        sendResponse(['error' => 'salon_id required'], 400);

    // Get staff profile
    $stmt = $db->prepare("SELECT id FROM staff_profiles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $salonId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff profile not found'], 404);

    // Update ongoing attendance record
    $stmt = $db->prepare("UPDATE staff_attendance SET check_out = CURRENT_TIMESTAMP WHERE staff_id = ? AND check_out IS NULL ORDER BY check_in DESC LIMIT 1");
    $stmt->execute([$staff['id']]);

    if ($stmt->rowCount() === 0)
        sendResponse(['error' => 'No active check-in found'], 400);

    sendResponse(['message' => 'Node check-out successful']);
}

// GET /api/staff/attendance/:staff_id - Get attendance history
if ($method === 'GET' && in_array('attendance', $uriParts) && !empty($uriParts[1])) {
    $staffId = $uriParts[2];
    $userData = protectRoute(['staff', 'manager', 'owner'], 'view_attendance');

    $stmt = $db->prepare("SELECT * FROM staff_attendance WHERE staff_id = ? ORDER BY check_in DESC");
    $stmt->execute([$staffId]);
    $history = $stmt->fetchAll();

    sendResponse(['attendance' => $history]);
}

// POST /api/staff/:id/services - Assign services to staff
if ($method === 'POST' && !empty($uriParts[1]) && ($uriParts[2] ?? '') === 'services') {
    $staffId = $uriParts[1];

    // Get staff record to find salon_id
    $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE id = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff)
        sendResponse(['error' => 'Staff record not found'], 404);

    protectRoute(['owner', 'manager'], 'manage_staff', $staff['salon_id']);

    $data = getRequestBody();
    $serviceIds = $data['service_ids'] ?? [];

    if (!is_array($serviceIds)) {
        sendResponse(['error' => 'service_ids must be an array'], 400);
    }

    $db->beginTransaction();
    try {
        // Clear existing assignments
        $db->prepare("DELETE FROM staff_services WHERE staff_id = ?")->execute([$staffId]);

        // Add new assignments
        if (!empty($serviceIds)) {
            $stmt = $db->prepare("INSERT INTO staff_services (id, staff_id, service_id) VALUES (?, ?, ?)");
            foreach ($serviceIds as $serviceId) {
                $stmt->execute([Auth::generateUuid(), $staffId, $serviceId]);
            }
        }

        $db->commit();
        sendResponse(['message' => 'Staff services synchronized successfully']);
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(['error' => 'Failed to sync staff services: ' . $e->getMessage()], 500);
    }
}

sendResponse(['error' => 'Staff route not found'], 404);
