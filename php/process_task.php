<?php
session_start();

if (!isset($_SESSION['coord_email'])) {
    header("Location: /disaster-management-/html/coordinator.html");
    exit;
}

$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");
if (!$conn) {
    die("DB error: " . pg_last_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $desc = $_POST['description'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $location = $_POST['location'];
    $volunteers = explode(",", $_POST['volunteers']);
    $created_by = $_POST['created_by'];

    // Insert task
    $task_sql = "INSERT INTO tasks (title, description, priority, location, due_date, created_by, created_at)
                 VALUES ($1, $2, $3, $4, $5, $6, NOW()) RETURNING id";

    $task_result = pg_query_params($conn, $task_sql, array(
        $title, $desc, $priority, $location, $due_date, $created_by
    ));

    if (!$task_result) {
        die("Failed to create task: " . pg_last_error());
    }

    $task = pg_fetch_assoc($task_result);
    $task_id = $task['id'];
    $assign_sql = "INSERT INTO task_assignments (task_id, volunteer_id, status) 
               VALUES ($1, $2, 'pending')";

    foreach ($volunteers as $email) {
        pg_query_params($conn, $assign_sql, array($task_id, $email));
    }


    header("Location: /disaster-management-/html/task_success.html");
    exit;
}

echo "Invalid Request.";
?>
