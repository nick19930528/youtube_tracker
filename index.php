<?php
require_once __DIR__ . '/config/bootstrap.php';

$page = $_GET['page'] ?? 'home';

if ($page === 'logout') {
    auth_logout();
    header('Location: index.php?page=login');
    exit;
}

if ($page === 'login' || $page === 'register') {
    require __DIR__ . '/views/auth/auth.php';
    exit;
}

if ($page === 'pay_return') {
    require __DIR__ . '/views/payment/return_mpg.php';
    exit;
}

auth_require_login();
$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$currentAuthUser = auth_user();

require_once __DIR__ . '/models/Video.php';
(new Video($pdo, $uid))->trimBothSidesToPlanLimits();

/* =========================
   🔀 路由區（非首頁）
========================= */
if ($page !== 'home') {

    switch ($page) {

        case 'videos':
            require __DIR__ . '/views/videos/list.php';
            break;

        case 'open_video':
            require_once __DIR__ . '/controllers/VideoController.php';
            $openVideoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($openVideoId <= 0) {
                header('Location: index.php');
                exit;
            }
            $stmt = $pdo->prepare('SELECT id, youtube_url, is_watched FROM videos WHERE id = ? AND user_id = ?');
            $stmt->execute([$openVideoId, $uid]);
            $openRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$openRow || empty($openRow['youtube_url'])) {
                header('Location: index.php');
                exit;
            }
            if ((int)$openRow['is_watched'] === 0) {
                $videoController = new VideoController($pdo, $uid);
                $videoController->markWatched($openVideoId);
            }
            header('Location: ' . $openRow['youtube_url']);
            exit;

        case 'videos_by_category':
            require __DIR__ . '/views/videos/by_category.php';
            break;

        case 'add':
            require __DIR__ . '/views/videos/add.php';
            break;

        case 'channels':
            require __DIR__ . '/views/channels/list.php';
            break;

        case 'channel_categories':
            require __DIR__ . '/views/channels/categories.php';
            break;

        case 'account':
            require __DIR__ . '/views/account/center.php';
            break;

        case 'support':
            require __DIR__ . '/views/account/support.php';
            break;

        case 'admin':
        case 'admin_members':
            require __DIR__ . '/views/admin/members.php';
            break;

        case 'admin_member':
            require __DIR__ . '/views/admin/member.php';
            break;

        case 'test_lab':
            require __DIR__ . '/views/test_lab.php';
            break;

        case 'pay':
            require __DIR__ . '/views/payment/checkout_mpg.php';
            break;

        default:
            echo "❌ 頁面不存在";
            break;
    }

    exit;
}

$allowedQuickNotices = ['channel_ok', 'channel_err', 'channel_limit', 'video_ok', 'video_err'];
$quickNotice = isset($_GET['notice']) && in_array($_GET['notice'], $allowedQuickNotices, true)
    ? $_GET['notice']
    : null;

if ($page === 'home' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controllers/ChannelController.php';
    require_once __DIR__ . '/controllers/VideoController.php';

    $channelController = new ChannelController($pdo, $uid);
    $videoController = new VideoController($pdo, $uid);

    $redirectHome = function (string $notice) {
        $q = ['notice' => $notice];
        if (!empty($_POST['home_category_id'])) {
            $cid = (int)$_POST['home_category_id'];
            if ($cid === FILTER_CATEGORY_UNCATEGORIZED || $cid > 0) {
                $q['category_id'] = $cid;
            }
        }
        if (!empty($_POST['home_dash_tab'])) {
            $dt = (string)$_POST['home_dash_tab'];
            if (in_array($dt, ['unwatched', 'watched', 'subscribed'], true)) {
                $q['dash_tab'] = $dt;
            }
        }
        header('Location: index.php?' . http_build_query($q));
        exit;
    };

    if (isset($_POST['quick_add_channel'])) {
        $controller = $channelController;
        $input = trim($_POST['channel_input'] ?? '');
        $categoryId = !empty($_POST['channel_category_id']) ? (int)$_POST['channel_category_id'] : null;

        $channelId = null;
        $name = '';
        $url = '';

        if ($input !== '') {
            if (strpos($input, '@') === 0) {
                $handle = substr($input, 1);
                $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&forHandle={$handle}&key=" . YOUTUBE_API_KEY;
                $json = @file_get_contents($apiUrl);
                $data = json_decode($json, true);

                if (!empty($data['items'][0])) {
                    $item = $data['items'][0];
                    $channelId = $item['id'];
                    $name = $item['snippet']['title'];
                    $url = "https://www.youtube.com/@{$handle}";
                }
            } elseif (preg_match('/(youtube\\.com\/(channel|@[^\/]+))/', $input)) {
                $url = $input;

                if (preg_match('/\/channel\/([a-zA-Z0-9_-]+)/', $input, $matches)) {
                    $channelId = $matches[1];
                } elseif (preg_match('/youtube\\.com\/@([a-zA-Z0-9._-]+)/', $input, $matches)) {
                    $handle = $matches[1];
                    $apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=channel&q={$handle}&key=" . YOUTUBE_API_KEY;
                    $json = @file_get_contents($apiUrl);
                    $data = json_decode($json, true);
                    if (!empty($data['items'][0])) {
                        $channelId = $data['items'][0]['snippet']['channelId'];
                        $name = $data['items'][0]['snippet']['title'];
                        $url = "https://www.youtube.com/channel/{$channelId}";
                    }
                }
            } elseif (preg_match('/^UC[a-zA-Z0-9_-]{20,}$/', $input)) {
                $channelId = $input;
                $url = "https://www.youtube.com/channel/{$channelId}";
            }

            if ($channelId && $name === '') {
                $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id={$channelId}&key=" . YOUTUBE_API_KEY;
                $json = @file_get_contents($apiUrl);
                $data = json_decode($json, true);
                if (!empty($data['items'][0])) {
                    $name = $data['items'][0]['snippet']['title'];
                }
            }

            if ($channelId && $name && $url) {
                require_once __DIR__ . '/config/plan_limits.php';
                if (!plan_limits_can_add_channel($pdo, $uid)) {
                    $redirectHome('channel_limit');
                }
                if ($controller->add($name, $channelId, $url, $categoryId)) {
                    $redirectHome('channel_ok');
                }
                $redirectHome('channel_err');
            }
        }
        $redirectHome('channel_err');
    }

    if (isset($_POST['quick_add_video'])) {
        $url = trim($_POST['video_url'] ?? '');

        $extractVideoId = function ($u) {
            if (preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_\-]+)/', $u, $matches)) {
                return $matches[1];
            }
            return null;
        };

        $videoId = $extractVideoId($url);
        if (!$videoId) {
            $redirectHome('video_err');
        }

        $ytApi = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id={$videoId}&key=" . YOUTUBE_API_KEY;
        $ytJson = @file_get_contents($ytApi);
        $ytData = json_decode($ytJson, true);
        $item = $ytData['items'][0] ?? null;

        if (!$item) {
            $redirectHome('video_err');
        }

        $title = $item['snippet']['title'];
        $publishedAt = $item['snippet']['publishedAt'] ?? null;
        $thumbnailUrl = $item['snippet']['thumbnails']['default']['url'] ?? null;
        $channelName = $item['snippet']['channelTitle'] ?? null;
        $viewCount = $item['statistics']['viewCount'] ?? 0;
        $likeCount = $item['statistics']['likeCount'] ?? 0;
        $commentCount = $item['statistics']['commentCount'] ?? 0;
        $durationIso = $item['contentDetails']['duration'] ?? null;
        $duration = null;
        if ($durationIso) {
            try {
                $interval = new DateInterval($durationIso);
                $duration = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
            } catch (Exception $e) {
                $duration = null;
            }
        }

        $summary = '';
        if ($title && defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
            $ch = curl_init("https://api.openai.com/v1/chat/completions");
            $payload = [
                "model" => "gpt-3.5-turbo",
                "messages" => [
                    ["role" => "user", "content" => "請簡單摘要這段影片標題的內容：「{$title}」"]
                ],
                "temperature" => 0.7
            ];
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . OPENAI_API_KEY
                ],
                CURLOPT_POSTFIELDS => json_encode($payload)
            ]);
            $response = curl_exec($ch);
            $result = json_decode($response, true);
            curl_close($ch);
            $summary = $result['choices'][0]['message']['content'] ?? '';
        }

        $watchedAt = date('Y-m-d H:i:s');
        if ($videoController->add(
                $title,
                $url,
                $summary,
                $publishedAt,
                $viewCount,
                $likeCount,
                $commentCount,
                $thumbnailUrl,
                $channelName,
                $duration,
                1,
                $watchedAt
            )) {
            $redirectHome('video_ok');
        }
        $redirectHome('video_err');
    }
}

require_once __DIR__ . '/includes/dashboard_render.php';

/* =========================
   📊 KPI
========================= */
$st = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE user_id = ? AND is_watched = 0");
$st->execute([$uid]);
$unwatched = $st->fetchColumn();
$st = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE user_id = ? AND is_watched = 1");
$st->execute([$uid]);
$watched = $st->fetchColumn();
$st = $pdo->prepare("SELECT COUNT(*) FROM channels WHERE user_id = ?");
$st->execute([$uid]);
$channels = $st->fetchColumn();

$st = $pdo->prepare("
    SELECT SUM(duration) FROM videos 
    WHERE user_id = ? AND is_watched = 1 AND DATE(watched_at) = CURDATE()
");
$st->execute([$uid]);
$todaySeconds = $st->fetchColumn();

$todaySeconds = (int)$todaySeconds;
$todayTime = $todaySeconds >= 3600
    ? gmdate("H:i:s", $todaySeconds)
    : gmdate("i:s", $todaySeconds);

/* =========================
   📂 分類篩選（須先於待看／已看／已訂閱查詢）
========================= */
require_once __DIR__ . '/config/plan_limits.php';
$_maxVideosList = plan_limits_max_videos_per_list($pdo, $uid);
$quotaBannerText = plan_limits_quota_banner_text($pdo, $uid);

$rawCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$filterCategoryId = 0;
$filterCategoryName = null;
if ($rawCategoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    $filterCategoryId = FILTER_CATEGORY_UNCATEGORIZED;
    $filterCategoryName = '未分類';
} elseif ($rawCategoryId > 0) {
    $stmt = $pdo->prepare("SELECT name FROM channel_categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$rawCategoryId, $uid]);
    $filterCategoryName = $stmt->fetchColumn();
    if ($filterCategoryName === false) {
        $filterCategoryId = 0;
        $filterCategoryName = null;
    } else {
        $filterCategoryId = $rawCategoryId;
    }
}
$hasCategoryFilter = ($filterCategoryId !== 0);

if (!isset($_SESSION['dash_auto_load']) && $uid > 0) {
    try {
        $stmt = $pdo->prepare('SELECT COALESCE(dash_auto_load, 1) FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $d = $stmt->fetchColumn();
        $_SESSION['dash_auto_load'] = ($d !== false && (int)$d) ? 1 : 0;
    } catch (Exception $e) {
        $_SESSION['dash_auto_load'] = 1;
    }
}
$dashAutoLoadPref = (isset($_SESSION['dash_auto_load']) && (int)$_SESSION['dash_auto_load'] === 0) ? 0 : 1;

/* =========================
   🎬 待看 / 已看（dash_auto_load=1：首屏＋捲動載入；=0：一次載入至方案上限）
========================= */
$dashFeedPage = (int) DASH_FEED_PAGE_SIZE;
$videoCap = ($_maxVideosList === null) ? PHP_INT_MAX : (int) $_maxVideosList;
$fullVideoLimit = ($_maxVideosList === null) ? 999999 : (int) $_maxVideosList;
if ($dashAutoLoadPref === 0) {
    $dashVideoInitialLimit = (int) $fullVideoLimit;
} else {
    $dashVideoInitialLimit = min($dashFeedPage, $videoCap);
}

if ($filterCategoryId > 0) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 0 AND ch.category_id = ?
    ");
    $stmt->execute([$uid, $filterCategoryId]);
    $dashUnwatchedTotal = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 1 AND ch.category_id = ?
    ");
    $stmt->execute([$uid, $filterCategoryId]);
    $dashWatchedTotal = (int) $stmt->fetchColumn();

    $lim = (int) $dashVideoInitialLimit;
    $stmt = $pdo->prepare("
        SELECT v.* FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 0 AND ch.category_id = ?
        ORDER BY ch.name ASC, v.published_at DESC
        LIMIT {$lim}
    ");
    $stmt->execute([$uid, $filterCategoryId]);
    $latestVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT v.* FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 1 AND ch.category_id = ?
        ORDER BY ch.name ASC, COALESCE(v.watched_at, v.added_at) DESC
        LIMIT {$lim}
    ");
    $stmt->execute([$uid, $filterCategoryId]);
    $latestWatchedVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($filterCategoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 0 AND ch.category_id IS NULL
    ");
    $stmt->execute([$uid]);
    $dashUnwatchedTotal = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 1 AND ch.category_id IS NULL
    ");
    $stmt->execute([$uid]);
    $dashWatchedTotal = (int) $stmt->fetchColumn();

    $lim = (int) $dashVideoInitialLimit;
    $stmt = $pdo->prepare("
        SELECT v.* FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 0 AND ch.category_id IS NULL
        ORDER BY ch.name ASC, v.published_at DESC
        LIMIT {$lim}
    ");
    $stmt->execute([$uid]);
    $latestVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT v.* FROM videos v
        INNER JOIN channels ch ON v.channel_name COLLATE utf8mb4_unicode_ci = ch.name COLLATE utf8mb4_unicode_ci
            AND v.user_id = ch.user_id
        WHERE v.user_id = ? AND v.is_watched = 1 AND ch.category_id IS NULL
        ORDER BY ch.name ASC, COALESCE(v.watched_at, v.added_at) DESC
        LIMIT {$lim}
    ");
    $stmt->execute([$uid]);
    $latestWatchedVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE user_id = ? AND is_watched = 0");
    $stmt->execute([$uid]);
    $dashUnwatchedTotal = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE user_id = ? AND is_watched = 1");
    $stmt->execute([$uid]);
    $dashWatchedTotal = (int) $stmt->fetchColumn();

    $lim = (int) $dashVideoInitialLimit;
    $stmt = $pdo->prepare("
        SELECT * FROM videos
        WHERE user_id = ? AND is_watched = 0
        ORDER BY published_at DESC
        LIMIT {$lim}
    ");
    $stmt->execute([$uid]);
    $latestVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT * FROM videos
        WHERE user_id = ? AND is_watched = 1
        ORDER BY COALESCE(watched_at, added_at) DESC
        LIMIT {$lim}
    ");
    $stmt->execute([$uid]);
    $latestWatchedVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$dashUnwatchedMax = min($dashUnwatchedTotal, $videoCap);
$dashWatchedMax = min($dashWatchedTotal, $videoCap);
$dashUnwatchedLoaded = count($latestVideos);
$dashWatchedLoaded = count($latestWatchedVideos);
$dashMoreUnwatched = ($dashAutoLoadPref === 1) && ($dashUnwatchedLoaded < $dashUnwatchedMax);
$dashMoreWatched = ($dashAutoLoadPref === 1) && ($dashWatchedLoaded < $dashWatchedMax);

/* =========================
   📺 已訂閱頻道（Dashboard 區塊）
========================= */
$subscribedCountSql = 'SELECT COUNT(*) FROM channels c WHERE c.user_id = ? ';
if ($filterCategoryId > 0) {
    $subscribedCountSql .= ' AND c.category_id = ? ';
} elseif ($filterCategoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    $subscribedCountSql .= ' AND c.category_id IS NULL ';
}
$stmt = $pdo->prepare($subscribedCountSql);
if ($filterCategoryId > 0) {
    $stmt->execute([$uid, $filterCategoryId]);
} else {
    $stmt->execute([$uid]);
}
$dashSubscribedTotal = (int) $stmt->fetchColumn();

$limCh = $dashAutoLoadPref === 0 ? 999999 : (int) $dashFeedPage;
$subscribedSql = "
    SELECT c.id, c.name, c.url, c.thumbnail_url, c.subscriber_count, c.video_count, c.published_at,
           c.is_favorite, c.category_id, cc.name AS category_name
    FROM channels c
    LEFT JOIN channel_categories cc ON c.category_id = cc.id AND cc.user_id = c.user_id
    WHERE c.user_id = ?
";
if ($filterCategoryId > 0) {
    $subscribedSql .= " AND c.category_id = ? ";
} elseif ($filterCategoryId === FILTER_CATEGORY_UNCATEGORIZED) {
    $subscribedSql .= " AND c.category_id IS NULL ";
}
$subscribedSql .= " ORDER BY c.name ASC LIMIT {$limCh}";

if ($filterCategoryId > 0) {
    $stmt = $pdo->prepare($subscribedSql);
    $stmt->execute([$uid, $filterCategoryId]);
} else {
    $stmt = $pdo->prepare($subscribedSql);
    $stmt->execute([$uid]);
}
$subscribedChannels = $stmt->fetchAll(PDO::FETCH_ASSOC);
$dashSubscribedLoaded = count($subscribedChannels);
$dashMoreSubscribed = ($dashAutoLoadPref === 1) && ($dashSubscribedLoaded < $dashSubscribedTotal);

$dashTabKey = isset($_GET['dash_tab']) ? (string)$_GET['dash_tab'] : 'unwatched';
if (!in_array($dashTabKey, ['unwatched', 'watched', 'subscribed'], true)) {
    $dashTabKey = 'unwatched';
}

/* =========================
   ⭐ 我的最愛頻道（channels.is_favorite = 1）
========================= */
$stmt = $pdo->prepare("
    SELECT c.name, c.url, cc.name AS category_name,
           (SELECT COUNT(*) FROM videos v
            WHERE v.user_id = c.user_id
              AND v.channel_name COLLATE utf8mb4_unicode_ci = c.name COLLATE utf8mb4_unicode_ci
              AND v.is_watched = 0
           ) AS unwatched_count,
           (SELECT COUNT(*) FROM videos v
            WHERE v.user_id = c.user_id
              AND v.channel_name COLLATE utf8mb4_unicode_ci = c.name COLLATE utf8mb4_unicode_ci
              AND v.is_watched = 1
           ) AS watched_count
    FROM channels c
    LEFT JOIN channel_categories cc ON c.category_id = cc.id AND cc.user_id = c.user_id
    WHERE c.user_id = ? AND c.is_favorite = 1
    ORDER BY c.name ASC
    LIMIT 20
");
$stmt->execute([$uid]);
$favoriteChannels = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   📂 分類
========================= */
$stmt = $pdo->prepare("
    SELECT cc.*, COUNT(c.id) as total
    FROM channel_categories cc
    LEFT JOIN channels c ON cc.id = c.category_id AND c.user_id = cc.user_id
    WHERE cc.user_id = ?
    GROUP BY cc.id
    ORDER BY cc.sort_order ASC
");
$stmt->execute([$uid]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT COUNT(*) FROM channels WHERE user_id = ? AND category_id IS NULL');
$stmt->execute([$uid]);
$uncategorizedChannelCount = (int) $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<style>
body {
    font-family: Arial, "Segoe UI", system-ui, "PingFang TC", "Microsoft JhengHei", sans-serif;
    margin: 0;
    min-height: 100vh;
    box-sizing: border-box;
    padding: 30px;
    color: #0f172a;
    background-color: #f1f5f9;
    background-image:
        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2394a3b8' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E"),
        radial-gradient(ellipse 90% 70% at 100% 0%, rgba(59, 130, 246, 0.14), transparent 55%),
        radial-gradient(ellipse 70% 55% at 0% 100%, rgba(14, 165, 233, 0.1), transparent 50%),
        linear-gradient(165deg, #f8fafc 0%, #f1f5f9 45%, #eef2f7 100%);
}

/* KPI + 快速操作（橫列：左直向 KPI、右快速操作） */
.dash-top-row {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    gap: 20px;
    margin-bottom: 25px;
}
.cards {
    display: flex;
    gap: 20px;
}
.cards.cards--vertical {
    flex: 0 0 auto;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 0;
}
.quick-actions-wrap {
    flex: 1;
    min-width: 0;
    margin-bottom: 0 !important;
}
@media (max-width: 720px) {
    .dash-top-row {
        flex-direction: column;
    }
    .cards.cards--vertical {
        flex-direction: column;
        width: 100%;
    }
}
.card {
    flex: 1;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}
.cards--vertical .card {
    flex: 0 0 auto;
    min-width: 7rem;
    padding: 10px 14px;
}
.cards--vertical .card h2 {
    margin: 0;
    font-size: 1.35rem;
    line-height: 1.2;
}
.cards--vertical .card p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #888;
}
.card--kpi-unwatched .kpi-clear-unwatched-btn {
    margin-top: 8px;
    width: 100%;
    box-sizing: border-box;
    padding: 5px 6px;
    font-size: 11px;
    border: 1px solid #fecaca;
    border-radius: 6px;
    background: #fef2f2;
    color: #b91c1c;
    cursor: pointer;
    font-family: inherit;
    line-height: 1.2;
}
.card--kpi-unwatched .kpi-clear-unwatched-btn:hover:not(:disabled) {
    background: #fee2e2;
}
.card--kpi-unwatched .kpi-clear-unwatched-btn:disabled {
    opacity: 0.6;
    cursor: wait;
}
.card.card--fetch {
    padding: 0;
    overflow: hidden;
}
.card--fetch .kpi-fetch-btn {
    display: block;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: bold;
    color: #fff;
    background: #0077cc;
    text-align: center;
    text-decoration: none;
    font-family: inherit;
}
.card--fetch .kpi-fetch-btn:hover {
    background: #005fa3;
    color: #fff;
}
.card h2 { margin: 0; }
.card p { color: #888; }

/* layout */
.grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
}

.section {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
}

/* 影片 */
.video {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}
.video .video-text a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
}
.video-media {
    position: relative;
    width: 120px;
    flex-shrink: 0;
    border-radius: 6px;
    overflow: hidden;
    align-self: flex-start;
}
.video-thumb-link {
    display: block;
    line-height: 0;
}
.video-media img {
    width: 100%;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    display: block;
    vertical-align: top;
}
.video-thumb-placeholder {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: linear-gradient(145deg, #e8ecf0, #dde3ea);
}
.video-thumb-link--empty {
    text-decoration: none;
}
.video-thumb-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.82);
    color: #f1f5f9;
    font-size: 10px;
    line-height: 1.35;
    padding: 6px 6px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 4px;
    text-align: left;
    opacity: 0;
    transition: opacity 0.2s ease;
    pointer-events: none;
}
.video-media:hover .video-thumb-overlay {
    opacity: 1;
}
.video-thumb-overlay-main {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    justify-content: center;
    min-height: 0;
}
.video-thumb-overlay-actions {
    align-self: flex-end;
    margin-top: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: flex-end;
    pointer-events: auto;
}
.video-thumb-btn {
    border: none;
    cursor: pointer;
    font-size: 11px;
    line-height: 1;
    padding: 5px 6px;
    border-radius: 5px;
    font-family: inherit;
    background: rgba(255, 255, 255, 0.15);
    color: #f8fafc;
}
.video-thumb-btn:hover {
    background: rgba(255, 255, 255, 0.28);
}
.video-thumb-btn--del:hover {
    background: rgba(239, 68, 68, 0.45);
}
.video-thumb-stat {
    display: flex;
    align-items: flex-start;
    gap: 4px;
}
.video-thumb-stat-label {
    flex: 0 0 auto;
    color: #94a3b8;
}
.video-thumb-stat-value {
    flex: 1;
    min-width: 0;
    word-break: break-word;
}

.video-channel-group {
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e8ecf0;
}
.video-channel-group:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.video-channel-title {
    margin: 0 0 8px;
    font-size: 14px;
    color: #0077cc;
    font-weight: bold;
    line-height: 1.3;
}

.section-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}
.section-head h3 { margin: 0; }

.section-head--category {
    margin-bottom: 12px;
}
.category-head-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    box-sizing: border-box;
    gap: 10px;
    padding: 10px 14px;
    font-size: 16px;
    font-weight: bold;
    font-family: inherit;
    line-height: 1.3;
    text-align: left;
}
.category-head-btn__label {
    flex: 1;
    min-width: 0;
}
.category-head-btn__edit {
    flex-shrink: 0;
    font-size: 13px;
    font-weight: normal;
    opacity: 0.9;
}

.section-head-video-tools {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}
.dash-video-search {
    min-width: 180px;
    max-width: 280px;
    flex: 1 1 180px;
    padding: 8px 12px;
    font-size: 14px;
    font-family: inherit;
    border: 1px solid #cce0f0;
    border-radius: 8px;
    background: #fff;
    box-sizing: border-box;
}
.dash-video-search::placeholder { color: #999; }
.dash-video-search:focus {
    outline: none;
    border-color: #0077cc;
    box-shadow: 0 0 0 2px rgba(0,119,204,0.15);
}

.video-tab-toggle {
    display: inline-flex;
    flex-wrap: wrap;
    border: 1px solid #cce0f0;
    border-radius: 8px;
    overflow: hidden;
    background: #f0f6fb;
}
.video-tab {
    border: none;
    background: transparent;
    color: #333;
    padding: 8px 14px;
    font-size: 14px;
    cursor: pointer;
    font-family: inherit;
}
.video-tab:hover { background: rgba(0,119,204,0.08); }
.video-tab.active {
    background: #0077cc;
    color: #fff;
}
.video-tab.active:hover { background: #0066b3; }

.video-empty {
    color: #888;
    font-size: 14px;
    margin: 0;
}

.dash-feed-sentinel {
    height: 1px;
    margin: 0;
    padding: 0;
    pointer-events: none;
}
.dash-feed-loading {
    text-align: center;
    color: #64748b;
    font-size: 13px;
    padding: 10px 8px;
    margin: 0;
}
.dash-feed-end {
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
    padding: 8px;
    margin: 0;
}
.dash-load-more-row {
    text-align: center;
    margin: 12px 0 0;
}
.dash-load-more-row .btn-outline {
    background: #fff;
    border: 1px solid #cce0f0;
    color: #0077cc;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-family: inherit;
}
.dash-load-more-row .btn-outline:hover {
    background: #f0f6fb;
}

/* 已訂閱頻道：圖上名下、多欄填滿 */
.subscribed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 16px 12px;
}
.channel-card {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    text-align: center;
    min-width: 0;
}
.channel-card-thumb {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
    background: #e8ecf0;
    display: block;
}
.channel-card-thumb--empty {
    background: linear-gradient(145deg, #e8ecf0, #dde3ea);
}
.channel-card-body {
    margin-top: 8px;
    min-height: 2.6em;
}
.channel-card-name {
    display: block;
    font-size: 13px;
    font-weight: bold;
    line-height: 1.35;
    color: #333;
    text-decoration: none;
    word-break: break-word;
}
.channel-card-name:hover { color: #0077cc; }
.channel-card-cat {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #888;
    line-height: 1.3;
}
.channel-card-category-select--overlay {
    flex: 1 1 auto;
    min-width: 0;
    max-width: 100%;
    font-size: 11px;
    line-height: 1.2;
    padding: 5px 4px;
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 6px;
    background: rgba(15, 23, 42, 0.65);
    color: #f8fafc;
    font-family: inherit;
    cursor: pointer;
    pointer-events: auto;
}
.channel-card-category-select--overlay:focus {
    outline: 2px solid rgba(56, 189, 248, 0.6);
    outline-offset: 1px;
}
.channel-card-category-select--overlay:disabled {
    opacity: 0.55;
    cursor: wait;
}
.channel-card-category-select--overlay option {
    color: #0f172a;
    background: #fff;
}

.channel-card-media {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}
.channel-card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.82);
    color: #f1f5f9;
    font-size: 11px;
    line-height: 1.45;
    padding: 10px 8px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 6px;
    text-align: left;
    opacity: 0;
    transition: opacity 0.2s ease;
    pointer-events: none;
}
.channel-card-media:hover .channel-card-overlay {
    opacity: 1;
}
.channel-card-overlay-main {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
    justify-content: center;
    min-height: 0;
}
.channel-card-overlay-actions {
    align-self: stretch;
    margin-top: auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    justify-content: flex-start;
    pointer-events: auto;
}
.channel-card-overlay-actions .channel-card-btn {
    flex: 0 0 auto;
}
.channel-card-btn {
    border: none;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
    padding: 6px 8px;
    border-radius: 6px;
    font-family: inherit;
    background: rgba(255, 255, 255, 0.15);
    color: #f8fafc;
}
.channel-card-btn:hover {
    background: rgba(255, 255, 255, 0.28);
}
.channel-card-btn--fav.channel-card-btn--on {
    background: rgba(234, 179, 8, 0.35);
}
.channel-card-btn--del:hover {
    background: rgba(239, 68, 68, 0.45);
}
.channel-card-stat {
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.channel-card-stat-label {
    flex: 0 0 auto;
    color: #94a3b8;
}
.channel-card-stat-value {
    flex: 1;
    min-width: 0;
    word-break: break-word;
}

/* 按鈕 */
.btn {
    background: #0077cc;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
}
.btn:hover { background: #005fa3; }

.quick-notice {
    padding: 10px 12px;
    border-radius: 8px;
    margin: 0 0 14px;
    font-size: 14px;
}
.quick-notice--ok {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.quick-notice--err {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.quick-forms {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}
.quick-form-block h4 {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: bold;
}
.quick-form-block label {
    display: block;
    font-size: 13px;
    color: #555;
    margin-bottom: 4px;
}
.quick-form-block input[type="text"],
.quick-form-block select {
    width: 100%;
    max-width: 480px;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 14px;
}
.quick-form-block .form-row { margin-bottom: 10px; }
.quick-form-block button[type="submit"] {
    background: #0077cc;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}
.quick-form-block button[type="submit"]:hover { background: #0066b3; }
.quick-form-hint {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
}

/* 分類 */
.category {
    display: inline-block;
    background: #eee;
    color: #333;
    padding: 6px 10px;
    margin: 5px;
    border-radius: 6px;
    text-decoration: none;
}
.category:hover { background: #e0e0e0; }
.category.category--active {
    background: #0077cc;
    color: #fff;
}
.category.category--active:hover { background: #0066b3; }

.category-filter-banner {
    font-size: 14px;
    color: #475569;
    margin: 0 0 14px;
    padding: 10px 12px;
    background: #f1f5f9;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.category-filter-banner strong { color: #0f172a; }
.category-filter-clear {
    margin-left: 10px;
    font-size: 13px;
    color: #0077cc;
    text-decoration: none;
}
.category-filter-clear:hover { text-decoration: underline; }

/* 右側分類標籤（可編輯／拖曳） */
.category-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-start;
}
.category-item {
    display: inline-block;
    vertical-align: top;
}
.category-item--system {
    cursor: default;
}
.category-tags--edit .category-item--system {
    cursor: default;
    transform: none;
    box-shadow: none;
}
.category-tags--edit .category-item--system .category--nav {
    display: inline !important;
}
.category-tags--edit .category-item {
    cursor: grab;
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    border-radius: 8px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.category-tags--edit .category-item:active {
    cursor: grabbing;
}
.category-item.dragging {
    opacity: 0.55;
    transform: scale(0.98);
}
.category--editor {
    display: none;
    align-items: center;
    gap: 6px;
    background: #eef6fc;
    border: 1px dashed #0077cc;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 14px;
}
.category-tags--edit .category--nav {
    display: none !important;
}
.category-tags--edit .category--editor {
    display: inline-flex !important;
}
.category-editor-grip {
    color: #64748b;
    font-size: 12px;
    user-select: none;
    line-height: 1;
}
.category-editor-input {
    width: 6.5em;
    min-width: 4em;
    max-width: 12em;
    padding: 4px 6px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 13px;
    font-family: inherit;
}
.category-editor-meta {
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
}
.category-delete-btn {
    display: none;
    border: none;
    background: transparent;
    color: #94a3b8;
    font-size: 18px;
    line-height: 1;
    padding: 0 4px;
    cursor: pointer;
    font-family: inherit;
    border-radius: 4px;
}
.category-delete-btn:hover {
    color: #b91c1c;
    background: #fef2f2;
}
.category-tags--edit .category-delete-btn {
    display: inline-block;
}
.category-add-row {
    display: none;
    width: 100%;
    flex-basis: 100%;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}
.category-tags--edit .category-add-row {
    display: flex;
}
.category-add-row input {
    flex: 1 1 140px;
    min-width: 120px;
    max-width: 280px;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
}
.category-add-row .btn-outline {
    flex-shrink: 0;
}

.btn-outline {
    background: #fff;
    color: #0077cc;
    border: 1px solid #0077cc;
}
.btn-outline:hover {
    background: #f0f7fc;
}
.btn-outline.is-active {
    background: #0077cc;
    color: #fff;
}
.btn-outline.is-active:hover {
    background: #0066b3;
}

/* 頻道 */
.channel {
    margin-bottom: 8px;
}
.fav-channel-row .fav-channel-meta {
    font-size: 12px;
    color: #666;
    margin-left: 4px;
}
.site-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}
.site-header h1 { margin: 0; font-size: 1.5rem; }
.site-user { font-size: 14px; color: #555; }
.site-user a { color: #0077cc; margin-left: 12px; }
</style>

</head>
<body>

<header class="site-header">
    <h1>🎬 YouTube Dashboard</h1>
    <div class="site-user">
        <?= htmlspecialchars($currentAuthUser['name'] !== '' ? $currentAuthUser['name'] : $currentAuthUser['email']) ?>
        <span style="color:#999;">(<?= htmlspecialchars($currentAuthUser['email']) ?>)</span>
        <a href="index.php?page=account">會員中心</a>
        <a href="index.php?page=support">客服</a>
        <?php if (auth_is_admin()): ?>
        <a href="index.php?page=admin">後台會員</a>
        <?php endif; ?>
        <?php if (auth_can_test_lab()): ?>
        <a href="index.php?page=test_lab">測試</a>
        <?php endif; ?>
        <a href="index.php?page=logout">登出</a>
    </div>
</header>

<?php if ($quotaBannerText !== ''): ?>
<p style="font-size:13px;color:#64748b;margin:0 0 16px;"><?= htmlspecialchars($quotaBannerText) ?></p>
<?php endif; ?>

<div class="dash-top-row">
<!-- KPI（左側直向） -->
<div class="cards cards--vertical" aria-label="統計摘要">
    <div class="card card--kpi-unwatched">
        <h2><?= (int)$unwatched ?></h2>
        <p>📋 未看</p>
        <?php if ((int)$unwatched > 0): ?>
            <button type="button" class="kpi-clear-unwatched-btn" id="btnKpiClearUnwatched" title="刪除帳號內全部待看（未看）影片">全部刪除</button>
        <?php endif; ?>
    </div>
    <div class="card"><h2><?= $watched ?></h2><p>✅ 已看</p></div>
    <div class="card"><h2><?= $channels ?></h2><p>📺 頻道</p></div>
    <div class="card"><h2><?= $todayTime ?></h2><p>⏱ 今日觀看</p></div>
    <div class="card card--fetch">
        <a class="kpi-fetch-btn" href="scripts/fetch_new_videos.php" target="_blank" rel="noopener noreferrer">📡 抓新影片</a>
    </div>
</div>

<!-- 快速操作 -->
<div class="section quick-actions-wrap">
    <h3>⚡ 快速操作</h3>

    <?php if ($quickNotice): ?>
        <?php
        $noticeTexts = [
            'channel_ok' => '✅ 頻道已成功新增。',
            'channel_err' => '⚠️ 無法新增頻道（頻道已存在、網址格式錯誤或無法從 YouTube 取得資料）。',
            'channel_limit' => '',
            'video_ok' => '✅ 影片已加入已看清單。',
            'video_err' => '⚠️ 無法新增影片（連結無效、影片已存在或無法取得影片資訊）。',
        ];
        if ($quickNotice === 'channel_limit') {
            $mc = plan_limits_max_channels($pdo, $uid);
            $noticeTexts['channel_limit'] = '⚠️ 您目前方案最多 ' . (int)$mc . ' 個頻道，請刪除部分頻道或升級方案。';
        }
        $noticeOk = in_array($quickNotice, ['channel_ok', 'video_ok'], true);
        ?>
        <p class="quick-notice <?= $noticeOk ? 'quick-notice--ok' : 'quick-notice--err' ?>">
            <?= htmlspecialchars($noticeTexts[$quickNotice] ?? '') ?>
        </p>
    <?php endif; ?>

    <div class="quick-forms">
        <div class="quick-form-block">
            <h4>➕ 新增頻道</h4>
            <form method="post" action="index.php">
                <input type="hidden" name="home_category_id" value="<?= $hasCategoryFilter ? (int)$filterCategoryId : '' ?>">
                <input type="hidden" name="home_dash_tab" value="<?= htmlspecialchars($dashTabKey) ?>">
                <div class="form-row">
                    <label for="channel_input">頻道網址 / @handle / Channel ID</label>
                    <input type="text" name="channel_input" id="channel_input" placeholder="例如：https://www.youtube.com/@handle 或 @handle" required autocomplete="off">
                </div>
                <div class="form-row">
                    <label for="channel_category_id">分類（選填）</label>
                    <select name="channel_category_id" id="channel_category_id">
                        <option value="">未指定</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"<?= ($filterCategoryId > 0 && (int)$cat['id'] === $filterCategoryId) ? ' selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="quick_add_channel" value="1">新增頻道</button>
            </form>
            <p class="quick-form-hint">與頻道管理頁相同：支援網址、@帳號或 UC開頭的頻道 ID。</p>
        </div>

        <div class="quick-form-block">
            <h4>➕ 新增影片</h4>
            <form method="post" action="index.php">
                <input type="hidden" name="home_category_id" value="<?= $hasCategoryFilter ? (int)$filterCategoryId : '' ?>">
                <input type="hidden" name="home_dash_tab" value="<?= htmlspecialchars($dashTabKey) ?>">
                <div class="form-row">
                    <label for="video_url">YouTube 影片網址</label>
                    <input type="text" name="video_url" id="video_url" placeholder="https://www.youtube.com/watch?v=…" required autocomplete="off">
                </div>
                <button type="submit" name="quick_add_video" value="1">加入已看清單</button>
            </form>
            <p class="quick-form-hint">會透過 API 取得資訊並加入「已看」；與舊版<a href="index.php?page=add">新增影片頁</a>相同邏輯。</p>
        </div>
    </div>
</div>
</div>

<div class="grid">

<!-- 左側欄：我的最愛、分類 -->
<div>

    <!-- 我的最愛頻道 -->
    <div class="section">
        <h3>⭐ 我的最愛頻道</h3>
        <?php if (empty($favoriteChannels)): ?>
            <p class="video-empty">尚無最愛頻道。請在「📺 已訂閱」頻道卡游標移到圖片上，於右下角按「☆ 最愛」；亦可至 <a href="index.php?page=channels">頻道管理</a> 操作。</p>
        <?php else: ?>
            <?php foreach ($favoriteChannels as $c): ?>
                <?php
                $favUw = (int)($c['unwatched_count'] ?? 0);
                $favWd = (int)($c['watched_count'] ?? 0);
                $favCat = isset($c['category_name']) && $c['category_name'] !== '' ? $c['category_name'] : '未分類';
                ?>
                <div class="channel fav-channel-row">
                    <a href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($c['name']) ?></a>
                    <span class="fav-channel-meta">
                        （ <?= $favUw ?> /  <?= $favWd ?> / <?= htmlspecialchars($favCat) ?>）
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 分類 -->
    <div class="section">
        <div class="section-head section-head--category" style="margin-top:0;">
            <button type="button" class="btn btn-outline category-head-btn" id="btnCategoryTagEdit" aria-pressed="false" title="分類：點此切換編輯（拖曳排序、改名、新增或刪除標籤）">
                <span class="category-head-btn__label">📂 分類</span>
                <span class="category-head-btn__edit">✏️ 編輯</span>
            </button>
        </div>
        <p class="video-empty" style="margin:0 0 10px;font-size:13px;"><strong>未分類</strong>為固定篩選（非資料庫標籤），僅顯示尚未指定分類的頻道。點上方 <strong>📂 分類</strong> 列的 <strong>✏️ 編輯</strong> 可切換編輯模式，拖曳排序、改名、<strong>新增</strong>或<strong>刪除</strong>自訂分類；刪除分類時，該分類內的頻道將改為<strong>未分類</strong>。</p>
        <div class="category-tags" id="categoryTags">
            <div class="category-item category-item--system" draggable="false">
                <a class="category category--nav<?= $filterCategoryId === FILTER_CATEGORY_UNCATEGORIZED ? ' category--active' : '' ?>" href="index.php?<?= http_build_query(['category_id' => FILTER_CATEGORY_UNCATEGORIZED, 'dash_tab' => $dashTabKey]) ?>">
                    未分類 (<?= (int)$uncategorizedChannelCount ?>)
                </a>
            </div>
            <div class="category-add-row" id="categoryAddRow">
                <input type="text" id="categoryNewNameInput" maxlength="100" placeholder="新分類名稱" autocomplete="off" aria-label="新分類名稱">
                <button type="button" class="btn btn-outline" id="btnCategoryAdd">➕ 新增標籤</button>
            </div>
            <?php foreach ($categories as $c): ?>
                <?php
                $cid = (int)$c['id'];
                $ctotal = (int)$c['total'];
                $isActive = ($filterCategoryId > 0 && $cid === $filterCategoryId);
                ?>
                <div class="category-item" data-id="<?= $cid ?>" data-name="<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>" data-total="<?= $ctotal ?>" draggable="false">
                    <a class="category category--nav<?= $isActive ? ' category--active' : '' ?>" href="index.php?<?= http_build_query(['category_id' => $cid, 'dash_tab' => $dashTabKey]) ?>">
                        <?= htmlspecialchars($c['name']) ?> (<?= $ctotal ?>)
                    </a>
                    <div class="category category--editor">
                        <span class="category-editor-grip" title="拖曳排序">⠿</span>
                        <input type="text" class="category-editor-input" value="<?= htmlspecialchars($c['name']) ?>" maxlength="100" aria-label="分類名稱">
                        <span class="category-editor-meta">(<?= $ctotal ?>)</span>
                        <button type="button" class="category-delete-btn" data-category-delete="<?= $cid ?>" title="刪除此分類">×</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- 右側欄：最新影片 -->
<div class="section">
    <div class="section-head">
        <h3>🎬 最新影片</h3>
        <div class="section-head-video-tools">
            <div class="video-tab-toggle" role="tablist" aria-label="待看、已看與已訂閱頻道">
                <button type="button" class="video-tab<?= $dashTabKey === 'unwatched' ? ' active' : '' ?>" role="tab" aria-selected="<?= $dashTabKey === 'unwatched' ? 'true' : 'false' ?>" data-panel="unwatched" id="tab-unwatched">📋 待看</button>
                <button type="button" class="video-tab<?= $dashTabKey === 'watched' ? ' active' : '' ?>" role="tab" aria-selected="<?= $dashTabKey === 'watched' ? 'true' : 'false' ?>" data-panel="watched" id="tab-watched">✅ 已看</button>
                <button type="button" class="video-tab<?= $dashTabKey === 'subscribed' ? ' active' : '' ?>" role="tab" aria-selected="<?= $dashTabKey === 'subscribed' ? 'true' : 'false' ?>" data-panel="subscribed" id="tab-subscribed">📺 已訂閱</button>
            </div>
            <input type="search" id="dash-video-search" class="dash-video-search" placeholder="搜尋標題或頻道名稱…" autocomplete="off" aria-label="搜尋目前分頁：影片標題或頻道名稱">
        </div>
    </div>

    <?php if ($hasCategoryFilter && $filterCategoryName): ?>
        <p class="category-filter-banner" style="margin-bottom:14px;">
            目前<?= $filterCategoryId === FILTER_CATEGORY_UNCATEGORIZED ? '篩選' : '分類' ?>：<strong><?= htmlspecialchars($filterCategoryName) ?></strong>
            （待看／已看／已訂閱皆套用）
            <a class="category-filter-clear" href="index.php?<?= http_build_query(['dash_tab' => $dashTabKey]) ?>">顯示全部</a>
        </p>
    <?php endif; ?>

    <div id="panel-unwatched" class="video-panel" role="tabpanel" aria-labelledby="tab-unwatched"<?= $dashTabKey !== 'unwatched' ? ' hidden' : '' ?>>
        <?php if ($dashUnwatchedTotal < 1): ?>
            <p class="video-empty"><?= $hasCategoryFilter ? '此篩選下尚無待看影片。' : '目前沒有待看影片。' ?></p>
        <?php else: ?>
            <div class="dash-feed-inner dash-feed-inner--videos"
                 data-dash-feed="unwatched"
                 data-dash-offset="<?= (int)$dashUnwatchedLoaded ?>"
                 data-dash-total="<?= (int)$dashUnwatchedMax ?>"
                 data-dash-more="<?= $dashMoreUnwatched ? '1' : '0' ?>"
                 data-dash-category="<?= (int)$filterCategoryId ?>">
                <?= render_dashboard_video_rows_flat($latestVideos, 'unwatched') ?>
            </div>
            <div class="dash-feed-sentinel dash-feed-sentinel--unwatched" aria-hidden="true"<?= $dashMoreUnwatched ? '' : ' hidden' ?>></div>
            <p class="dash-feed-loading" id="dash-feed-loading-unwatched" hidden>載入中…</p>
            <p class="dash-feed-end" id="dash-feed-end-unwatched"<?= $dashMoreUnwatched ? ' hidden' : '' ?>>已顯示全部</p>
            <?php if ($dashMoreUnwatched): ?>
                <p class="dash-load-more-row" data-dash-tab="unwatched" hidden>
                    <button type="button" class="btn btn-outline dash-load-more-btn" data-dash-tab="unwatched">載入更多</button>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="panel-watched" class="video-panel" role="tabpanel" aria-labelledby="tab-watched"<?= $dashTabKey !== 'watched' ? ' hidden' : '' ?>>
        <?php if ($dashWatchedTotal < 1): ?>
            <p class="video-empty"><?= $hasCategoryFilter ? '此篩選下尚無已看影片。' : '目前沒有已看影片。' ?></p>
        <?php else: ?>
            <div class="dash-feed-inner dash-feed-inner--videos"
                 data-dash-feed="watched"
                 data-dash-offset="<?= (int)$dashWatchedLoaded ?>"
                 data-dash-total="<?= (int)$dashWatchedMax ?>"
                 data-dash-more="<?= $dashMoreWatched ? '1' : '0' ?>"
                 data-dash-category="<?= (int)$filterCategoryId ?>">
                <?= render_dashboard_video_rows_flat($latestWatchedVideos, 'watched') ?>
            </div>
            <div class="dash-feed-sentinel dash-feed-sentinel--watched" aria-hidden="true"<?= $dashMoreWatched ? '' : ' hidden' ?>></div>
            <p class="dash-feed-loading" id="dash-feed-loading-watched" hidden>載入中…</p>
            <p class="dash-feed-end" id="dash-feed-end-watched"<?= $dashMoreWatched ? ' hidden' : '' ?>>已顯示全部</p>
            <?php if ($dashMoreWatched): ?>
                <p class="dash-load-more-row" data-dash-tab="watched" hidden>
                    <button type="button" class="btn btn-outline dash-load-more-btn" data-dash-tab="watched">載入更多</button>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="panel-subscribed" class="video-panel" role="tabpanel" aria-labelledby="tab-subscribed"<?= $dashTabKey !== 'subscribed' ? ' hidden' : '' ?>>
        <?php if ($dashSubscribedTotal < 1): ?>
            <?php if ($hasCategoryFilter): ?>
                <p class="video-empty">此篩選下尚無已訂閱頻道。<a href="index.php?<?= http_build_query(['dash_tab' => $dashTabKey]) ?>">顯示全部</a> 或 <a href="index.php?page=channels">頻道管理</a></p>
            <?php else: ?>
                <p class="video-empty">尚未加入任何頻道。<a href="index.php?page=channels">前往頻道管理</a></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="dash-feed-inner dash-feed-inner--subscribed"
                 data-dash-feed="subscribed"
                 data-dash-offset="<?= (int)$dashSubscribedLoaded ?>"
                 data-dash-total="<?= (int)$dashSubscribedTotal ?>"
                 data-dash-more="<?= $dashMoreSubscribed ? '1' : '0' ?>"
                 data-dash-category="<?= (int)$filterCategoryId ?>">
                <div class="subscribed-grid">
                    <?php foreach ($subscribedChannels as $ch): ?>
                        <?php render_dashboard_channel_card($ch, $categories); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="dash-feed-sentinel dash-feed-sentinel--subscribed" aria-hidden="true"<?= $dashMoreSubscribed ? '' : ' hidden' ?>></div>
            <p class="dash-feed-loading" id="dash-feed-loading-subscribed" hidden>載入中…</p>
            <p class="dash-feed-end" id="dash-feed-end-subscribed"<?= $dashMoreSubscribed ? ' hidden' : '' ?>>已顯示全部</p>
            <?php if ($dashMoreSubscribed): ?>
                <p class="dash-load-more-row" data-dash-tab="subscribed" hidden>
                    <button type="button" class="btn btn-outline dash-load-more-btn" data-dash-tab="subscribed">載入更多</button>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
(function () {
    var tabs = document.querySelectorAll('.video-tab');
    var panels = document.querySelectorAll('.video-panel');
    var searchInput = document.getElementById('dash-video-search');
    if (!tabs.length || !panels.length) return;

    function norm(s) {
        return (s || '').trim().toLowerCase();
    }

    function txt(el) {
        return el ? (el.textContent || '').trim() : '';
    }

    function filterVideoPanel(panelId) {
        var panel = document.getElementById(panelId);
        if (!panel || panel.hidden) return;
        var q = searchInput ? norm(searchInput.value) : '';
        if (panelId === 'panel-subscribed') {
            panel.querySelectorAll('.channel-card').forEach(function (card) {
                var nameEl = card.querySelector('.channel-card-name');
                var blob = norm(txt(nameEl));
                var show = !q || blob.indexOf(q) !== -1;
                card.style.display = show ? '' : 'none';
            });
            return;
        }
        var inner = panel.querySelector('.dash-feed-inner--videos');
        if (!inner) return;
        inner.querySelectorAll('.video').forEach(function (v) {
            var show = !q || norm(txt(v)).indexOf(q) !== -1;
            v.style.display = show ? '' : 'none';
        });
    }

    function applyDashVideoSearch() {
        panels.forEach(function (p) {
            filterVideoPanel(p.id);
        });
    }

    function syncCategoryLinksDashTab(key) {
        document.querySelectorAll('a.category--nav[href*="category_id="]').forEach(function (a) {
            try {
                var u = new URL(a.href, window.location.origin);
                u.searchParams.set('dash_tab', key);
                a.href = u.pathname + '?' + u.searchParams.toString();
            } catch (e) {}
        });
    }

    function switchPanel(key) {
        tabs.forEach(function (b) {
            var on = b.getAttribute('data-panel') === key;
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(function (p) {
            p.hidden = p.id !== 'panel-' + key;
        });
        var params = new URLSearchParams(window.location.search);
        params.set('dash_tab', key);
        var qs = params.toString();
        if (qs) {
            history.replaceState(null, '', window.location.pathname + '?' + qs);
        }
        syncCategoryLinksDashTab(key);
        applyDashVideoSearch();
    }

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            switchPanel(btn.getAttribute('data-panel'));
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyDashVideoSearch);
    }

    var active = document.querySelector('.video-tab.active');
    if (active) {
        syncCategoryLinksDashTab(active.getAttribute('data-panel'));
    }
    applyDashVideoSearch();
    window.dashApplySearch = applyDashVideoSearch;
})();
</script>

<script>window.YT_DASH_AUTO_LOAD = <?= (int)$dashAutoLoadPref ?>;</script>
<script>
(function () {
    var feedApi = 'scripts/dashboard_feed_api.php';
    var searchInput = document.getElementById('dash-video-search');
    var loading = {};

    function getAutoLoad() {
        var v = window.YT_DASH_AUTO_LOAD;
        if (v === undefined || v === null) return true;
        return String(v) !== '0' && v !== 0;
    }

    function searchBlocksFeed() {
        return searchInput && searchInput.value.trim() !== '';
    }

    function syncAutoLoadUi() {
        var on = getAutoLoad();
        document.querySelectorAll('.dash-feed-sentinel').forEach(function (s) {
            if (s.hasAttribute('hidden')) return;
            s.style.display = on ? '' : 'none';
        });
        document.querySelectorAll('.dash-load-more-row').forEach(function (row) {
            var tab = row.getAttribute('data-dash-tab');
            var panel = tab ? document.getElementById('panel-' + tab) : null;
            var inner = panel && panel.querySelector('.dash-feed-inner');
            var more = inner && inner.getAttribute('data-dash-more') === '1';
            var show = !on && more;
            row.hidden = !show;
        });
    }

    syncAutoLoadUi();

    function runFetch(tab, sentinel) {
        if (loading[tab]) return;
        var panel = document.getElementById('panel-' + tab);
        if (!panel || panel.hidden) return;
        var inner = panel.querySelector('.dash-feed-inner');
        if (!inner || inner.getAttribute('data-dash-more') !== '1') return;
        if (searchBlocksFeed()) return;

        loading[tab] = true;
        var offset = parseInt(inner.getAttribute('data-dash-offset'), 10) || 0;
        var cat = parseInt(inner.getAttribute('data-dash-category'), 10) || 0;
        var loadEl = document.getElementById('dash-feed-loading-' + tab);
        var endEl = document.getElementById('dash-feed-end-' + tab);
        if (loadEl) loadEl.hidden = false;

        var url = feedApi + '?tab=' + encodeURIComponent(tab) + '&offset=' + offset + '&category_id=' + cat;
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (loadEl) loadEl.hidden = true;
                loading[tab] = false;
                if (!data || !data.ok) return;

                if (data.html) {
                    if (tab === 'subscribed') {
                        var grid = inner.querySelector('.subscribed-grid');
                        if (grid) grid.insertAdjacentHTML('beforeend', data.html);
                    } else {
                        inner.insertAdjacentHTML('beforeend', data.html);
                    }
                }
                inner.setAttribute('data-dash-offset', String(data.next_offset != null ? data.next_offset : offset));
                inner.setAttribute('data-dash-more', data.has_more ? '1' : '0');
                if (!data.has_more) {
                    if (sentinel) sentinel.hidden = true;
                    if (endEl) endEl.hidden = false;
                }
                syncAutoLoadUi();
                if (window.dashApplySearch) window.dashApplySearch();
            })
            .catch(function () {
                if (loadEl) loadEl.hidden = true;
                loading[tab] = false;
            });
    }

    document.querySelectorAll('.dash-feed-sentinel').forEach(function (sentinel) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                if (!getAutoLoad()) return;
                var panel = sentinel.closest('.video-panel');
                if (!panel || panel.hidden) return;
                var inner = panel.querySelector('.dash-feed-inner');
                if (!inner || inner.getAttribute('data-dash-more') !== '1') return;
                var tab = inner.getAttribute('data-dash-feed');
                if (!tab) return;
                runFetch(tab, sentinel);
            });
        }, { root: null, rootMargin: '0px 0px 320px 0px', threshold: 0 });
        io.observe(sentinel);
    });

    document.addEventListener('click', function (e) {
        var b = e.target.closest('.dash-load-more-btn');
        if (!b) return;
        var tab = b.getAttribute('data-dash-tab');
        if (!tab) return;
        var sentinel = document.querySelector('.dash-feed-sentinel--' + tab);
        runFetch(tab, sentinel);
    });
})();
</script>

<script>
(function () {
    var api = 'scripts/channel_card_api.php';
    var grid = document.querySelector('#panel-subscribed .subscribed-grid');
    if (!grid) return;

    function postJson(body) {
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }

    grid.addEventListener('change', function (e) {
        var sel = e.target.closest('.channel-card-category-select');
        if (!sel || !grid.contains(sel)) return;
        var chId = parseInt(sel.getAttribute('data-channel-id'), 10);
        if (chId < 1) return;
        var last = sel.getAttribute('data-last-saved') || '';
        var val = sel.value;
        var payload = { action: 'update_category', channel_id: chId };
        payload.category_id = val === '' ? null : parseInt(val, 10);
        sel.disabled = true;
        postJson(payload).then(function (res) {
            sel.disabled = false;
            if (!res || !res.ok) {
                sel.value = last;
                alert('分類變更失敗');
                return;
            }
            sel.setAttribute('data-last-saved', val);
            var inner = document.querySelector('#panel-subscribed .dash-feed-inner--subscribed');
            var fCat = inner ? (parseInt(inner.getAttribute('data-dash-category'), 10) || 0) : 0;
            if (fCat === 0) return;
            var newVal = val === '' ? null : parseInt(val, 10);
            var drop = false;
            if (fCat === -1) {
                drop = newVal !== null;
            } else if (fCat > 0) {
                drop = newVal !== fCat;
            }
            if (drop) {
                var card = sel.closest('.channel-card');
                if (card) card.remove();
                if (!grid.querySelector('.channel-card')) {
                    window.location.reload();
                }
            }
        }).catch(function () {
            sel.disabled = false;
            sel.value = last;
            alert('分類變更失敗');
        });
    });

    grid.addEventListener('click', function (e) {
        var fav = e.target.closest('.channel-card-btn--fav');
        var del = e.target.closest('.channel-card-btn--del');
        if (fav) {
            e.preventDefault();
            e.stopPropagation();
            var id = parseInt(fav.getAttribute('data-channel-id'), 10);
            postJson({ action: 'toggle_favorite', channel_id: id }).then(function (res) {
                if (!res || !res.ok) return;
                var on = res.is_favorite === 1;
                fav.setAttribute('data-is-favorite', on ? '1' : '0');
                fav.textContent = on ? '⭐ 最愛' : '☆ 最愛';
                fav.classList.toggle('channel-card-btn--on', on);
            });
            return;
        }
        if (del) {
            e.preventDefault();
            e.stopPropagation();
            if (!confirm('確定要從訂閱清單刪除此頻道？（不會影響 YouTube 上的頻道）')) return;
            var cid = parseInt(del.getAttribute('data-channel-id'), 10);
            postJson({ action: 'delete_channel', channel_id: cid }).then(function (res) {
                if (!res || !res.ok) {
                    alert('刪除失敗');
                    return;
                }
                var card = del.closest('.channel-card');
                if (card) card.remove();
                if (!grid.querySelector('.channel-card')) {
                    window.location.reload();
                }
            }).catch(function () {
                alert('刪除失敗');
            });
        }
    });
})();
</script>

<script>
(function () {
    var api = 'scripts/video_card_api.php';
    function postJson(body) {
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.video-thumb-btn--del');
        if (!btn) return;
        var panel = btn.closest('.video-panel');
        if (!panel || (panel.id !== 'panel-unwatched' && panel.id !== 'panel-watched')) return;
        e.preventDefault();
        e.stopPropagation();
        if (!confirm('確定要從清單刪除此影片？')) return;
        var id = parseInt(btn.getAttribute('data-video-id'), 10);
        postJson({ action: 'delete_video', video_id: id }).then(function (res) {
            if (!res || !res.ok) {
                alert('刪除失敗');
                return;
            }
            var row = btn.closest('.video');
            if (row) row.remove();
            var inner = panel.querySelector('.dash-feed-inner--videos');
            if (panel && inner && !inner.querySelector('.video')) {
                window.location.reload();
            }
        }).catch(function () {
            alert('刪除失敗');
        });
    });
})();
</script>

<script>window.CATEGORY_DASH_TAB = <?= json_encode($dashTabKey, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;</script>
<script>
(function () {
    var wrap = document.getElementById('categoryTags');
    var btn = document.getElementById('btnCategoryTagEdit');
    var btnAdd = document.getElementById('btnCategoryAdd');
    var addInput = document.getElementById('categoryNewNameInput');
    var api = 'scripts/category_dashboard_api.php';
    if (!wrap || !btn) return;

    var editMode = false;

    function categoryNavHref(id) {
        var p = new URLSearchParams();
        p.set('category_id', String(id));
        p.set('dash_tab', window.CATEGORY_DASH_TAB || 'unwatched');
        return 'index.php?' + p.toString();
    }

    function setNavLabel(item, name, total) {
        var a = item.querySelector('.category--nav');
        if (a) {
            a.textContent = name + ' (' + total + ')';
            a.href = categoryNavHref(parseInt(item.getAttribute('data-id'), 10));
        }
    }

    function postJson(body) {
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }

    function saveOrder() {
        var order = [];
        wrap.querySelectorAll('.category-item:not(.category-item--system)').forEach(function (el) {
            order.push(parseInt(el.getAttribute('data-id'), 10));
        });
        return postJson({ action: 'reorder', order: order });
    }

    function bindEditorInput(input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
        input.addEventListener('blur', function () {
            if (!editMode) return;
            var item = input.closest('.category-item');
            if (!item) return;
            var id = parseInt(item.getAttribute('data-id'), 10);
            var total = parseInt(item.getAttribute('data-total'), 10) || 0;
            var prev = item.getAttribute('data-name') || '';
            var name = input.value.trim();
            if (name === '' || name === prev) {
                input.value = prev;
                return;
            }
            postJson({ action: 'rename', id: id, name: name }).then(function (res) {
                if (res && res.ok) {
                    item.setAttribute('data-name', name);
                    setNavLabel(item, name, total);
                } else {
                    input.value = prev;
                }
            }).catch(function () {
                input.value = prev;
            });
        });
    }

    function createCategoryItem(id, name, total) {
        var item = document.createElement('div');
        item.className = 'category-item';
        item.setAttribute('data-id', String(id));
        item.setAttribute('data-name', name);
        item.setAttribute('data-total', String(total));
        item.setAttribute('draggable', editMode ? 'true' : 'false');

        var a = document.createElement('a');
        a.className = 'category category--nav';
        a.href = categoryNavHref(id);
        a.textContent = name + ' (' + total + ')';

        var ed = document.createElement('div');
        ed.className = 'category category--editor';

        var grip = document.createElement('span');
        grip.className = 'category-editor-grip';
        grip.title = '拖曳排序';
        grip.textContent = '⠿';

        var inp = document.createElement('input');
        inp.type = 'text';
        inp.className = 'category-editor-input';
        inp.value = name;
        inp.maxLength = 100;
        inp.setAttribute('aria-label', '分類名稱');

        var meta = document.createElement('span');
        meta.className = 'category-editor-meta';
        meta.textContent = '(' + total + ')';

        var del = document.createElement('button');
        del.type = 'button';
        del.className = 'category-delete-btn';
        del.setAttribute('data-category-delete', String(id));
        del.title = '刪除此分類';
        del.setAttribute('aria-label', '刪除');
        del.textContent = '×';

        ed.appendChild(grip);
        ed.appendChild(inp);
        ed.appendChild(meta);
        ed.appendChild(del);

        item.appendChild(a);
        item.appendChild(ed);
        bindEditorInput(inp);
        return item;
    }

    function tryAddCategory() {
        if (!editMode || !addInput) return;
        var name = addInput.value.trim();
        if (name === '') return;
        postJson({ action: 'add', name: name }).then(function (res) {
            if (!res || !res.ok) {
                if (res && res.error === 'duplicate') {
                    alert('已有相同名稱的分類。');
                } else {
                    alert('新增失敗。');
                }
                return;
            }
            addInput.value = '';
            var item = createCategoryItem(res.id, res.name, 0);
            wrap.appendChild(item);
        }).catch(function () {
            alert('新增失敗。');
        });
    }

    btn.addEventListener('click', function () {
        editMode = !editMode;
        wrap.classList.toggle('category-tags--edit', editMode);
        btn.classList.toggle('is-active', editMode);
        btn.setAttribute('aria-pressed', editMode ? 'true' : 'false');

        wrap.querySelectorAll('.category-item').forEach(function (el) {
            var sys = el.classList.contains('category-item--system');
            el.setAttribute('draggable', (editMode && !sys) ? 'true' : 'false');
        });

        if (!editMode) {
            saveOrder().then(function (res) {
                if (res && res.ok) {
                    window.location.reload();
                }
            }).catch(function () {
                window.location.reload();
            });
        }
    });

    if (btnAdd) {
        btnAdd.addEventListener('click', function (e) {
            e.preventDefault();
            tryAddCategory();
        });
    }
    if (addInput) {
        addInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                tryAddCategory();
            }
        });
    }

    wrap.addEventListener('click', function (e) {
        var del = e.target.closest('.category-delete-btn');
        if (!del || !editMode) return;
        e.preventDefault();
        var id = parseInt(del.getAttribute('data-category-delete'), 10);
        if (id < 1) return;
        if (!confirm('確定要刪除此分類？若分類內有頻道，這些頻道將改為「未分類」。')) return;
        postJson({ action: 'delete', id: id }).then(function (res) {
            if (!res || !res.ok) {
                alert('刪除失敗。');
                return;
            }
            var item = del.closest('.category-item');
            var params = new URLSearchParams(window.location.search);
            var curCat = parseInt(params.get('category_id') || '0', 10);
            if (item) item.remove();
            if (id === curCat) {
                window.location.href = 'index.php?' + new URLSearchParams({ dash_tab: window.CATEGORY_DASH_TAB || 'unwatched' }).toString();
            }
        }).catch(function () {
            alert('刪除失敗。');
        });
    });

    var dragEl = null;
    wrap.addEventListener('dragstart', function (e) {
        if (!editMode) return;
        var row = e.target.closest('.category-item');
        if (!row || !wrap.contains(row) || row.classList.contains('category-item--system')) return;
        dragEl = row;
        row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(row.getAttribute('data-id')));
    });

    wrap.addEventListener('dragend', function (e) {
        var row = e.target.closest('.category-item');
        if (row) row.classList.remove('dragging');
        dragEl = null;
    });

    wrap.addEventListener('dragover', function (e) {
        if (!editMode || !dragEl) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        var row = e.target.closest('.category-item');
        if (!row || row === dragEl || !wrap.contains(row)) return;
        if (row.classList.contains('category-item--system')) {
            var addRow = document.getElementById('categoryAddRow');
            var anchor = addRow && addRow.nextSibling ? addRow.nextSibling : row.nextSibling;
            if (anchor && anchor !== dragEl) {
                wrap.insertBefore(dragEl, anchor);
            }
            return;
        }
        var rect = row.getBoundingClientRect();
        var mid = rect.top + rect.height / 2;
        if (e.clientY < mid) {
            wrap.insertBefore(dragEl, row);
        } else {
            wrap.insertBefore(dragEl, row.nextSibling);
        }
    });

    wrap.addEventListener('drop', function (e) {
        e.preventDefault();
    });

    wrap.querySelectorAll('.category-editor-input').forEach(bindEditorInput);
})();
</script>

<script>
(function () {
    var btn = document.getElementById('btnKpiClearUnwatched');
    if (!btn) return;
    btn.addEventListener('click', function () {
        if (!confirm('確定要刪除全部「待看／未看」影片？此操作無法復原。')) return;
        btn.disabled = true;
        fetch('scripts/video_card_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_all_unwatched' }),
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    alert('刪除失敗');
                    btn.disabled = false;
                    return;
                }
                window.location.reload();
            })
            .catch(function () {
                alert('刪除失敗');
                btn.disabled = false;
            });
    });
})();
</script>

</body>
</html>