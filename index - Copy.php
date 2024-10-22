<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('navbar.php'); ?>
    <header class="bg-dark text-white p-3">
        <div class="container">
            <h1 class="h3">Production Dashboard</h1>
            <nav class="d-inline">
                <a href="submit_data.php" class="btn btn-primary btn-sm">Submit New Data</a>
                <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container mt-4">
        <h2 class="h4">Production Overview</h2>

        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Date</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Line</th>
                    <th>Staff Count</th>
                    <th>Units per Staff</th>
                    <th>WO Number</th>
                    <th>Status</th>
                    <th>Memo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                session_start();
                include('db.php');

                $sql = "SELECT * FROM production_data ORDER BY date DESC";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td>" . $row['item_code'] . "</td>";
                        echo "<td>" . $row['item_name'] . "</td>";
                        echo "<td>" . $row['quantity'] . "</td>";
                        echo "<td>" . $row['line'] . "</td>";
                        echo "<td>" . $row['staff_count'] . "</td>";
                        echo "<td>" . $row['units_per_staff'] . "</td>";
                        echo "<td>" . $row['wo_number'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . $row['memo'] . "</td>";
                        if ($row['status'] == 'In Process') {
                            echo "<td>
                                    <form method='post' action='mark_complete.php'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit' class='btn btn-success btn-sm'>Mark as Completed</button>
                                    </form>
                                  </td>";
                        } else {
                            echo "<td>Completed</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No production data available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
