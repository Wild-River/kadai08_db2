<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $note = $_POST['note'];

    $sql = 'INSERT INTO customers (name, company, email, phone, note) VALUES (:name, :company, :email, :phone, :note)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':company', $company, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $status = $stmt->execute();

    if (!$status) {
        sql_error($stmt);
    }
    redirect('customer_list.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>顧客新規登録 | 請求書管理</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>
    <div class="container">
        <h1 class="page-title">顧客新規登録</h1>
        <div class="card">
            <form method="post" action="./customer_new.php" id="new-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        顧客名
                        <input type="text" id="name" name="name" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="company" class="form-label">
                        会社名
                        <input type="text" id="company" name="company" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        電話番号
                        <input type="text" id="phone" name="phone" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        メール
                        <input type="email" id="email" name="email" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="note" class="form-label">
                        備考
                        <textarea id="note" name="note" class="form-input"></textarea>
                    </label>
                </div>

            </form>

            <div class="form-actions">
                <button type="submit" form="new-form" class="submit-btn">登録する</button>
                <a href="customer_list.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>
</body>

</html>