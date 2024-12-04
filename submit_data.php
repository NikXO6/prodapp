<?php
include('db.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $work_order_id = intval($_POST['work_order_id']);  // Selected Work Order ID
    $production_date = $_POST['production_date'];
    $quantity = intval($_POST['quantity']);
    $line = $_POST['line'];
    $memo = $_POST['memo'];

    // Calculate total staff count from workstations
    $total_staff_count = 0;
    $workstation_staff = $_POST['workstation_staff'] ?? [];
    foreach ($workstation_staff as $count) {
        $total_staff_count += floatval($count);
    }

    // Auto-calculate Units per Staff
    $units_per_staff = ($total_staff_count > 0) ? $quantity / $total_staff_count : 0;

    // Insert daily production data into the database
    $sql = "INSERT INTO daily_production (work_order_id, production_date, quantity, line, staff_count, units_per_staff, memo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isissss", $work_order_id, $production_date, $quantity, $line, $total_staff_count, $units_per_staff, $memo);

    if ($stmt->execute()) {
        // Get the last inserted daily production ID
        $daily_production_id = $stmt->insert_id;

        // Insert data into daily_production_workstation for each workstation
        $workstation_insert_sql = "INSERT INTO daily_production_workstation (daily_production_id, workstation_name, staff_count)
                                   VALUES (?, ?, ?)";
        $workstation_stmt = $conn->prepare($workstation_insert_sql);

        foreach ($workstation_staff as $station_name => $staff_count) {
            $workstation_stmt->bind_param("iss", $daily_production_id, $station_name, $staff_count);
            $workstation_stmt->execute();
        }

        $workstation_stmt->close();

        echo "Daily production data and workstation details inserted successfully!";
        header('Location: submit_data.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Daily Production Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-3">Submit Daily Production Data</h1>
        <form method="post" action="submit_data.php" class="form">

            <!-- Work Order Selection with Preview -->
            <div class="mb-3">
                <label for="work_order_id" class="form-label">Work Order</label>
                <select name="work_order_id" id="work_order_id" class="form-select" required>
                    <option value="" disabled selected>Select Work Order</option>
                    <?php
                    $sql = "SELECT id, work_order_number, line FROM work_orders WHERE status IN ('In Process', 'Released')";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['id']}'>{$row['work_order_number']}-{$row['line']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Work Order Preview -->
            <div id="wo_preview" class="mt-4">
                <h4>Work Order Preview</h4>
                <p id="wo_details">Please select a work order to see the details...</p>
            </div>

            <!-- Line Field (Auto-filled) -->
            <div class="mb-3">
                <label for="line" class="form-label">Line</label>
                <input type="text" name="line" id="line" class="form-control" readonly>
            </div>

            <!-- Daily Production Data -->
            <div class="mb-3">
                <label for="production_date" class="form-label">Production Date</label>
                <input type="date" value="<?= date('Y-m-d'); ?>" name="production_date" id="production_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter Quantity" required>
            </div>

            <!-- Workstation Staff Counts -->
            <h5>Workstation Staff Counts</h5>
            <div class="row gx-3 gy-2 align-items-center" id="workstationContainer">
                <!-- Workstation fields will be dynamically injected here based on the line -->
            </div>

            <!-- Memo Input -->
            <div class="mb-3">
                <label for="memo" class="form-label">Memo</label>
                <textarea name="memo" id="memo" class="form-control" rows="3" placeholder="Enter any additional notes"></textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Submit Data</button>
        </form>
    </div>

    <!-- AJAX Script to Fetch Work Order Details -->
    <script>
        const workstationsByLine = {
            "Full Auto": { "Cutting Station": 1.5, "Sewing Station": null, "Folding Station": 2, "Box Station": 1, "Sleeving Station": 1 },
            "Auto Pack": { "Folding Station": 2, "Box Station": 1, "Sleeving Station": 1 },
            "Manual Pack": { "Manual Packing": null },
            "Repack": { "Manual Repacking": null }
        };

        $(document).ready(function() {
            $('#work_order_id').select2({
                placeholder: 'Select Work Order',
                allowClear: true
            });

            $('#work_order_id').change(function() {
                const wo_id = $(this).val();
                if (wo_id) {
                    $.ajax({
                        url: 'get_wo_details.php',
                        type: 'GET',
                        data: { id: wo_id },
                        success: function(response) {
                            const wo = JSON.parse(response);
                            if (!wo.error) {
                                $('#line').val(wo.line);
                                updateWorkstationFields(wo.line);
                                
                                $('#wo_details').html(
                                    `<strong>Work Order Number:</strong> ${wo.work_order_number}<br>` +
                                    `<strong>Item Code:</strong> ${wo.item_code}<br>` +
                                    `<strong>Item Name:</strong> ${wo.item_name}<br>` +
                                    `<strong>Required Quantity:</strong> ${wo.required_qty}<br>` +
                                    `<strong>Start Date:</strong> ${wo.start_date}<br>` +
                                    `<strong>End Date:</strong> ${wo.end_date}<br>` +
                                    `<strong>Memo:</strong> ${wo.memo}`
                                );
                            } else {
                                $('#line').val('');
                                $('#workstationContainer').empty();
                                alert(wo.error);
                            }
                        },
                        error: function() {
                            alert('Error fetching work order details.');
                        }
                    });
                } else {
                    $('#line').val('');
                    $('#workstationContainer').empty();
                }
            });
        });

        // Update workstation fields based on line
        function updateWorkstationFields(line) {
            const container = $('#workstationContainer');
            container.empty();
            const workstations = workstationsByLine[line] || {};

            for (const [station, defaultCount] of Object.entries(workstations)) {
                const count = defaultCount !== null ? `value="${defaultCount}"` : "";
                container.append(`
                    <div class="col-auto">
                        <label class="form-label">${station} ${defaultCount !== null ? "(default " + defaultCount + ")" : "(no default)"}</label>
                        <input type="number" step="0.5" name="workstation_staff[${station}]" class="form-control" placeholder="Enter staff count" ${count}>
                    </div>
                `);
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>
</html>
