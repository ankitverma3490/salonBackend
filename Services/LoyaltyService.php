<?php

class LoyaltyService
{
    private $db;
    private $notifService;

    public function __construct($db)
    {
        $this->db = $db;
        $this->notifService = new NotificationService($db);
    }

    // --- Settings ---

    public function getSettings($salonId)
    {
        $stmt = $this->db->prepare("SELECT * FROM loyalty_programs WHERE salon_id = ?");
        $stmt->execute([$salonId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$settings) {
            // Create default settings if not exists
            $id = Auth::generateUuid();
            $stmt = $this->db->prepare("
                INSERT INTO loyalty_programs (id, salon_id, is_active)
                VALUES (?, ?, 0)
            ");
            $stmt->execute([$id, $salonId]);
            return $this->getSettings($salonId);
        }

        return $settings;
    }

    public function updateSettings($salonId, $data)
    {
        $fields = [];
        $params = [];
        $validFields = ['program_name', 'is_active', 'points_per_currency_unit', 'min_points_redemption', 'signup_bonus_points', 'description'];

        foreach ($validFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $salonId;
        $sql = "UPDATE loyalty_programs SET " . implode(', ', $fields) . " WHERE salon_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // --- Rewards ---

    public function getRewards($salonId, $activeOnly = false)
    {
        $sql = "SELECT * FROM loyalty_rewards WHERE salon_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY points_required ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$salonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createReward($salonId, $data)
    {
        $id = Auth::generateUuid();
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_rewards (id, salon_id, name, description, points_required, discount_amount, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $salonId,
            $data['name'],
            $data['description'] ?? null,
            (int) $data['points_required'],
            (float) ($data['discount_amount'] ?? 0),
            isset($data['is_active']) ? (int) $data['is_active'] : 1
        ]);
        return $id;
    }

    public function deleteReward($salonId, $rewardId)
    {
        $stmt = $this->db->prepare("DELETE FROM loyalty_rewards WHERE id = ? AND salon_id = ?");
        return $stmt->execute([$rewardId, $salonId]);
    }

    // --- Points Management ---

    public function getCustomerPoints($salonId, $userId)
    {
        $stmt = $this->db->prepare("SELECT loyalty_points FROM customer_salon_profiles WHERE salon_id = ? AND user_id = ?");
        $stmt->execute([$salonId, $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function earnPoints($salonId, $userId, $amountSpent, $bookingId)
    {
        $settings = $this->getSettings($salonId);
        if (!$settings || !$settings['is_active']) {
            return false;
        }

        $points = floor($amountSpent * $settings['points_per_currency_unit']);
        if ($points <= 0)
            return false;

        $this->db->beginTransaction();
        try {
            // Log transaction
            $txnId = Auth::generateUuid();
            $stmt = $this->db->prepare("
                INSERT INTO loyalty_transactions (id, salon_id, user_id, points, transaction_type, reference_id, description)
                VALUES (?, ?, ?, ?, 'earned', ?, 'Points earned from service')
            ");
            $stmt->execute([$txnId, $salonId, $userId, $points, $bookingId]);

            // Update balance
            $stmt = $this->db->prepare("
                INSERT INTO customer_salon_profiles (id, salon_id, user_id, loyalty_points)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE loyalty_points = loyalty_points + ?
            ");
            // Note: ID generation for insert on duplicate key is tricky if we rely on it auto-creating.
            // But usually profile exists. If not, we might need to be careful.
            // For now, assume profile exists or handle safely.

            // Check existence first to be safe
            $check = $this->db->prepare("SELECT id FROM customer_salon_profiles WHERE salon_id = ? AND user_id = ?");
            $check->execute([$salonId, $userId]);
            if (!$check->fetch()) {
                $newId = Auth::generateUuid();
                $stmt = $this->db->prepare("INSERT INTO customer_salon_profiles (id, salon_id, user_id, loyalty_points) VALUES (?, ?, ?, ?)");
                $stmt->execute([$newId, $salonId, $userId, $points]);
            } else {
                $stmt = $this->db->prepare("UPDATE customer_salon_profiles SET loyalty_points = loyalty_points + ? WHERE salon_id = ? AND user_id = ?");
                $stmt->execute([$points, $salonId, $userId]);
            }

            $this->db->commit();

            // Notify user
            $this->notifService->notifyUser(
                $userId,
                "Loyalty Points Earned!",
                "You just earned $points loyalty points from your recent visit.",
                "success",
                "/client/rewards"
            );

            return $points;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Loyalty Earn Error: " . $e->getMessage());
            return false;
        }
    }

    public function redeemPoints($salonId, $userId, $rewardId)
    {
        $settings = $this->getSettings($salonId);
        if (!$settings || !$settings['is_active']) {
            return ['error' => 'Loyalty program is not active'];
        }

        $stmt = $this->db->prepare("SELECT * FROM loyalty_rewards WHERE id = ? AND salon_id = ?");
        $stmt->execute([$rewardId, $salonId]);
        $reward = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reward || !$reward['is_active']) {
            return ['error' => 'Reward not available'];
        }

        $currentPoints = $this->getCustomerPoints($salonId, $userId);
        if ($currentPoints < $reward['points_required']) {
            return ['error' => 'Insufficient points'];
        }

        $this->db->beginTransaction();
        try {
            // Deduct points
            $stmt = $this->db->prepare("UPDATE customer_salon_profiles SET loyalty_points = loyalty_points - ? WHERE salon_id = ? AND user_id = ?");
            $stmt->execute([$reward['points_required'], $salonId, $userId]);

            // Log transaction
            $txnId = Auth::generateUuid();
            $stmt = $this->db->prepare("
                INSERT INTO loyalty_transactions (id, salon_id, user_id, points, transaction_type, reference_id, description)
                VALUES (?, ?, ?, ?, 'redeemed', ?, ?)
            ");
            $stmt->execute([
                $txnId,
                $salonId,
                $userId,
                -$reward['points_required'],
                $rewardId,
                "Redeemed: " . $reward['name']
            ]);

            $this->db->commit();
            return ['success' => true, 'balance' => $currentPoints - $reward['points_required']];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['error' => $e->getMessage()];
        }
    }
}
