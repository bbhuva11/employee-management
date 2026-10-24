<?php 
include 'db.php'; 

$designations = $conn->query("SELECT * FROM designation");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee</title>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f8f9fa; color: #333; }
        .container { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h2 { margin: 0; color: #2c3e50; }
        .btn-link { color: #007bff; text-decoration: none; font-weight: bold; background-color: #e2f0ff; padding: 8px 16px; border-radius: 20px; display: inline-block; transition: background-color 0.2s; }
        .btn-link:hover { background-color: #d0e4ff; text-decoration: none; }
        
        .form-table { width: 100%; border-collapse: collapse; }
        .form-table td { padding: 10px; vertical-align: middle; }
        .form-table td.label-cell { width: 30%; font-weight: bold; color: #495057; text-align: right; padding-right: 20px; }
        
        input[type="text"], input[type="email"], input[type="number"], select, textarea {
            width: 100%; padding: 8px 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box; font-size: 14px;
        }
        .date-select-group { display: flex; gap: 8px; }
        .date-select-group select { width: 33%; }

        textarea { height: 80px; resize: vertical; }
        .radio-group label { margin-right: 20px; font-weight: normal; cursor: pointer; }
        
        .error-text { color: red; font-size: 12px; margin-top: 4px; display: block; font-weight: bold; }

        .btn-submit { background-color: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 15px; }
        .btn-submit:hover { background-color: #218838; }
        .form-footer { text-align: right; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>Add New Employee</h2>
        <a href="index.php" class="btn-link">Back to Directory</a>
    </div>

    <form id="employeeForm" action="save.php" method="POST" enctype="multipart/form-data">
        <table class="form-table">
            <tr>
                <td class="label-cell"><label>Full Name:</label></td>
                <td>
                    <input type="text" id="name" name="name" required>
                    <span id="nameError" class="error-text"></span>
                </td>
            </tr>
            <tr>
                <td class="label-cell"><label>Gender:</label></td>
                <td>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="Male" required> Male</label>
                        <label><input type="radio" name="gender" value="Female" required> Female</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label-cell"><label>Birthdate:</label></td>
                <td>
                    <div class="date-select-group">
                        <select id="birth_year" name="birth_year" required>
                            <option value="">Year</option>
                            <?php 
                            $current_year = date('Y');
                            for ($y = $current_year; $y >= 1940; $y--) {
                                echo "<option value=\"$y\">$y</option>";
                            }
                            ?>
                        </select>
                        <select id="birth_month" name="birth_month" required>
                            <option value="">Month</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <select id="birth_day" name="birth_day" required>
                            <option value="">Day</option>
                            <?php 
                            for ($d = 1; $d <= 31; $d++) {
                                $day_val = str_pad($d, 2, '0', STR_PAD_LEFT);
                                echo "<option value=\"$day_val\">$d</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" id="birthdate" name="birthdate">
                    <span id="birthdateError" class="error-text"></span>
                </td>
            </tr>
            <tr>
                <td class="label-cell"><label>Mobile Number:</label></td>
                <td>
                    <input type="text" id="mobile" name="mobile" maxlength="10" required>
                    <span id="mobileError" class="error-text"></span>
                </td>
            </tr>
            <tr>
                <td class="label-cell"><label>Email Address:</label></td>
                <td>
                    <input type="email" id="email" name="email" required>
                    <span id="emailError" class="error-text"></span>
                </td>
            </tr>
            <tr>
                <td class="label-cell"><label>Address:</label></td>
                <td><textarea name="address"></textarea></td>
            </tr>
            <tr>
                <td class="label-cell"><label>Degree:</label></td>
                <td><input type="text" name="degree"></td>
            </tr>
            <tr>
                <td class="label-cell"><label>Salary:</label></td>
                <td><input type="number" step="0.01" name="salary" required></td>
            </tr>
            <tr>
                <td class="label-cell"><label>Designation:</label></td>
                <td>
                    <select name="designation_id" required>
                        <option value="">-- Select Designation --</option>
                        <?php while($row = $designations->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label-cell"><label>Photo:</label></td>
                <td><input type="file" name="photo" accept="image/*"></td>
            </tr>
        </table>

        <div class="form-footer">
            <button type="submit" id="submitBtn" class="btn-submit">Save Employee</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    let isNameUnique = true;
    let isMobileValid = false;
    let isEmailValid = false;

    $('#name').on('blur', function() {
        let nameVal = $(this).val().trim();
        if (nameVal !== '') {
            $.ajax({
                url: 'check_duplicate.php',
                type: 'POST',
                data: { name: nameVal },
                success: function(response) {
                    if (response.trim() === 'exists') {
                        $('#nameError').text('Name already exists. Name should not be repeated.');
                        isNameUnique = false;
                    } else {
                        $('#nameError').text('');
                        isNameUnique = true;
                    }
                }
            });
        } else {
            $('#nameError').text('');
            isNameUnique = true;
        }
    });

    $('#mobile').on('input', function() {
        let mobileVal = $(this).val();
        mobileVal = mobileVal.replace(/\D/g, '');
        $(this).val(mobileVal);

        if (mobileVal.length < 10) {
            $('#mobileError').text('Enter valid mobile number, max 10 digits allowed.');
            isMobileValid = false;
        } else if (mobileVal.length > 10) {
            $('#mobileError').text('Enter valid mobile number, max 10 digits allowed.');
            isMobileValid = false;
            $(this).val(mobileVal.substring(0, 10));
        } else {
            $('#mobileError').text('');
            isMobileValid = true;
        }
    });

    $('#email').on('input', function() {
        let emailVal = $(this).val();
        if (!emailVal.includes('@')) {
            $('#emailError').text('Email must contain "@".');
            isEmailValid = false;
        } else {
            $('#emailError').text('');
            isEmailValid = true;
        }
    });

    $('#employeeForm').on('submit', function(e) {
        let mobileVal = $('#mobile').val();
        let emailVal = $('#email').val();
        let year = $('#birth_year').val();
        let month = $('#birth_month').val();
        let day = $('#birth_day').val();

        if (mobileVal.length !== 10) {
            $('#mobileError').text('Enter valid mobile number, max 10 digits allowed.');
            isMobileValid = false;
        }

        if (!emailVal.includes('@')) {
            $('#emailError').text('Email must contain "@".');
            isEmailValid = false;
        }

        if (!year || !month || !day) {
            $('#birthdateError').text('Please select a complete birthdate.');
            e.preventDefault();
            return;
        } else {
            $('#birthdateError').text('');
            $('#birthdate').val(year + '-' + month + '-' + day);
        }

        if (!isNameUnique || !isMobileValid || !isEmailValid) {
            e.preventDefault();
            alert('Please correct the errors marked in red before saving.');
        }
    });
});
</script>

</body>
</html>