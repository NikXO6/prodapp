<?php
include('db.php');
session_start();
// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $work_order_number = $_POST['work_order_number'];
    $item_code = $_POST['item_code'];
    $item_name = $_POST['item_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];  // Capture end_date
    $required_qty = intval($_POST['required_qty']);  // Capture Required Qty
    $memo = $_POST['memo'];  // Capture memo

    // Prepare the SQL statement to insert a new Work Order
    $sql = "INSERT INTO work_orders (work_order_number, item_code, item_name, start_date, end_date, required_qty, memo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind the parameters, end_date and memo could be NULL
    $stmt->bind_param("sssssis", $work_order_number, $item_code, $item_name, $start_date, $end_date, $required_qty, $memo);

    if ($stmt->execute()) {
        echo "Work Order added successfully!";
        header('Location: add_work_order.php?msg=success'); // Redirect after successful insertion
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Work Order</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-3">Add New Work Order</h1>
        <form method="post" action="add_work_order.php" class="form">
            <div class="mb-3">
                <label for="work_order_number" class="form-label">Work Order Number</label>
                <input type="text" name="work_order_number" id="work_order_number" class="form-control" placeholder="Enter Work Order Number" required>
            </div>
            <div class="mb-3">
                <label for="item_code" class="form-label">Item Code</label>
                <input type="text" name="item_code" id="item_code" class="form-control" placeholder="Enter Item Code" required>
            </div>
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" name="item_name" id="item_name" class="form-control" placeholder="Enter Item Name" required>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" required>
            </div>

            <!-- Required Qty field -->
            <div class="mb-3">
                <label for="required_qty" class="form-label">Required Quantity</label>
                <input type="number" name="required_qty" id="required_qty" class="form-control" placeholder="Enter Required Quantity" required>
            </div>
            <!-- End Date -->
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control">
            </div>
            <div class="mb-3">
                <label for="memo" class="form-label">Memo (Optional)</label>
                <textarea name="memo" id="memo" class="form-control" rows="3" placeholder="Enter any additional notes"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Work Order</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
