<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$keyword = $_GET['keyword'] ?? '';

$sql = "SELECT
            customers.id,
            customers.name,
            customers.company,
            customers.email,
            customers.created_at,
            COUNT(invoices.id) AS invoice_count
        FROM customers
        LEFT JOIN invoices ON customers.id = invoices.customer_id";

if ($keyword !== '') {
    $sql .= " WHERE customers.name LIKE :keyword1 OR customers.email LIKE :keyword2 ";
}

$sortableColumns = [
    'name'          => 'customers.name',
    'company' => 'customers.company',
    'email'         => 'customers.email',
    'created_at'    => 'customers.created_at',
    'invoice_count' => 'invoice_count',
];

$sortKey = $_GET['sort'] ?? 'name';
if (!array_key_exists($sortKey, $sortableColumns)) {
    $sortKey = 'name';
}
$order = (($_GET['order'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

$sql .= " GROUP BY customers.id, customers.name, customers.company, customers.email, customers.created_at
        ORDER BY {$sortableColumns[$sortKey]} {$order}";

$stmt = $pdo->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword1', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->bindValue(':keyword2', '%' . $keyword . '%', PDO::PARAM_STR);
}
$stmt->execute();
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客一覧 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>顧客一覧</h1>
            <a href="customer_new.php" class="btn-primary">+ 新規登録</a>
        </div>

        <form method="get" action="" class="search-form">
            <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="顧客名・メールで検索" autocomplete="off">
            <button type="submit">検索</button>
            <a href="customer_list.php" class="back-btn">クリア</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= sortLink('顧客名', 'name', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('会社名', 'company', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('メール', 'email', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('登録日', 'created_at', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('請求書数', 'invoice_count', $sortKey, $order, $keyword) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr class="row-link" data-href="customer_edit.php?id=<?= h($customer['id']) ?>">
                            <td><?= h($customer['name']) ?></td>
                            <td><?= h($customer['company']) ?></td>
                            <td><?= h($customer['email']) ?></td>
                            <td><?= h($customer['created_at']) ?></td>
                            <td><?= h($customer['invoice_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/row-link.js"></script>
</body>

</html>