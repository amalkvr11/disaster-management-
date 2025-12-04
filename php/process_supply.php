<?php
$db_host = "localhost";
$db_port = "5432";
$db_name = "disastermanagement";
$db_user = "postgres";
$db_pass = "venda";

$conn = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['donorName'] ?? '');
    $email     = trim($_POST['donorEmail'] ?? '');
    $phone     = trim($_POST['donorPhone'] ?? '');
    $type      = trim($_POST['donationType'] ?? '');
    $details   = trim($_POST['donationDetails'] ?? '');
    $pickup    = trim($_POST['pickupAddress'] ?? '');
    $errors = [];

    if ($name === "") $errors[] = "Full name is required.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if ($phone === "" || !preg_match('/^(\+91[-\s]?)?[0]?(91)?[6789]\d{9}$/', $phone)) 
        $errors[] = "Valid phone number required.";
    if ($type === "") $errors[] = "Donation type is required.";
    if (!empty($errors)) {
        echo "<h3>⚠ Please fix the following errors:</h3><ul>";
        foreach ($errors as $err) {
            echo "<li>" . htmlspecialchars($err) . "</li>";
        }
        echo "</ul><p><a href='../html/supply.html'>Go Back</a></p>";
        exit;
    }
    $sql = "INSERT INTO supplies 
            (fname, email, phone, donation_type, additional, pick_address)
            VALUES ($1, $2, $3, $4, $5, $6)";

    $params = [$name, $email, $phone, $type, $details, $pickup];

    $result = pg_query_params($conn, $sql, $params);

    if ($result) {
        header("Location: success_supply.php");
        exit;
    } else {
        echo "<h3>❌ Database error:</h3>";
        echo pg_last_error($conn);
    }

} else {
    echo "Invalid request.";
}

pg_close($conn);
?>
