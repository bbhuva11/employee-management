<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Soft delete: Update status enum to 'Inactive'
        $stmt = $conn->prepare("UPDATE employee_master SET status = 'Inactive', updated = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'danger', 'message' => 'Employee moved to Recycle Bin successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to move employee to Recycle Bin.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID provided.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>