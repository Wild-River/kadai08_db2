<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    $sql = 'SELECT id, name, email FROM clients WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $client = $stmt->fetch();

    // このクライアントの請求書履歴も表示（①のmovements相当）
    $sql = 'SELECT invoice_number, status, issue_date, due_date
            FROM invoices
            WHERE client_id = :id
            ORDER BY issue_date DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $invoices = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    $sql = 'UPDATE clients SET name = :name, email = :email WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クライアント編集 | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">クライアント編集</h1>
        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./client_edit.php" id="edit-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        クライアント名
                        <input type="text" id="name" name="name" value="<?= h($client['name']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        メール
                        <input type="email" id="email" name="email" value="<?= h($client['email']) ?>" class="form-input">
                    </label>
                </div>

                <input type="hidden" name="id" value="<?= h($client['id']) ?>">
            </form>

            <div class="form-actions">
                <button type="submit" form="edit-form" class="submit-btn">決定</button>

                <form method="post" action="client_delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($client['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="clients_list.php" class="back-btn">戻る</a>
            </div>

            <div class="table-wrapper">
                <?php if (empty($invoices)): ?>
                    <p>請求書がありません</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>請求書番号</th>
                                <th>ステータス</th>
                                <th>発行日</th>
                                <th>支払期限</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><?= h($invoice['invoice_number']) ?></td>
                                    <td><span class="status-badge status-<?= h($invoice['status']) ?>"><?= h(statusLabels()[$invoice['status']]) ?></span></td>
                                    <td><?= h($invoice['issue_date']) ?></td>
                                    <td><?= h($invoice['due_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>