<?php
include 'db.php';

// Auto-generate next employee number (e.g., OP0001, OP0002)
$result = $conn->query("SELECT employee_no FROM employee_master ORDER BY id DESC LIMIT 1");
$next_emp_no = 'OP0001';
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_no = $row['employee_no'];
    if (preg_match('/^OP(\d+)$/', $last_no, $matches)) {
        $num = intval($matches[1]) + 1;
        $next_emp_no = 'OP' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_no = trim($_POST['employee_no']);
    $name = trim($_POST['name']);
    $gender = $_POST['gender'] ?? '';
    
    $birth_year = $_POST['birth_year'] ?? '';
    $birth_month = $_POST['birth_month'] ?? '';
    $birth_day = $_POST['birth_day'] ?? '';
    $birthdate = ($birth_year && $birth_month && $birth_day) ? "$birth_year-$birth_month-$birth_day" : '';

    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $degree = trim($_POST['degree']);
    $salary = floatval($_POST['salary']);
    $designation_id = intval($_POST['designation_id']);
    
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['photo']['name']));
        $file_name = time() . '_' . $clean_name;
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $photo = $file_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO employee_master (employee_no, name, gender, birthdate, mobile, email, address, degree, salary, designation_id, photo, created, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("ssssssssdis", $employee_no, $name, $gender, $birthdate, $mobile, $email, $address, $degree, $salary, $designation_id, $photo);
    
    if ($stmt->execute()) {
        header("Location: index.php?status=created");
        exit;
    } else {
        $error = "Error adding employee: " . $conn->error;
    }
    $stmt->close();
}

$designations = $conn->query("SELECT * FROM designation ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee</title>
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
            <h3 class="fw-bold text-dark m-0" style="font-size: 22px;">Add New Employee</h3>
            <a href="index.php" class="btn btn-info text-white btn-sm px-3 fw-bold" style="background-color: #cfe2ff; color: #052c65 !important; border: none;">Back</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="employeeForm" novalidate>
            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Employee No:</label>
                <div class="col-sm-9">
                    <input type="text" name="employee_no" class="form-control bg-light" value="<?= $next_emp_no ?>" readonly>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Full Name:</label>
                <div class="col-sm-9">
                    <input type="text" name="name" id="name" class="form-control">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Gender:</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male">
                        <label class="form-check-label" for="genderMale">Male</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female">
                        <label class="form-check-label" for="genderFemale">Female</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other">
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
                                    <option value="<?= $y ?>"><?= $y ?></option>
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
                                    <option value="<?= $m_num ?>"><?= $m_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="birth_day" id="birth_day" class="form-select">
                                <option value="">Day</option>
                                <?php for ($d = 1; $d <= 31; $d++): 
                                    $d_val = str_pad($d, 2, '0', STR_PAD_LEFT);
                                ?>
                                    <option value="<?= $d_val ?>"><?= $d ?></option>
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
                    <input type="text" name="mobile" id="mobile" maxlength="10" class="form-control">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Email:</label>
                <div class="col-sm-9">
                    <input type="email" name="email" id="email" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold text-end pt-0">Address:</label>
                <div class="col-sm-9">
                    <textarea name="address" id="address" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Degree:</label>
                <div class="col-sm-9">
                    <input type="text" name="degree" id="degree" class="form-control">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Salary:</label>
                <div class="col-sm-9">
                    <input type="number" step="0.01" name="salary" id="salary" class="form-control">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Designation:</label>
                <div class="col-sm-9">
                    <select name="designation_id" id="designation_id" class="form-select">
                        <option value="">Select Designation</option>
                        <?php while($d = $designations->fetch_assoc()): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Photo:</label>
                <div class="col-sm-9">
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="row">
                <div class="col-sm-9 offset-sm-3 text-end">
                    <button type="submit" id="submitBtn" class="btn btn-success px-4 fw-bold">Save Employee</button>
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
    $('#employeeForm').validate({
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
                        value: function() { return $('#mobile').val(); }
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
                        value: function() { return $('#email').val(); }
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