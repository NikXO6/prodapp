<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if the user is in the Approvers group
$isApprover = ($_SESSION['user_role'] ?? '') === 'approver'; // Assume user_role is stored in session after login

// Get work order ID from query
$work_order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Approve action for a specific daily production entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $daily_production_id = intval($_POST['daily_production_id']);
    $approve_sql = "UPDATE daily_production SET approved_to_build = 1 WHERE id = ?";
    $stmt = $conn->prepare($approve_sql);
    $stmt->bind_param("i", $daily_production_id);
    $stmt->execute();
    $stmt->close();
}

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
        stillage_number, 
        products_produced, fabric_code,
        SUM(meters_used) AS total_meters_used
    FROM material_usage
    WHERE work_order_id = ?
    GROUP BY DATE(usage_date), stillage_number
    ORDER BY usage_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $work_order_id);
$stmt->execute();
$material_usage_summary = $stmt->get_result();

// Function to fetch workstation staff count for a specific daily production ID
function getWorkstationStaffCount($conn, $daily_production_id) {
    $sql = "SELECT workstation_name, staff_count FROM daily_production_workstation WHERE daily_production_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $daily_production_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Define NetSuite base URLs
$netsuite_view_base_url = "https://5084726.app.netsuite.com/app/accounting/transactions/workord.nl";
$netsuite_build_base_url = "https://5084726.app.netsuite.com/app/accounting/transactions/build.nl";
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
                    <th>Total Staff Count</th>
                    <th>Units per Staff</th>
                    <th>Memo</th>
                    <th>Workstation Staff Count</th>
                    <?php if ($isApprover): ?>
                        <th>Approval Action</th>
                    <?php endif; ?>
                    <th>Build in NetSuite</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $production_result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['production_date']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['line']) ?></td>
                        <td><?= htmlspecialchars($row['staff_count']) ?></td>
                        <td><?= htmlspecialchars($row['units_per_staff']) ?></td>
                        <td><?= htmlspecialchars($row['memo']) ?></td>
                        <td>
                            <ul>
                                <?php
                                $workstations = getWorkstationStaffCount($conn, $row['id']);
                                while ($ws = $workstations->fetch_assoc()) {
                                    echo "<li>" . htmlspecialchars($ws['workstation_name']) . ": " . htmlspecialchars($ws['staff_count']) . "</li>";
                                }
                                ?>
                            </ul>
                        </td>
                        <?php if ($isApprover): ?>
                            <td>
                                <?php if ($row['approved_to_build'] == 1) : ?>
                                    <span class="text-success">Approved</span>
                                <?php else : ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="daily_production_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="approve" class="btn btn-warning btn-sm">Approve</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php if ($row['approved_to_build'] == 1) : ?>
                                <a href="<?= $netsuite_build_base_url ?>?id=<?= $work_order['internal_id'] ?>&e=T&transform=workord&memdoc=0&whence=" 
                                   target="_blank" class="btn btn-success btn-sm">Build in NetSuite</a>
                            <?php else : ?>
                                <span class="text-muted">Awaiting Approval</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2 class="h4 mt-5">Material Usage Summary</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Usage Date</th>
                    <th>Fabric Code</th>
                    <th>Stillage Number</th>
                    <th>Total Meters Used</th>
                    <th>Units Cut</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $material_usage_summary->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['usage_date']) ?></td>
                        <td><?= htmlspecialchars($row['fabric_code']) ?></td>
                        <td><?= htmlspecialchars($row['stillage_number']) ?></td>
                        <td><?= htmlspecialchars($row['total_meters_used']) ?> meters</td>
                        <td><?= htmlspecialchars($row['products_produced']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php
        // Display the "View in NetSuite" button outside of the daily production table
        if (!empty($work_order['internal_id'])) {
            $netsuite_view_url = "{$netsuite_view_base_url}?id={$work_order['internal_id']}&whence=";
            echo "<div class='mb-3'>";
            echo "<a href='$netsuite_view_url' target='_blank' class='btn btn-secondary'>View WO in NetSuite</a>";
            echo "</div>";
        }
        ?>

        <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
