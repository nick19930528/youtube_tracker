<?php

require_once __DIR__ . '/../config/subscription_sync.php';

class AccountController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getProfile($userId)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id, email, name, gender, COALESCE(dash_auto_load, 1) AS dash_auto_load, COALESCE(fetch_max_age_days, 7) AS fetch_max_age_days, COALESCE(fetch_max_per_channel, 1) AS fetch_max_per_channel, email_verified_at, created_at FROM users WHERE id = ? LIMIT 1');
            $stmt->execute(array($userId));
        } catch (Throwable $e) {
            $stmt = $this->pdo->prepare('SELECT id, email, name, gender, COALESCE(dash_auto_load, 1) AS dash_auto_load, email_verified_at, created_at FROM users WHERE id = ? LIMIT 1');
            $stmt->execute(array($userId));
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        if (!array_key_exists('fetch_max_age_days', $row)) {
            $row['fetch_max_age_days'] = 7;
            $row['fetch_max_per_channel'] = 1;
        }
        return $row;
    }

    /**
     * 目前主要訂閱（優先 active，否則最新一筆）
     */
    public function getSubscriptionWithPlan($userId)
    {
        subscription_sync_expired_for_user($this->pdo, $userId);

        $stmt = $this->pdo->prepare("
            SELECT s.id, s.status, s.current_period_start, s.current_period_end, s.cancel_at_period_end,
                   p.name AS plan_name, p.slug, p.price_cents, p.currency, p.billing_interval
            FROM subscriptions s
            INNER JOIN subscription_plans p ON p.id = s.plan_id
            WHERE s.user_id = ?
            ORDER BY (s.status = 'active') DESC, s.id DESC
            LIMIT 1
        ");
        $stmt->execute(array($userId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * 歷史訂閱紀錄（新到舊，含目前與過往方案）
     *
     * @return array<int, array<string, mixed>>
     */
    public function listSubscriptionHistory($userId)
    {
        subscription_sync_expired_for_user($this->pdo, $userId);

        $stmt = $this->pdo->prepare("
            SELECT s.id, s.status, s.current_period_start, s.current_period_end, s.cancel_at_period_end,
                   s.created_at, s.updated_at,
                   p.name AS plan_name, p.slug, p.price_cents, p.currency, p.billing_interval
            FROM subscriptions s
            INNER JOIN subscription_plans p ON p.id = s.plan_id
            WHERE s.user_id = ?
            ORDER BY s.id DESC
        ");
        $stmt->execute(array($userId));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userId, $name, $gender)
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }
        $allowedG = array('', 'm', 'f', 'other');
        $g = in_array($gender, $allowedG, true) ? ($gender === '' ? null : $gender) : null;
        $stmt = $this->pdo->prepare('UPDATE users SET name = ?, gender = ? WHERE id = ?');
        return (bool)$stmt->execute(array($name, $g, $userId));
    }

    public function updateDashAutoLoad($userId, $enabled)
    {
        $v = $enabled ? 1 : 0;
        $stmt = $this->pdo->prepare('UPDATE users SET dash_auto_load = ? WHERE id = ?');
        return (bool)$stmt->execute(array($v, $userId));
    }

    /**
     * @param int $maxAgeDays 1–7
     * @param int $maxPerChannel 1–3
     */
    public function updateFetchPrefs($userId, $maxAgeDays, $maxPerChannel)
    {
        $d = max(1, min(7, (int) $maxAgeDays));
        $m = max(1, min(3, (int) $maxPerChannel));
        try {
            $stmt = $this->pdo->prepare('UPDATE users SET fetch_max_age_days = ?, fetch_max_per_channel = ? WHERE id = ?');
            return (bool) $stmt->execute([$d, $m, $userId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @return bool|string true 或錯誤訊息
     */
    public function changePassword($userId, $current, $new, $new2)
    {
        if (strlen($new) < 8) {
            return '新密碼至少 8 個字元。';
        }
        if ($new !== $new2) {
            return '兩次新密碼不一致。';
        }
        $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute(array($userId));
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            return '目前密碼不正確。';
        }
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute(array($newHash, $userId)) ? true : '更新失敗。';
    }
}
