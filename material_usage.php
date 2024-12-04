<?php
include('db.php');
session_start();

// Start a new WO session when selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_wo'])) {
    $work_order_id = $_POST['work_order_id'];
    $stmt = $conn->prepare("SELECT work_order_number FROM work_orders WHERE id = ?");
    $stmt->bind_param("i", $work_order_id);
    $stmt->execute();
    $stmt->bind_result($work_order_number);
    $stmt->fetch();
    $stmt->close();

    $_SESSION['work_order_id'] = $work_order_id;
    $_SESSION['work_order_number'] = $work_order_number;
}

// Set individual session variables for fabric_code, po_number, and stillage_number
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_fabric_code'])) {
        $_SESSION['fabric_code'] = $_POST['fabric_code'];
    }
    if (isset($_POST['set_po_number'])) {
        $_SESSION['po_number'] = $_POST['po_number'];
    }
    if (isset($_POST['set_stillage_number'])) {
        $_SESSION['stillage_number'] = $_POST['stillage_number'];
    }
}

// End the WO session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['end_wo'])) {
    unset($_SESSION['work_order_id'], $_SESSION['work_order_number'], $_SESSION['fabric_code'], $_SESSION['po_number'], $_SESSION['stillage_number']);
}

$work_order_id = $_SESSION['work_order_id'] ?? '';
$work_order_number = $_SESSION['work_order_number'] ?? '';
$default_fabric_code = $_SESSION['fabric_code'] ?? '';
$default_po_number = $_SESSION['po_number'] ?? '';
$default_stillage_number = $_SESSION['stillage_number'] ?? '';

$work_orders = [];
$result = $conn->query("SELECT id, work_order_number FROM work_orders ORDER BY work_order_number ASC");
while ($row = $result->fetch_assoc()) {
    $work_orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Usage Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-4">Roll Usage Tracking</h1>

        <?php if ($work_order_number): ?>
            <div class="alert alert-success d-flex justify-content-between" role="alert">
                <span>Active Work Order: <?= htmlspecialchars($work_order_number) ?></span>
                <form method="post" class="d-inline">
                    <button type="submit" name="end_wo" class="btn btn-danger btn-sm">End Work Order</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Start WO Dropdown -->
        <form method="post" class="mb-4">
            <div class="row">
                <div class="col-12 col-md-8 mb-3 mb-md-0">
                    <label for="work_order_id" class="form-label">Select Work Order</label>
                    <select name="work_order_id" id="work_order_id" class="form-select">
                        <option value="">-- Select Work Order --</option>
                        <?php foreach ($work_orders as $wo): ?>
                            <option value="<?= htmlspecialchars($wo['id']) ?>" <?= $work_order_id == $wo['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($wo['work_order_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end">
                    <button type="submit" name="start_wo" class="btn btn-primary w-100">Start Work Order</button>
                </div>
            </div>
        </form>

        <!-- Set Fabric Code, PO Number, Stillage Number Individually -->
        <form method="post" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="fabric_code" class="form-label">Fabric Code</label>
                    <select name="fabric_code" id="fabric_code" class="form-select">
                        <option value="">-- Select Fabric Code --</option>
                        <option value="CM_FBR00002" <?= $default_fabric_code == "CM_FBR00002" ? 'selected' : '' ?>>CM_FBR00002 Bamboo Jersey</option>
                        <option value="CM_FBR00018" <?= $default_fabric_code == "CM_FBR00018" ? 'selected' : '' ?>>CM_FBR00018 Double Sided Terry</option>
                        <option value="CM_FBR00022" <?= $default_fabric_code == "CM_FBR00022" ? 'selected' : '' ?>>CM_FBR00022 Cotton Quilted Quilted</option>
                        <option value="CM_FBR00030" <?= $default_fabric_code == "CM_FBR00030" ? 'selected' : '' ?>>CM_FBR00030 Dynatex/Polyester Jersey</option>
                        <option value="CM_FBR00034" <?= $default_fabric_code == "CM_FBR00034" ? 'selected' : '' ?>>CM_FBR00034 Tencel Jacquard FRESCHE PAB LOGO UNBLEACHED</option>
                        <option value="CM_FBR00036" <?= $default_fabric_code == "CM_FBR00036" ? 'selected' : '' ?>>CM_FBR00036 Tencel Jersey(White) FRESCHE</option>
                        <option value="CM_FBR00037" <?= $default_fabric_code == "CM_FBR00037" ? 'selected' : '' ?>>CM_FBR00037 Cotton Terry FRESCHE</option>
                        <option value="CM_FBR00054" <?= $default_fabric_code == "CM_FBR00054" ? 'selected' : '' ?>>CM_FBR00054 Polartex Jacquard FRESCHE</option>
                        <option value="CM_FBR00057" <?= $default_fabric_code == "CM_FBR00057" ? 'selected' : '' ?>>CM_FBR00057 Cotton Jacquard FRESCHE PEBBLE PATTERN</option>
                        <option value="CM_FBR00058" <?= $default_fabric_code == "CM_FBR00058" ? 'selected' : '' ?>>CM_FBR00058 Bamboo Reversible/Cotton FRESCHE</option>
                        <option value="CM_FBR00059" <?= $default_fabric_code == "CM_FBR00059" ? 'selected' : '' ?>>CM_FBR00059 Cotton Jacquard FRESCHE DIAMOND PATTERN</option>
                        <option value="CM_FBR00060" <?= $default_fabric_code == "CM_FBR00060" ? 'selected' : '' ?>>CM_FBR00060 Polartex/Tencel Jacquard FRESCHE</option>
                        <option value="CM_FBR00062" <?= $default_fabric_code == "CM_FBR00062" ? 'selected' : '' ?>>CM_FBR00062 Tencel Jersey(CREAM)</option>
                        <option value="CM_FBR00063" <?= $default_fabric_code == "CM_FBR00063" ? 'selected' : '' ?>>CM_FBR00063 Tencel Jersey(CHARCOAL)</option>
                        <option value="CM_FBR00064" <?= $default_fabric_code == "CM_FBR00064" ? 'selected' : '' ?>>CM_FBR00064 Tencel Jersey(LATTE)</option>
                        <option value="CM_FBR00065" <?= $default_fabric_code == "CM_FBR00065" ? 'selected' : '' ?>>CM_FBR00065 Tencel Jersey(COBALT)</option>
                        <option value="CM_FBR00070" <?= $default_fabric_code == "CM_FBR00070" ? 'selected' : '' ?>>CM_FBR00070 Tencel Jersey(WHITE)</option>
                        <option value="CM_FBR00071" <?= $default_fabric_code == "CM_FBR00071" ? 'selected' : '' ?>>CM_FBR00071 Tencel Jersey Reversible(BLUE)</option>
                        <option value="CM_FBR00072" <?= $default_fabric_code == "CM_FBR00072" ? 'selected' : '' ?>>CM_FBR00072 Tencel Jersey Reversible(CHARCOAL)</option>
                        <option value="CM_FBR00073" <?= $default_fabric_code == "CM_FBR00073" ? 'selected' : '' ?>>CM_FBR00073 Tencel Jersey Reversible(LATTE)</option>
                        <option value="CM_FBR00074" <?= $default_fabric_code == "CM_FBR00074" ? 'selected' : '' ?>>CM_FBR00074 Tencel Jersey Reversible(CREAM)</option>
                        <option value="CM_FBR00075" <?= $default_fabric_code == "CM_FBR00075" ? 'selected' : '' ?>>CM_FBR00075 Bamboo Jacquard NO FRESCHE</option>
                        <option value="CM_FBR00076" <?= $default_fabric_code == "CM_FBR00076" ? 'selected' : '' ?>>CM_FBR00076 Bamboo Jacquard FRESCHE</option>
                        <option value="CM_FBR00096" <?= $default_fabric_code == "CM_FBR00096" ? 'selected' : '' ?>>CM_FBR00096 Tencel Jacquard FRESCHE BLEACHED</option>
                        <option value="CM_FBR00092" <?= $default_fabric_code == "CM_FBR00092" ? 'selected' : '' ?>>CM_FBR00092 Tencel Sateen(CHARCOAL) FRESCHE</option>
                        <option value="CM_FBR00093" <?= $default_fabric_code == "CM_FBR00093" ? 'selected' : '' ?>>CM_FBR00093 Tencel Sateen(INDIGO) FRESCHE</option>
                        <option value="CM_FBR00094" <?= $default_fabric_code == "CM_FBR00094" ? 'selected' : '' ?>>CM_FBR00094 Tencel Sateen(SILVER) FRESCHE</option>
                        <option value="CM_FBR00095" <?= $default_fabric_code == "CM_FBR00095" ? 'selected' : '' ?>>CM_FBR00095 Tencel Sateen(WHITE) FRESCHE</option>
                        <option value="CM_FBR00097" <?= $default_fabric_code == "CM_FBR00097" ? 'selected' : '' ?>>CM_FBR00097 Tencel Sateen(GREEN) FRESCHE</option>
                        <option value="CM_FBR00099" <?= $default_fabric_code == "CM_FBR00099" ? 'selected' : '' ?>>CM_FBR00099 FRESCHE</option>
                        <option value="CM_FBR00100" <?= $default_fabric_code == "CM_FBR00100" ? 'selected' : '' ?>>CM_FBR00100 FRESCHE</option>
                        <option value="CM_FBR00101" <?= $default_fabric_code == "CM_FBR00101" ? 'selected' : '' ?>>CM_FBR00101 FRESCHE</option>
                    </select>
                    <button type="submit" name="set_fabric_code" class="btn btn-secondary mt-2 w-100">Set Fabric Code</button>
                </div>

                <div class="col-md-4">
                    <label for="po_number" class="form-label">PO Number</label>
                    <input type="text" name="po_number" id="po_number" class="form-control" value="<?= htmlspecialchars($default_po_number) ?>">
                    <button type="submit" name="set_po_number" class="btn btn-secondary mt-2 w-100">Set PO Number</button>
                </div>

                <div class="col-md-4">
                    <label for="stillage_number" class="form-label">Stillage Number</label>
                    <input type="text" name="stillage_number" id="stillage_number" class="form-control" value="<?= htmlspecialchars($default_stillage_number) ?>">
                    <button type="submit" name="set_stillage_number" class="btn btn-secondary mt-2 w-100">Set Stillage Number</button>
                </div>
            </div>
        </form>

        <!-- Button to open "Add Roll" modal -->
        <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addRollModal">
            Add Roll
        </button>

        <!-- Material Usage Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Work Order Number</th>
                        <th>Roll Number</th>
                        <th>Fabric Code</th>
                        <th>Meters Used</th>
                        <th>Stillage Number</th>
                        <th>PO Number</th>
                        <th>Initial Count</th>
                        <th>Final Count</th>
                        <th>Actual Count</th>
                        <th>Usage Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("
                        SELECT 
                            material_usage.*, 
                            work_orders.work_order_number 
                        FROM 
                            material_usage 
                        JOIN 
                            work_orders ON material_usage.work_order_id = work_orders.id 
                        ORDER BY 
                            material_usage.usage_date DESC
                    ");

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['work_order_number']) . "</td>
                            <td>" . htmlspecialchars($row['roll_number']) . "</td>
                            <td>" . htmlspecialchars($row['fabric_code']) . "</td>
                            <td>" . htmlspecialchars($row['meters_used']) . "</td>
                            <td>" . htmlspecialchars($row['stillage_number']) . "</td>
                            <td>" . htmlspecialchars($row['po_number']) . "</td>
                            <td>" . htmlspecialchars($row['initial_machine_count']) . "</td>
                            <td>" . htmlspecialchars($row['final_machine_count']) . "</td>
                            <td>" . htmlspecialchars($row['products_produced']) . "</td>
                            <td>" . htmlspecialchars($row['usage_date']) . "</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- "Add Roll" Modal for Material Usage Entry -->
        <div class="modal fade" id="addRollModal" tabindex="-1" aria-labelledby="addRollModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="submit_material_usage.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addRollModalLabel">Add Material Usage</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="work_order_id" value="<?= htmlspecialchars($work_order_id) ?>">
                            <div class="mb-3">
                                <label for="roll_number" class="form-label">Roll Number</label>
                                <input type="text" name="roll_number" id="roll_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="meters_used" class="form-label">Meters Used</label>
                                <input type="number" step="0.01"
                                name="meters_used" id="meters_used" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="initial_machine_count" class="form-label">Initial Machine Count</label>
                                <input type="number" name="initial_machine_count" id="initial_machine_count" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="final_machine_count" class="form-label">Final Machine Count</label>
                                <input type="number" name="final_machine_count" id="final_machine_count" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
