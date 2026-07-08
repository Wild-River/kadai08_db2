<?php
require_once __DIR__ . '/env.php';

$dbn = sprintf(
    'mysql:dbname=%s;charset=utf8mb4;port=%s;host=%s',
    getenv('DB_NAME'),
    getenv('DB_PORT'),
    getenv('DB_HOST')
);
$user = getenv('DB_USER');
$pwd  = getenv('DB_PASS');

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dbn, $user, $pwd, $options);
} catch (PDOException $e) {
    exit('DB接続失敗: ' . $e->getMessage());
}
