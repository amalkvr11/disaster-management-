<?php
session_start();

if (!isset($_SESSION['admin_email'])) {
    die("Unauthorized");
}

$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");

if (!$conn) {
    die("DB connection failed: " . pg_last_error());
}

$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role'];
$password = $_POST['password'];

// If password empty → Do not update password
if (empty($password)) {
    $sql = "UPDATE administration SET name=$1, type=$2 WHERE email=$3";
    $update = pg_query_params($conn, $sql, [$name, $role, $email]);
} else {
    $sql = "UPDATE administration SET name=$1, type=$2, password=$3 WHERE email=$4";
    $update = pg_query_params($conn, $sql, [$name, $role, $password, $email]);
}

if (!$update) {
    die("Update failed: " . pg_last_error());
}

header("Location: /disaster-management-/php/admin.php");
exit;
