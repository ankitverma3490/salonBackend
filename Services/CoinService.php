<?php

class CoinService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // --- Price Settings ---

    public function getSetting($key, $default = null)
    {
        $stmt = $this->db->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return $default;
        }

        // Standardize: Admin API stores everything as JSON. 
        // We attempt to decode it, if it fails or isn't JSON, we return the raw value.
        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    public function getCoinPrice()
    {
        return (float) $this->getSetting('coin_price', 1.00);
    }

    public function setCoinPrice($price, $updatedBy)
    {
        // For consistency with Admin API, store as JSON
        $stmt = $this->db->prepare("UPDATE platform_settings SET setting_value = ?, updated_by = ? WHERE setting_key = 'coin_price'");
        return $stmt->execute([json_encode((float)$price), $updatedBy]);
    }

    // --- User Balance ---

    public function getBalance($userId)
    {
        $stmt = $this->db->prepare("SELECT coin_balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return (float) ($stmt->fetchColumn() ?: 0.00);
    }

    public function getTransactions($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM coin_transactions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Transaction Logic ---

    public function adjustBalance($userId, $amount, $type, $description, $referenceId = null)
    {
        $inTransaction = $this->db->inTransaction();
        if (!$inTransaction) {
            $this->db->beginTransaction();
        }

        try {
            // Update balance
            $stmt = $this->db->prepare("UPDATE users SET coin_balance = coin_balance + ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);

            // Log transaction
            $txnId = Auth::generateUuid();
            $stmt = $this->db->prepare("
                INSERT INTO coin_transactions (id, user_id, amount, transaction_type, description, reference_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$txnId, $userId, $amount, $type, $description, $referenceId]);

            if (!$inTransaction) {
                $this->db->commit();
            }
            return true;
        } catch (Exception $e) {
            if (!$inTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Coin Adjustment Error: " . $e->getMessage());
            return false;
        }
    }

    public function spendCoins($userId, $amount, $description, $referenceId = null)
    {
        $currentBalance = $this->getBalance($userId);
        if ($currentBalance < $amount) {
            return ['error' => 'Insufficient coin balance'];
        }

        if ($this->adjustBalance($userId, -$amount, 'spent', $description, $referenceId)) {
            return ['success' => true];
        }
        return ['error' => 'Failed to process transaction'];
    }

    public function earnCoins($userId, $amount, $description, $referenceId = null)
    {
        if ($this->adjustBalance($userId, $amount, 'earned', $description, $referenceId)) {
            return ['success' => true];
        }
        return ['error' => 'Failed to process transaction'];
    }

    public function hasTransactionForReference($referenceId)
    {
        if (!$referenceId) return false;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM coin_transactions WHERE reference_id = ?");
        $stmt->execute([$referenceId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
