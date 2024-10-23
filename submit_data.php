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
    $staff_count = intval($_POST['staff_count']);
    $memo = $_POST['memo'];

    // Auto-calculate Units per Staff
    $units_per_staff = ($staff_count > 0) ? $quantity / $staff_count : 0;

    // Insert daily production data into the database
    $sql = "INSERT INTO daily_production (work_order_id, production_date, quantity, line, staff_count, units_per_staff, memo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isisiss", $work_order_id, $production_date, $quantity, $line, $staff_count, $units_per_staff, $memo);

    if ($stmt->execute()) {
        echo "Daily production data inserted successfully!";
        header('Location: index.php');
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                    // Fetch active work orders from the database
                    $sql = "SELECT id, work_order_number FROM work_orders WHERE status IN ('In Process', 'Released')";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['id']}'>{$row['work_order_number']}</option>";
                    }
                    ?>
                </select>
            </div>


            <!-- Work Order Preview -->
            <div id="wo_preview" class="mt-4">
                <h4>Work Order Preview</h4>
                <p id="wo_details">Please select a work order to see the details...</p>
            </div>
            <?php

            $month = date('m');
            $day = date('d');
            $year = date('Y');

            $today = $year . '-' . $month . '-' . $day;
            ?>
            <!-- Daily Production Data -->
            <div class="mb-3">
                <label for="production_date" class="form-label">Production Date</label>
                <input type="date" value="<?php echo $today; ?>" name="production_date" id="production_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter Quantity" required>
            </div>
            <div class="mb-3">
                <label for="line" class="form-label">Line</label>
                <input type="text" name="line" id="line" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="staff_count" class="form-label">Staff Count</label>
                <input type="number" name="staff_count" id="staff_count" class="form-control" placeholder="Enter Staff Count" required>
            </div>
            <div class="mb-3">
                <label for="memo" class="form-label">Memo</label>
                <textarea name="memo" id="memo" class="form-control" rows="3" placeholder="Enter any additional notes"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Data</button>
        </form>
    </div>

    <!-- AJAX Script to Fetch Work Order Details -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 on the work order select box
            $('#work_order_id').select2({
                placeholder: 'Select Work Order',
                allowClear: true
            });

            $('#work_order_id').change(function() {
                var wo_id = $(this).val();
                if (wo_id) {
                    $.ajax({
                        url: 'get_wo_details.php',
                        type: 'GET',
                        data: {
                            id: wo_id
                        },
                        success: function(response) {
                            var wo = JSON.parse(response);

                            if (wo.error) {
                                $('#wo_details').html(wo.error);
                                $('#line').val(''); // Clear line field if error
                            } else {
                                // Populate work order details
                                $('#wo_details').html(
                                    `<strong>Work Order Number:</strong> ${wo.work_order_number}<br>` +
                                    `<strong>Item Code:</strong> ${wo.item_code}<br>` +
                                    `<strong>Item Name:</strong> ${wo.item_name}<br>` +
                                    `<strong>Required Quantity:</strong> ${wo.required_qty}<br>` +
                                    `<strong>Start Date:</strong> ${wo.start_date}<br>` +
                                    `<strong>Memo:</strong> ${wo.memo}`
                                );

                                // Automatically set the line field
                                $('#line').val(wo.line);
                            }
                        },
                        error: function() {
                            $('#wo_details').html('Error fetching work order details. Please try again.');
                            $('#line').val(''); // Clear line field if AJAX fails
                        }
                    });
                } else {
                    $('#wo_details').html('Please select a work order to see the details...');
                    $('#line').val(''); // Clear line field if no selection
                }
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>

</html>