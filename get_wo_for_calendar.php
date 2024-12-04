<?php
include('db.php');

// Increase memory limit
ini_set('memory_limit', '512M');

// Function to distribute required quantity across available days using waterfall approach for multiple lines
function schedule_work_orders_per_line($work_orders_by_line, $daily_capacities_by_line)
{
    $scheduled_events = [];
    $daily_usage_by_line = [];  // Track daily usage for each line

    foreach ($work_orders_by_line as $line => $work_orders) {
        if (empty($line) || empty($work_orders)) {
            continue; // Skip if line is empty or no work orders are available
        }

        $line_capacity = $daily_capacities_by_line[$line];

        foreach ($work_orders as $order) {
            // Set the current day to the specific work order's start date to reflect updated information
            $current_day = new DateTime($order['start_date']);
            $remaining_qty = $order['required_qty'];
            $is_affected = false; // Flag to check if the work order is affected due to capacity limitations

            // Generate a unique background color for each work order based on its ID
            $background_color = generate_unique_color_hsl($order['id'], $order['status']);

            // Assign border color based on line
            $border_color = match ($line) {
                'Full Auto' => 'red',
                'Auto Pack' => 'indigo',
                'Repack' => 'crimson',
                'Manual Pack' => 'green',
                default => 'gray',
            };

            while ($remaining_qty > 0) {
                // Skip weekends
                if ($current_day->format('N') >= 6) { // 6 = Saturday, 7 = Sunday
                    $current_day->modify('+1 day');
                    continue; // Move to the next day if it's a weekend
                }

                $current_day_str = $current_day->format('Y-m-d');

                // Initialize daily usage if not already set for this line and day
                if (!isset($daily_usage_by_line[$line][$current_day_str])) {
                    $daily_usage_by_line[$line][$current_day_str] = 0;
                }

                // Calculate remaining capacity for the current day
                $remaining_capacity = $line_capacity - $daily_usage_by_line[$line][$current_day_str];

                // Determine how much of the order can be produced today
                $qty_to_produce = min($remaining_capacity, $remaining_qty);

                // If remaining capacity is not enough for the entire work order, set the affected flag
                if ($qty_to_produce < $remaining_qty) {
                    $is_affected = true;
                }

                $scheduled_events[] = [
                    'id' => $order['id'],
                    'title' => $order['work_order_number'] . '-' . $qty_to_produce, // Display WO-QTY
                    'start' => $current_day_str,
                    'end' => $current_day_str,
                    'backgroundColor' => $background_color,
                    'borderColor' => $border_color,
                    'borderWidth' => '3px',  // Specify a thicker border width
                    'padding' => '2px',  // Add padding to the event
                    'extendedProps' => [
                        'required_qty' => $qty_to_produce,
                        'actual_qty' => $order['required_qty'],
                        'item_code' => $order['item_code'],
                        'line' => $line,
                        'status' => $order['status'],
                        'is_affected' => $is_affected,
                        'capacity_exceeded' => ($daily_usage_by_line[$line][$current_day_str] + $qty_to_produce > $line_capacity)
                    ]
                ];

                // Update daily usage and reduce remaining quantity
                $daily_usage_by_line[$line][$current_day_str] += $qty_to_produce;
                $remaining_qty -= $qty_to_produce;

                // Move to the next day if needed
                if ($remaining_qty > 0) {
                    $current_day->modify('+1 day');
                }

                // Safeguard to prevent infinite loop in case of incorrect data
                if (count($scheduled_events) > 10000) {
                    error_log("Too many scheduled events, terminating to prevent memory exhaustion.");
                    break 2;  // Exit the entire loop
                }
            }
        }
    }

    return $scheduled_events;
}

// Function to generate a unique color for each work order using HSL
function generate_unique_color_hsl($work_order_id, $status)
{
    // If the work order is completed, use a fixed green color
    if ($status == 'Completed') {
        return 'hsl(120, 50%, 50%)';  // Standard green color
    }

    // Generate a hue by hashing the work order ID
    $hash = md5($work_order_id);
    $hue = (hexdec(substr($hash, 0, 8)) % 360); // Convert to a value between 0 and 359

    // Vary the saturation and lightness slightly for more color distinction
    $saturation = (hexdec(substr($hash, 6, 2)) % 40) + 40; // Random value between 60% - 80%
    $lightness = (hexdec(substr($hash, 8, 2)) % 30) + 40;  // Random value between 40% - 60%

    return "hsl($hue, {$saturation}%, {$lightness}%)";
}










// Fetch all work orders, grouped by line, and sorted by priority within each line
$sql = "SELECT id, work_order_number, item_code, required_qty, line, priority, start_date, end_date, status
        FROM work_orders
        WHERE status IN ('In Process', 'Released', 'Completed') 
        ORDER BY line, priority ASC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die('Error: ' . mysqli_error($conn));
}

// Organize work orders by line
$work_orders_by_line = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['line'])) {
        $work_orders_by_line[$row['line']][] = $row;
    }
}

// Define the daily capacity for each line
$daily_capacities_by_line = [
    'Full Auto' => 1200,
    'Auto Pack' => 600,
    'Repack' => 300,
    'Manual Pack' => 300
];

// Schedule work orders using the waterfall approach per line
$scheduled_events = schedule_work_orders_per_line($work_orders_by_line, $daily_capacities_by_line);

header('Content-Type: application/json');
echo json_encode($scheduled_events);
