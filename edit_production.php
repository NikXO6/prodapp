<?php
include('db.php');

// Get the production data id from the URL
$production_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch production data for the given id
$sql = "SELECT * FROM daily_production WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $production_id);
$stmt->execute();
$result = $stmt->get_result();
$production = $result->fetch_assoc();

// If the form is submitted, update the production data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = $_POST['quantity'];
    $line = $_POST['line'];
    $staff_count = $_POST['staff_count'];
    $units_per_staff = $_POST['units_per_staff'];
    $memo = $_POST['memo'];

    // Update the production entry in the database
    $update_sql = "UPDATE daily_production SET quantity = ?, line = ?, staff_count = ?, units_per_staff = ?, memo = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('isiisi', $quantity, $line, $staff_count, $units_per_staff, $memo, $production_id);
    if ($update_stmt->execute()) {
        // Redirect back to the production dashboard after successful update
        header('Location: index.php');
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Production Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container mt-4">
        <h2 class="h4">Edit Production Data</h2>

        <form action="" method="post">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="<?= htmlspecialchars($production['quantity']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="line" class="form-label">Line</label>
                <input type="text" name="line" id="line" class="form-control" value="<?= htmlspecialchars($production['line']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="staff_count" class="form-label">Staff Count</label>
                <input type="number" name="staff_count" id="staff_count" class="form-control" value="<?= htmlspecialchars($production['staff_count']) ?>" required>
            </div>
            <!-- <div class="mb-3">
                <label for="units_per_staff" class="form-label">Units per Staff</label>
                <input type="number" name="units_per_staff" id="units_per_staff" class="form-control" value="<?= htmlspecialchars($production['units_per_staff']) ?>" required>
            </div> -->
            <div class="mb-3">
                <label for="memo" class="form-label">Memo</label>
                <textarea name="memo" id="memo" class="form-control"><?= htmlspecialchars($production['memo']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
