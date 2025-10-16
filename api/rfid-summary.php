<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$line = $_GET['line'] ?? '%%';

function getSectionData($pdo, $month, $year, $line, $section) {
    $sql = "
    WITH latest_per_day AS (
        SELECT
            machine_location,
            name,
            nik,
            DAY(timestamp) AS day_of_month,
            result,
            timestamp,
            ROW_NUMBER() OVER (
                PARTITION BY machine_location, name, nik, DAY(timestamp)
                ORDER BY timestamp DESC
            ) AS rn
        FROM log
        WHERE 
            MONTH(timestamp) = ? AND 
            YEAR(timestamp) = ? AND 
            section = ? AND 
            machine_location LIKE ?
    )
    SELECT
        machine_location,
        name,
        nik,";

    // Add dynamic day columns
    for ($day = 1; $day <= 31; $day++) {
        $sql .= "
        MAX(CASE WHEN day_of_month = $day THEN result ELSE '' END) AS `$day`,";
    }
    $sql = rtrim($sql, ',');

    $sql .= "
    FROM latest_per_day
    WHERE rn = 1
    GROUP BY machine_location, name, nik
    ORDER BY machine_location, name, nik
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$month, $year, $section, $line]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get both sections
$section1 = getSectionData($pdo, $month, $year, $line, 1);
$section2 = getSectionData($pdo, $month, $year, $line, 2);

// Return both in JSON
echo json_encode([
    'section1' => $section1,
    'section2' => $section2
]);
