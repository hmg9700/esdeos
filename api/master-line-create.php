<?php
require_once __DIR__ . '/../config/db.php';
if (empty($_SESSION['user'])) { header('Location: ../index.php?page=login'); exit; }
$name = trim($_POST['name'] ?? '');
if ($name) {
  $pdo->exec("CREATE TABLE IF NOT EXISTS master_lines (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
  $stmt = $pdo->prepare("INSERT IGNORE INTO master_lines (name) VALUES (?)");
  $stmt->execute([$name]);
}
header('Location: ../index.php?page=master');
