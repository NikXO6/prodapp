<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Initialize filter variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date_filter = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date_filter = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$wo_number_filter = isset($_GET['wo_number']) ? $_GET['wo_number'] : '';

// Build the query dynamically based on the filters applied
$query = "
    SELECT 
        wo.id,
        wo.work_order_number,
        wo.item_name,
        wo.item_code,
        wo.required_qty,
        DATE_FORMAT(wo.start_date, '%d-%m-%Y') AS start_date,
        DATE_FORMAT(wo.end_date, '%d-%m-%Y') AS end_date,
        wo.status,
        wo.memo,
        wo.line,
        wo.priority,
        IFNULL(SUM(dp.quantity), 0) AS total_produced
    FROM work_orders wo
    LEFT JOIN daily_production dp ON wo.id = dp.work_order_id
    WHERE 1 = 1
";

// Add filters to the query
$params = [];
$types = '';
if ($status_filter) {
    $query .= " AND wo.status = ?";
    $types .= 's';
    $params[] = $status_filter;
}
if ($start_date_filter) {
    $query .= " AND wo.start_date >= ?";
    $types .= 's';
    $params[] = $start_date_filter;
}
if ($end_date_filter) {
    $query .= " AND wo.start_date <= ?";
    $types .= 's';
    $params[] = $end_date_filter;
}
// if ($wo_number_filter) {
//     $query .= " AND wo.work_order_number LIKE ?";
//     $types .= 's';
//     $params[] = '%' . $wo_number_filter . '%';
// }

$query .= " GROUP BY wo.id";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order Tracking Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Table CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.21.2/dist/bootstrap-table.min.css" rel="stylesheet">
</head>

<body>

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <div class="container-fluid mt-6">
        <h1 class="h3 mb-4">Work Order Tracking Dashboard</h1>

        <!-- Filter Form -->
        <form method="GET" action="dashboard.php" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="In Process" <?= $status_filter == 'In Process' ? 'selected' : '' ?>>In Process</option>
                        <option value="Completed" <?= $status_filter == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date (From)</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($start_date_filter) ?>">
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date (To)</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($end_date_filter) ?>">
                </div>

                <!-- <div class="col-md-3">
                    <label for="wo_number" class="form-label">Work Order Number</label>
                    <input type="text" name="wo_number" id="wo_number" class="form-control" value="<?= htmlspecialchars($wo_number_filter) ?>" placeholder="Enter WO Number">
                </div> -->
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="dashboard.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- Work Orders Table with Bootstrap Table -->
        <table
            class="table table-striped table-bordered"
            data-toggle="table"
            data-pagination="true"
            data-search="true"
            data-sortable="true">
            <thead>
                <tr>
                    <th data-field="work_order_number" data-sortable="true">Work Order Number</th>
                    <th data-field="wo.start_date" data-sortable="true">Start Date</th>
                    <th data-field="wo.end_date" data-sortable="true">End Date</th>
                    <th data-field="item_code" data-sortable="true">Item Code</th>
                    <th data-field="item_name" data-sortable="true">Item Name</th>
                    <th data-field="required_qty" data-sortable="true">Required Quantity</th>
                    <th data-field="total_produced" data-sortable="true">Total Produced</th>
                    <th data-field="progress" data-sortable="false">Progress</th>
                    <th data-field="status" data-sortable="true">Status</th>
                    <th data-field="memo" data-sortable="true">Memo</th>
                    <th data-field="line" data-sortable="true">Line</th>
                    <th data-field="priority" data-sortable="true">Prority</th>
                    <th data-field="actions" data-sortable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        // Calculate progress percentage
                        $progress = ($row['total_produced'] / $row['required_qty']) * 100;
                        $progress = ($progress > 100) ? 100 : $progress; // Prevent over 100%
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['work_order_number']) ?></td>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['item_code']) ?></td>
                            <td><?= htmlspecialchars($row['required_qty']) ?></td>
                            <td><?= htmlspecialchars($row['total_produced']) ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= round($progress, 2) ?>%
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['memo']) ?></td>
                            <td><?= htmlspecialchars($row['line']) ?></td>
                            <td><?= htmlspecialchars($row['priority']) ?></td>
                            <td>
                                <a href="view_work_order.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                                <a href="edit_work_order.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No work orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required by Bootstrap Table) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Table JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.21.2/dist/bootstrap-table.min.js"></script>
</body>

</html>