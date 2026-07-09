<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$keyword = $_GET['keyword'] ?? '';

$sql = "SELECT
            clients.id,
            clients.name,
            clients.email,
            clients.created_at,
            COUNT(invoices.id) AS invoice_count
        FROM clients
        LEFT JOIN invoices ON clients.id = invoices.client_id";

if ($keyword !== '') {
    $sql .= " WHERE clients.name LIKE :keyword1 OR clients.email LIKE :keyword2 ";
}

$sortableColumns = [
    'name'          => 'clients.name',
    'email'         => 'clients.email',
    'created_at'    => 'clients.created_at',
    'invoice_count' => 'invoice_count',
];

$sortKey = $_GET['sort'] ?? 'name';
if (!array_key_exists($sortKey, $sortableColumns)) {
    $sortKey = 'name';
}
$order = (($_GET['order'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

$sql .= " GROUP BY clients.id, clients.name, clients.email, clients.created_at
        ORDER BY {$sortableColumns[$sortKey]} {$order}";

$stmt = $pdo->prepare($sql);
if ($keyword !== '') {
    $stmt->bindValue(':keyword1', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->bindValue(':keyword2', '%' . $keyword . '%', PDO::PARAM_STR);
}
$stmt->execute();
$clients = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クライアント一覧 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>クライアント一覧</h1>
            <a href="client_new.php" class="btn-primary">+ 新規登録</a>
        </div>

        <form method="get" action="" class="search-form">
            <input type="text" name="keyword" value="<?= h($keyword) ?>" placeholder="クライアント名・メールで検索" autocomplete="off">
            <button type="submit">検索</button>
            <a href="clients_list.php" class="back-btn">クリア</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= sortLink('クライアント名', 'name', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('メール', 'email', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('登録日', 'created_at', $sortKey, $order, $keyword) ?></th>
                        <th><?= sortLink('請求書数', 'invoice_count', $sortKey, $order, $keyword) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr class="row-link" data-href="client_edit.php?id=<?= h($client['id']) ?>">
                            <td><?= h($client['name']) ?></td>
                            <td><?= h($client['email']) ?></td>
                            <td><?= h($client['created_at']) ?></td>
                            <td><?= h($client['invoice_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/row-link.js"></script>
</body>

</html>