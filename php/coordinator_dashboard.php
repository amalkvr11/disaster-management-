<?php
session_start();

// Prevent unauthorized access
if (!isset($_SESSION['coord_email'])) {
    header("Location: /disaster-management-/html/coordinator.html");
    exit;
}

$coord_email = $_SESSION['coord_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Coordinator Dashboard - K-DISASTERA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            margin: 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(102,126,234,0.35);
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            top: -40%;
            left: -40%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .dashboard-header h2 {
            font-weight: 700;
            font-size: 2.2rem;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .dashboard-header p {
            opacity: 0.9;
            margin-top: 5px;
            font-size: 1rem;
            position: relative;
            z-index: 2;
        }

        /* Cards */
        .card-box {
            background: white;
            border-radius: 18px;
            padding: 30px 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(102,126,234,0.15);
            transition: all 0.3s ease;
            text-align: center;
        }

        .card-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(102,126,234,0.2);
        }

        .card-box i {
            font-size: 2.8rem;
            margin-bottom: 12px;
            color: #764ba2;
        }

        .card-box h4 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .logout-btn {
            margin-top: 25px;
            padding: 12px 30px;
            border-radius: 50px;
            background: #ff5c5c;
            border: none;
            color: white;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            background: #e60000;
            box-shadow: 0 8px 25px rgba(255,0,0,0.3);
        }
    </style>
</head>

<body>

<div class="main-container">

    <!-- HEADER -->
    <header class="dashboard-header">
        <h2><i class="bi bi-people-fill me-2"></i>Coordinator Dashboard</h2>
        <p>Monitor volunteers, manage field operations, and track supply distribution</p>
    </header>

    <!-- DASHBOARD CARDS -->
<!-- DASHBOARD CARDS -->
<div class="row g-4 mb-4">

    <div class="col-md-4">
        <a href="volunteers.php" style="text-decoration: none; color: inherit;">
        <div class="card-box">
            <i class="bi bi-people-fill"></i></a>
            <h4>Volunteers</h4>
            <p>View and assign volunteer tasks</p>
        </div>
    </div>

    <div class="col-md-4">
        <a href="supplies.php" style="text-decoration: none; color: inherit;">
        <div class="card-box">
            <i class="bi bi-truck"></i></a>
            <h4>Supplies & Donations</h4>
            <p>Track relief materials & logistics</p>
        </div>
    </div>

    <div class="col-md-4">
        <a href="create_task.php" style="text-decoration: none; color: inherit;">
        <div class="card-box">
            <i class="bi bi-plus-circle-fill"></i></a>
            <h4>Create Task</h4>
            <p>Assign tasks to volunteers</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <i class="bi bi-geo-alt-fill"></i>
            <h4>Field Locations</h4>
            <p>View affected zones & reports</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <i class="bi bi-megaphone-fill"></i>
            <h4>Emergency Alerts</h4>
            <p>Issue quick notifications to teams</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <i class="bi bi-clipboard2-check"></i>
            <h4>Assigned Tasks</h4>
            <p>Check and update task status</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <h4>Reports</h4>
            <p>View daily and weekly field updates</p>
        </div>
    </div>

</div>

    <!-- LOGOUT BUTTON -->
    <form action="/disaster-management-/php/logout.php" method="POST">
        <button class="logout-btn"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
    </form>

</div>

</body>
</html>
