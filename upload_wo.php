<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload Work Orders (CSV)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1 class="h3 mb-4">Bulk Upload Work Orders</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="alert alert-success">Work Orders uploaded successfully!</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
            <div class="alert alert-danger">Error uploading Work Orders. Please check the file format.</div>
        <?php endif; ?>

        <form action="check_duplicates.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Choose CSV File (.csv)</label>
                <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Check for Duplicates</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
