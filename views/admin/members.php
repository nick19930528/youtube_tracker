<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_admin();

require_once __DIR__ . '/../../controllers/AdminController.php';

$pdo = (new Database())->getConnection();
$ctrl = new AdminController($pdo);

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$perPage = 25;

$total = $ctrl->countUsers($q === '' ? null : $q);
$pages = (int) ceil($total / $perPage);
if ($pages < 1) {
    $pages = 1;
}
if ($page > $pages) {
    $page = $pages;
}

$rows = $ctrl->listUsers($page, $perPage, $q === '' ? null : $q);

function admin_members_gender_label($g)
{
    if ($g === 'm') {
        return '男';
    }
    if ($g === 'f') {
        return '女';
    }
    if ($g === 'other') {
        return '其他';
    }

    return '—';
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <title>後台 — 會員列表</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 0 20px 48px;
            color: #0f172a;
            background: #f1f5f9;
        }
        .admin-wrap { max-width: 1100px; margin: 0 auto; padding-top: 20px; }
        .admin-top {
            display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            justify-content: space-between; margin-bottom: 20px;
        }
        .admin-top h1 { margin: 0; font-size: 1.35rem; }
        .admin-nav a { color: #0369a1; margin-right: 14px; text-decoration: none; }
        .admin-nav a:hover { text-decoration: underline; }
        .search-form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .search-form input[type="search"] {
            min-width: 200px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px;
        }
        .search-form button {
            padding: 8px 14px; border: 0; border-radius: 8px; background: #0ea5e9; color: #fff;
            cursor: pointer;
        }
        .search-form button:hover { background: #0284c7; }
        .admin-table-wrap {
            background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(15,23,42,.08);
            overflow: auto;
        }
        table.admin-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .admin-table th, .admin-table td {
            padding: 10px 12px; text-align: left; border-bottom: 1px solid #e2e8f0;
        }
        .admin-table th { background: #f8fafc; font-weight: 600; color: #475569; white-space: nowrap; }
        .admin-table tr:hover td { background: #f8fafc; }
        .admin-table a { color: #0369a1; }
        .pager { margin-top: 16px; font-size: 14px; color: #64748b; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .pager a { color: #0369a1; }
        .muted { color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>
<div class="admin-wrap">
    <div class="admin-top">
        <h1>後台 — 會員列表</h1>
        <nav class="admin-nav">
            <a href="index.php">← 首頁</a>
        </nav>
    </div>

    <form class="search-form" method="get" action="index.php">
        <input type="hidden" name="page" value="admin">
        <label>
            <span class="muted">搜尋 Email／姓名</span>
            <input type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="關鍵字">
        </label>
        <button type="submit">搜尋</button>
        <?php if ($q !== ''): ?>
            <a href="index.php?page=admin">清除</a>
        <?php endif; ?>
    </form>
    <p class="muted">共 <?= (int) $total ?> 位會員<?= $q !== '' ? '（已篩選）' : '' ?></p>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>姓名</th>
                    <th>性別</th>
                    <th>註冊時間</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6">沒有符合的會員。</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int) $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(admin_members_gender_label($r['gender'] ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($r['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><a href="index.php?page=admin_member&amp;id=<?= (int) $r['id'] ?>">詳情</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pager">
            <span>第 <?= $page ?>／<?= $pages ?> 頁</span>
            <?php
            $base = 'index.php?page=admin';
            if ($q !== '') {
                $base .= '&amp;q=' . rawurlencode($q);
            }
            if ($page > 1): ?>
                <a href="<?= $base ?>&amp;p=<?= $page - 1 ?>">上一頁</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
                <a href="<?= $base ?>&amp;p=<?= $page + 1 ?>">下一頁</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
