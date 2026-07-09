<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $sql = 'DELETE FROM customers WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    try {
        $stmt->execute();
    } catch (PDOException $e) {
        // 請求書などが紐づいている場合は外部キー制約違反になる（customers ← invoices は ON DELETE NO ACTION）
        $_SESSION['error'] = 'この顧客には請求書が紐づいているため削除できません。';
        redirect('customer_edit.php?id=' . $id);
    }

    redirect('customer_list.php');
}
