<?php
/**
 * 首頁儀表板：無限捲動載入待看／已看／已訂閱
 */
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../config/plan_limits.php';
require_once __DIR__ . '/../includes/dashboard_render.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method'], JSON_UNESCAPED_UNICODE);
    exit;
}

$tab = isset($_GET['tab']) ? (string) $_GET['tab'] : '';
if (!in_array($tab, ['unwatched', 'watched', 'subscribed'], true)) {
    echo json_encode(['ok' => false, 'error' => 'tab'], JSON_UNESCAPED_UNICODE);
    exit;
}

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
if ($offset < 0) {
    $offset = 0;
}

$pdo = (new Database())->getConnection();
$uid = auth_user_id();

$stmt = $pdo->prepare('SELECT id, name FROM channel_categories WHERE user_id = ? ORDER BY sort_order ASC, name ASC');
$stmt->execute([$uid]);
$dashCategoryOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    $stmt = $pdo->prepare('SELECT COALESCE(dash_auto_load, 1) FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$uid]);
    if ((int) $stmt->fetchColumn() === 0) {
        echo json_encode(['ok' => true, 'html' => '', 'has_more' => false, 'next_offset' => $offset], JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Exception $e) {
}

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
if ($categoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    // 保留：非 DB 之「未分類」篩選
} elseif ($categoryId > 0) {
    $stmt = $pdo->prepare('SELECT id FROM channel_categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$categoryId, $uid]);
    if ($stmt->fetchColumn() === false) {
        $categoryId = 0;
    }
} else {
    $categoryId = 0;
}

$_maxVideosList = plan_limits_max_videos_per_list($pdo, $uid);
$videoCap = ($_maxVideosList === null) ? PHP_INT_MAX : (int) $_maxVideosList;
$pageSize = (int) DASH_FEED_PAGE_SIZE;

$html = '';
$hasMore = false;
$nextOffset = $offset;

if ($tab === 'subscribed') {
    $countSql = 'SELECT COUNT(*) FROM channels c WHERE c.user_id = ? ';
    if ($categoryId > 0) {
        $countSql .= ' AND c.category_id = ? ';
    } elseif ($categoryId === FILTER_CATEGORY_UNCATEGORIZED) {
        $countSql .= ' AND c.category_id IS NULL ';
    }
    $stmt = $pdo->prepare($countSql);
    if ($categoryId > 0) {
        $stmt->execute([$uid, $categoryId]);
    } else {
        $stmt->execute([$uid]);
    }
    $total = (int) $stmt->fetchColumn();

    if ($offset >= $total) {
        echo json_encode(['ok' => true, 'html' => '', 'has_more' => false, 'next_offset' => $offset], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $take = min($pageSize, $total - $offset);
    $sql = "
        SELECT c.id, c.name, c.url, c.thumbnail_url, c.subscriber_count, c.video_count, c.published_at,
               c.is_favorite, c.category_id, cc.name AS category_name
        FROM channels c
        LEFT JOIN channel_categories cc ON c.category_id = cc.id AND cc.user_id = c.user_id
        WHERE c.user_id = ?
    ";
    if ($categoryId > 0) {
        $sql .= ' AND c.category_id = ? ';
    } elseif ($categoryId === FILTER_CATEGORY_UNCATEGORIZED) {
        $sql .= ' AND c.category_id IS NULL ';
    }
    $sql .= ' ORDER BY c.name ASC LIMIT ' . (int) $take . ' OFFSET ' . (int) $offset;

    $stmt = $pdo->prepare($sql);
    if ($categoryId > 0) {
        $stmt->execute([$uid, $categoryId]);
    } else {
        $stmt->execute([$uid]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $html = render_dashboard_channel_cards_html($rows, $dashCategoryOptions);
    $nextOffset = $offset + count($rows);
    $hasMore = ($nextOffset < $total);
    echo json_encode(['ok' => true, 'html' => $html, 'has_more' => $hasMore, 'next_offset' => $nextOffset], JSON_UNESCAPED_UNICODE);
    exit;
}

// 待看 / 已看
$isWatched = ($tab === 'watched') ? 1 : 0;

if ($categoryId > 0) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = ? AND ch.category_id = ?
    ");
    $stmt->execute([$uid, $isWatched, $categoryId]);
} elseif ($categoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = ? AND ch.category_id IS NULL
    ");
    $stmt->execute([$uid, $isWatched]);
} else {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM videos WHERE user_id = ? AND is_watched = ?');
    $stmt->execute([$uid, $isWatched]);
}
$total = (int) $stmt->fetchColumn();
$maxLoad = min($total, $videoCap);

if ($offset >= $maxLoad) {
    echo json_encode(['ok' => true, 'html' => '', 'has_more' => false, 'next_offset' => $offset], JSON_UNESCAPED_UNICODE);
    exit;
}

$remaining = $maxLoad - $offset;
$take = min($pageSize, $remaining);
$lim = (int) $take;
$off = (int) $offset;

if ($categoryId > 0) {
    if ($isWatched === 0) {
        $sql = "
            SELECT v.* FROM videos v
            INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
                AND v.user_id = ch.user_id
            WHERE v.user_id = ? AND v.is_watched = 0 AND ch.category_id = ?
            ORDER BY ch.name ASC, v.published_at DESC
            LIMIT {$lim} OFFSET {$off}
        ";
    } else {
        $sql = "
            SELECT v.* FROM videos v
            INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
                AND v.user_id = ch.user_id
            WHERE v.user_id = ? AND v.is_watched = 1 AND ch.category_id = ?
            ORDER BY ch.name ASC, COALESCE(v.watched_at, v.added_at) DESC
            LIMIT {$lim} OFFSET {$off}
        ";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $categoryId]);
} elseif ($categoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    if ($isWatched === 0) {
        $sql = "
            SELECT v.* FROM videos v
            INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
                AND v.user_id = ch.user_id
            WHERE v.user_id = ? AND v.is_watched = 0 AND ch.category_id IS NULL
            ORDER BY ch.name ASC, v.published_at DESC
            LIMIT {$lim} OFFSET {$off}
        ";
    } else {
        $sql = "
            SELECT v.* FROM videos v
            INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
                AND v.user_id = ch.user_id
            WHERE v.user_id = ? AND v.is_watched = 1 AND ch.category_id IS NULL
            ORDER BY ch.name ASC, COALESCE(v.watched_at, v.added_at) DESC
            LIMIT {$lim} OFFSET {$off}
        ";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid]);
} else {
    if ($isWatched === 0) {
        $sql = "
            SELECT * FROM videos
            WHERE user_id = ? AND is_watched = 0
            ORDER BY published_at DESC
            LIMIT {$lim} OFFSET {$off}
        ";
    } else {
        $sql = "
            SELECT * FROM videos
            WHERE user_id = ? AND is_watched = 1
            ORDER BY COALESCE(watched_at, added_at) DESC
            LIMIT {$lim} OFFSET {$off}
        ";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$mode = $isWatched === 0 ? 'unwatched' : 'watched';
$html = render_dashboard_video_rows_flat($rows, $mode);
$nextOffset = $offset + count($rows);
$hasMore = ($nextOffset < $maxLoad);

echo json_encode(['ok' => true, 'html' => $html, 'has_more' => $hasMore, 'next_offset' => $nextOffset], JSON_UNESCAPED_UNICODE);
