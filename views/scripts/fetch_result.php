<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <title>影片擷取結果</title>
    <style>
        body {
            font-family: "Courier New", monospace;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .console {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 10px;
            border-radius: 8px;
            white-space: pre-wrap;
            overflow-x: auto;
            max-height: 80vh;
        }
        .success { color: #9cdcfe; }     /* 淺藍 */
        .update { color: #b5cea8; }      /* 淺綠 */
        .skip   { color: #dcdcaa; }      /* 黃 */
        .error  { color: #f48771; }      /* 紅 */
        .title  { color: #c586c0; }      /* 紫 */
        .done   { color: #6a9955; font-weight: bold; }
        .btn {
            display: inline-block;
            margin-top: 6px;
            padding: 10px 16px;
            background-color: #007acc;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>

    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
        <h2 style="margin: 0;">📥 自動擷取結果</h2>
        <a href="/index.php" class="btn">🏠 返回首頁</a>
    </div>

    <div class="console">
<?= htmlspecialchars($output) ?>
    </div>

</body>

</html>
