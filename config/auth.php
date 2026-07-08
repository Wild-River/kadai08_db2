<?php
require_once __DIR__ . '/func.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect('login.php');
}

$timeout = 1800; // タイムアウトまでの秒数（30分）
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}
$_SESSION['last_activity'] = time();
