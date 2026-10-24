<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Check if designation is in use
        $check = $conn->prepare("SELECT id FROM employee_master WHERE designation_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Cannot delete this designation because it is assigned to active employees."]);
        } else {
            $stmt = $conn->prepare("DELETE FROM designation WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "danger", "message" => "The designation has been deleted successfully!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to delete designation."]);
            }
            $stmt->close();
        }
        $check->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid ID."]);
    }
}
?>