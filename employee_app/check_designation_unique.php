<?php
include 'db.php';

if (isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("SELECT id FROM designation WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM designation WHERE name = ?");
        $stmt->bind_param("s", $name);
    }

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "false"; // Already exists
    } else {
        echo "true"; // Unique
    }
    $stmt->close();
}
?>