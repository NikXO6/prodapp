<?php
include('db.php');
session_start();

// Get the action for handling duplicates, default to 'skip'
$duplicate_action = $_POST['duplicate_action'] ?? 'skip';

// Get the duplicate and non-duplicate work orders from the session
$duplicate_work_orders = $_SESSION['duplicate_work_orders'] ?? [];
$non_duplicate_work_orders = $_SESSION['non_duplicate_work_orders'] ?? [];

// Process non-duplicate work orders (insert directly)
foreach ($non_duplicate_work_orders as $data) {
    $work_order_number = $data[0];
    $item_code = $data[1];
    $item_name = $data[2];
    $start_date = $data[3];
    $end_date = isset($data[4]) ? $data[4] : null;  // Optional end_date
    $required_qty = intval($data[5]);
    $memo = isset($data[6]) ? $data[6] : '';  // Memo
    $line = isset($data[7]) ? $data[7] : null;  // Production line
    $priority = isset($data[8]) ? intval($data[8]) : null;  // Priority

    // If total_produced is 0, set status to 'Released'
    $total_produced = 0;  // New work orders will have 0 produced initially
    $status = ($total_produced == 0) ? 'Released' : 'In Process';

    // Insert the non-duplicate work orders
    $sql = "INSERT INTO work_orders (work_order_number, item_code, item_name, start_date, end_date, required_qty, memo, status, line, priority)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssssssissi', $work_order_number, $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority);
        if (!$stmt->execute()) {
            // Log the error message or handle it in some way
            echo "Error inserting work order: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Handle prepare error
        echo "Error preparing query: " . $conn->error;
    }
}

// Process duplicate work orders based on the selected action
if (!empty($duplicate_work_orders)) {
    foreach ($duplicate_work_orders as $data) {
        $work_order_number = $data[0];
        $item_code = $data[1];
        $item_name = $data[2];
        $start_date = $data[3];
        $end_date = isset($data[4]) ? $data[4] : null;  // Optional end_date
        $required_qty = intval($data[5]);
        $memo = isset($data[6]) ? $data[6] : '';
        $line = isset($data[7]) ? $data[7] : null;  // Production line
        $priority = isset($data[8]) ? intval($data[8]) : null;  // Priority

        // Assume total_produced is 0 if this is a "Released" work order
        $total_produced = 0;
        $status = ($total_produced == 0) ? 'Released' : 'In Process';

        if ($duplicate_action == 'skip') {
            // Skip the duplicate, do nothing
            continue;
        } elseif ($duplicate_action == 'update') {
            // Update existing records
            $sql = "UPDATE work_orders SET item_code = ?, item_name = ?, start_date = ?, end_date = ?, required_qty = ?, memo = ?, status = ?, line = ?, priority = ?
                    WHERE work_order_number = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('ssssssissi', $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority, $work_order_number);
                if (!$stmt->execute()) {
                    // Log the error message or handle it in some way
                    echo "Error updating work order: " . $stmt->error;
                }
                $stmt->close();
            } else {
                // Handle prepare error
                echo "Error preparing query: " . $conn->error;
            }
        } elseif ($duplicate_action == 'ignore') {
            // Ignore the duplicate, do nothing
            continue;
        }
    }
}

// Redirect back to the upload page with a success message
header('Location: upload_wo.php?msg=success');
exit;
?>
