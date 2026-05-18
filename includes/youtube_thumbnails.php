<?php
/**
 * YouTube 縮圖：API 取最佳畫質，並將已存的低畫質 URL 升級為顯示用高清連結。
 */

function youtube_best_thumbnail_from_api(array $thumbnails): ?string
{
    foreach (['maxres', 'standard', 'high', 'medium', 'default'] as $key) {
        if (!empty($thumbnails[$key]['url'])) {
            return (string) $thumbnails[$key]['url'];
        }
    }

    return null;
}

function youtube_video_thumbnail_url(string $videoId, string $quality = 'hq'): string
{
    $suffix = $quality === 'sd' ? 'sddefault' : 'hqdefault';

    return 'https://i.ytimg.com/vi/' . $videoId . '/' . $suffix . '.jpg';
}

/**
 * @param 'hq'|'sd' $quality
 */
function youtube_upgrade_thumbnail_url(?string $url, string $quality = 'hq'): ?string
{
    if ($url === null || trim($url) === '') {
        return null;
    }

    $url = trim($url);
    $suffix = $quality === 'sd' ? 'sddefault' : 'hqdefault';

    if (preg_match('#(?:i\.ytimg\.com|img\.youtube\.com)/vi(?:_webp)?/([a-zA-Z0-9_-]{11})/#', $url, $m)) {
        return 'https://i.ytimg.com/vi/' . $m[1] . '/' . $suffix . '.jpg';
    }

    if (preg_match('#/(?:default|mqdefault|hqdefault|sddefault|maxresdefault)(\.\w+)?#', $url)) {
        return preg_replace(
            '#/(?:default|mqdefault|hqdefault|sddefault|maxresdefault)(\.\w+)?#',
            '/' . $suffix . '$1',
            $url,
            1
        );
    }

    return $url;
}

function youtube_video_thumbnail_img_attrs(?string $storedUrl): string
{
    $hq = youtube_upgrade_thumbnail_url($storedUrl, 'hq');
    if ($hq === null || $hq === '') {
        return '';
    }

    $sd = youtube_upgrade_thumbnail_url($storedUrl, 'sd');
    $src = htmlspecialchars($hq, ENT_QUOTES, 'UTF-8');
    if ($sd !== null && $sd !== '' && $sd !== $hq) {
        $srcset = htmlspecialchars($hq, ENT_QUOTES, 'UTF-8') . ' 480w, '
            . htmlspecialchars($sd, ENT_QUOTES, 'UTF-8') . ' 640w';
        $sizes = htmlspecialchars('(max-width: 720px) min(100vw, 520px), 120px', ENT_QUOTES, 'UTF-8');

        return 'src="' . $src . '" srcset="' . $srcset . '" sizes="' . $sizes . '" loading="lazy"';
    }

    return 'src="' . $src . '" loading="lazy"';
}

function youtube_best_channel_thumbnail_from_api(array $thumbnails): ?string
{
    foreach (['high', 'medium', 'default'] as $key) {
        if (!empty($thumbnails[$key]['url'])) {
            return (string) $thumbnails[$key]['url'];
        }
    }

    return null;
}

/**
 * 將頻道頭像 URL（ggpht / googleusercontent 的 s88 等）升級為較大尺寸。
 */
function youtube_upgrade_channel_thumbnail_url(?string $url, int $targetSize = 240): ?string
{
    if ($url === null || trim($url) === '') {
        return null;
    }

    $url = trim($url);
    $targetSize = max(88, min(800, $targetSize));

    if (preg_match('#/s(\d+)(-[^/]*)#', $url, $m)) {
        $cur = (int) $m[1];
        if ($cur >= $targetSize) {
            return $url;
        }

        return preg_replace('#/s\d+(-[^/]*)#', '/s' . $targetSize . '$1', $url, 1);
    }

    if (preg_match('#=s(\d+)(?=-c|-k|/|$)#', $url, $m)) {
        $cur = (int) $m[1];
        if ($cur >= $targetSize) {
            return $url;
        }

        return preg_replace('#=s\d+#', '=s' . $targetSize, $url, 1);
    }

    return $url;
}

function youtube_channel_thumbnail_img_attrs(?string $storedUrl): string
{
    $med = youtube_upgrade_channel_thumbnail_url($storedUrl, 240);
    if ($med === null || $med === '') {
        return '';
    }

    $high = youtube_upgrade_channel_thumbnail_url($storedUrl, 800);
    $src = htmlspecialchars($med, ENT_QUOTES, 'UTF-8');
    if ($high !== null && $high !== '' && $high !== $med) {
        $srcset = htmlspecialchars($med, ENT_QUOTES, 'UTF-8') . ' 240w, '
            . htmlspecialchars($high, ENT_QUOTES, 'UTF-8') . ' 800w';
        $sizes = htmlspecialchars('(max-width: 720px) 50vw, 140px', ENT_QUOTES, 'UTF-8');

        return 'src="' . $src . '" srcset="' . $srcset . '" sizes="' . $sizes . '" loading="lazy"';
    }

    return 'src="' . $src . '" loading="lazy"';
}
