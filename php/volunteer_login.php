<?php
session_start();

$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$query = "SELECT * FROM registration WHERE email=$1 AND password=$2";
$result = pg_query_params($conn, $query, array($email, $password));

if (pg_num_rows($result) === 1) {
    $volunteer = pg_fetch_assoc($result);

    $_SESSION['volunteer_email'] = $volunteer['email'];
    $_SESSION['volunteer_name'] = $volunteer['name'];

    header("Location: /disaster-management-/php/volunteer_dashboard.php");
    exit;
}

echo "<script>
    alert('Invalid email or password');
    window.location.href='/disaster-management-/html/volunteerlogin.html';
</script>";
?>
