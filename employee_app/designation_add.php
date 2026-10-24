<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Designation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        label.error {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
            display: block;
            font-weight: 500;
        }
        input.error {
            border-color: #dc3545;
        }
    </style>
</head>
<body class="bg-white">

<div class="container py-5" style="max-width: 850px;">
    <div class="card shadow-sm border rounded-0 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold text-dark m-0" style="font-size: 22px;">Add New Designation</h3>
            <a href="designations.php" class="btn btn-info text-white btn-sm px-3 fw-bold" style="background-color: #cfe2ff; color: #052c65 !important; border: none;">Back</a>
        </div>

        <form id="designationForm" action="designation_save.php" method="POST">
            <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label fw-bold text-end">Designation Name:</label>
                <div class="col-sm-9">
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-9 offset-sm-3 text-end">
                    <button type="submit" class="btn btn-success px-4 fw-bold">Save Designation</button>
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
    $('#designationForm').validate({
        rules: {
            name: {
                required: true,
                remote: {
                    url: "check_designation_unique.php",
                    type: "POST",
                    data: {
                        name: function() {
                            return $("#name").val();
                        }
                    }
                }
            }
        },
        messages: {
            name: {
                required: "Please enter a designation name.",
                remote: "This designation name already exists. Please choose a unique name."
            }
        },
        errorElement: 'label',
        errorPlacement: function(error, element) {
            error.appendTo(element.parent());
        }
    });
});
</script>

</body>
</html>