<?php
include('db.php');

// Get the production data id from the URL
$production_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($production_id > 0) {
    // First, get the work_order_id for the production entry
    $sql_get_wo = "SELECT work_order_id FROM daily_production WHERE id = ?";
    $stmt = $conn->prepare($sql_get_wo);
    $stmt->bind_param('i', $production_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $work_order_id = $row['work_order_id'];
    
    // Delete the production entry
    $sql_delete = "DELETE FROM daily_production WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $production_id);
    
    if ($stmt_delete->execute()) {
        // Check if there are any remaining production records for the same work order
        $sql_check_remaining = "SELECT SUM(quantity) as total_produced FROM daily_production WHERE work_order_id = ?";
        $stmt_check = $conn->prepare($sql_check_remaining);
        $stmt_check->bind_param('i', $work_order_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $total_produced = $row_check['total_produced'];

        // If total produced is now 0, set the status of the work order to "Released"
        if ($total_produced == 0) {
            $sql_update_status = "UPDATE work_orders SET status = 'Released' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update_status);
            $stmt_update->bind_param('i', $work_order_id);
            $stmt_update->execute();
        } else {
            // If production exists, make sure status remains "In Process" or "Completed"
            $sql_get_required_qty = "SELECT required_qty FROM work_orders WHERE id = ?";
            $stmt_get_qty = $conn->prepare($sql_get_required_qty);
            $stmt_get_qty->bind_param('i', $work_order_id);
            $stmt_get_qty->execute();
            $result_qty = $stmt_get_qty->get_result();
            $row_qty = $result_qty->fetch_assoc();
            $required_qty = $row_qty['required_qty'];

            $new_status = ($total_produced < $required_qty) ? 'In Process' : 'Completed';

            $sql_update_status = "UPDATE work_orders SET status = ? WHERE id = ?";
            $stmt_update_status = $conn->prepare($sql_update_status);
            $stmt_update_status->bind_param('si', $new_status, $work_order_id);
            $stmt_update_status->execute();
        }

        // Redirect to the dashboard with a success message
        header('Location: index.php?msg=deleted');
        exit;

    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    // If no valid production ID is provided, redirect back
    header('Location: index.php');
    exit;
}
?>
