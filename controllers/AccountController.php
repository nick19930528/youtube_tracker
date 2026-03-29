<?php

class AccountController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getProfile($userId)
    {
        $stmt = $this->pdo->prepare('SELECT id, email, name, gender, email_verified_at, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute(array($userId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * 目前主要訂閱（優先 active，否則最新一筆）
     */
    public function getSubscriptionWithPlan($userId)
    {
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
