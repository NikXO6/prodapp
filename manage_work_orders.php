<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Work Orders</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('navbar.php'); ?>

    <div class="container-fluid mt-4">
        <h1 class="h3 mb-3">Manage Work Orders</h1>
        <a href="add_work_order.php" class="btn btn-primary mb-3">Add New Work Order</a>
        <div class="table-responsive">

        <!-- Table to display all Work Orders -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Work Order Number</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Required Quantity</th> <!-- New Required Qty Column -->
                    <th>Status</th>
                    <th>Memo</th>
                    <th>Line</th>
                    <th>Priority</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all Work Orders
                $sql = "SELECT * FROM work_orders";
                $result = mysqli_query($conn, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        $formatted_startdate = date('d-m-Y', strtotime($row['start_date']));
                        $formatted_enddate = date('d-m-Y', strtotime($row['end_date']));
                        echo "<td>{$row['work_order_number']}</td>";
                        echo "<td>{$row['item_code']}</td>";
                        echo "<td>{$row['item_name']}</td>";
                        echo "<td>" . $formatted_startdate . "</td>";
                        echo "<td>" . $formatted_enddate . "</td>";
                        echo "<td>{$row['required_qty']}</td>";  // Display Required Qty
                        echo "<td>{$row['status']}</td>";
                        echo "<td>{$row['memo']}</td>";
                        echo "<td>{$row['line']}</td>";
                        echo "<td>{$row['priority']}</td>";
                        echo "<td><a href='edit_work_order.php?id={$row['id']}' class='btn btn-info btn-sm'>Edit</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No Work Orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
