<?php
include('db.php');

$date = $_POST['date'];
$item_code = $_POST['item_code'];

// Get the production rate for the item code (units per hour)
$sql = "SELECT production_rate FROM item_capacity WHERE item_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $item_code);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$production_rate = $row['production_rate'];

// Calculate the total daily capacity based on 7-hour workday
$daily_capacity = $production_rate * 7;  // 7 hours per day

// Calculate the total required quantity for the day from all WOs
$sql = "SELECT SUM(required_qty) AS total_qty FROM work_orders WHERE start_date <= ? AND end_date >= ? AND item_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $date, $date, $item_code);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_qty = $row['total_qty'];

// Compare total required quantity with daily capacity
if ($total_qty > $daily_capacity) {
    echo "OVER";  // If total WO quantities exceed the daily capacity
} else {
    echo "OK";    // If total WO quantities can be completed within the daily capacity
}
?>
