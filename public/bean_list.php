<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$keyword = $_GET['keyword'] ?? '';

$sql = "SELECT
            beans.id,
            beans.name,
            beans.supplier,
            beans.lot_no,
            beans.price,
            beans.kg_per_bag,
            SUM(CASE WHEN type = 'in'      THEN bags ELSE 0 END) AS total_in,
            SUM(CASE WHEN type = 'reserve' THEN bags ELSE 0 END) AS total_reserve,
            SUM(CASE WHEN type = 'out'     THEN bags ELSE 0 END) AS total_out
        FROM beans
        -- LEFT JOIN にすると「beans に登録されている生豆は、履歴が無くても必ず表示される（在庫0として）」
        LEFT JOIN stock_movements ON beans.id = stock_movements.bean_id";

if ($keyword !== '') {
    $sql .= " WHERE beans.name LIKE :keyword1 OR beans.supplier LIKE :keyword2 ";
}

$sortableColumns = [
    'name'          => 'beans.name',
    'supplier'      => 'beans.supplier',
    'lot_no'        => 'beans.lot_no',
    'price'         => 'beans.price',
    'kg_per_bag'    => 'beans.kg_per_bag',
    'total_in'      => 'total_in',
    'total_reserve' => 'total_reserve',
    'total_out'     => 'total_out',
    'zaiko'         => '(total_in - total_out)',
    'mishukka'      => '(total_reserve - total_out)',
];

$sortKey = $_GET['sort'] ?? 'name';
if (!array_key_exists($sortKey, $sortableColumns)) {
    $sortKey = 'name';
}
$order = (($_GET['order'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

$sql .= " GROUP BY beans.id, beans.name, beans.supplier, beans.lot_no, beans.price, beans.kg_per_bag
        ORDER BY {$sortableColumns[$sortKey]} {$order}";

$stmt = $pdo->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword1', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->bindValue(':keyword2', '%' . $keyword . '%', PDO::PARAM_STR);
}
$stmt->execute();
$stocks = $stmt->fetchAll();

// グラフ・サマリー用の集計（入荷袋数を「出荷済み」「予約中・未出荷」「未予約在庫」に分解）
$chartLabels = [];
$chartShipped = [];
$chartPending = [];
$chartUnreserved = [];
$totalShipped = 0;
$totalPending = 0;
$totalUnreserved = 0;
foreach ($stocks as $stock) {
    $shipped = (int) $stock['total_out'];
    $pending = max((int) $stock['total_reserve'] - $shipped, 0);
    $unreserved = max((int) $stock['total_in'] - $shipped - $pending, 0);
    $chartLabels[] = $stock['name'];
    $chartShipped[] = $shipped;
    $chartPending[] = $pending;
    $chartUnreserved[] = $unreserved;
    $totalShipped += $shipped;
    $totalPending += $pending;
    $totalUnreserved += $unreserved;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生豆在庫一覧 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>生豆在庫一覧</h1>
            <a href="bean_new.php" class="btn-primary">+ 新規登録</a>
        </div>

        <div class="insight-row">
            <div class="card stat-tile">
                <div class="section-head">
                    <p class="section-title">現在の在庫状況</p>
                    <p class="stat-note">未予約・予約中・出荷済みの割合</p>
                </div>
                <div id="statusDonutChart"></div>
            </div>
            <div class="card chart-card">
                <div class="section-head">
                    <p class="section-title">生豆別 入荷内訳</p>
                    <p class="section-sub">入荷袋数を「出荷済み」「予約中・未出荷」「未予約在庫」に分解</p>
                </div>
                <div id="stockChart"></div>
            </div>
        </div>

        <form method="get" action="" class="search-form">
            <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="商品名・仕入先で検索" autocomplete="off">
            <button type="submit">検索</button>
            <a href="bean_list.php" class="back-btn">クリア</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= sortLink('商品名', 'name', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('仕入先', 'supplier', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('Lot No.', 'lot_no', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('販売定価', 'price', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('kg/袋', 'kg_per_bag', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('入荷', 'total_in', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('予約', 'total_reserve', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('販売', 'total_out', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('在庫数', 'zaiko', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('未出荷', 'mishukka', $sortKey, $order, $keyword) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $stock):
                        // 在庫数 = 入荷 − 販売
                        $zaiko = $stock['total_in'] - $stock['total_out'];
                        $isLow = $zaiko <= 5;   // 在庫5袋以下なら「少ない」とみなす
                        // 未出荷 = 予約 − 販売
                        $mishukka = $stock['total_reserve'] - $stock['total_out'];
                    ?>
                        <tr class="row-link <?= $isLow ? 'low-stock' : '' ?>" data-href="bean_edit.php?id=<?= h($stock['id']) ?>">
                            <td><?= h($stock['name']) ?></td>
                            <td><?= h($stock['supplier']) ?></td>
                            <td><?= h($stock['lot_no']) ?></td>
                            <td><?= h(number_format($stock['price'])) ?></td>
                            <td><?= h($stock['kg_per_bag']) ?></td>
                            <td><?= h($stock['total_in']) ?></td>
                            <td><?= h($stock['total_reserve']) ?></td>
                            <td><?= h($stock['total_out']) ?></td>
                            <td><?= h($zaiko) ?></td>
                            <td><?= h($mishukka) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
        const chartShipped = <?= json_encode($chartShipped) ?>;
        const chartPending = <?= json_encode($chartPending) ?>;
        const chartUnreserved = <?= json_encode($chartUnreserved) ?>;
        const totalShipped = <?= json_encode($totalShipped) ?>;
        const totalPending = <?= json_encode($totalPending) ?>;
        const totalUnreserved = <?= json_encode($totalUnreserved) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="js/chart.js"></script>
    <script src="js/row-link.js"></script>
</body>

</html>