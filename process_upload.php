<?php
include('db.php');
session_start();

// Get the action for handling duplicates, default to 'skip'
$duplicate_action = $_POST['duplicate_action'] ?? 'skip';

// Get the duplicate and non-duplicate work orders from the session
$duplicate_work_orders = $_SESSION['duplicate_work_orders'] ?? [];
$non_duplicate_work_orders = $_SESSION['non_duplicate_work_orders'] ?? [];

// Insert work orders first (ignore parent references initially)
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
    $parent_so_or_wo = $data[9];  // Parent Work Order/Sales Order number column
    $sales_order_number = isset($data[10]) ? $data[10] : null;  // Sales Order Number

    // If total_produced is 0, set status to 'Released'
    $total_produced = 0;  // New work orders will have 0 produced initially
    $status = ($total_produced == 0) ? 'Released' : 'In Process';

    // Insert the work order without parent info
    $sql = "INSERT INTO work_orders (work_order_number, item_code, item_name, start_date, end_date, required_qty, memo, status, line, priority, sales_order_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssssssissis', $work_order_number, $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority, $sales_order_number);
        if (!$stmt->execute()) {
            echo "Error inserting work order: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
}

// Step 2: After inserting all work orders, now handle the parent work order assignments
foreach (array_merge($non_duplicate_work_orders, $duplicate_work_orders) as $data) {
    $work_order_number = $data[0];
    $parent_so_or_wo = $data[9];  // This is the column that may contain Parent Work Order or Sales Order

    // Initialize variables for parent work order ID
    $parent_work_order_id = null;

    // Determine whether the value is a parent work order or a sales order
    if (strpos($parent_so_or_wo, 'Work Order #') === 0) {
        // It's a Parent Work Order, we fetch the ID from the number
        $parent_work_order_number = str_replace('Work Order #', '', $parent_so_or_wo);

        // Check if the Parent Work Order exists
        $sql = "SELECT id FROM work_orders WHERE work_order_number = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $parent_work_order_number);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $parent_work_order_id = $row['id']; // Parent Work Order exists
            } else {
                // Parent Work Order doesn't exist, confirm with the user
                echo "<script>
                    if (confirm('Parent Work Order #{$parent_work_order_number} does not exist. Do you want to create it?')) {
                        window.location.href = 'create_parent_wo.php?parent_wo_number={$parent_work_order_number}&child_wo={$work_order_number}';
                    } else {
                        // Skip creating the parent
                        window.location.href = 'upload_wo.php?msg=skipped';
                    }
                </script>";
                exit;
            }
            $stmt->close();
        }
    }

    // Update the child work orders with the parent_work_order_id
    $sql_update = "UPDATE work_orders SET parent_wo_id = ? WHERE work_order_number = ?";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param('is', $parent_work_order_id, $work_order_number);
        if (!$stmt_update->execute()) {
            echo "Error updating parent references: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

header('Location: upload_wo.php?msg=success');
exit;
?>
