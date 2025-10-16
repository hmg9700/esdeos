<?php
require_once __DIR__ . '/../config/db.php';
if (empty($_SESSION['user'])) { header('Location: ../index.php?page=login'); exit; }
$id = (int)($_GET['id'] ?? 0);
if ($id) {
  $pdo->exec("CREATE TABLE IF NOT EXISTS master_items (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) UNIQUE)");
  $stmt = $pdo->prepare("DELETE FROM master_items WHERE id=?");
  $stmt->execute([$id]);
}
header('Location: ../index.php?page=master');
