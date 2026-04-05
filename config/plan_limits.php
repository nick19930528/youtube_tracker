<?php
/**
 * 各方案額度：優先讀取 subscription_plans.quota_*；無欄位或皆為 0 時回退常數（相容舊庫）
 */
require_once __DIR__ . '/subscription_sync.php';
define('PLAN_FREE_MAX_CHANNELS', 50);
define('PLAN_FREE_MAX_VIDEOS_PER_LIST', 500);

define('PLAN_GO_MAX_CHANNELS', 200);
define('PLAN_GO_MAX_VIDEOS_PER_LIST', 2000);

/**
 * 目前訂閱方案 slug（無訂閱紀錄視為 free）
 */
function plan_limits_get_active_slug(PDO $pdo, $userId)
{
    subscription_sync_expired_for_user($pdo, $userId);

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

    return (string) $slug;
}

/**
 * @return array|null 含 videos（單邊清單筆數）, channels（頻道數）, name；null 表示未定義方案
 */
function plan_limits_get_tier_limits_legacy($slug)
{
    switch ($slug) {
        case 'free':
            return array(
                'videos' => PLAN_FREE_MAX_VIDEOS_PER_LIST,
                'channels' => PLAN_FREE_MAX_CHANNELS,
                'name' => '免費',
            );
        case 'go':
            return array(
                'videos' => PLAN_GO_MAX_VIDEOS_PER_LIST,
                'channels' => PLAN_GO_MAX_CHANNELS,
                'name' => 'Go',
            );
        default:
            return null;
    }
}

/**
 * @return array|null 含 videos, channels, name
 */
function plan_limits_get_tier_limits(PDO $pdo, $slug)
{
    static $cache = array();
    $slug = (string) $slug;
    if (isset($cache[$slug])) {
        return $cache[$slug];
    }

    try {
        $stmt = $pdo->prepare('SELECT name, quota_max_channels, quota_max_videos_per_list FROM subscription_plans WHERE slug = ? LIMIT 1');
        $stmt->execute(array($slug));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $legacy = plan_limits_get_tier_limits_legacy($slug);
        $cache[$slug] = $legacy;

        return $legacy;
    }

    if (!$row) {
        $legacy = plan_limits_get_tier_limits_legacy($slug);
        $cache[$slug] = $legacy;

        return $legacy;
    }

    $ch = (int) $row['quota_max_channels'];
    $vid = (int) $row['quota_max_videos_per_list'];
    if ($ch === 0 && $vid === 0) {
        $legacy = plan_limits_get_tier_limits_legacy($slug);
        $cache[$slug] = $legacy;

        return $legacy;
    }

    $out = array(
        'videos' => $vid,
        'channels' => $ch,
        'name' => (string) $row['name'],
    );
    $cache[$slug] = $out;

    return $out;
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
    $cfg = plan_limits_get_tier_limits($pdo, plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return null;
    }

    return (int) $cfg['videos'];
}

function plan_limits_max_channels(PDO $pdo, $userId)
{
    $cfg = plan_limits_get_tier_limits($pdo, plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return null;
    }

    return (int) $cfg['channels'];
}

/**
 * 分類數量不限制（回傳 null 表示無上限，與頻道／影片清單額度分開）
 */
function plan_limits_max_categories(PDO $pdo, $userId)
{
    return null;
}

/**
 * 是否為有限額方案（首頁顯示說明用）
 */
function plan_limits_has_quota(PDO $pdo, $userId)
{
    return plan_limits_get_tier_limits($pdo, plan_limits_get_active_slug($pdo, $userId)) !== null;
}

/**
 * 首頁／說明用一行文字（先頻道數、再清單筆數，與會員中心價目表一致）
 */
function plan_limits_quota_banner_text(PDO $pdo, $userId)
{
    $cfg = plan_limits_get_tier_limits($pdo, plan_limits_get_active_slug($pdo, $userId));
    if ($cfg === null) {
        return '';
    }

    return $cfg['name'] . '：頻道最多 ' . (int) $cfg['channels'] . ' 個；待看／已看列表各最多顯示 '
        . (int) $cfg['videos'] . ' 筆；分類數量不限。';
}

function plan_limits_channel_count(PDO $pdo, $userId)
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM channels WHERE user_id = ?');
    $st->execute(array($userId));

    return (int) $st->fetchColumn();
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

    return (int) $st->fetchColumn();
}

function plan_limits_can_add_category(PDO $pdo, $userId)
{
    $max = plan_limits_max_categories($pdo, $userId);
    if ($max === null) {
        return true;
    }

    return plan_limits_category_count($pdo, $userId) < $max;
}
