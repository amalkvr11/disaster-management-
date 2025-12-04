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
    $name         = trim($_POST['name'] ?? '');
    $district     = trim($_POST['district'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $area         = trim($_POST['area'] ?? '');
    $address      = trim($_POST['address'] ?? '');

    $errors = [];
    if ($name === '') {
        $errors[] = "Full Name is required.";
    }

    if ($district === '') {
        $errors[] = "District is required.";
    } else {
        $allowed_districts = [
            "kozhikode", "trivandrum", "malappuram", "alappuzha",
            "kannur", "kottayam", "thrissur", "kasargod",
            "wayanad", "idukki", "palakkad", "ernakulam",
            "pathanamthitta", "kollam"
        ];
        if (!in_array($district, $allowed_districts, true)) {
            $errors[] = "Invalid district selected.";
        }
    }

    if ($phone === '') {
        $errors[] = "Phone number is required.";
    } else {
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = "Phone number must be a 10-digit number.";
        }
    }

    if ($area === '') {
        $errors[] = "Area of volunteering is required.";
    }

    if ($address === '') {
        $errors[] = "Complete address is required.";
    }
    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

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
    $sql = "INSERT INTO registration 
                (fname, district, phone, email, organization, areavolunteer, address) 
            VALUES 
                ($1, $2, $3, $4, $5, $6, $7)";

    $params = [
        $name,
        $district,
        $phone,
        $email === '' ? null : $email,
        $organization === '' ? null : $organization,
        $area,
        $address
    ];

    $result = pg_query_params($conn, $sql, $params);

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
