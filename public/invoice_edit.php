<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$statusLabels = statusLabels();

// 顧客選択用に全顧客取得
$customerstmt = $pdo->query("SELECT id, name FROM customers ORDER BY name");
$customers = $customerstmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    // 請求書本体を取得
    $sql = 'SELECT * FROM invoices WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $invoice = $stmt->fetch();

    // 既存の明細行を取得
    $sql = 'SELECT * FROM invoice_items WHERE invoice_id = :id ORDER BY sort_order';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll();

    // プレビュータブ用に小計・消費税・合計を算出
    $previewSubtotal = 0;
    foreach ($items as $item) {
        $previewSubtotal += $item['quantity'] * $item['unit_price'];
    }
    $previewTax = round($previewSubtotal * ($invoice['tax_rate'] / 100));
    $previewTotal = $previewSubtotal + $previewTax;
    $company = companyInfo();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $customer_id = $_POST['customer_id'];
    $invoice_number = $_POST['invoice_number'];
    $title = $_POST['title'];
    $status = $_POST['status'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];
    $tax_rate = $_POST['tax_rate'];

    $pdo->beginTransaction();
    try {
        // ①請求書本体をUPDATE
        $sql = 'UPDATE invoices SET
                    customer_id = :customer_id,
                    invoice_number = :invoice_number,
                    title = :title,
                    status = :status,
                    issue_date = :issue_date,
                    due_date = :due_date,
                    tax_rate = :tax_rate
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->bindValue(':invoice_number', $invoice_number, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':issue_date', $issue_date, PDO::PARAM_STR);
        $stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
        $stmt->bindValue(':tax_rate', $tax_rate, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // ②既存の明細を全削除
        $sql = 'DELETE FROM invoice_items WHERE invoice_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // ③フォームの明細を全INSERT（new と同じ処理）
        $sql = 'INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, sort_order)
                VALUES (:invoice_id, :item_name, :quantity, :unit_price, :sort_order)';
        $stmt = $pdo->prepare($sql);

        foreach ($_POST['item_name'] as $i => $item_name) {
            if (trim($item_name) === '') {
                continue;
            }
            // 単価はJS側で桁区切り表示しているため、カンマを除去してから保存する
            $unit_price = str_replace(',', '', $_POST['unit_price'][$i]);

            $stmt->bindValue(':invoice_id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':item_name', $item_name, PDO::PARAM_STR);
            $stmt->bindValue(':quantity', $_POST['quantity'][$i], PDO::PARAM_STR);
            $stmt->bindValue(':unit_price', $unit_price, PDO::PARAM_STR);
            $stmt->bindValue(':sort_order', $i, PDO::PARAM_INT);
            $stmt->execute();
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        exit('更新エラー: ' . $e->getMessage());
    }

    redirect('invoices_list.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>請求書編集 | 請求書管理</title>
    <?php require_once '../config/head.php'; ?>
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">請求書編集</h1>
        <div class="card">
            <div class="edit-layout">
                <div class="edit-layout__main">
                    <form method="post" action="./invoice_edit.php" id="edit-form">
                        <div class="form-group">
                            <label class="form-label">顧客
                                <select name="customer_id" class="form-input" required>
                                    <option value="">選択してください</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= h($customer['id']) ?>" <?= $invoice['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                            <?= h($customer['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">請求書番号
                                <input type="text" name="invoice_number" class="form-input" value="<?= h($invoice['invoice_number']) ?>" required>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">件名
                                <input type="text" name="title" class="form-input" value="<?= h($invoice['title']) ?>">
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ステータス
                                <select name="status" class="form-input">
                                    <?php foreach ($statusLabels as $key => $label): ?>
                                        <option value="<?= h($key) ?>" <?= $invoice['status'] === $key ? 'selected' : '' ?>>
                                            <?= h($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">発行日
                                <input type="date" name="issue_date" class="form-input" value="<?= h($invoice['issue_date']) ?>" required>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">支払期限
                                <input type="date" name="due_date" class="form-input" value="<?= h($invoice['due_date']) ?>" required>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">消費税率（%）
                                <input type="number" name="tax_rate" class="form-input" value="<?= h($invoice['tax_rate']) ?>" required>
                            </label>
                        </div>

                        <input type="hidden" name="id" value="<?= h($invoice['id']) ?>">
                    </form>
                </div>

                <aside class="edit-layout__preview no-print">
                    <div class="preview-panel">
                        <div class="preview-panel__head">
                            <span class="preview-panel__label">プレビュー</span>

                        </div>

                        <div class="preview-mini">
                            <div class="preview-mini__scale">
                                <div class="invoice-sheet">
                                    <div class="invoice-sheet__head">
                                        <h2 class="invoice-sheet__title">請求書</h2>
                                        <span class="status-badge status-<?= h($invoice['status']) ?>"><?= h($statusLabels[$invoice['status']]) ?></span>
                                    </div>

                                    <div class="invoice-sheet__meta">
                                        <div class="invoice-sheet__customer">
                                            <p class="invoice-sheet__customer-name">
                                                <?php
                                                foreach ($customers as $customer) {
                                                    if ($customer['id'] == $invoice['customer_id']) {
                                                        echo h($customer['name']);
                                                        break;
                                                    }
                                                }
                                                ?> 御中</p>
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
                                            <td><?= h(number_format($previewSubtotal)) ?> 円</td>
                                        </tr>
                                        <tr>
                                            <th>消費税（<?= h($invoice['tax_rate']) ?>%）</th>
                                            <td><?= h(number_format($previewTax)) ?> 円</td>
                                        </tr>
                                        <tr class="net">
                                            <th>合計</th>
                                            <td><?= h(number_format($previewTotal)) ?> 円</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <a href="invoice_preview.php?id=<?= h($invoice['id']) ?>" class="btn-primary preview-panel__print" target="_blank">
                            <i class="fa-solid fa-print"></i> 印刷 / PDF保存
                        </a>
                    </div>
                </aside>
            </div>

            <div class="invoice-items-section">
                <h2>明細</h2>
                <table id="items-table" data-form="edit-form">
                    <thead>
                        <tr>
                            <th>品目</th>
                            <th>数量</th>
                            <th>単価</th>
                            <th>金額</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <?php if (empty($items)): ?>
                            <!-- 明細が無い場合は空行を1つ用意 -->
                            <tr>
                                <td><input form="edit-form" type="text" name="item_name[]" class="form-input"></td>
                                <td><input form="edit-form" type="number" name="quantity[]" class="form-input" value="1"></td>
                                <td><input form="edit-form" type="text" name="unit_price[]" class="form-input money-input" value="0" inputmode="numeric" autocomplete="off"></td>
                                <td class="item-amount">0 円</td>
                                <td><button type="button" class="delete-btn delete-btn--icon" onclick="removeRow(this)" title="削除" aria-label="削除"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><input form="edit-form" type="text" name="item_name[]" class="form-input" value="<?= h($item['item_name']) ?>"></td>
                                    <td><input form="edit-form" type="number" name="quantity[]" class="form-input" value="<?= h($item['quantity']) ?>"></td>
                                    <td><input form="edit-form" type="text" name="unit_price[]" class="form-input money-input" value="<?= h(number_format($item['unit_price'])) ?>" inputmode="numeric" autocomplete="off"></td>
                                    <td class="item-amount"><?= h(number_format($item['quantity'] * $item['unit_price'])) ?> 円</td>
                                    <td><button type="button" class="delete-btn delete-btn--icon" onclick="removeRow(this)" title="削除" aria-label="削除"><i class="fa-solid fa-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="button" form="edit-form" class="submit-btn" onclick="addRow()">＋ 明細行を追加</button>
            </div>

            <div class="form-actions">
                <button type="submit" form="edit-form" class="submit-btn">決定</button>

                <form method="post" action="invoice_delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($invoice['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="invoices_list.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>

    <script src="js/invoice-items.js"></script>
    <script src="js/money-input.js"></script>
    <script src="js/item-amount.js"></script>
    <script src="js/preview-mini.js"></script>
</body>

</html>