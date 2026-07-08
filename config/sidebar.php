<button type="button" class="menu-toggle" aria-label="メニューを開く" aria-expanded="false" aria-controls="sidebar-nav">
    <span class="menu-toggle__bar"></span>
    <span class="menu-toggle__bar"></span>
    <span class="menu-toggle__bar"></span>
</button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
        <a href="dashboard.php">請求書管理システム</a>
    </div>
    <nav class="sidebar__nav" id="sidebar-nav">
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
        <div class="sidebar__group">
            <a href="clients_list.php" class="sidebar__link">
                <i class="fa-solid fa-users"></i>
                <span>クライアント管理</span>
            </a>
            <a href="client_new.php" class="sidebar__link sidebar__link--sub">
                <i class="fa-solid fa-user-plus"></i>
                <span>クライアント登録</span>
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
