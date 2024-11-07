<?php
include('db.php');
session_start(); // Ensure the session is started

// Retrieve session data or set default values
$work_order_id = $_SESSION['work_order_id'] ?? null;
$po_number = $_SESSION['po_number'] ?? '';
$fabric_code = $_SESSION['fabric_code'] ?? '';
$stillage_number = $_SESSION['stillage_number'] ?? '';

// Check if essential data is available before proceeding
if ($work_order_id === null) {
    die("Work Order ID is required to submit material usage.");
}

// Sanitize and validate input data from POST request
$roll_number = $_POST['roll_number'] ?? '';
$meters_used = isset($_POST['meters_used']) ? floatval($_POST['meters_used']) : 0;
$initial_machine_count = isset($_POST['initial_machine_count']) ? intval($_POST['initial_machine_count']) : 0;
$final_machine_count = isset($_POST['final_machine_count']) ? intval($_POST['final_machine_count']) : 0;

// Insert data into material_usage table
$sql = "INSERT INTO material_usage (work_order_id, roll_number, fabric_code, meters_used, stillage_number, po_number, initial_machine_count, final_machine_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    'issdssdd', 
    $work_order_id, 
    $roll_number, 
    $fabric_code, 
    $meters_used, 
    $stillage_number, 
    $po_number, 
    $initial_machine_count, 
    $final_machine_count, 
);

if ($stmt->execute()) {
    echo "Material usage entry added successfully!";
    echo $po_number;
    echo $fabric_code;
    echo $stillage_number;
    
    // Optionally, redirect back to the input page
    header("Location: material_usage.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
