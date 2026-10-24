<?php
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 1. Get the current photo filename from the database
    $stmt = $conn->prepare("SELECT photo FROM employee_master WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $photo = $row['photo'];

        // 2. Delete the physical image file from the folder using unlink()
        if (!empty($photo)) {
            $file_path = 'uploads/' . $photo;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // 3. Clear the photo entry in the database
        $update = $conn->prepare("UPDATE employee_master SET photo = NULL WHERE id = ?");
        $update->bind_param("i", $id);
        $update->execute();
        $update->close();
    }
    $stmt->close();
}

// Redirect back to index.php
header("Location: index.php");
exit();
?>