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

// Fetch daily production details
$sql = "SELECT * FROM daily_production WHERE work_order_id = ? ORDER BY production_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $work_order_id);
$stmt->execute();
$production_result = $stmt->get_result();
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
        <h1 class="h3 mb-3">Work Order: <?= $work_order['work_order_number'] ?></h1>
        <p><strong>Item Name:</strong> <?= $work_order['item_name'] ?></p>
        <p><strong>Required Quantity:</strong> <?= $work_order['required_qty'] ?></p>
        <p><strong>Status:</strong> <?= ucfirst($work_order['status']) ?></p>

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
                <?php while ($row = mysqli_fetch_assoc($production_result)) : ?>
                    <tr>
                        <td><?= $row['production_date'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['line'] ?></td>
                        <td><?= $row['staff_count'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
