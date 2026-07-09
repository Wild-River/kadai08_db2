<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$id = $_POST['id'];

$sql = 'DELETE FROM stock_movements WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

if (!$status) {
    sql_error($stmt);
}
redirect('movement_list.php');
