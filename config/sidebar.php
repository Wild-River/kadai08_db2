<button type="button" class="menu-toggle" aria-label="メニューを開く" aria-expanded="false" aria-controls="sidebar-nav">
    <span class="menu-toggle__bar"></span>
    <span class="menu-toggle__bar"></span>
    <span class="menu-toggle__bar"></span>
</button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
        <a href="dashboard.php">生豆管理システム</a>
    </div>
    <nav class="sidebar__nav" id="sidebar-nav">
        <div class="sidebar__group">
            <a href="bean_list.php" class="sidebar__link">
                <i class="fa-solid fa-seedling"></i>
                <span>生豆在庫管理</span>
            </a>
            <a href="bean_new.php" class="sidebar__link sidebar__link--sub">
                <i class="fa-solid fa-plus"></i>
                <span>生豆登録</span>
            </a>
        </div>
        <div class="sidebar__group">
            <a href="movement_list.php" class="sidebar__link">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>入出荷記録</span>
            </a>
            <a href="movement_new.php" class="sidebar__link sidebar__link--sub">
                <i class="fa-solid fa-plus"></i>
                <span>入出荷登録</span>
            </a>
        </div>
        <div class="sidebar__group">
            <a href="customer_list.php" class="sidebar__link">
                <i class="fa-solid fa-users"></i>
                <span>顧客管理</span>
            </a>
            <a href="customer_new.php" class="sidebar__link sidebar__link--sub">
                <i class="fa-solid fa-user-plus"></i>
                <span>顧客登録</span>
            </a>
        </div>
        <div class="sidebar__group">
            <a href="invoices_list.php" class="sidebar__link">
                <i class="fa-solid fa-file-invoice"></i>
                <span>請求書一覧</span>
            </a>
            <a href="invoice_new.php" class="sidebar__link sidebar__link--sub">
                <i class="fa-solid fa-plus"></i>
                <span>請求書作成</span>
            </a>
        </div>

    </nav>
    <div class="sidebar__footer">
        <a href="logout.php" class="sidebar__link" title="ログアウト" aria-label="ログアウト">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>ログアウト</span>
        </a>
    </div>
</aside>
<script src="js/script.js"></script>