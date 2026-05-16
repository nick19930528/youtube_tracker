<?php

/**
 * 法律／政策頁：外觀模式（淺色／深色）與共用樣式
 */

function legal_ui_theme()
{
    if (isset($_SESSION['ui_theme']) && $_SESSION['ui_theme'] === 'dark') {
        return 'dark';
    }

    return 'light';
}

/**
 * @param bool $withCardH3 隱私權政策等含 h3 小標的頁面
 */
function legal_print_styles($withCardH3 = false)
{
    ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 0 20px 48px;
            color: #0f172a;
            background-color: #f1f5f9;
            background-image:
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2394a3b8' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E"),
                radial-gradient(ellipse 90% 70% at 100% 0%, rgba(37, 99, 235, 0.08), transparent 55%),
                linear-gradient(165deg, #f8fafc 0%, #f1f5f9 100%);
        }
        .legal-top {
            max-width: 760px;
            margin: 0 auto;
            padding: 16px 0 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 16px;
        }
        .legal-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
            padding: 8px 4px;
            border-radius: 10px;
            transition: background 0.15s, color 0.15s;
        }
        .legal-back:hover { background: rgba(37, 99, 235, 0.08); color: #1d4ed8; }
        .wrap { max-width: 760px; margin: 0 auto; }
        .page-head { margin-bottom: 20px; }
        h1 { font-size: 1.55rem; font-weight: 700; margin: 0 0 8px; letter-spacing: -0.02em; }
        .meta { color: #64748b; font-size: 0.88rem; margin: 0 0 12px; }
        .intro { color: #475569; font-size: 0.95rem; line-height: 1.65; margin: 0; }
        .card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 16px;
            padding: 22px 24px 26px;
            margin-bottom: 16px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 10px 24px -8px rgba(15, 23, 42, 0.08);
        }
        .card h2 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 12px;
            color: #0f172a;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        <?php if ($withCardH3): ?>
        .card h3 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 14px 0 8px;
            color: #1e293b;
        }
        <?php endif; ?>
        .card p, .card li { font-size: 0.92rem; line-height: 1.75; color: #334155; }
        .card p { margin: 0 0 10px; }
        .card p:last-child { margin-bottom: 0; }
        .card ol, .card ul { margin: 8px 0 0; padding-left: 1.35rem; }
        .card li { margin-bottom: 6px; }
        .card li:last-child { margin-bottom: 0; }
        .card a { color: #2563eb; font-weight: 600; text-decoration: none; }
        .card a:hover { text-decoration: underline; }
        .legal-footer {
            margin-top: 8px;
            padding-top: 16px;
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.6;
        }
        .legal-footer a { color: #2563eb; font-weight: 600; text-decoration: none; }
        .legal-footer a:hover { text-decoration: underline; }
        .legal-link-sep { color: #94a3b8; }

        body[data-theme="dark"] {
            color: #e2e8f0;
            background-color: #0b1220;
            background-image:
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2394a3b8' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E"),
                radial-gradient(ellipse 90% 70% at 100% 0%, rgba(59, 130, 246, 0.12), transparent 55%),
                linear-gradient(165deg, #0f172a 0%, #0b1220 100%);
        }
        body[data-theme="dark"] .legal-back {
            color: #93c5fd;
        }
        body[data-theme="dark"] .legal-back:hover {
            background: rgba(59, 130, 246, 0.12);
            color: #bfdbfe;
        }
        body[data-theme="dark"] h1 { color: #f1f5f9; }
        body[data-theme="dark"] .meta,
        body[data-theme="dark"] .intro,
        body[data-theme="dark"] .legal-footer {
            color: rgba(226, 232, 240, 0.72);
        }
        body[data-theme="dark"] .card {
            background: rgba(15, 23, 42, 0.92);
            border-color: rgba(51, 65, 85, 0.75);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25), 0 10px 24px -8px rgba(0, 0, 0, 0.35);
        }
        body[data-theme="dark"] .card h2 {
            color: rgba(241, 245, 249, 0.95);
            border-bottom-color: rgba(51, 65, 85, 0.75);
        }
        <?php if ($withCardH3): ?>
        body[data-theme="dark"] .card h3 { color: rgba(226, 232, 240, 0.88); }
        <?php endif; ?>
        body[data-theme="dark"] .card p,
        body[data-theme="dark"] .card li {
            color: rgba(226, 232, 240, 0.82);
        }
        body[data-theme="dark"] .card a,
        body[data-theme="dark"] .legal-footer a {
            color: #93c5fd;
        }
        body[data-theme="dark"] .legal-footer {
            border-top-color: rgba(51, 65, 85, 0.75);
        }
        body[data-theme="dark"] .legal-link-sep {
            color: rgba(148, 163, 184, 0.55);
        }
    </style>
    <?php
}
