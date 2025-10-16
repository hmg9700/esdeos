<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_login_or_redirect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../index.php?page=schedule');
  exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
  header('Location: ../index.php?page=schedule');
  exit;
}

$taskName = trim((string)($_POST['task_name'] ?? ''));
$scheduledDate = (string)($_POST['scheduled_date'] ?? '');
$assignedTo = trim((string)($_POST['assigned_to'] ?? '')); // PIC
$location = trim((string)($_POST['location'] ?? ''));
$activity = trim((string)($_POST['activity'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if ($taskName === '' || $scheduledDate === '') {
  header('Location: ../index.php?page=schedule');
  exit;
}

// discover available columns to avoid breaking your existing schema
$cols = [];
$stmtCols = $pdo->query("SHOW COLUMNS FROM schedules");
foreach ($stmtCols->fetchAll(PDO::FETCH_ASSOC) as $c) $cols[$c['Field']] = true;

// map title field
$titleField = isset($cols['task_name']) ? 'task_name' : (isset($cols['title']) ? 'title' : null);
if ($titleField === null) { $titleField = 'task_name'; } // fallback

$fields = [$titleField, 'scheduled_date'];
$values = [$taskName, $scheduledDate];

if (isset($cols['location']) && $location !== '') {
  $fields[] = 'location'; $values[] = $location;
}
if (isset($cols['pic']) && $assignedTo !== '') {
  $fields[] = 'pic'; $values[] = $assignedTo;
} elseif (isset($cols['assigned_to']) && $assignedTo !== '') {
  $fields[] = 'assigned_to'; $values[] = $assignedTo;
}
if (isset($cols['activity']) && $activity !== '') {
  $fields[] = 'activity'; $values[] = $activity;
}
if (isset($cols['notes']) && $notes !== '') {
  $fields[] = 'notes'; $values[] = $notes;
}
if (isset($cols['created_by']) && current_user()) {
  $fields[] = 'created_by'; $values[] = (int) current_user()['id'];
}

$placeholders = implode(',', array_fill(0, count($fields), '?'));
$sql = "INSERT INTO schedules (" . implode(',', $fields) . ") VALUES ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($values);

header('Location: ../index.php?page=schedule');
exit;
