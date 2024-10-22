<?php
session_start();
$duplicate_work_orders = $_SESSION['duplicate_work_orders'] ?? [];
$non_duplicate_work_orders = $_SESSION['non_duplicate_work_orders'] ?? [];

if (empty($duplicate_work_orders)) {
    header('Location: upload_wo.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handle Duplicates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="h3 mb-4">Duplicate Work Orders Found</h1>

        <p>We found duplicate work orders in the uploaded file. How would you like to handle them?</p>

        <form action="process_upload.php" method="POST">
            <div class="mb-3">
                <label for="duplicate_action" class="form-label">How to handle duplicates?</label>
                <select name="duplicate_action" id="duplicate_action" class="form-select" required>
                    <option value="skip">Skip Duplicates</option>
                    <option value="update">Update Existing Records</option>
                    <option value="ignore">Ignore Duplicates</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Process Work Orders</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
