<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php') === 'register') {
    $_GET['page'] = 'register';
}
require __DIR__ . '/auth.php';
