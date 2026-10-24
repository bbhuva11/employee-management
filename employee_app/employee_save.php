<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id             = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name           = trim($_POST['name']);
    $gender         = trim($_POST['gender']);
    
    // Combine individual dropdowns into YYYY-MM-DD
    $dob_year       = trim($_POST['dob_year']);
    $dob_month      = trim($_POST['dob_month']);
    $dob_day        = trim($_POST['dob_day']);
    $birthdate      = $dob_year . '-' . $dob_month . '-' . $dob_day;

    $mobile         = trim($_POST['mobile']);
    $email          = trim($_POST['email']);
    $address        = trim($_POST['address']);
    $degree         = trim($_POST['degree']);
    $salary         = floatval($_POST['salary']);
    $designation_id = intval($_POST['designation_id']);

    // Handle File Upload
    $photoName = "";
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $photoName = time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            move_uploaded_file($fileTmpPath, $uploadDir . $photoName);
        }
    }

    if ($id > 0) {
        // Update
        if (!empty($photoName)) {
            $old = $conn->prepare("SELECT photo FROM employee_master WHERE id = ?");
            $old->bind_param("i", $id);
            $old->execute();
            $oldRes = $old->get_result()->fetch_assoc();
            if ($oldRes && !empty($oldRes['photo']) && file_exists($uploadDir . $oldRes['photo'])) {
                unlink($uploadDir . $oldRes['photo']);
            }
            $old->close();

            $stmt = $conn->prepare("UPDATE employee_master SET name = ?, gender = ?, birthdate = ?, mobile = ?, email = ?, address = ?, degree = ?, salary = ?, designation_id = ?, photo = ?, updated = NOW() WHERE id = ?");
            $stmt->bind_param("ssssssssisi", $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $photoName, $id);
        } else {
            $stmt = $conn->prepare("UPDATE employee_master SET name = ?, gender = ?, birthdate = ?, mobile = ?, email = ?, address = ?, degree = ?, salary = ?, designation_id = ?, updated = NOW() WHERE id = ?");
            $stmt->bind_param("ssssssssii", $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $id);
        }
        $status = "updated";
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO employee_master (name, gender, birthdate, mobile, email, address, degree, salary, designation_id, photo, created, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssssssis", $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $photoName);
        $status = "created";
    }

    $stmt->execute();
    $stmt->close();

    header("Location: index.php?status=" . $status);
    exit();
}

header("Location: index.php");
exit();
?>