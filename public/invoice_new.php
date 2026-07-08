<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

// クライアント選択用のプルダウンに使うため、全クライアントを取得
$stmt = $pdo->query("SELECT id, name FROM clients ORDER BY name");
$clients = $stmt->fetchAll();

$statusLabels = statusLabels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $invoice_number = $_POST['invoice_number'];
    $title = $_POST['title'];
    $status = $_POST['status'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];
    $tax_rate = $_POST['tax_rate'];

    // ①トランザクション開始（請求書本体と明細を「まとめて成功 or まとめて失敗」させる）
    // 「全部成功したら確定、1つでも失敗したら全部なかったことにする」
    $pdo->beginTransaction();
    try {
        // 請求書本体をINSERT
        $sql = 'INSERT INTO invoices (client_id, invoice_number, title, status, issue_date, due_date, tax_rate)
                VALUES (:client_id, :invoice_number, :title, :status, :issue_date, :due_date, :tax_rate)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindValue(':invoice_number', $invoice_number, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':issue_date', $issue_date, PDO::PARAM_STR);
        $stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
        $stmt->bindValue(':tax_rate', $tax_rate, PDO::PARAM_STR);
        $stmt->execute();

        // ②今INSERTした請求書のidを取得（明細のinvoice_idに使う）
        // 明細のinvoice_idには「今登録したばかりの請求書のid」が必要。
        // でもAUTO_INCREMENTのidは登録するまで分かりません。
        // $pdo->lastInsertId()は「直前にINSERTしたレコードのid」を取得する関数、これで本体と明細を紐付ける。
        $invoice_id = $pdo->lastInsertId();

        // ③明細行をループでINSERT
        $sql = 'INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, sort_order)
                VALUES (:invoice_id, :item_name, :quantity, :unit_price, :sort_order)';
        $stmt = $pdo->prepare($sql);

        foreach ($_POST['item_name'] as $i => $item_name) {
            // 品目名が空の行はスキップ（フォームで余った空行を無視する）
            if (trim($item_name) === '') {
                continue;
            }
            // 単価はJS側で桁区切り表示しているため、カンマを除去してから保存する
            $unit_price = str_replace(',', '', $_POST['unit_price'][$i]);

            $stmt->bindValue(':invoice_id', $invoice_id, PDO::PARAM_INT);
            $stmt->bindValue(':item_name', $item_name, PDO::PARAM_STR);
            $stmt->bindValue(':quantity', $_POST['quantity'][$i], PDO::PARAM_STR);
            $stmt->bindValue(':unit_price', $unit_price, PDO::PARAM_STR);
            $stmt->bindValue(':sort_order', $i, PDO::PARAM_INT);
            $stmt->execute();
        }

        // ④全部成功したらコミット（確定）
        $pdo->commit();
    } catch (Exception $e) {
        // ⑤途中で失敗したらロールバック（全部なかったことにする）
        $pdo->rollBack();
        exit('登録エラー: ' . $e->getMessage());
    }

    redirect('invoices_list.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求書作成 | 請求書管理</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once '../config/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">請求書作成</h1>
        <div class="card">
            <form method="post" action="./invoice_new.php">
                <div class="form-group">
                    <label class="form-label">クライアント
                        <select name="client_id" class="form-input" required>
                            <option value="">選択してください</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= h($client['id']) ?>"><?= h($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">請求書番号
                        <input type="text" name="invoice_number" class="form-input" placeholder="INV-2026-0001" required>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">件名
                        <input type="text" name="title" class="form-input">
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">ステータス
                        <select name="status" class="form-input">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= h($key) ?>"><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">発行日
                        <input type="date" name="issue_date" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">支払期限
                        <input type="date" name="due_date" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">消費税率（%）
                        <input type="number" name="tax_rate" class="form-input" value="10" required>
                    </label>
                </div>

                <h2>明細</h2>
                <table id="items-table">
                    <thead>
                        <tr>
                            <th>品目</th>
                            <th>数量</th>
                            <th>単価</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <!-- JSで行を追加する。最初に1行だけ用意しておく -->
                        <tr>
                            <td><input type="text" name="item_name[]" class="form-input"></td>
                            <td><input type="number" name="quantity[]" class="form-input" value="1"></td>
                            <td><input type="text" name="unit_price[]" class="form-input money-input" value="0" inputmode="numeric" autocomplete="off"></td>
                            <td><button type="button" class="delete-btn" onclick="removeRow(this)">削除</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="submit-btn" onclick="addRow()">＋ 明細行を追加</button>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">保存</button>
                    <a href="invoices_list.php" class="back-btn">戻る</a>
                </div>
            </form>
        </div>
    </div>

    <script src="js/invoice-items.js"></script>
    <script src="js/money-input.js"></script>
</body>

</html>