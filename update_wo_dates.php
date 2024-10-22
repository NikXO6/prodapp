<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $work_order_id = intval($_POST['work_order_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Update the work order dates in the database
    $sql = "UPDATE work_orders SET start_date = ?, end_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $start_date, $end_date, $work_order_id);  // Use the correct ID

    if ($stmt->execute()) {
        echo "Work Order updated successfully!";
        header('Location: calendar.php');  // Redirect back to calendar page after update
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
