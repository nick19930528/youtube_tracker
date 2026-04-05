<?php
/**
 * 後台管理員：依使用者 ID 白名單（請依實際管理員帳號修改）
 */
function admin_config_user_ids()
{
    return array(1);
}

function auth_is_admin()
{
    if (!auth_check()) {
        return false;
    }
    $uid = auth_user_id();

    return in_array($uid, admin_config_user_ids(), true);
}

function auth_require_admin()
{
    auth_require_login();
    if (!auth_is_admin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * 測試功能頁：僅使用者 ID = 1 可見／存取
 */
function auth_can_test_lab()
{
    if (!auth_check()) {
        return false;
    }

    return auth_user_id() === 1;
}

function auth_require_test_lab()
{
    auth_require_login();
    if (!auth_can_test_lab()) {
        header('Location: index.php');
        exit;
    }
}
