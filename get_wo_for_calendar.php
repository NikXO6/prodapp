<?php
include('db.php');

// Function to distribute required quantity across available days using waterfall approach for multiple lines
function schedule_work_orders_per_line($work_orders_by_line, $daily_capacities_by_line) {
    $scheduled_events = [];
    $line_current_day = [];  // Keep track of the current day for each line

    foreach ($work_orders_by_line as $line => $work_orders) {
        $line_capacity = $daily_capacities_by_line[$line];
        $line_current_day[$line] = new DateTime($work_orders[0]['start_date']);  // Start scheduling from the earliest work order's start date
        $remaining_capacity = $line_capacity;

        foreach ($work_orders as $order) {
            $remaining_qty = $order['required_qty'];

            // Assign background color based on status
            $background_color = '';
            switch ($order['status']) {
                case 'In Process':
                    $background_color = 'orange';  // Background for In Process
                    break;
                case 'Completed':
                    $background_color = 'green';  // Background for Completed
                    break;
                case 'Released':
                    $background_color = 'blue';   // Background for Released
                    break;
                default:
                    $background_color = 'gray';   // Default background for other statuses
            }

            // Assign border color based on line
            $border_color = '';
            switch ($line) {
                case 'PAB AUTO':
                    $border_color = 'red';  // Border color for PAB AUTO
                    break;
                case 'PAB MANUAL':
                    $border_color = 'yellow';  // Border color for PAB MANUAL
                    break;
                default:
                    $border_color = 'gray';  // Default border color
            }

            while ($remaining_qty > 0) {
                if ($remaining_capacity <= 0) {
                    // Move to the next day for this line and reset the capacity
                    $line_current_day[$line]->modify('+1 day');
                    $remaining_capacity = $line_capacity;
                }

                // Determine how much of the order can be produced today
                $qty_to_produce = min($remaining_capacity, $remaining_qty);

                $scheduled_events[] = [
                    'id' => $order['id'],
                    'title' => $order['work_order_number'] . ' - ' . $order['item_code'] . ' (' . $order['status'] . ')',
                    'start' => $line_current_day[$line]->format('Y-m-d'),
                    'end' => $order['end_date'],  // Use the end date for multi-day events
                    'backgroundColor' => $background_color,  // Background based on status
                    'borderColor' => $border_color,          // Border based on line
                    'extendedProps' => [
                        'required_qty' => $qty_to_produce,
                        'item_code' => $order['item_code'],
                        'line' => $line,
                        'status' => $order['status']
                    ]
                ];

                // Decrease remaining quantities and capacity for the current line
                $remaining_qty -= $qty_to_produce;
                $remaining_capacity -= $qty_to_produce;
            }
        }
    }

    return $scheduled_events;
}

// Fetch all work orders, grouped by line, and sorted by priority within each line
$sql = "SELECT id, work_order_number, item_code, required_qty, line, priority, start_date, end_date, status
        FROM work_orders
        WHERE status IN ('In Process', 'Released', 'Completed') 
        ORDER BY line, priority ASC";

$result = mysqli_query($conn, $sql);

// Organize work orders by line
$work_orders_by_line = [];
while ($row = $result->fetch_assoc()) {
    $work_orders_by_line[$row['line']][] = $row;
}

// Define the daily capacity for each line
$daily_capacities_by_line = [
    'PAB AUTO' => 1200,  // Line AUTO capacity
    'PAB MANUAL' => 600,  // Line MANUAL capacity
    'BUDDIES' => 300,  // Line BUDDIES capacity
];

// Schedule work orders using the waterfall approach per line
$scheduled_events = schedule_work_orders_per_line($work_orders_by_line, $daily_capacities_by_line);

header('Content-Type: application/json');
echo json_encode($scheduled_events);
?>
