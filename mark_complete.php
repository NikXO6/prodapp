<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the id
    $original_id = intval($_POST['id']);

    // Fetch the original record data from the database
    $sql = "SELECT * FROM production_data WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $original_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $original_record = $result->fetch_assoc();

    if ($original_record) {
        // Prepare the SQL statement to insert a new record with status = 'Completed'
        $stmt_insert = $conn->prepare("INSERT INTO production_data (item_code, item_name, quantity, line, staff_count, units_per_staff, wo_number, status, memo, status_change_timestamp, original_id)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        
        $status = 'Completed';
        $stmt_insert->bind_param(
            "ssisiisssi",
            $original_record['item_code'],
            $original_record['item_name'],
            $original_record['quantity'],
            $original_record['line'],
            $original_record['staff_count'],
            $original_record['units_per_staff'],
            $original_record['wo_number'],
            $status,
            $original_record['memo'],
            $original_id // Reference to the original record
        );

        // Execute the insert query
        if ($stmt_insert->execute()) {
            // Redirect back to the dashboard with a success message
            header('Location: index.php?msg=record_added');
        } else {
            echo "Error inserting new record: " . $stmt_insert->error;
        }

        // Close the statement
        $stmt_insert->close();
    } else {
        echo "Original record not found.";
    }

    // Close the original select statement and connection
    $stmt->close();
    $conn->close();
}
?>
