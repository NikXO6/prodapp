<?php
include('db.php');
session_start();
// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $work_order_id = intval($_GET['id']);

    // Fetch the work order details from the database
    $stmt = $conn->prepare("SELECT * FROM work_orders WHERE id = ?");
    $stmt->bind_param("i", $work_order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $work_order = $result->fetch_assoc();

    if (!$work_order) {
        echo "<p style='color: red;'>Work order not found.</p>";
        exit;
    }
} else {
    echo "<p style='color: red;'>Invalid work order ID.</p>";
    exit;
}

// Handle form submission for editing the work order
if (isset($_POST['update_work_order'])) {
    $work_order_number = $_POST['work_order_number'];
    $item_code = $_POST['item_code'];
    $item_name = $_POST['item_name'];
    $required_qty = intval($_POST['required_qty']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $priority = intval($_POST['priority']);
    $line = $_POST['line'];
    $memo = $_POST['memo'];

    // Update the work order in the database
    $stmt = $conn->prepare("UPDATE work_orders SET work_order_number = ?, item_code = ?, item_name = ?, required_qty = ?, start_date = ?, end_date = ?, priority = ?, line = ?, memo = ? WHERE id = ?");
    $stmt->bind_param("sssississi", $work_order_number, $item_code, $item_name, $required_qty, $start_date, $end_date, $priority, $line, $memo, $work_order_id);

    if ($stmt->execute()) {
        // Check the total produced quantity for this work order
        $stmt = $conn->prepare("SELECT IFNULL(SUM(quantity), 0) AS total_produced FROM daily_production WHERE work_order_id = ?");
        $stmt->bind_param("i", $work_order_id);
        $stmt->execute();
        $production_data = $stmt->get_result()->fetch_assoc();
        $total_produced = $production_data['total_produced'];

        // Update the status based on total produced vs required quantity
        if ($total_produced >= $required_qty) {
            $new_status = 'Completed';
        } elseif ($total_produced == 0) {
            $new_status = 'Released';
        } else {
            $new_status = 'In Process';
        }

        // Update the work order status in the database
        $stmt = $conn->prepare("UPDATE work_orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $work_order_id);
        $stmt->execute();

        // Redirect to the dashboard after the update
        header("Location: dashboard.php");
        exit;
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Work Order</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-3">Edit Work Order</h1>
        <form method="post" action="edit_work_order.php?id=<?= $work_order_id ?>">
            <div class="mb-3">
                <label for="work_order_number" class="form-label">Work Order Number</label>
                <input type="text" name="work_order_number" id="work_order_number" class="form-control" value="<?= $work_order['work_order_number'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="item_code" class="form-label">Item Code</label>
                <input type="text" name="item_code" id="item_code" class="form-control" value="<?= $work_order['item_code'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" name="item_name" id="item_name" class="form-control" value="<?= $work_order['item_name'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="required_qty" class="form-label">Required Quantity</label>
                <input type="number" name="required_qty" id="required_qty" class="form-control" value="<?= $work_order['required_qty'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $work_order['start_date'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $work_order['end_date'] ?>">
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <input type="number" name="priority" id="priority" class="form-control" value="<?= $work_order['priority'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="line" class="form-label">Line</label>
                <input type="text" name="line" id="line" class="form-control" value="<?= $work_order['line'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="parent_wo_id" class="form-label">Parent Work Order (if sub-assembly)</label>
                <select name="parent_wo_id" id="parent_wo_id" class="form-select">
                    <option value="">None</option>
                    <?php
                    // Fetch all available work orders to select as parent
                    $result = $conn->query("SELECT id, work_order_number FROM work_orders WHERE parent_wo_id IS NULL");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['work_order_number']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="memo" class="form-label">Memo</label>
                <textarea name="memo" id="memo" class="form-control" rows="3"><?= $work_order['memo'] ?></textarea>
            </div>


            <button type="submit" name="update_work_order" class="btn btn-primary">Update Work Order</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>