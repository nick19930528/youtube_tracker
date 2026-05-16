<?php
/**
 * 法律／政策頁面底部相關連結（供 terms、privacy、consumer_rights、refund 等頁共用）
 */
function legal_related_links_html(): string
{
    $items = array(
        array('terms', '服務條款'),
        array('privacy', '隱私權政策'),
        array('consumer_rights', '消費者權益'),
        array('refund', '退款政策'),
    );
    $parts = array();
    foreach ($items as $item) {
        $parts[] = '<a href="index.php?page=' . htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8') . '</a>';
    }
    return implode(' <span style="color:#94a3b8;">·</span> ', $parts);
}
