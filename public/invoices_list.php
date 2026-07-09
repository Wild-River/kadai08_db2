<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$keyword = $_GET['keyword'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// 明細から小計を集計するサブクエリを使い、請求書ごとの金額を出す
$sql = "SELECT
            invoices.id,
            invoices.invoice_number,
            invoices.status,
            invoices.issue_date,
            invoices.due_date,
            invoices.tax_rate,
            clients.name AS client_name,
            -- COALESCEは「NULLだったら代わりに0を使う」
            COALESCE(SUM(invoice_items.quantity * invoice_items.unit_price), 0) AS subtotal
        FROM invoices
        JOIN clients ON invoices.client_id = clients.id
        LEFT JOIN invoice_items ON invoices.id = invoice_items.invoice_id";

$conditions = [];
if ($keyword !== '') {
    $conditions[] = "(invoices.invoice_number LIKE :keyword1 OR clients.name LIKE :keyword2)";
}
if ($statusFilter !== '') {
    $conditions[] = "invoices.status = :status";
}
if ($conditions) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sortableColumns = [
    'invoice_number' => 'invoices.invoice_number',
    'client_name'    => 'clients.name',
    'status'         => 'invoices.status',
    'issue_date'     => 'invoices.issue_date',
    'due_date'       => 'invoices.due_date',
    'subtotal'       => 'subtotal',
];

$sortKey = $_GET['sort'] ?? 'issue_date';
if (!array_key_exists($sortKey, $sortableColumns)) {
    $sortKey = 'issue_date';
}
$order = (($_GET['order'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';

$sql .= " GROUP BY invoices.id, invoices.invoice_number, invoices.status,
                   invoices.issue_date, invoices.due_date, invoices.tax_rate, clients.name
        ORDER BY {$sortableColumns[$sortKey]} {$order}";

$stmt = $pdo->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword1', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->bindValue(':keyword2', '%' . $keyword . '%', PDO::PARAM_STR);
}
if ($statusFilter !== '') {
    $stmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
}
$stmt->execute();
$invoices = $stmt->fetchAll();

$statusLabels = statusLabels();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求書一覧 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>請求書一覧</h1>
            <a href="invoice_new.php" class="btn-primary">+ 新規作成</a>
        </div>

        <form method="get" action="" class="search-form">
            <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="請求書番号・クライアント名で検索" autocomplete="off">
            <select name="status">
                <option value="">すべてのステータス</option>
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">検索</button>
            <a href="invoices_list.php" class="back-btn">クリア</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= sortLink('請求書番号', 'invoice_number', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('クライアント', 'client_name', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('ステータス', 'status', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('発行日', 'issue_date', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('支払期限', 'due_date', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('金額（税込）', 'subtotal', $sortKey, $order, $keyword) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice):
                        // 税込合計を計算（一覧ではサブクエリで出した小計に税率を掛ける）
                        $total = $invoice['subtotal'] + round($invoice['subtotal'] * ($invoice['tax_rate'] / 100));
                    ?>
                        <tr class="row-link" data-href="invoice_edit.php?id=<?= h($invoice['id']) ?>">
                            <td><?= h($invoice['invoice_number']) ?></td>
                            <td><?= h($invoice['client_name']) ?></td>
                            <td><span class="status-badge status-<?= h($invoice['status']) ?>"><?= h($statusLabels[$invoice['status']]) ?></span></td>
                            <td><?= h($invoice['issue_date']) ?></td>
                            <td><?= h($invoice['due_date']) ?></td>
                            <td><?= h(number_format($total)) ?> 円</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/row-link.js"></script>
</body>

</html>