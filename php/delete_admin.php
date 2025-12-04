<?php
session_start();

if (!isset($_SESSION['admin_email'])) {
    header("Location: /disaster-management-/html/super_admin.html");
    exit;
}
if (!isset($_GET['email'])) {
    die("Invalid request.");
}

$email = $_GET['email'];

$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");
if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

// Delete admin using email
$query = "DELETE FROM administration WHERE email = $1";
$result = pg_query_params($conn, $query, array($email));

if ($result) {
    echo "<script>
            alert('Admin deleted successfully!');
            window.location.href = 'admin.php';
          </script>";
} else {
    echo "<script>
            alert('Failed to delete admin.');
            window.location.href = 'admin.php';
          </script>";
}

pg_close($conn);
?>
