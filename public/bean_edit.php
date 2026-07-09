<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$typeLabels = typeLabels();

// 商品名の候補（datalist用）：既存の登録データから重複を除いて取り出す
$stmt = $pdo->query('SELECT DISTINCT name FROM beans ORDER BY name');
$beanNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 仕入先の候補（datalist用）：既存の登録データから重複を除いて取り出す
$stmt = $pdo->query('SELECT DISTINCT supplier FROM beans ORDER BY supplier');
$suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    $sql = 'SELECT id, name, supplier, lot_no, price, kg_per_bag FROM beans WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $bean = $stmt->fetch();

    // この生豆の入出荷履歴も表示
    $sql = 'SELECT stock_movements.type, stock_movements.bags, stock_movements.moved_at,
            customers.name AS customer_name, customers.company AS customer_company
            FROM stock_movements
            LEFT JOIN customers ON stock_movements.customer_id = customers.id
            WHERE stock_movements.bean_id = :id
            ORDER BY stock_movements.moved_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $movements = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $supplier = $_POST['supplier'];
    $lot_no = $_POST['lot_no'];
    // 販売定価はJS側で桁区切り表示しているため、カンマを除去してから保存する
    $price = str_replace(',', '', $_POST['price']);
    $kg_per_bag = $_POST['kg_per_bag'];

    $sql = 'UPDATE beans SET name = :name, supplier = :supplier, lot_no = :lot_no, price = :price, kg_per_bag = :kg_per_bag WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':supplier', $supplier, PDO::PARAM_STR);
    $stmt->bindValue(':lot_no', $lot_no, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_INT);
    $stmt->bindValue(':kg_per_bag', $kg_per_bag, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $status = $stmt->execute();

    if (!$status) {
        sql_error($stmt);
    }
    redirect('bean_list.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>生豆編集 | 請求書管理</title>
    <?php require_once '../config/head.php'; ?>
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">生豆編集</h1>
        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./bean_edit.php" id="edit-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        商品名
                        <input list="bean-name-list" type="text" id="name" name="name" value="<?= h($bean['name']) ?>" class="form-input" autocomplete="off" required>
                    </label>
                    <datalist id="bean-name-list">
                        <?php foreach ($beanNames as $beanName): ?>
                            <option value="<?= h($beanName) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="supplier" class="form-label">
                        仕入先
                        <input list="supplier-list" type="text" id="supplier" name="supplier" value="<?= h($bean['supplier']) ?>" class="form-input" autocomplete="off" required>
                    </label>
                    <datalist id="supplier-list">
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= h($supplier) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="lot_no" class="form-label">
                        Lot No.
                        <input type="text" id="lot_no" name="lot_no" value="<?= h($bean['lot_no']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">
                        販売定価
                        <input type="text" id="price" name="price" value="<?= h(number_format($bean['price'])) ?>" class="form-input money-input" inputmode="numeric" autocomplete="off" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="kg_per_bag" class="form-label">
                        kg/袋
                        <input type="number" step="1" id="kg_per_bag" name="kg_per_bag" value="<?= h($bean['kg_per_bag']) ?>" class="form-input" required>
                    </label>
                </div>

                <input type="hidden" name="id" value="<?= h($bean['id']) ?>">
            </form>

            <div class="form-actions">
                <button type="submit" form="edit-form" class="submit-btn">決定</button>

                <form method="post" action="bean_delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($bean['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="bean_list.php" class="back-btn">戻る</a>
            </div>

            <?php if (empty($movements)): ?>
                <p>記録がありません</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th>種類</th>
                                <th>袋数</th>
                                <th>顧客</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td><?= h($movement['moved_at']) ?></td>
                                    <td><span class="type-badge type-<?= h($movement['type']) ?>"><?= h($typeLabels[$movement['type']]) ?></span></td>
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
    </div>

    <script src="js/money-input.js"></script>
</body>

</html>
