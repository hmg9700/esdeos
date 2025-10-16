<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['log_id']) || !isset($data['result'])) {
    echo json_encode(['success' => false, 'error' => 'Bad input']);
    exit;
}
$log_id = intval($data['log_id']);
$result = strtoupper($data['result']);
if ($result !== 'OK' && $result !== 'NG') {
    echo json_encode(['success' => false, 'error' => 'Invalid result']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "rfid");
$stmt = $conn->prepare("UPDATE log SET result = ? WHERE log_id = ?");
$stmt->bind_param('si', $result, $log_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
