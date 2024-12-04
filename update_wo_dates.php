<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $work_order_id = intval($_POST['work_order_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($work_order_id && $start_date && $end_date) {
        // Update the work order dates in the database
        $sql = "UPDATE work_orders SET start_date = ?, end_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $start_date, $end_date, $work_order_id);

        if ($stmt->execute()) {
            // Redirect back to calendar page after update with success message
            header('Location: calendar.php?message=success');
            exit;
        } else {
            // Redirect back with error message
            header('Location: calendar.php?message=error');
            exit;
        }


        $stmt->close();
    } else {
        // Redirect if required fields are missing
        header('Location: calendar.php?message=missing_fields');
        exit;
    }

    $conn->close();
} else {
    // Redirect to calendar if not accessed via POST method
    header('Location: calendar.php');
    exit;
}
