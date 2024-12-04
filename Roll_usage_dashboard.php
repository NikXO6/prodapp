<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch all available work orders for the filter dropdown
$work_orders = [];
$result = $conn->query("SELECT id, work_order_number FROM work_orders ORDER BY work_order_number ASC");
while ($row = $result->fetch_assoc()) {
    $work_orders[] = $row;
}

// Check if a specific WO filter is selected
$selected_wo = isset($_GET['filter_wo_id']) ? intval($_GET['filter_wo_id']) : 0;

// Query with JOIN to get filtered material usage data
$sql = "
    SELECT 
        material_usage.*, 
        work_orders.work_order_number 
    FROM 
        material_usage 
    JOIN 
        work_orders ON material_usage.work_order_id = work_orders.id 
    " . ($selected_wo ? "WHERE work_order_id = $selected_wo" : "") . "
    ORDER BY 
        material_usage.usage_date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Usage Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-4">Roll Usage Tracking</h1>

        <!-- WO Filter -->
        <form method="get" class="mb-4">
            <div class="row mb-3">
                <div class="col-12 col-md-8">
                    <label for="filter_wo_id" class="form-label">Filter by Work Order</label>
                    <select name="filter_wo_id" id="filter_wo_id" class="form-select">
                        <option value="0">-- All Work Orders --</option>
                        <?php foreach ($work_orders as $wo): ?>
                            <option value="<?= $wo['id'] ?>" <?= $selected_wo == $wo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($wo['work_order_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                </div>
            </div>
        </form>

        <!-- Material Usage Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Work Order</th>
                        <th>Roll Number</th>
                        <th>Fabric Code</th>
                        <th>Meters Used</th>
                        <th>Stillage No.</th>
                        <th>PO Number</th>
                        <th>Initial Count</th>
                        <th>Final Count</th>
                        <th>Actual Count</th>
                        <th>Usage Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['work_order_number']) ?></td>
                            <td><?= htmlspecialchars($row['roll_number']) ?></td>
                            <td><?= htmlspecialchars($row['fabric_code']) ?></td>
                            <td><?= htmlspecialchars($row['meters_used']) ?> meters</td>
                            <td><?= htmlspecialchars($row['stillage_number']) ?></td>
                            <td><?= htmlspecialchars($row['po_number']) ?></td>
                            <td><?= htmlspecialchars($row['initial_machine_count']) ?></td>
                            <td><?= htmlspecialchars($row['final_machine_count']) ?></td>
                            <td><?= htmlspecialchars($row['products_produced']) ?></td>
                            <td><?= htmlspecialchars($row['usage_date']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
