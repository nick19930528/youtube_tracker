<?php
/**
 * 付費訂閱到期：將方案改回免費（僅當 current_period_end 已早於現在）
 */
function subscription_sync_expired_for_user(PDO $pdo, $userId)
{
    static $done = array();
    $userId = (int) $userId;
    if ($userId < 1) {
        return;
    }
    if (isset($done[$userId])) {
        return;
    }
    $done[$userId] = true;

    try {
        $stmt = $pdo->prepare("
            SELECT s.id
            FROM subscriptions s
            INNER JOIN subscription_plans p ON p.id = s.plan_id
            WHERE s.user_id = ?
              AND s.status = 'active'
              AND p.slug <> 'free'
              AND s.current_period_end IS NOT NULL
              AND s.current_period_end < NOW()
            ORDER BY s.id DESC
            LIMIT 1
        ");
        $stmt->execute(array($userId));
        $sid = $stmt->fetchColumn();
        if ($sid === false || $sid === null) {
            return;
        }
        $sid = (int) $sid;

        $freeId = $pdo->query("SELECT id FROM subscription_plans WHERE slug = 'free' LIMIT 1")->fetchColumn();
        if ($freeId === false || $freeId === null) {
            return;
        }
        $freeId = (int) $freeId;

        $upd = $pdo->prepare("
            UPDATE subscriptions SET
                plan_id = ?,
                status = 'active',
                current_period_start = NULL,
                current_period_end = NULL,
                cancel_at_period_end = 0,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ?
        ");
        $upd->execute(array($freeId, $sid, $userId));
    } catch (Throwable $e) {
        // 不阻斷頁面；生產環境可改為寫入 log
    }
}

/**
 * 會員中心顯示：免費 active 不顯示「使用中」；付費 active 顯示「付費週期內」
 *
 * @param string $status subscriptions.status
 * @param string $planSlug subscription_plans.slug
 */
function subscription_status_label_member($status, $planSlug)
{
    $status = (string) $status;
    $slug = (string) $planSlug;

    if ($status === 'active' && $slug === 'free') {
        return '免費方案（無需續約）';
    }

    $map = array(
        'trialing' => '試用中',
        'active' => '付費週期內',
        'past_due' => '付款逾期',
        'canceled' => '已取消',
        'expired' => '已到期',
    );

    if (isset($map[$status])) {
        return $map[$status];
    }

    return $status;
}
