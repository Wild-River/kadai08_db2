<?php
require_once '../config/auth.php';
require_once '../config/func.php';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <title>ダッシュボード | 請求書管理</title>
    <?php require_once '../config/head.php'; ?>
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">ダッシュボード</h1>
        <p><?= h($_SESSION['username']) ?> さん、ようこそ</p>

        <div class="card-grid">
            <a href="bean_list.php" class="menu-card">
                <i class="fa-solid fa-seedling"></i>
                <span>生豆在庫管理</span>
            </a>
            <a href="movement_list.php" class="menu-card">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>入出荷記録</span>
            </a>
            <a href="customer_list.php" class="menu-card">
                <i class="fa-solid fa-users"></i>
                <span>顧客管理</span>
            </a>
            <a href="invoices_list.php" class="menu-card">
                <i class="fa-solid fa-file-invoice"></i>
                <span>請求書一覧</span>
            </a>
            <a href="invoice_new.php" class="menu-card">
                <i class="fa-solid fa-plus"></i>
                <span>請求書作成</span>
            </a>
        </div>
    </div>
</body>

</html>