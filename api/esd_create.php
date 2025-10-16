<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_login_or_redirect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php?page=checksheet');
  exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
  header('Location: index.php?page=checksheet');
  exit;
}

$assetTag = trim((string)($_POST['asset_tag'] ?? ''));
$location = trim((string)($_POST['location'] ?? ''));
$result   = (string)($_POST['result'] ?? '');
$remarks  = trim((string)($_POST['remarks'] ?? ''));

if ($assetTag === '' || $location === '' || !in_array($result, ['pass','fail'], true)) {
  header('Location: index.php?page=checksheet');
  exit;
}

$user = current_user();
$uid = $user ? (int) $user['id'] : null;

$stmt = $pdo->prepare("INSERT INTO esd_checks (asset_tag, location, result, remarks, created_by) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$assetTag, $location, $result, $remarks !== '' ? $remarks : null, $uid]);

header('Location: index.php?page=dashboard');
exit;
