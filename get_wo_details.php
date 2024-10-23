<?php
include('db.php');

if (isset($_GET['id'])) {
    $wo_id = intval($_GET['id']);

    // Fetch the work order details from the database, including the line
    $sql = "SELECT work_order_number, item_code, item_name, required_qty, start_date, status, memo, line FROM work_orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $wo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Format the output
        $formatted_date = date('d-m-Y', strtotime($row['start_date']));
        echo "<strong>Work Order Number:</strong> " . $row['work_order_number'] . "<br>";
        echo "<strong>Item Code:</strong> " . $row['item_code'] . "<br>";
        echo "<strong>Item Name:</strong> " . $row['item_name'] . "<br>";
        echo "<strong>Required Quantity:</strong> " . $row['required_qty'] . "<br>";
        echo "<strong>Start Date:</strong> " . $formatted_date . "<br>";
        echo "<strong>Memo:</strong> " . $row['memo'] . "<br>";
        echo "<strong>Status:</strong> " . $row['status'] . "<br>";
        echo "<strong>Line:</strong> " . $row['line'];  // Include the line here
    } else {
        echo "No details found for this work order.";
    }

    $stmt->close();
    $conn->close();
}
?>
