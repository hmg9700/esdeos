<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');


// --- 1. Get Filters from Query String ---
$from = $_GET['from'] ?? null;
$line = $_GET['line'] ?? '%%'; // Default to '%%' (SQL LIKE wildcard)
$section = $_GET['section'] ?? '%%'; // Default to '%%'
$result = $_GET['result'] ?? '%%'; // Default to '%%'

$where = [];
$params = [];

// --- 2. Build WHERE Clause ---

// Date Filter
if ($from) { 
    $where[] = "DATE(timestamp) = ?"; 
    $params[] = $from; 
}

// Line Filter (using LIKE with '%%' as the 'All' option)
$where[] = "machine_location LIKE ?";
$params[] = $line;

// Section Filter (using LIKE with '%%' as the 'All' option)
$where[] = "section LIKE ?";
$params[] = $section;

// Result Filter (using LIKE with '%%' as the 'All' option)
$where[] = "result LIKE ?";
$params[] = $result;


// --- 3. Execute Query ---
$sql = "SELECT *
        FROM log " . (count($where) ? ("WHERE " . implode(' AND ', $where)) : '') . "
        ORDER BY timestamp DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['rows' => $rows]);