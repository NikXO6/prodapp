<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Get work order ID from query
$work_order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch work order details
$sql = "SELECT * FROM work_orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $work_order_id);
$stmt->execute();
$work_order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch daily production details
$sql = "SELECT * FROM daily_production WHERE work_order_id = ? ORDER BY production_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $work_order_id);
$stmt->execute();
$production_result = $stmt->get_result();

// Fetch material usage summary grouped by date and stillage
$sql = "
    SELECT 
        DATE(usage_date) AS usage_date, 
        stillage_number, products_produced,
        SUM(meters_used) AS total_meters_used
    FROM material_usage
    WHERE work_order_id = ?
    GROUP BY DATE(usage_date), stillage_number
    ORDER BY usage_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $work_order_id);
$stmt->execute();
$material_usage_summary = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-3">Work Order: <?= htmlspecialchars($work_order['work_order_number']) ?></h1>
        <p><strong>Item Name:</strong> <?= htmlspecialchars($work_order['item_name']) ?></p>
        <p><strong>Required Quantity:</strong> <?= htmlspecialchars($work_order['required_qty']) ?></p>
        <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($work_order['status'])) ?></p>

        <h2 class="h4">Daily Production</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Line</th>
                    <th>Staff Count</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $production_result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['production_date']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['line']) ?></td>
                        <td><?= htmlspecialchars($row['staff_count']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2 class="h4 mt-5">Oteman Material Usage Summary</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Usage Date</th>
                    <th>Stillage Number</th>
                    <th>Total Meters Used</th>
                    <th>Units Cut</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $material_usage_summary->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['usage_date']) ?></td>
                        <td><?= htmlspecialchars($row['stillage_number']) ?></td>
                        <td><?= htmlspecialchars($row['total_meters_used']) ?> meters</td>
                        <td><?= htmlspecialchars($row['products_produced']) ?></td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php
// Check if the internal_id exists for this work order
if (!empty($work_order['internal_id'])) {
    $netsuite_build_url = "https://5084726.app.netsuite.com/app/accounting/transactions/build.nl?id={$work_order['internal_id']}&e=T&transform=workord&memdoc=0&whence=";
    $netsuite_view_url = "https://5084726.app.netsuite.com/app/accounting/transactions/workord.nl?id={$work_order['internal_id']}&whence=";
    
    echo "<div class='mb-3'>";
    echo "<a href='$netsuite_build_url' target='_blank' class='btn btn-success me-2'>Build in NetSuite</a>";
    echo "<a href='$netsuite_view_url' target='_blank' class='btn btn-secondary'>View WO in NetSuite</a>";
    echo "</div>";
} else {
    echo "<p>No NetSuite link available for this work order.</p>";
}
?>

<br>
        <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>