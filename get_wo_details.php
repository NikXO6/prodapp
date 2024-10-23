<?php
include('db.php');

if (isset($_GET['id'])) {
    $wo_id = intval($_GET['id']);

    // Fetch the work order details from the database
    $sql = "SELECT work_order_number, item_code, item_name, required_qty, start_date, status, memo, line FROM work_orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $wo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Format the output
        $formatted_date = date('d-m-Y', strtotime($row['start_date']));
        echo json_encode([
            'work_order_number' => $row['work_order_number'],
            'item_code' => $row['item_code'],
            'item_name' => $row['item_name'],
            'required_qty' => $row['required_qty'],
            'start_date' => $formatted_date,
            'memo' => $row['memo'],
            'status' => $row['status'],
            'line' => $row['line']  // Include the line
        ]);
    } else {
        echo json_encode(['error' => 'No details found for this work order.']);
    }

    $stmt->close();
    $conn->close();
}
?>
