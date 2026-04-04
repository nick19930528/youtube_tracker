<?php
/**
 * 首頁儀表板：影片縮圖、頻道卡（供 index.php 與 dashboard_feed_api 共用）
 */
if (!defined('DASH_FEED_PAGE_SIZE')) {
    define('DASH_FEED_PAGE_SIZE', 18);
}

/** 依頻道名稱分組（僅供舊資料或外掛使用；首頁無限捲動改為平面列表） */
function group_videos_by_channel_for_dashboard(array $rows) {
    $groups = [];
    foreach ($rows as $v) {
        $k = (string)($v['channel_name'] ?? '');
        if (!isset($groups[$k])) {
            $groups[$k] = [];
        }
        $groups[$k][] = $v;
    }
    ksort($groups, SORT_NATURAL);
    return $groups;
}

function dash_video_published_ago($publishedAt) {
    if ($publishedAt === null || $publishedAt === '') {
        return '—';
    }
    $ts = strtotime($publishedAt);
    if ($ts === false) {
        return '—';
    }
    $sec = time() - $ts;
    if ($sec < 0) {
        return '—';
    }
    if ($sec < 60) {
        return '剛剛';
    }
    if ($sec < 3600) {
        return (string) (int) floor($sec / 60) . ' 分鐘前';
    }
    if ($sec < 86400) {
        return (string) (int) floor($sec / 3600) . ' 小時前';
    }
    if ($sec < 604800) {
        return (string) (int) floor($sec / 86400) . ' 天前';
    }
    if ($sec < 2592000) {
        return (string) (int) floor($sec / 604800) . ' 週前';
    }
    if ($sec < 31536000) {
        return (string) (int) floor($sec / 2592000) . ' 個月前';
    }
    return (string) (int) floor($sec / 31536000) . ' 年前';
}

function dash_video_duration_label($duration) {
    $s = (int) $duration;
    if ($s < 1) {
        return '—';
    }
    return $s >= 3600 ? gmdate('H:i:s', $s) : gmdate('i:s', $s);
}

function render_dashboard_video_thumb_block(array $v, $mode) {
    if (!in_array($mode, ['unwatched', 'watched'], true)) {
        $mode = 'unwatched';
    }
    $vid = (int) ($v['id'] ?? 0);
    if ($vid < 1) {
        return;
    }
    $thumb = trim((string) ($v['thumbnail_url'] ?? ''));
    if ($mode === 'unwatched') {
        $targetHref = 'index.php?page=open_video&amp;id=' . $vid;
    } else {
        $yu = (string) ($v['youtube_url'] ?? '');
        $targetHref = $yu !== '' ? htmlspecialchars($yu, ENT_QUOTES, 'UTF-8') : '#';
    }
    $pubAgo = htmlspecialchars(dash_video_published_ago($v['published_at'] ?? null), ENT_QUOTES, 'UTF-8');
    $views = htmlspecialchars(number_format((int) ($v['view_count'] ?? 0)), ENT_QUOTES, 'UTF-8');
    $comments = htmlspecialchars(number_format((int) ($v['comment_count'] ?? 0)), ENT_QUOTES, 'UTF-8');
    $dur = htmlspecialchars(dash_video_duration_label($v['duration'] ?? 0), ENT_QUOTES, 'UTF-8');
    ?>
    <div class="video-media">
        <?php if ($thumb !== ''): ?>
            <a href="<?= $targetHref ?>" class="video-thumb-link" target="_blank" rel="noopener noreferrer">
                <img src="<?= htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8') ?>" alt="">
            </a>
        <?php else: ?>
            <a href="<?= $targetHref ?>" class="video-thumb-link video-thumb-link--empty" target="_blank" rel="noopener noreferrer">
                <span class="video-thumb-placeholder" role="img" aria-label="無縮圖"></span>
            </a>
        <?php endif; ?>
        <div class="video-thumb-overlay">
            <div class="video-thumb-overlay-main">
                <div class="video-thumb-stat">
                    <span class="video-thumb-stat-label">發布</span>
                    <span class="video-thumb-stat-value"><?= $pubAgo ?></span>
                </div>
                <div class="video-thumb-stat">
                    <span class="video-thumb-stat-label">觀看</span>
                    <span class="video-thumb-stat-value"><?= $views ?></span>
                </div>
                <div class="video-thumb-stat">
                    <span class="video-thumb-stat-label">留言</span>
                    <span class="video-thumb-stat-value"><?= $comments ?></span>
                </div>
                <div class="video-thumb-stat">
                    <span class="video-thumb-stat-label">長度</span>
                    <span class="video-thumb-stat-value"><?= $dur ?></span>
                </div>
            </div>
            <div class="video-thumb-overlay-actions">
                <button type="button" class="video-thumb-btn video-thumb-btn--del"
                        data-video-id="<?= $vid ?>"
                        title="從清單刪除">🗑 刪除</button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 平面列表 HTML 片段（待看或已看）
 *
 * @param array $rows
 * @param 'unwatched'|'watched' $mode
 */
function render_dashboard_video_rows_flat(array $rows, $mode) {
    if (!in_array($mode, ['unwatched', 'watched'], true)) {
        $mode = 'unwatched';
    }
    ob_start();
    foreach ($rows as $v) {
        ?>
        <div class="video">
            <?php render_dashboard_video_thumb_block($v, $mode); ?>
            <div class="video-text">
                <?php if ($mode === 'unwatched'): ?>
                    <a href="index.php?page=open_video&amp;id=<?= (int)$v['id'] ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlspecialchars($v['title']) ?>
                    </a>
                    <br>
                    <small><?= htmlspecialchars($v['channel_name'] ?? '') ?></small>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($v['youtube_url'] ?? '#', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlspecialchars($v['title']) ?>
                    </a>
                    <br>
                    <small><?= htmlspecialchars($v['channel_name'] ?? '') ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

function render_dashboard_channel_card(array $ch) {
    $subN = (int)($ch['subscriber_count'] ?? 0);
    $vidN = (int)($ch['video_count'] ?? 0);
    if ($subN >= 100000000) {
        $subStr = round($subN / 100000000, 1) . ' 億';
    } elseif ($subN >= 10000) {
        $subStr = round($subN / 10000, 1) . ' 萬';
    } else {
        $subStr = number_format($subN);
    }
    $vidStr = $vidN >= 10000 ? round($vidN / 10000, 1) . ' 萬' : number_format($vidN);
    $yearsStr = '—';
    if (!empty($ch['published_at'])) {
        try {
            $pub = new DateTime($ch['published_at']);
            $y = $pub->diff(new DateTime())->y;
            $yearsStr = $y >= 1 ? ('創立 ' . $y . ' 年') : '未滿 1 年';
        } catch (Exception $e) {
            $yearsStr = '—';
        }
    }
    $isFav = !empty($ch['is_favorite']);
    ?>
    <article class="channel-card" data-channel-id="<?= (int)$ch['id'] ?>">
        <div class="channel-card-media">
            <?php if (!empty($ch['thumbnail_url'])): ?>
                <img class="channel-card-thumb" src="<?= htmlspecialchars($ch['thumbnail_url']) ?>" alt="">
            <?php else: ?>
                <span class="channel-card-thumb channel-card-thumb--empty" role="img" aria-label="無頻道圖片"></span>
            <?php endif; ?>
            <div class="channel-card-overlay">
                <div class="channel-card-overlay-main">
                    <div class="channel-card-stat">
                        <span class="channel-card-stat-label">訂閱</span>
                        <span class="channel-card-stat-value"><?= htmlspecialchars($subStr) ?></span>
                    </div>
                    <div class="channel-card-stat">
                        <span class="channel-card-stat-label">影片</span>
                        <span class="channel-card-stat-value"><?= htmlspecialchars($vidStr) ?></span>
                    </div>
                    <div class="channel-card-stat">
                        <span class="channel-card-stat-label">成立</span>
                        <span class="channel-card-stat-value"><?= htmlspecialchars($yearsStr) ?></span>
                    </div>
                </div>
                <div class="channel-card-overlay-actions">
                    <button type="button" class="channel-card-btn channel-card-btn--fav<?= $isFav ? ' channel-card-btn--on' : '' ?>"
                            data-channel-id="<?= (int)$ch['id'] ?>"
                            data-is-favorite="<?= $isFav ? '1' : '0' ?>"
                            title="我的最愛"><?= $isFav ? '⭐ 最愛' : '☆ 最愛' ?></button>
                    <button type="button" class="channel-card-btn channel-card-btn--del"
                            data-channel-id="<?= (int)$ch['id'] ?>"
                            title="從訂閱清單刪除">🗑 刪除</button>
                </div>
            </div>
        </div>
        <div class="channel-card-body">
            <a class="channel-card-name" href="<?= htmlspecialchars($ch['url']) ?>" target="_blank" rel="noopener noreferrer">
                <?= htmlspecialchars($ch['name']) ?>
            </a>
            <?php if (!empty($ch['category_name'])): ?>
                <span class="channel-card-cat"><?= htmlspecialchars($ch['category_name']) ?></span>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

function render_dashboard_channel_cards_html(array $channels) {
    ob_start();
    foreach ($channels as $ch) {
        render_dashboard_channel_card($ch);
    }
    return ob_get_clean();
}
