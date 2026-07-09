<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$sql = 'SELECT stock_movements.id, stock_movements.type, stock_movements.bags, stock_movements.moved_at, beans.name,
        customers.name AS customer_name, customers.company AS customer_company
        FROM stock_movements
        JOIN beans ON stock_movements.bean_id = beans.id
        LEFT JOIN customers ON stock_movements.customer_id = customers.id
        ORDER BY stock_movements.moved_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$movements = $stmt->fetchAll();

$typeLabels = typeLabels();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>入出荷記録一覧 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>入出荷記録一覧</h1>
            <a href="movement_new.php" class="btn-primary">+ 新規登録</a>
        </div>

        <?php if (empty($movements)): ?>
            <p>記録がありません</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>生豆</th>
                            <th>種類</th>
                            <th>袋数</th>
                            <th>顧客</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement): ?>
                            <tr class="row-link" data-href="movement_edit.php?id=<?= h($movement['id']) ?>">
                                <td><?= h($movement['moved_at']) ?></td>
                                <td><?= h($movement['name']) ?></td>
                                <td><?= h($typeLabels[$movement['type']]) ?></td>
                                <td><?= h($movement['bags']) ?></td>
                                <td>
                                    <?php if ($movement['customer_name']): ?>
                                        <?= h($movement['customer_name']) ?><?= $movement['customer_company'] ? '（' . h($movement['customer_company']) . '）' : '' ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/row-link.js"></script>
</body>

</html>