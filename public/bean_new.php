<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

// 商品名の候補（datalist用）：既存の登録データから重複を除いて取り出す
$stmt = $pdo->query('SELECT DISTINCT name FROM beans ORDER BY name');
$beanNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 仕入先の候補（datalist用）：既存の登録データから重複を除いて取り出す
$stmt = $pdo->query('SELECT DISTINCT supplier FROM beans ORDER BY supplier');
$suppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $supplier = $_POST['supplier'];
    $lot_no = $_POST['lot_no'];
    // 販売定価はJS側で桁区切り表示しているため、カンマを除去してから保存する
    $price = str_replace(',', '', $_POST['price']);
    $kg_per_bag = $_POST['kg_per_bag'];

    $sql = 'INSERT INTO beans (name, supplier, lot_no, price, kg_per_bag) VALUES (:name, :supplier, :lot_no, :price, :kg_per_bag)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':supplier', $supplier, PDO::PARAM_STR);
    $stmt->bindValue(':lot_no', $lot_no, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_INT);
    $stmt->bindValue(':kg_per_bag', $kg_per_bag, PDO::PARAM_STR);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生豆登録 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">生豆登録</h1>
        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./bean_new.php">
                <div class="form-group">
                    <label for="name" class="form-label">
                        商品名
                        <input list="bean-name-list" type="text" id="name" name="name" class="form-input" autocomplete="off" required>
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
                        <input list="supplier-list" type="text" id="supplier" name="supplier" class="form-input" autocomplete="off" required>
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
                        <input type="text" id="lot_no" name="lot_no" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">
                        販売定価
                        <input type="text" id="price" name="price" class="form-input money-input" inputmode="numeric" autocomplete="off" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="kg_per_bag" class="form-label">
                        kg/袋
                        <input type="number" step="1" id="kg_per_bag" name="kg_per_bag" min="0" max="9999" class="form-input" required>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">登録する</button>
                    <a href="bean_list.php" class="back-btn">戻る</a>
                </div>
            </form>
        </div>
    </div>

    <script src="js/money-input.js"></script>
</body>

</html>