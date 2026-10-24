<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $id   = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if (!empty($name)) {
        if ($id > 0) {
            // Update existing designation
            $stmt = $conn->prepare("UPDATE designation SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            $status = "updated";
        } else {
            // Insert new designation
            $stmt = $conn->prepare("INSERT INTO designation (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $status = "created";
        }

        $stmt->execute();
        $stmt->close();

        header("Location: designations.php?status=" . $status);
        exit();
    }
}

header("Location: designations.php");
exit();
?>