<?php
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch current employee details
$stmt = $conn->prepare("SELECT * FROM employee_master WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$employee = $res->fetch_assoc();
$stmt->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $gender = $_POST['gender'] ?? '';
    
    $birth_year = $_POST['birth_year'] ?? '';
    $birth_month = $_POST['birth_month'] ?? '';
    $birth_day = $_POST['birth_day'] ?? '';
    $birthdate = ($birth_year && $birth_month && $birth_day) ? "$birth_year-$birth_month-$birth_day" : $employee['birthdate'];

    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $degree = trim($_POST['degree']);
    $salary = floatval($_POST['salary']);
    $designation_id = intval($_POST['designation_id']);
    
    // Retain existing photo if no new one is uploaded
    $photo = $employee['photo'];

    // Handle new photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp = $_FILES['photo']['tmp_name'];
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['photo']['name']));
        $file_name = time() . '_' . $clean_name;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Delete old photo file using unlink()
            if (!empty($employee['photo']) && file_exists($upload_dir . $employee['photo'])) {
                unlink($upload_dir . $employee['photo']);
            }
            $photo = $file_name;
        }
    }

    $update_stmt = $conn->prepare("UPDATE employee_master SET name = ?, gender = ?, birthdate = ?, mobile = ?, email = ?, address = ?, degree = ?, salary = ?, designation_id = ?, photo = ?, updated = NOW() WHERE id = ?");
    $update_stmt->bind_param("sssssssdisi", $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $photo, $id);

    if ($update_stmt->execute()) {
        header("Location: index.php?status=updated");
        exit();
    } else {
        $error = "Error updating employee: " . $conn->error;
    }
    $update_stmt->close();
}

$designations = $conn->query("SELECT * FROM designation ORDER BY name ASC");

// Parse existing birthdate
$b_year = '';
$b_month = '';
$b_day = '';
if (!empty($employee['birthdate']) && $employee['birthdate'] !== '0000-00-00') {
    $date_parts = explode('-', $employee['birthdate']);
    if (count($date_parts) === 3) {
        $b_year = $date_parts[0];
        $b_month = $date_parts[1];
        $b_day = $date_parts[2];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        label.error {
            color: #dc3545;
            font-size: 0.825rem;
            margin-top: 0.25rem;
            display: block;
            font-weight: normal;
        }
        .form-control.error, .form-select.error {
            border-color: #dc3545;
        }
    </style>
</head>
<body class="bg-white">

<div class="container py-5" style="max-width: 850px;">
    <div class="card shadow-sm border rounded-0 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold text-dark m-0" style="font-size: 22px;">Edit Employee</h3>
            <a href="index.php" class="btn btn-info text-white btn-sm px-3 fw-bold" style="background-color: #cfe2ff; color: #052c65 !important; border: none;">Back</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" id="employeeEditForm" novalidate>
            <input type="hidden" name="id" id="employee_id" value="<?= $employee['id'] ?>">

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Employee No:</label>
                <div class="col-sm-9">
                    <input type="text" name="employee_no" class="form-control bg-light" value="<?= htmlspecialchars($employee['employee_no']) ?>" readonly>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Full Name:</label>
                <div class="col-sm-9">
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($employee['name']) ?>">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Gender:</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" <?= ($employee['gender'] === 'Male') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="genderMale">Male</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" <?= ($employee['gender'] === 'Female') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="genderFemale">Female</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" <?= ($employee['gender'] === 'Other') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="genderOther">Other</label>
                    </div>
                    <div id="gender-error-container"></div>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Birthdate:</label>
                <div class="col-sm-9">
                    <div class="row g-2">
                        <div class="col-4">
                            <select name="birth_year" id="birth_year" class="form-select">
                                <option value="">Year</option>
                                <?php for ($y = date('Y'); $y >= 1940; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($b_year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="birth_month" id="birth_month" class="form-select">
                                <option value="">Month</option>
                                <?php 
                                $months = [
                                    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                                    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                                    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                                ];
                                foreach ($months as $m_num => $m_name): ?>
                                    <option value="<?= $m_num ?>" <?= ($b_month == $m_num) ? 'selected' : '' ?>><?= $m_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="birth_day" id="birth_day" class="form-select">
                                <option value="">Day</option>
                                <?php for ($d = 1; $d <= 31; $d++): 
                                    $d_val = str_pad($d, 2, '0', STR_PAD_LEFT);
                                ?>
                                    <option value="<?= $d_val ?>" <?= ($b_day == $d_val) ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div id="birthdate-error-container"></div>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Mobile:</label>
                <div class="col-sm-9">
                    <input type="text" name="mobile" id="mobile" maxlength="10" class="form-control" value="<?= htmlspecialchars($employee['mobile']) ?>">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Email:</label>
                <div class="col-sm-9">
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold text-end pt-0">Address:</label>
                <div class="col-sm-9">
                    <textarea name="address" id="address" class="form-control" rows="3"><?= htmlspecialchars($employee['address']) ?></textarea>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Degree:</label>
                <div class="col-sm-9">
                    <input type="text" name="degree" id="degree" class="form-control" value="<?= htmlspecialchars($employee['degree']) ?>">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Salary:</label>
                <div class="col-sm-9">
                    <input type="number" step="0.01" name="salary" id="salary" class="form-control" value="<?= htmlspecialchars($employee['salary']) ?>">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Designation:</label>
                <div class="col-sm-9">
                    <select name="designation_id" id="designation_id" class="form-select">
                        <option value="">Select Designation</option>
                        <?php while($d = $designations->fetch_assoc()): ?>
                            <option value="<?= $d['id'] ?>" <?= ($employee['designation_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Photo:</label>
                <div class="col-sm-9">
                    <div class="mb-2 d-flex align-items-center gap-3">
                        <?php 
                        $hasPhoto = !empty($employee['photo']) && file_exists(__DIR__ . '/uploads/' . $employee['photo']);
                        ?>
                        <img id="photoPreview" 
                             src="<?= $hasPhoto ? 'uploads/' . htmlspecialchars($employee['photo']) : '#' ?>" 
                             alt="Photo Preview" 
                             class="rounded-circle border <?= $hasPhoto ? '' : 'd-none' ?>" 
                             style="width: 55px; height: 55px; object-fit: cover;">
                        <span id="photoStatusText" class="text-muted small">
                            <?= $hasPhoto ? 'Current: ' . htmlspecialchars($employee['photo']) : 'No image uploaded yet.' ?>
                        </span>
                    </div>
                    <input type="file" name="photo" id="photoInput" class="form-control" accept="image/*">
                    <div class="form-text text-muted">Select an image to update the photo. Leave empty to keep the existing photo.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-9 offset-sm-3 text-end">
                    <button type="submit" id="submitBtn" class="btn btn-primary px-4 fw-bold">Update Employee</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script>
$(document).ready(function() {
    const employeeId = <?= $employee['id'] ?>;

    // Instant photo preview when selecting a new file while editing
    $('#photoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#photoPreview').attr('src', event.target.result).removeClass('d-none');
                $('#photoStatusText').text('New file selected: ' + file.name);
            };
            reader.readAsDataURL(file);
        }
    });

    $('#employeeEditForm').validate({
        rules: {
            name: {
                required: true
            },
            gender: {
                required: true
            },
            birth_year: {
                required: true
            },
            birth_month: {
                required: true
            },
            birth_day: {
                required: true
            },
            mobile: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10,
                remote: {
                    url: 'check_duplicate.php',
                    type: 'GET',
                    data: {
                        field: 'mobile',
                        value: function() { return $('#mobile').val(); },
                        exclude_id: employeeId
                    },
                    dataFilter: function(response) {
                        const json = JSON.parse(response);
                        return !json.exists;
                    }
                }
            },
            email: {
                required: true,
                email: true,
                remote: {
                    url: 'check_duplicate.php',
                    type: 'GET',
                    data: {
                        field: 'email',
                        value: function() { return $('#email').val(); },
                        exclude_id: employeeId
                    },
                    dataFilter: function(response) {
                        const json = JSON.parse(response);
                        return !json.exists;
                    }
                }
            },
            address: {
                required: true
            },
            degree: {
                required: true
            },
            salary: {
                required: true,
                number: true
            },
            designation_id: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Name is required."
            },
            gender: {
                required: "Gender is required."
            },
            birth_year: {
                required: "Birthdate is required."
            },
            birth_month: {
                required: "Birthdate is required."
            },
            birth_day: {
                required: "Birthdate is required."
            },
            mobile: {
                required: "Phone number is required.",
                digits: "Please enter a valid 10-digit mobile number.",
                minlength: "Mobile number must be exactly 10 digits.",
                maxlength: "Mobile number must be exactly 10 digits.",
                remote: "This mobile number is already registered."
            },
            email: {
                required: "Email is required.",
                email: "Please enter a valid email address.",
                remote: "This email address is already registered."
            },
            address: {
                required: "Address is required."
            },
            degree: {
                required: "Degree is required."
            },
            salary: {
                required: "Salary is required.",
                number: "Please enter a valid salary amount."
            },
            designation_id: {
                required: "Designation is required."
            }
        },
        errorElement: 'label',
        errorPlacement: function(error, element) {
            if (element.attr("name") === "gender") {
                error.appendTo("#gender-error-container");
            } else if (element.attr("name") === "birth_year" || element.attr("name") === "birth_month" || element.attr("name") === "birth_day") {
                if ($("#birthdate-error-container").find("label.error").length === 0) {
                    error.appendTo("#birthdate-error-container");
                }
            } else {
                error.appendTo(element.parent());
            }
        }
    });
});
</script>
</body>
</html>