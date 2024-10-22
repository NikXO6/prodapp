<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch production data for each Work Order
$sql = "SELECT dp.*, wo.work_order_number, wo.item_name, wo.item_code 
        FROM daily_production dp
        JOIN work_orders wo ON dp.work_order_id = wo.id
        ORDER BY dp.production_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Confirm delete action
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this production record?")) {
                window.location.href = 'delete_production.php?id=' + id;
            }
        }
    </script>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container mt-4">
        <h2 class="h4">Daily Production Overview</h2>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Production Date</th>
                    <th>Work Order</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Line</th>
                    <th>Staff Count</th>
                    <th>Units per Staff</th>
                    <th>Memo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        // Format the production date to dd-mm-yyyy
                        $formatted_date = date('d-m-Y', strtotime($row['production_date']));
                        echo "<td>" . $formatted_date . "</td>";
                        echo "<td>" . $row['work_order_number'] . "</td>";
                        echo "<td>" . $row['item_code'] . "</td>";
                        echo "<td>" . $row['item_name'] . "</td>";
                        echo "<td>" . $row['quantity'] . "</td>";
                        echo "<td>" . $row['line'] . "</td>";
                        echo "<td>" . $row['staff_count'] . "</td>";
                        echo "<td>" . $row['units_per_staff'] . "</td>";
                        echo "<td>" . $row['memo'] . "</td>";
                        echo "<td>
                            <a href='edit_production.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='#' onclick='confirmDelete(" . $row['id'] . ")' class='btn btn-danger btn-sm'>Delete</a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No production data available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
