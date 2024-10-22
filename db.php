<?php

// Database connection details
$servername = "localhost";  // Your database server, usually localhost if you're working locally
$username = "root";         // Your MySQL username (default for local XAMPP/WAMP setups is 'root')
$password = "";             // Your MySQL password (default for local XAMPP/WAMP setups is empty)
$dbname = "production_db";  // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
