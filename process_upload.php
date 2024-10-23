<?php
include('db.php');
session_start();

// Get the action for handling duplicates, default to 'skip'
$duplicate_action = $_POST['duplicate_action'] ?? 'skip';

// Get the duplicate and non-duplicate work orders from the session
$duplicate_work_orders = $_SESSION['duplicate_work_orders'] ?? [];
$non_duplicate_work_orders = $_SESSION['non_duplicate_work_orders'] ?? [];

$new_work_orders = 0;
$updated_work_orders = 0;
$skipped_duplicates = 0;

// Process non-duplicate work orders (insert directly)
foreach ($non_duplicate_work_orders as $data) {
    $work_order_number = $data[0];
    $item_code = $data[1];
    $item_name = $data[2];
    $start_date = $data[3];
    $end_date = isset($data[4]) ? $data[4] : null;  // Optional end_date
    $required_qty = intval($data[5]);
    $memo = isset($data[6]) ? $data[6] : null;  // Memo
    $line = isset($data[7]) ? $data[7] : null;  // Production line
    $priority = isset($data[8]) ? intval($data[8]) : null;  // Priority
    $parent_wo_id = isset($data[9]) ? intval($data[9]) : null; // Parent Work Order ID for sub-assemblies

    // If total_produced is 0, set status to 'Released'
    $total_produced = 0;  // New work orders will have 0 produced initially
    $status = ($total_produced == 0) ? 'Released' : 'In Process';

    // Insert the non-duplicate work orders
    $sql = "INSERT INTO work_orders (work_order_number, item_code, item_name, start_date, end_date, required_qty, memo, status, line, priority, parent_wo_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssssssissii', $work_order_number, $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority, $parent_wo_id);
        if ($stmt->execute()) {
            $new_work_orders++;
        } else {
            error_log("Error inserting work order: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Error preparing query: " . $conn->error);
    }
}

// Process duplicate work orders based on the selected action
foreach ($duplicate_work_orders as $data) {
    $work_order_number = $data[0];
    $item_code = $data[1];
    $item_name = $data[2];
    $start_date = $data[3];
    $end_date = isset($data[4]) ? $data[4] : null;  // Optional end_date
    $required_qty = intval($data[5]);
    $memo = isset($data[6]) ? $data[6] : null;
    $line = isset($data[7]) ? $data[7] : null;  // Production line
    $priority = isset($data[8]) ? intval($data[8]) : null;  // Priority
    $parent_wo_id = isset($data[9]) ? intval($data[9]) : null; // Parent Work Order ID for sub-assemblies

    // Assume total_produced is 0 if this is a "Released" work order
    $total_produced = 0;
    $status = ($total_produced == 0) ? 'Released' : 'In Process';

    if ($duplicate_action == 'skip') {
        $skipped_duplicates++;
        continue;
    } elseif ($duplicate_action == 'update') {
        // Update existing records
        $sql = "UPDATE work_orders SET item_code = ?, item_name = ?, start_date = ?, end_date = ?, required_qty = ?, memo = ?, status = ?, line = ?, priority = ?, parent_wo_id = ?
                WHERE work_order_number = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssssssissis', $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority, $parent_wo_id, $work_order_number);
            if ($stmt->execute()) {
                $updated_work_orders++;
            } else {
                error_log("Error updating work order: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Error preparing query: " . $conn->error);
        }
    } elseif ($duplicate_action == 'ignore') {
        continue;
    }
}

// Redirect back to the upload page with detailed success message
$msg = "New work orders: $new_work_orders, Updated work orders: $updated_work_orders, Skipped duplicates: $skipped_duplicates";
header("Location: upload_wo.php?msg=" . urlencode($msg));
exit;
?>
