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
    $fname         = $_POST['name'];
    $district      = $_POST['district'];
    $phone         = $_POST['phone'];
    $email         = $_POST['email'];
    $organization  = $_POST['organization'];
    $area          = $_POST['area'];
    $address       = $_POST['address'];
    $password      = $_POST['password'];

    $errors = [];

    // Required field checks
    if ($fname === '') $errors[] = "Full Name is required.";
    if ($district === '') $errors[] = "District is required.";
    if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Phone number must be 10 digits.";
    if ($email === '') $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if ($area === '') $errors[] = "Area of volunteering is required.";
    if ($address === '') $errors[] = "Complete address is required.";

    // Validate allowed district
    $allowed_districts = [
        "kozhikode", "trivandrum", "malappuram", "alappuzha",
        "kannur", "kottayam", "thrissur", "kasargod",
        "wayanad", "idukki", "palakkad", "ernakulam",
        "pathanamthitta", "kollam"
    ];
    if (!in_array($district, $allowed_districts, true)) {
        $errors[] = "Invalid district selected.";
    }

    // ❗ Check if email already exists
    $check_sql = "SELECT email FROM registration WHERE email = $1 LIMIT 1";
    $check_result = pg_query_params($conn, $check_sql, array($email));

    if (pg_num_rows($check_result) > 0) {
        $errors[] = "This email is already registered. Please use another email.";
    }

    // Show errors if any
    if (!empty($errors)) {
        echo "<h2>There were some problems:</h2>";
        echo "<ul>";
        foreach ($errors as $err) {
            echo "<li>" . htmlspecialchars($err) . "</li>";
        }
        echo "</ul>";
        echo '<p><a href="newVolunteer.html">Go back to the form</a></p>';
        exit;
    }

    // Insert new volunteer
    $sql = "INSERT INTO registration 
                (fname, district, phone, email, organization, areavolunteer, address, password) 
            VALUES 
                ($1, $2, $3, $4, $5, $6, $7, $8)";

    $result = pg_query_params($conn, $sql, array(
        $fname,
        $district,
        $phone,
        $email,
        $organization,
        $area,
        $address,
        $password
    ));

    if ($result) {
        header("Location: success.php");
        exit;
    } else {
        echo "<h2>Something went wrong while saving your data.</h2>";
        echo "<p>" . htmlspecialchars(pg_last_error($conn)) . "</p>";
    }

} else {
    echo "<h2>Invalid request method.</h2>";
    echo '<p><a href="newVolunteer.html">Go to registration form</a></p>';
}

pg_close($conn);
?>
