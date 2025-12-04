<?php
session_start();
$db_host = "localhost";
$db_port = "5432";
$db_name = "disastermanagement";
$db_user = "postgres";
$db_pass = "venda";

$conn = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

$email = trim($_POST['said'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    die("<h3>❌ Email or Password is missing.</h3><a href='/disaster-management-/html/super_admin.html'>Back</a>");
}

$sql = "SELECT email, password, type FROM administration WHERE email=$1 AND type='admin' LIMIT 1";
$result = pg_query_params($conn, $sql, [$email]);

if (!$result || pg_num_rows($result) === 0) {
    echo "<h3>❌ Admin account not found.</h3>";
    echo "<a href='/disaster-management-/html/super_admin.html'>Try Again</a>";
    exit;
}

$user = pg_fetch_assoc($result);
if ($password !== $user['password']) {
    echo "<h3>❌ Incorrect password.</h3>";
    echo "<a href='../super_admin.html'>Try Again</a>";
    exit;
}
$_SESSION['admin_email'] = $user['email'];
$_SESSION['admin_role'] = "admin";

header("Location: /disaster-management-/php/admin.php");
exit;
?>
