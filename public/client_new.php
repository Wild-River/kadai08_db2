<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $sql = 'INSERT INTO clients (name, email) VALUES (:name, :email)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $status = $stmt->execute();

    if (!$status) {
        sql_error($stmt);
    }
    redirect('clients_list.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>クライアント新規登録 | 請求書管理</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>
    <div class="container">
        <h1 class="page-title">クライアント新規登録</h1>
        <div class="card">
            <form method="post" action="./client_new.php">
                <div class="form-group">
                    <label for="name" class="form-label">
                        クライアント名
                        <input type="text" id="name" name="name" class="form-input" required>
                    </label>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">
                        メール
                        <input type="email" id="email" name="email" class="form-input">
                    </label>
                </div>
                <button type="submit" class="submit-btn">登録</button>
                <a href="clients_list.php" class="back-btn">戻る</a>
            </form>
        </div>
    </div>
</body>

</html>