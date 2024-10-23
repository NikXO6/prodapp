<?php
include('db.php');

if (isset($_GET['parent_wo_number']) && isset($_GET['child_wo'])) {
    $parent_wo_number = $_GET['parent_wo_number'];
    $child_wo_number = $_GET['child_wo'];

    // Create the new Parent Work Order
    $sql = "INSERT INTO work_orders (work_order_number, status) VALUES (?, 'Released')";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $parent_wo_number);
        if ($stmt->execute()) {
            $parent_wo_id = $stmt->insert_id;  // Get the new parent ID

            // Now update the child work order with the new parent ID
            $sql_update = "UPDATE work_orders SET parent_wo_id = ? WHERE work_order_number = ?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param('is', $parent_wo_id, $child_wo_number);
                if ($stmt_update->execute()) {
                    header('Location: upload_wo.php?msg=parent_created');
                    exit;
                } else {
                    echo "Error updating child work order: " . $stmt_update->error;
                }
                $stmt_update->close();
            }
        } else {
            echo "Error creating parent work order: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
}
?>
