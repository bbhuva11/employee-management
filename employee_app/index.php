<?php
include 'db.php';

$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$allowed_sorts = [
    'id' => 'e.id',
    'employee_no' => 'e.employee_no',
    'name' => 'e.name',
    'gender' => 'e.gender',
    'birthdate' => 'e.birthdate',
    'mobile' => 'e.mobile',
    'email' => 'e.email',
    'address' => 'e.address',
    'degree' => 'e.degree',
    'salary' => 'e.salary',
    'designation' => 'd.name',
    'created' => 'e.created',
    'updated' => 'e.updated'
];

$sort = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowed_sorts) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';
$sort_column = $allowed_sorts[$sort];

$allowed_limits = [5, 10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $allowed_limits) ? intval($_GET['limit']) : 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter for Active employees only
$where_sql = " WHERE (e.status = 'Active' OR e.status IS NULL)";
$bind_types = "";
$bind_params = [];

if ($search !== '') {
    $where_sql .= " AND (e.employee_no LIKE ? OR e.name LIKE ? OR e.gender LIKE ? OR d.name LIKE ? OR e.degree LIKE ? OR e.mobile LIKE ? OR e.email LIKE ?)";
    $bind_types = "sssssss";
    $searchTerm = "%" . $search . "%";
    for ($i = 0; $i < 7; $i++) {
        $bind_params[] = $searchTerm;
    }
}

$count_query = "SELECT COUNT(e.id) AS total FROM employee_master e LEFT JOIN designation d ON e.designation_id = d.id" . $where_sql;
$count_stmt = $conn->prepare($count_query);
if ($search !== '') {
    $count_stmt->bind_param($bind_types, ...$bind_params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result()->fetch_assoc();
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}
$count_stmt->close();

$query = "SELECT e.*, d.name AS designation_name 
          FROM employee_master e 
          LEFT JOIN designation d ON e.designation_id = d.id" 
          . $where_sql . " ORDER BY $sort_column $order LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if ($search !== '') {
    $types = $bind_types . "ii";
    $params = array_merge($bind_params, [$limit, $offset]);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

function renderTh($columnName, $label, $current_sort, $current_order, $search, $limit) {
    $next_order = ($current_sort === $columnName && $current_order === 'ASC') ? 'desc' : 'asc';
    $asc_color = ($current_sort === $columnName && $current_order === 'ASC') ? '#0d6efd' : '#adb5bd';
    $desc_color = ($current_sort === $columnName && $current_order === 'DESC') ? '#0d6efd' : '#adb5bd';
    
    $url = "?sort=" . $columnName . "&order=" . $next_order . "&limit=" . $limit;
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    
    echo '<th class="text-nowrap"><a href="' . $url . '" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">';
    echo '<span><span style="font-size: 9px; color: ' . $asc_color . ';">▲</span> ' . $label . ' <span style="font-size: 9px; color: ' . $desc_color . ';">▼</span></span>';
    echo '</a></th>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
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
<body class="bg-white">

<div class="container-fluid py-4 px-4">
    <!-- Top Row: Navigation Tabs on the Left & Recycle Bin on the Far Right -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="index.php" class="page-tab active me-1">Employee Management</a>
            <a href="designations.php" class="page-tab inactive">Designation Management</a>
        </div>
        <div>
            <a href="employee_recycle_bin.php" class="btn btn-danger btn-sm fw-bold px-3 py-2 text-decoration-none">
                Recycle Bin
            </a>
        </div>
    </div>

    <!-- Controls Row: Title on Left, Controls & Add New Employee on Right -->
    <div class="row align-items-center mb-3 g-3">
        <div class="col-auto">
            <h2 class="fw-bold text-dark m-0" style="font-size: 24px;">Employee Management</h2>
        </div>
        <div class="col">
            <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                <div class="d-flex align-items-center gap-1">
                    <label for="limitSelect" class="form-label m-0 text-dark small">Show:</label>
                    <select id="limitSelect" class="form-select form-select-sm w-auto" onchange="changeRecordsPerPage(this)">
                        <?php foreach ($allowed_limits as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($limit === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <form method="GET" action="index.php" id="searchForm" class="d-flex gap-2 m-0">
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
                    <input type="hidden" name="limit" value="<?= htmlspecialchars($limit) ?>">
                    <input type="text" id="searchInput" name="search" placeholder="Search name, mobile, email..." value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm" style="width: 240px;" autocomplete="off">
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php?limit=<?= $limit ?>" class="btn btn-secondary btn-sm">Reset</a>
                    <?php endif; ?>
                </form>
                <a href="employee_add.php" class="btn btn-success btn-sm fw-bold px-3 py-2 text-nowrap">Add New Employee</a>
            </div>
        </div>
    </div>

    <div id="alert-container">
        <?php if ($status === 'created'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">Employee created successfully!</div>
        <?php elseif ($status === 'updated'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">Employee updated successfully!</div>
        <?php elseif ($status === 'deleted'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">The employee has been moved to Recycle Bin!</div>
        <?php elseif ($status === 'photo_removed'): ?>
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">Employee photo removed successfully!</div>
        <?php endif; ?>
    </div>

    <div class="card shadow-none border rounded-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <?php renderTh('id', 'ID', $sort, $order, $search, $limit); ?>
                            <th class="text-center">Photo</th>
                            <?php 
                            renderTh('employee_no', 'Emp No', $sort, $order, $search, $limit);
                            renderTh('name', 'Name', $sort, $order, $search, $limit);
                            renderTh('gender', 'Gender', $sort, $order, $search, $limit);
                            renderTh('birthdate', 'Birthdate', $sort, $order, $search, $limit);
                            renderTh('mobile', 'Mobile', $sort, $order, $search, $limit);
                            renderTh('email', 'Email', $sort, $order, $search, $limit);
                            renderTh('address', 'Address', $sort, $order, $search, $limit);
                            renderTh('degree', 'Degree', $sort, $order, $search, $limit);
                            renderTh('salary', 'Salary', $sort, $order, $search, $limit);
                            renderTh('designation', 'Designation', $sort, $order, $search, $limit);
                            renderTh('created', 'Created', $sort, $order, $search, $limit);
                            renderTh('updated', 'Updated', $sort, $order, $search, $limit);
                            ?>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr id="row-<?= $row['id'] ?>">
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['photo']) && file_exists("uploads/" . $row['photo'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($row['photo']) ?>" data-fancybox="gallery" data-caption="<?= htmlspecialchars($row['name']) ?>">
                                                <img src="uploads/<?= htmlspecialchars($row['photo']) ?>" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;" alt="Photo">
                                            </a>
                                            <br>
                                            <a href="employee_remove_photo.php?id=<?= $row['id'] ?>" class="text-danger text-decoration-none" style="font-size: 10px;" onclick="return confirm('Remove this photo?');">Remove Photo</a>
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['employee_no'] ?? 'OP' . str_pad($row['id'], 4, '0', STR_PAD_LEFT)) ?></strong></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['gender']) ?></td>
                                    <td><?= htmlspecialchars($row['birthdate']) ?></td>
                                    <td><?= htmlspecialchars($row['mobile']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['address']) ?></td>
                                    <td><?= htmlspecialchars($row['degree']) ?></td>
                                    <td><?= number_format($row['salary'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['designation_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['created']) ?></td>
                                    <td><?= htmlspecialchars($row['updated']) ?></td>
                                    <td class="text-nowrap text-center">
                                        <a href="employee_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm px-3 py-1 text-dark fw-bold" style="font-size: 11px;">Edit</a>
                                        <button type="button" class="btn btn-danger btn-sm px-3 py-1 fw-bold btn-delete" data-id="<?= $row['id'] ?>" style="font-size: 11px;">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="15" class="text-center py-4 text-muted fst-italic">No matching employees found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($total_records > 0): ?>
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2 text-muted small">
            <div>Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_records) ?> of <?= $total_records ?> entries</div>
            <ul class="pagination pagination-sm m-0">
                <?php if ($page > 1): ?>
                    <?php $prev_url = "?page=" . ($page - 1) . "&limit=" . $limit . "&sort=" . $sort . "&order=" . $order; if (!empty($search)) $prev_url .= "&search=" . urlencode($search); ?>
                    <li class="page-item"><a class="page-link" href="<?= $prev_url ?>">Previous</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <?php endif; ?>

                <?php 
                $start_p = max(1, $page - 2);
                $end_p = min($total_pages, $page + 2);
                for ($p = $start_p; $p <= $end_p; $p++): 
                    $p_url = "?page=" . $p . "&limit=" . $limit . "&sort=" . $sort . "&order=" . $order;
                    if (!empty($search)) $p_url .= "&search=" . urlencode($search);
                ?>
                    <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                        <?php if ($p === $page): ?>
                            <span class="page-link"><?= $p ?></span>
                        <?php else: ?>
                            <a class="page-link" href="<?= $p_url ?>"><?= $p ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <?php $next_url = "?page=" . ($page + 1) . "&limit=" . $limit . "&sort=" . $sort . "&order=" . $order; if (!empty($search)) $next_url .= "&search=" . urlencode($search); ?>
                    <li class="page-item"><a class="page-link" href="<?= $next_url ?>">Next</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
Fancybox.bind("[data-fancybox]", {});

function changeRecordsPerPage(selectObj) {
    const limit = selectObj.value;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('limit', limit);
    urlParams.set('page', 1);
    window.location.search = urlParams.toString();
}

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

    // Live typing search with debounce
    let searchTimer;
    const searchInput = $('#searchInput');
    
    if (searchInput.val().length > 0) {
        const len = searchInput.val().length;
        searchInput.focus();
        searchInput[0].setSelectionRange(len, len);
    }

    searchInput.on('input', function() {
        clearTimeout(searchTimer);
        const currentVal = $(this).val();
        searchTimer = setTimeout(function() {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('search', currentVal);
            urlParams.set('page', 1);
            window.location.search = urlParams.toString();
        }, 400);
    });

    $(document).on('click', '.btn-delete', function() {
        const employeeId = $(this).data('id');
        if (confirm('Are you sure you want to move this employee to the Recycle Bin?')) {
            $.ajax({
                url: 'employee_delete.php',
                type: 'POST',
                data: { id: employeeId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const urlParams = new URLSearchParams(window.location.search);
                        urlParams.set('status', 'deleted');
                        window.location.search = urlParams.toString();
                    } else {
                        const alertHtml = `<div id="dynamic-alert" class="alert alert-danger alert-dismissible fade show" role="alert">${response.message}</div>`;
                        $('#alert-container').html(alertHtml);
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