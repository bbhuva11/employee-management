<?php
include 'db.php';

$status = isset($_GET['status']) ? $_GET['status'] : '';
$result = $conn->query("SELECT * FROM designation ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designation Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .page-tab {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            font-size: 1rem;
            line-height: 1.5;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid transparent;
            transition: none;
        }
        .page-tab.inactive {
            color: #0d6efd;
            background-color: transparent;
            font-weight: 400;
        }
        .page-tab.inactive:hover {
            color: #0a58ca;
        }
        .page-tab.active {
            color: #fff;
            background-color: #0d6efd;
            font-weight: 700;
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <!-- Top Navigation Tabs -->
    <div class="mb-4">
        <a href="index.php" class="page-tab inactive me-1">Employee Management</a>
        <a href="designations.php" class="page-tab active">Designation Management</a>
    </div>

    <!-- Alert Container -->
    <div id="alert-container">
        <?php if ($status === 'created'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">
                Designation created successfully!
            </div>
        <?php elseif ($status === 'updated'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">
                Designation updated successfully!
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Card Container -->
    <div class="card shadow-sm border rounded-0 p-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark m-0" style="font-size: 22px;">Designation Management</h3>
            <a href="designation_add.php" class="btn btn-success fw-bold px-3 py-2" style="background-color: #198754; border: none; font-size: 14px;">Add New Designation</a>
        </div>

        <!-- Table Container -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Designation Name</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th style="width: 160px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?= $row['id'] ?>">
                                <td class="fw-bold"><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= $row['created'] ?></td>
                                <td><?= $row['updated'] ?></td>
                                <td class="text-center">
                                    <a href="designation_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm fw-bold px-3 text-dark">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold px-3 btn-delete" data-id="<?= $row['id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No designations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Auto-dismiss and clean up flash message URL
    const flashMessage = $('#flash-message');
    if (flashMessage.length) {
        setTimeout(() => {
            flashMessage.fadeOut(500, function() { $(this).remove(); });
        }, 3000);
        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    }

    // AJAX Delete handler matching designation_delete.php JSON output
    $(document).on('click', '.btn-delete', function() {
        const designationId = $(this).data('id');
        if (confirm('Are you sure you want to delete this designation?')) {
            $.ajax({
                url: 'designation_delete.php',
                type: 'POST',
                data: { id: designationId },
                dataType: 'json',
                success: function(response) {
                    $('#dynamic-alert').remove();
                    if (response.status === 'success') {
                        $('#row-' + designationId).fadeOut(400, function() {
                            $(this).remove();
                            if ($('tbody tr').length === 0) location.reload();
                        });
                        const alertHtml = `<div id="dynamic-alert" class="alert alert-success alert-dismissible fade show" role="alert">${response.message}</div>`;
                        $('#alert-container').html(alertHtml);
                        setTimeout(() => { $('#dynamic-alert').fadeOut(500, function() { $(this).remove(); }); }, 3000);
                    } else {
                        const alertHtml = `<div id="dynamic-alert" class="alert alert-danger alert-dismissible fade show" role="alert">${response.message}</div>`;
                        $('#alert-container').html(alertHtml);
                        setTimeout(() => { $('#dynamic-alert').fadeOut(500, function() { $(this).remove(); }); }, 4000);
                    }
                },
                error: function() {
                    alert('An error occurred while communicating with the server.');
                }
            });
        }
    });
});
</script>
</body>
</html>