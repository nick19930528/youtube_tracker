<?php
/**
 * 免費版方案限制（可依訂閱方案擴充）
 */
define('PLAN_FREE_MAX_CHANNELS', 10);
define('PLAN_FREE_MAX_CATEGORIES', 10);
define('PLAN_FREE_MAX_VIDEOS_PER_LIST', 100);

function plan_limits_is_free(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare("
        SELECT p.slug, p.billing_interval
        FROM subscriptions s
        INNER JOIN subscription_plans p ON p.id = s.plan_id
        WHERE s.user_id = ?
        ORDER BY (s.status = 'active') DESC, s.id DESC
        LIMIT 1
    ");
    $stmt->execute(array($userId));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return true;
    }
    return ($row['slug'] === 'free' || $row['billing_interval'] === 'free');
}

/**
 * 待看／已看清單單邊筆數上限；非免費回傳 null 表示不限制
 */
function plan_limits_max_videos_per_list(PDO $pdo, $userId)
{
    return plan_limits_is_free($pdo, $userId) ? PLAN_FREE_MAX_VIDEOS_PER_LIST : null;
}

function plan_limits_channel_count(PDO $pdo, $userId)
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM channels WHERE user_id = ?');
    $st->execute(array($userId));
    return (int)$st->fetchColumn();
}

function plan_limits_can_add_channel(PDO $pdo, $userId)
{
    if (!plan_limits_is_free($pdo, $userId)) {
        return true;
    }
    return plan_limits_channel_count($pdo, $userId) < PLAN_FREE_MAX_CHANNELS;
}

function plan_limits_category_count(PDO $pdo, $userId)
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM channel_categories WHERE user_id = ?');
    $st->execute(array($userId));
    return (int)$st->fetchColumn();
}

function plan_limits_can_add_category(PDO $pdo, $userId)
{
    if (!plan_limits_is_free($pdo, $userId)) {
        return true;
    }
    return plan_limits_category_count($pdo, $userId) < PLAN_FREE_MAX_CATEGORIES;
}
