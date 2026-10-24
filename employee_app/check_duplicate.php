<?php
include 'db.php';
header('Content-Type: application/json');

$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');
$exclude_id = intval($_GET['exclude_id'] ?? 0);

if (!in_array($field, ['email', 'mobile']) || $value === '') {
    echo json_encode(['exists' => false]);
    exit;
}

if ($exclude_id > 0) {
    $stmt = $conn->prepare("SELECT id FROM employee_master WHERE $field = ? AND id != ?");
    $stmt->bind_param("si", $value, $exclude_id);
} else {
    $stmt = $conn->prepare("SELECT id FROM employee_master WHERE $field = ?");
    $stmt->bind_param("s", $value);
}
$stmt->execute();
$stmt->store_result();
$exists = $stmt->num_rows > 0;
$stmt->close();

echo json_encode(['exists' => $exists]);