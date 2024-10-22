<?php
session_start();
include('db.php'); // Ensure this connects to your database

// Handle registration
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<p style='color: red;'>Passwords do not match!</p>";
    } else {
        // Prepare a statement to check if username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p style='color: red;'>Username already exists!</p>";
        } else {
            // Hash the password and insert the new user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                // Redirect to login page after successful registration
                header("Location: login.php?msg=registration_success");
                exit;
            } else {
                echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <h2 class="h4">Register</h2>
        <form method="post" action="register.php">
            <div class="mb-3">
                <label for="reg_username" class="form-label">Username</label>
                <input type="text" name="reg_username" id="reg_username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="reg_password" class="form-label">Password</label>
                <input type="password" name="reg_password" id="reg_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-success">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
