<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    // customer_delete.php からのエラーメッセージ（削除に失敗した場合）を1回だけ表示する
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }

    $sql = 'SELECT id, name, company, email, phone, note FROM customers WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch();

    // この顧客の請求書履歴も表示（①のmovements相当）
    $sql = 'SELECT id, invoice_number, status, issue_date, due_date
            FROM invoices
            WHERE customer_id = :id
            ORDER BY issue_date DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $invoices = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $note = $_POST['note'];

    $sql = 'UPDATE customers SET name = :name, company = :company, email = :email, phone = :phone, note = :note WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':company', $company, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindValue(':note', $note, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
    <title>顧客編集 | 請求書管理</title>
    <?php require_once '../config/head.php'; ?>
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">顧客編集</h1>
        <div class="card">
            <?php if (!empty($error)): ?>
                <p class="error-message"><?= h($error) ?></p>
            <?php endif; ?>
            <form method="post" action="./customer_edit.php" id="edit-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        顧客名
                        <input type="text" id="name" name="name" value="<?= h($customer['name']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="company" class="form-label">
                        会社名
                        <input type="text" id="company" name="company" value="<?= h($customer['company']) ?>" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        電話番号
                        <input type="text" id="phone" name="phone" value="<?= h($customer['phone']) ?>" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        メール
                        <input type="email" id="email" name="email" value="<?= h($customer['email']) ?>" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label for="note" class="form-label">
                        備考
                        <textarea id="note" name="note" class="form-input"><?= h($customer['note']) ?></textarea>
                    </label>
                </div>

                <input type="hidden" name="id" value="<?= h($customer['id']) ?>">
            </form>

            <div class="form-actions">
                <button type="submit" form="edit-form" class="submit-btn">変更</button>

                <form method="post" action="customer_delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($customer['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="customer_list.php" class="back-btn">戻る</a>
            </div>

            <?php if (empty($invoices)): ?>
                <p>請求書がありません</p>
            <?php else: ?>
                <div class="table-wrapper">
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
                                <tr class="row-link" data-href="invoice_edit.php?id=<?= h($invoice['id']) ?>">
                                    <td><?= h($invoice['invoice_number']) ?></td>
                                    <td><span class="status-badge status-<?= h($invoice['status']) ?>"><?= h(statusLabels()[$invoice['status']]) ?></span></td>
                                    <td><?= h($invoice['issue_date']) ?></td>
                                    <td><?= h($invoice['due_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/row-link.js"></script>
</body>

</html>