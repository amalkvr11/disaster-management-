<?php
session_start();

// Prevent unauthorized access
if (!isset($_SESSION['admin_email'])) {
    header("Location: /disaster-management-/html/super_admin.html");
    exit;
}

// Database connection
$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");
if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

// Get form data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$role = trim($_POST['role']);

// Basic validation
if ($name === "" || $email === "" || $password === "" || $role === "") {
    die("All fields are required.");
}

// Check if email exists
$check_query = "SELECT email FROM administration WHERE email = $1";
$check_result = pg_query_params($conn, $check_query, array($email));

if (pg_num_rows($check_result) > 0) {
    echo "<script>
        alert('Email already exists!');
        window.location.href = '/disaster-management-/php/admin.php';
    </script>";
    exit;
}

// Insert new admin
$insert_query = "
    INSERT INTO administration (name, email, password, type)
    VALUES ($1, $2, $3, $4)
";

$insert_result = pg_query_params($conn, $insert_query, array($name, $email, $password, $role));

if ($insert_result) {
    echo "<script>
        alert('Admin added successfully!');
        window.location.href = 'admin.php';
    </script>";
} else {
    echo "Error inserting admin: " . pg_last_error($conn);
}

pg_close($conn);
?>
