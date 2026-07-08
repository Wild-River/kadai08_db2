<?php
session_start();
require_once '../config/db.php';
require_once '../config/func.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        redirect('dashboard.php'); // ①既存のredirect()をそのまま使う
    } else {
        $error = 'ユーザー名またはパスワードが違います';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>ログイン</h1>
    <?php if ($error): ?>
        <p style="color:red;"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label>ユーザー名: <input type="text" name="username" required></label><br>
        <label>パスワード: <input type="password" name="password" required></label><br>
        <button type="submit">ログイン</button>
    </form>
</body>

</html>