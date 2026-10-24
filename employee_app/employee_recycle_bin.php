<?php
include 'db.php';

// Handle Restore action
if (isset($_GET['action']) && $_GET['action'] === 'restore') {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE employee_master SET status = 'Active', updated = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: employee_recycle_bin.php?status=restored");
        exit();
    }
}

// Handle Permanent Delete action (permanently deletes record & unlinks photo)
if (isset($_GET['action']) && $_GET['action'] === 'permanent_delete') {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $stmt_photo = $conn->prepare("SELECT photo FROM employee_master WHERE id = ?");
        $stmt_photo->bind_param("i", $id);
        $stmt_photo->execute();
        $res_photo = $stmt_photo->get_result();
        if ($res_photo->num_rows > 0) {
            $row_photo = $res_photo->fetch_assoc();
            if (!empty($row_photo['photo']) && file_exists(__DIR__ . '/uploads/' . $row_photo['photo'])) {
                unlink(__DIR__ . '/uploads/' . $row_photo['photo']);
            }
        }
        $stmt_photo->close();

        $del_stmt = $conn->prepare("DELETE FROM employee_master WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        $del_stmt->execute();
        $del_stmt->close();

        header("Location: employee_recycle_bin.php?status=deleted");
        exit();
    }
}

// Fetch all Inactive employees
$sql = "SELECT e.*, d.name AS designation_name 
        FROM employee_master e 
        LEFT JOIN designation d ON e.designation_id = d.id 
        WHERE e.status = 'Inactive' 
        ORDER BY e.updated DESC";
$result = $conn->query($sql);
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin - Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h2 class="fw-bold text-dark m-0" style="font-size: 24px;">Employee Recycle Bin</h2>
        <a href="index.php" class="btn btn-info text-white btn-sm px-3 fw-bold" style="background-color: #cfe2ff; color: #052c65 !important; border: none;">Back to Employee List</a>
    </div>

    <div id="alert-container">
        <?php if ($status === 'restored'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">Employee restored successfully!</div>
        <?php elseif ($status === 'deleted'): ?>
            <div id="flash-message" class="alert alert-danger alert-dismissible fade show" role="alert">Employee deleted permanently!</div>
        <?php endif; ?>
    </div>

    <div class="card shadow-none border rounded-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th class="text-center" style="width: 80px;">Photo</th>
                            <th>Emp No</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Deleted At</th>
                            <th class="text-center" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['photo']) && file_exists(__DIR__ . "/uploads/" . $row['photo'])): ?>
                                            <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;" alt="Photo">
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['employee_no']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['gender']) ?></td>
                                    <td><?= htmlspecialchars($row['mobile']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['designation_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['updated']) ?></td>
                                    <td class="text-center text-nowrap">
                                        <a href="employee_recycle_bin.php?action=restore&id=<?= $row['id'] ?>" class="btn btn-success btn-sm px-3 py-1 fw-bold" onclick="return confirm('Restore this employee back to the active list?');">Restore</a>
                                        <a href="employee_recycle_bin.php?action=permanent_delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm px-3 py-1 fw-bold" onclick="return confirm('Permanently delete this employee? This action CANNOT be undone.');">Delete Permanently</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted fst-italic">Recycle bin is empty.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
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
});
</script>
</body>
</html>