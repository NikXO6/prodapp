<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if a file was uploaded
    if (isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {
        $file = $_FILES['file']['tmp_name'];

        // Open the file in read mode
        if (($handle = fopen($file, "r")) !== FALSE) {
            $duplicate_work_orders = [];
            $non_duplicate_work_orders = [];

            // Skip the header row (if the first row is headers)
            fgetcsv($handle);

            // Loop through the file and check for duplicates in the database
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $work_order_number = $data[0];

                // Check if the work_order_number already exists in the database
                $check_sql = "SELECT id FROM work_orders WHERE work_order_number = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param('s', $work_order_number);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Duplicate found
                    $duplicate_work_orders[] = $data;
                } else {
                    // No duplicate
                    $non_duplicate_work_orders[] = $data;
                }
            }

            fclose($handle);  // Close the file after checking

            // If duplicates found, present options to the user
            if (!empty($duplicate_work_orders)) {
                session_start();
                $_SESSION['duplicate_work_orders'] = $duplicate_work_orders;
                $_SESSION['non_duplicate_work_orders'] = $non_duplicate_work_orders;
                $_SESSION['file'] = $_FILES['file'];  // Save the file info in session to reuse

                // Redirect to the page that asks for the user's choice
                header('Location: handle_duplicates.php');
                exit;
            } else {
                // If no duplicates found, insert directly into the database
                session_start();
                $_SESSION['non_duplicate_work_orders'] = $non_duplicate_work_orders;
                header('Location: process_upload.php?msg=insert_direct');
                exit;
            }

        } else {
            // Handle file open failure
            header('Location: upload_wo.php?msg=error');
            exit;
        }

    } else {
        // If no file was uploaded
        header('Location: upload_wo.php?msg=error');
        exit;
    }
}
?>
