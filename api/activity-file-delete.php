<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
if (!verify_csrf($_POST['csrf'] ?? '')) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Bad CSRF']); exit; }

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Bad id']); exit; }

$stmt = $pdo->prepare("SELECT filename FROM activity_files WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$file) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }

$del = $pdo->prepare("DELETE FROM activity_files WHERE id=?");
$del->execute([$id]);

$path = dirname(__DIR__) . '/uploads/activity/' . $file['filename'];
if (is_file($path)) @unlink($path);

echo json_encode(['ok' => true]);
