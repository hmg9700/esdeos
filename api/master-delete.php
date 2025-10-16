<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_login_or_redirect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../index.php?page=master'); exit;
}
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
  header('Location: ../index.php?page=master'); exit;
}

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: ../index.php?page=master'); exit; }

$table = match ($type) {
  'line' => 'master_lines',
  'process' => 'master_processes',
  'item' => 'master_items',
  default => null
};
if (!$table) { header('Location: ../index.php?page=master'); exit; }

$stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
$stmt->execute([$id]);

header('Location: ../index.php?page=master'); exit;
