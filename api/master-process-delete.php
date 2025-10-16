<?php
require_once __DIR__ . '/../config/db.php';
if (empty($_SESSION['user'])) { header('Location: ../index.php?page=login'); exit; }
$id = (int)($_GET['id'] ?? 0);
if ($id) {
  $pdo->exec("CREATE TABLE IF NOT EXISTS master_processes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128) UNIQUE)");
  $stmt = $pdo->prepare("DELETE FROM master_processes WHERE id=?");
  $stmt->execute([$id]);
}
header('Location: ../index.php?page=master');
