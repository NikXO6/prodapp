<?php
include('db.php');
session_start();

// Get the action for handling duplicates, default to 'skip'
$duplicate_action = $_POST['duplicate_action'] ?? 'skip';

// Get the duplicate and non-duplicate work orders from the session
$duplicate_work_orders = $_SESSION['duplicate_work_orders'] ?? [];
$non_duplicate_work_orders = $_SESSION['non_duplicate_work_orders'] ?? [];

// Function to extract Work Order or Sales Order number
$sales_order_number = null;
$parent_wo_number = null;

if (strpos($data[9], 'SO') === 0) {
    $sales_order_number = trim($data[9]);
} elseif (strpos($data[9], 'WO') === 0) {
    $parent_wo_number = trim($data[9]);
}

function parse_date($date_str)
{
    $date_obj = DateTime::createFromFormat('d/m/Y', $date_str);
    if ($date_obj !== false) {
        return $date_obj->format('Y-m-d');
    }
    return null; // Return NULL if parsing fails
}

// Step 1: Insert all non-duplicate work orders first
foreach ($non_duplicate_work_orders as $data) {
    $work_order_number = $data[0];
    $item_code = !empty($data[1]) ? $data[1] : null;
    $item_name = !empty($data[2]) ? $data[2] : null;
    $internal_id = !empty($data[10]) ? $data[10] : null;
    $start_date = parse_date($data[3]);  // Parse the date to YYYY-MM-DD
    $end_date = isset($data[4]) ? parse_date($data[4]) : null;  // Optional end_date with parsing
    $required_qty = intval($data[5]);
    $memo = isset($data[6]) && !empty($data[6]) ? $data[6] : null;
    $line = !empty($data[7]) ? $data[7] : null;
    $priority = isset($data[8]) && !empty($data[8]) ? $data[8] : null;
    $sales_order_number = null;
    $parent_wo_number = null;

    if (strpos($data[9], 'SO') === 0) {
        $sales_order_number = trim($data[9]);
    } elseif (strpos($data[9], 'WO') === 0) {
        $parent_wo_number = trim($data[9]);
    }


    $total_produced = 0;
    $status = ($total_produced == 0) ? 'Released' : 'In Process';

    $sql = "INSERT INTO work_orders (work_order_number, item_code, item_name, start_date, end_date, required_qty, memo, status, line, priority, sales_order_number, parent_wo_number, internal_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('sssssissssssi', $work_order_number, $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority, $sales_order_number, $parent_wo_number, $internal_id);
        if (!$stmt->execute()) {
            echo "Error inserting record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
}

// Step 2: Handle duplicate work orders based on the selected action
if (!empty($duplicate_work_orders)) {
    foreach ($duplicate_work_orders as $data) {
        $work_order_number = $data[0];
        $item_code = !empty($data[1]) ? $data[1] : null;
        $item_name = !empty($data[2]) ? $data[2] : null;
        $internal_id = !empty($data[10]) ? $data[10] : null;
        $start_date = parse_date($data[3]);  // Parse the date to YYYY-MM-DD
        $end_date = isset($data[4]) ? parse_date($data[4]) : null;  // Optional end_date with parsing
        $required_qty = intval($data[5]);
        $memo = isset($data[6]) && !empty($data[6]) ? $data[6] : null;
        $line = !empty($data[7]) ? $data[7] : null;
        $priority = isset($data[8]) && !empty($data[8]) ? $data[8] : null;
        $sales_order_number = null;
        $parent_wo_number = null;

        if (strpos($data[9], 'SO') === 0) {
            $sales_order_number = trim($data[9]);
        } elseif (strpos($data[9], 'WO') === 0) {
            $parent_wo_number = trim($data[9]);
        }


        $total_produced = 0;
        $status = ($total_produced == 0) ? 'Released' : 'In Process';

        if ($duplicate_action == 'skip') {
            continue;
        } elseif ($duplicate_action == 'update') {
            $sql = "UPDATE work_orders SET item_code = ?, item_name = ?, start_date = ?, end_date = ?, required_qty = ?, memo = ?, status = ?, line = ?, priority = ?, sales_order_number = ?, parent_wo_number = ?, internal_id = ?
                    WHERE work_order_number = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('sssssissssssi', $work_order_number, $item_code, $item_name, $start_date, $end_date, $required_qty, $memo, $status, $line, $priority, $sales_order_number, $parent_wo_number, $internal_id);
                if (!$stmt->execute()) {
                    echo "Error inserting record: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error preparing query: " . $conn->error;
            }
        }
    }
}

// Step 3: Update Parent Work Orders (if they exist in the same upload)
$sql_update_parent_wo = "UPDATE work_orders wo
                         JOIN work_orders parent_wo ON wo.parent_wo_number = parent_wo.work_order_number
                         SET wo.parent_wo_id = parent_wo.id
                         WHERE wo.parent_wo_id IS NULL";
$conn->query($sql_update_parent_wo);

// Redirect back to the upload page with a success message
header('Location: upload_wo.php?msg=success');
exit;
