<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$id = $_GET['id'] ?? null;

$sql = 'SELECT invoices.*, customers.name AS customer_name
        FROM invoices
        JOIN customers ON invoices.customer_id = customers.id
        WHERE invoices.id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$invoice = $stmt->fetch();

if (!$invoice) {
    exit('請求書が見つかりません');
}

$sql = 'SELECT * FROM invoice_items WHERE invoice_id = :id ORDER BY sort_order';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// 品目ごとの金額を合計して小計・消費税・合計を算出
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['quantity'] * $item['unit_price'];
}
$tax = round($subtotal * ($invoice['tax_rate'] / 100));
$total = $subtotal + $tax;

$statusLabels = statusLabels();
$company = companyInfo();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求書プレビュー | 請求書管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <div class="page-head no-print">
            <h1 class="page-title">請求書プレビュー</h1>
            <div class="preview-actions">
                <button type="button" class="btn-primary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> 印刷 / PDF保存
                </button>
                <a href="invoice_edit.php?id=<?= h($invoice['id']) ?>" class="back-btn" id="back-to-edit-link">編集に戻る</a>
            </div>
        </div>

        <div class="invoice-sheet">
            <div class="invoice-sheet__head">
                <h2 class="invoice-sheet__title">請求書</h2>
                <span class="status-badge status-<?= h($invoice['status']) ?> no-print"><?= h($statusLabels[$invoice['status']]) ?></span>
            </div>

            <div class="invoice-sheet__meta">
                <div class="invoice-sheet__customer">
                    <p class="invoice-sheet__customer-name"><?= h($invoice['customer_name']) ?> 御中</p>
                    <?php if (!empty($invoice['title'])): ?>
                        <p class="invoice-sheet__subject">件名：<?= h($invoice['title']) ?></p>
                    <?php endif; ?>
                </div>
                <table class="invoice-sheet__info">
                    <tr>
                        <th>請求書番号</th>
                        <td><?= h($invoice['invoice_number']) ?></td>
                    </tr>
                    <tr>
                        <th>発行日</th>
                        <td><?= h($invoice['issue_date']) ?></td>
                    </tr>
                    <tr>
                        <th>支払期限</th>
                        <td><?= h($invoice['due_date']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="invoice-sheet__company">
                <p><?= h($company['name']) ?></p>
                <p>〒<?= h($company['zip']) ?> <?= h($company['address']) ?></p>
                <p>TEL: <?= h($company['tel']) ?> / <?= h($company['email']) ?></p>
            </div>

            <table class="invoice-sheet__items">
                <thead>
                    <tr>
                        <th>品目</th>
                        <th>数量</th>
                        <th>単価</th>
                        <th>金額</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= h($item['item_name']) ?></td>
                            <td><?= h($item['quantity']) ?></td>
                            <td><?= h(number_format($item['unit_price'])) ?> 円</td>
                            <td><?= h(number_format($item['quantity'] * $item['unit_price'])) ?> 円</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table class="invoice-sheet__summary">
                <tr>
                    <th>小計</th>
                    <td><?= h(number_format($subtotal)) ?> 円</td>
                </tr>
                <tr>
                    <th>消費税（<?= h($invoice['tax_rate']) ?>%）</th>
                    <td><?= h(number_format($tax)) ?> 円</td>
                </tr>
                <tr class="net">
                    <th>合計</th>
                    <td><?= h(number_format($total)) ?> 円</td>
                </tr>
            </table>
        </div>
    </div>

    <script src="js/invoice-preview.js"></script>
</body>

</html>