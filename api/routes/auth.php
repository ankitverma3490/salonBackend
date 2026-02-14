<?php
// Authentication routes

if ($uriParts[1] === 'signup') {
    if ($method !== 'POST') {
        sendResponse(['error' => 'Method not allowed'], 405);
    }

    $data = getRequestBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $fullName = $data['full_name'] ?? '';
    $phone = $data['phone'] ?? '';
    $userType = $data['user_type'] ?? 'customer';

    // Salon specific fields
    $salonName = $data['salon_name'] ?? '';
    $salonSlug = $data['salon_slug'] ?? '';

    if (empty($email) || empty($password)) {
        sendResponse(['error' => 'Email and password are required'], 400);
    }

    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendResponse(['error' => 'User already exists'], 409);
    }

    // Create user
    error_log("[Signup Trace] 1. Starting user signup process for: $email");
    $userId = Auth::generateUuid();
    $passwordHash = Auth::hashPassword($password);

    error_log("[Signup Trace] 2. Beginning database transaction");
    $db->beginTransaction();
    try {
        error_log("[Signup Trace] 3. Inserting user record");
        $stmt = $db->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $email, $passwordHash]);

        // Create profile
        error_log("[Signup Trace] 4. Creating user profile");
        $profileId = Auth::generateUuid();
        $stmt = $db->prepare("INSERT INTO profiles (id, user_id, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$profileId, $userId, $fullName, $phone, $userType]);

        // --- Coin System: Signup Bonus ---
        error_log("[Signup Trace] 5. Loading CoinService");
        require_once __DIR__ . '/../../Services/CoinService.php';
        $coinService = new CoinService($db);
        
        error_log("[Signup Trace] 6. Fetching signup bonus setting");
        $stmtBonus = $db->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = 'coin_signup_bonus'");
        $stmtBonus->execute();
        $signupBonus = (float) ($stmtBonus->fetchColumn() ?: 0);

        if ($signupBonus > 0) {
            error_log("[Signup Trace] 7. Awarding signup bonus: $signupBonus coins");
            $coinService->adjustBalance(
                $userId,
                $signupBonus,
                'earned',
                'Clinical Account Initialization Reward'
            );
        }

        // If salon owner, create salon and link
        if ($userType === 'salon_owner' && !empty($salonName)) {
            error_log("[Signup Trace] 8. Initializing salon: $salonName");
            $salonId = Auth::generateUuid();
            if (empty($salonSlug)) {
                $salonSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $salonName)));
                $salonSlug .= '-' . substr(Auth::generateUuid(), 0, 4);
            }

            $stmt = $db->prepare("INSERT INTO salons (id, name, slug, email, phone, approval_status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$salonId, $salonName, $salonSlug, $email, $phone]);

            // Link user to salon as owner
            error_log("[Signup Trace] 9. Linking user to salon as owner");
            $roleId = Auth::generateUuid();
            $stmt = $db->prepare("INSERT INTO user_roles (id, user_id, salon_id, role) VALUES (?, ?, ?, 'owner')");
            $stmt->execute([$roleId, $userId, $salonId]);
        }

        error_log("[Signup Trace] 10. Committing transaction");
        $db->commit();

        // 📢 Dispatch Welcome Notification & Email
        if (isset($notifService)) {
            error_log("[Signup Trace] 11. Dispatching welcome notification");
            $welcomeMsg = "Welcome to the elite grooming network, {$fullName}! Your account has been successfully initialized.";
            if ($userType === 'salon_owner') {
                $welcomeMsg = "Your partner node '{$salonName}' has been initialized. Please wait for Super Admin approval to activate your dashboard.";
            }
            $notifService->notifyUser($userId, "Account Activated", $welcomeMsg, 'success', null, true);
        }

        error_log("[Signup Trace] 12. Generating auth token");
        $token = Auth::generateToken($userId, $email, $userType);
        
        error_log("[Signup Trace] 13. Signup successful for: $email");
        sendResponse([
            'user' => ['id' => $userId, 'email' => $email, 'full_name' => $fullName, 'user_type' => $userType],
            'token' => $token
        ], 201);
    } catch (Exception $e) {
        error_log("[Signup Trace ERROR] " . $e->getMessage());
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        sendResponse(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
    }
}

if ($uriParts[1] === 'login') {
    if ($method !== 'POST') {
        sendResponse(['error' => 'Method not allowed'], 405);
    }

    $data = getRequestBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendResponse(['error' => 'Email and password are required'], 400);
    }

    $stmt = $db->prepare("
        SELECT u.id, u.email, u.password_hash, p.full_name, p.user_type
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !Auth::verifyPassword($password, $user['password_hash'])) {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }

    // For salon owners, check salon approval status
    if ($user['user_type'] === 'salon_owner') {
        $stmt = $db->prepare("
            SELECT s.approval_status 
            FROM salons s
            JOIN user_roles ur ON s.id = ur.salon_id
            WHERE ur.user_id = ? AND ur.role = 'owner'
        ");
        $stmt->execute([$user['id']]);
        $salon = $stmt->fetch();

        if ($salon && $salon['approval_status'] === 'pending') {
            sendResponse([
                'error' => 'WAITING_APPROVAL',
                'message' => 'Your salon account is waiting for approval by Super Admin.'
            ], 403);
        }

        if ($salon && $salon['approval_status'] === 'rejected') {
            sendResponse([
                'error' => 'REJECTED',
                'message' => 'Your salon account registration has been rejected.'
            ], 403);
        }
    }

    // Get specific role and salon context
    $salonId = null;
    $role = $user['user_type'];

    $stmt = $db->prepare("SELECT salon_id, role FROM user_roles WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $roleInfo = $stmt->fetch();

    if ($roleInfo && $user['user_type'] !== 'admin') {
        $salonId = $roleInfo['salon_id'];
        $role = $roleInfo['role'];

        // If it's a staff member or manager, check if their profile is active
        if ($role === 'staff' || $role === 'manager') {
            $stmt = $db->prepare("SELECT is_active FROM staff_profiles WHERE user_id = ? AND salon_id = ?");
            $stmt->execute([$user['id'], $salonId]);
            $staffProfile = $stmt->fetch();

            if ($staffProfile && !$staffProfile['is_active']) {
                sendResponse(['error' => 'ACCOUNT_SUSPENDED', 'message' => 'Your staff access has been suspended by the salon owner.'], 403);
            }
        }
    }

    $token = Auth::generateToken($user['id'], $user['email'], $role);
    sendResponse([
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'user_type' => $user['user_type'],
            'salon_role' => $role,
            'salon_id' => $salonId
        ],
        'token' => $token
    ]);
}

if ($uriParts[1] === 'me') {
    if ($method !== 'GET') {
        sendResponse(['error' => 'Method not allowed'], 405);
    }

    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $stmt = $db->prepare("
        SELECT u.id, u.email, p.full_name, p.phone, p.avatar_url, p.user_type
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userData['user_id']]);
    $user = $stmt->fetch();

    // Get salon role
    $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userData['user_id']]);
    $roleInfo = $stmt->fetch();
    $user['salon_role'] = $roleInfo ? $roleInfo['role'] : null;

    sendResponse(['user' => $user]);
}

if ($uriParts[1] === 'logout') {
    sendResponse(['message' => 'Logged out successfully']);
}

if ($uriParts[1] === 'forgot-password') {
    if ($method !== 'POST') {
        sendResponse(['error' => 'Method not allowed'], 405);
    }

    $data = getRequestBody();
    $email = $data['email'] ?? '';

    if (empty($email)) {
        sendResponse(['error' => 'Email is required'], 400);
    }

    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // To prevent account enumeration, we still return success but don't do anything
        sendResponse(['message' => 'If this email exists, a reset link has been sent.']);
    }

    // Generate token
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Insert into password_resets
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires]);

    // Send notification/email
    $resetLink = "http://localhost:5173/reset-password?token=" . $token;

    // Call Real Email Service
    $emailBody = "
        <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <h2 style='color: #4f46e5;'>Password Reset Request</h2>
            <p>You requested a password reset for your Salon account.</p>
            <p>Click the button below to reset your password. This link expires in 1 hour.</p>
            <a href='{$resetLink}' style='display: inline-block; padding: 12px 24px; bg-color: #4f46e5; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
            <p style='margin-top: 20px; font-size: 12px; color: #666;'>If you didn't request this, you can safely ignore this email.</p>
            <p style='font-size: 12px; color: #666;'>Link: {$resetLink}</p>
        </div>
    ";

    $mailResult = EmailService::send($email, "Reset Your Password", $emailBody);

    if (isset($notifService)) {
        $notifService->notifyUser($user['id'], "Password Reset", "A password reset email has been sent to " . $email, 'info');
    }

    sendResponse([
        'success' => $mailResult['success'],
        'message' => $mailResult['success'] ? 'Reset link sent successfully.' : 'Reset link generated but email failed to send.',
        'error' => $mailResult['error'] ?? null,
        'mock_token' => $token // Keep for local development ease
    ]);
}

if ($uriParts[1] === 'reset-password') {
    if ($method !== 'POST') {
        sendResponse(['error' => 'Method not allowed'], 405);
    }

    $data = getRequestBody();
    $token = $data['token'] ?? '';
    $newPassword = $data['password'] ?? '';

    if (empty($token) || empty($newPassword)) {
        sendResponse(['error' => 'Token and password are required'], 400);
    }

    // Check token
    $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        sendResponse(['error' => 'Invalid or expired token'], 400);
    }

    $email = $reset['email'];
    $newPasswordHash = Auth::hashPassword($newPassword);

    $db->beginTransaction();
    try {
        // Update password
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$newPasswordHash, $email]);

        // Delete used token
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        $db->commit();
        sendResponse(['message' => 'Password reset successful.']);
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(['error' => 'Failed to reset password: ' . $e->getMessage()], 500);
    }
}

sendResponse(['error' => 'Auth route not found'], 404);
