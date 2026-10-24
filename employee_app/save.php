<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    $name = trim($_POST['name']);
    $gender = $_POST['gender'] ?? '';

    // Birthdate resolution
    if (isset($_POST['birthdate']) && !empty($_POST['birthdate'])) {
        $birthdate = $_POST['birthdate'];
    } else {
        $year = $_POST['birth_year'] ?? '';
        $month = $_POST['birth_month'] ?? '';
        $day = $_POST['birth_day'] ?? '';
        $birthdate = ($year && $month && $day) ? "$year-$month-$day" : null;
    }

    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $degree = trim($_POST['degree']);
    $salary = floatval($_POST['salary']);
    $designation_id = intval($_POST['designation_id']);

    if ($id > 0) {
        // Fetch current photo from DB
        $stmt_curr = $conn->prepare("SELECT photo FROM employee_master WHERE id = ?");
        $stmt_curr->bind_param("i", $id);
        $stmt_curr->execute();
        $res_curr = $stmt_curr->get_result();
        $curr_row = $res_curr->fetch_assoc();
        $photo = $curr_row ? $curr_row['photo'] : null;
        $stmt_curr->close();

        // Check if a new file is uploaded
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['photo']['name']));
            $upload_dir = 'uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                if (!empty($photo) && file_exists($upload_dir . $photo)) {
                    @unlink($upload_dir . $photo);
                }
                $photo = $file_name;
            }
        }

        $stmt = $conn->prepare("UPDATE employee_master SET name = ?, gender = ?, birthdate = ?, mobile = ?, email = ?, address = ?, degree = ?, salary = ?, designation_id = ?, photo = ?, updated = NOW() WHERE id = ?");
        $stmt->bind_param("sssssssdisi", $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $photo, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?status=updated");
        exit();
    } else {
        // Insert flow
        $employee_no = trim($_POST['employee_no'] ?? '');
        $photo = null;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['photo']['name']));
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $photo = $file_name;
            }
        }

        $stmt = $conn->prepare("INSERT INTO employee_master (employee_no, name, gender, birthdate, mobile, email, address, degree, salary, designation_id, photo, created, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssssssdis", $employee_no, $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $photo);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?status=created");
        exit();
    }
}

header("Location: index.php");
exit();
?>