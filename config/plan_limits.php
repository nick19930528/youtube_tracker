<?php
/**
 * 各方案額度：依 subscription_plans.slug 對應（free、go …）
 */
define('PLAN_FREE_MAX_CHANNELS', 200);
define('PLAN_FREE_MAX_CATEGORIES', 10);
define('PLAN_FREE_MAX_VIDEOS_PER_LIST', 10000);

define('PLAN_GO_MAX_CHANNELS', 50);
define('PLAN_GO_MAX_CATEGORIES', 50);
define('PLAN_GO_MAX_VIDEOS_PER_LIST', 500);

/**
 * 目前訂閱方案 slug（無訂閱紀錄視為 free）
 */
function plan_limits_get_active_slug(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare("
        SELECT p.slug
        FROM subscriptions s
        INNER JOIN subscription_plans p ON p.id = s.plan_id
        WHERE s.user_id = ?
        ORDER BY (s.status = 'active') DESC, s.id DESC
        LIMIT 1
    ");
    $stmt->execute(array($userId));
    $slug = $stmt->fetchColumn();
    if ($slug === false || $slug === '') {
        return 'free';
    }
    return (string)$slug;
}

/**
 * @return array|null 含 videos, channels, categories, name；null 表示不限制
 */
function plan_limits_get_tier_limits($slug)
{
    switch ($slug) {
        case 'free':
            return array(
                'videos' => PLAN_FREE_MAX_VIDEOS_PER_LIST,
                'channels' => PLAN_FREE_MAX_CHANNELS,
                'categories' => PLAN_FREE_MAX_CATEGORIES,
                'name' => '免費',
            );
        case 'go':
            return array(
                'videos' => PLAN_GO_MAX_VIDEOS_PER_LIST,
                'channels' => PLAN_GO_MAX_CHANNELS,
                'categories' => PLAN_GO_MAX_CATEGORIES,
                'name' => 'Go',
            );
        default:
            return null;
    }
}

/**
 * 是否為免費 slug（相容舊邏輯）
 */
function plan_limits_is_free(PDO $pdo, $userId)
{
    return plan_limits_get_active_slug($pdo, $userId) === 'free';
}

/**
 * 待看／已看清單單邊筆數上限；無限制時回傳 null
 */
function plan_limits_max_videos_per_list(PDO $pdo, $userId)
{
    $cfg = plan_limits_get_tier_limits(plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return null;
    }
    return (int)$cfg['videos'];
}

function plan_limits_max_channels(PDO $pdo, $userId)
{
    $cfg = plan_limits_get_tier_limits(plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return null;
    }
    return (int)$cfg['channels'];
}

function plan_limits_max_categories(PDO $pdo, $userId)
{
    $cfg = plan_limits_get_tier_limits(plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return null;
    }
    return (int)$cfg['categories'];
}

/**
 * 是否為有限額方案（首頁顯示說明用）
 */
function plan_limits_has_quota(PDO $pdo, $userId)
{
    return plan_limits_get_tier_limits(plan_limits_get_active_slug($pdo, $userId)) !== null;
}

/**
 * 首頁／說明用一行文字
 */
function plan_limits_quota_banner_text(PDO $pdo, $userId)
{
    $cfg = plan_limits_get_tier_limits(plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return '';
    }
    return $cfg['name'] . '：待看／已看列表各最多顯示 ' . (int)$cfg['videos'] . ' 筆；頻道與分類各最多 '
        . (int)$cfg['channels'] . ' 個。';
}

function plan_limits_channel_count(PDO $pdo, $userId)
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM channels WHERE user_id = ?');
    $st->execute(array($userId));
    return (int)$st->fetchColumn();
}

function plan_limits_can_add_channel(PDO $pdo, $userId)
{
    $max = plan_limits_max_channels($pdo, $userId);
    if ($max === null) {
        return true;
    }
    return plan_limits_channel_count($pdo, $userId) < $max;
}

function plan_limits_category_count(PDO $pdo, $userId)
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM channel_categories WHERE user_id = ?');
    $st->execute(array($userId));
    return (int)$st->fetchColumn();
}

function plan_limits_can_add_category(PDO $pdo, $userId)
{
    $max = plan_limits_max_categories($pdo, $userId);
    if ($max === null) {
        return true;
    }
    return plan_limits_category_count($pdo, $userId) < $max;
}
