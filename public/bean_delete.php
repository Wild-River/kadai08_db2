<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/func.php';

$id = $_POST['id'];

$sql = 'DELETE FROM beans WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

if (!$status) {
    sql_error($stmt);
}
redirect('bean_list.php');
