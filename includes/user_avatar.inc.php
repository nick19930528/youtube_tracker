<?php
/**
 * 大頭照 HTML（圓形；無圖時顯示姓名字首）
 */
function user_avatar_html($url, $name, $sizePx = 40)
{
    $url = trim((string) $url);
    $name = trim((string) $name);
    $sizePx = (int) $sizePx;
    if ($sizePx < 20) {
        $sizePx = 40;
    }
    $alt = $name !== '' ? $name : '大頭照';
    if ($url !== '') {
        return '<img class="user-avatar" src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
            . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8')
            . '" width="' . $sizePx . '" height="' . $sizePx . '">';
    }
    $ch = '會';
    if ($name !== '') {
        if (function_exists('mb_substr')) {
            $ch = mb_substr($name, 0, 1, 'UTF-8');
        } else {
            $ch = substr($name, 0, 1);
        }
    }
    return '<span class="user-avatar user-avatar--fallback" style="width:' . $sizePx . 'px;height:' . $sizePx . 'px;font-size:'
        . max(12, (int) round($sizePx * 0.42)) . 'px">' . htmlspecialchars($ch, ENT_QUOTES, 'UTF-8') . '</span>';
}
