<?php
session_start();

if (!isset($_SESSION['admin_email'])) {
    header("Location: /disaster-management-/html/super_admin.html");
    exit;
}

$coord_email = $_SESSION['admin_email'];

$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");
if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

$query = "SELECT name, email, type AS role FROM administration";
$result = pg_query($conn, $query);

// For stats
$total_admins = pg_num_rows($result);

$super_admins = 0;
$coordinators = 0;
$data_managers = 0;

$admins = [];

while ($row = pg_fetch_assoc($result)) {
    $admins[] = $row;

    if ($row['role'] == 'admin' || $row['role'] == 'Super Admin') $super_admins++;
    if ($row['role'] == 'coordinator' || $row['role'] == 'Coordinator') $coordinators++;
    if ($row['role'] == 'data_manager' || $row['role'] == 'Data Manager') $data_managers++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Management - Disaster Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            margin: 0;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .admin-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .admin-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 2.2rem;
            position: relative;
            z-index: 2;
        }
        
        .admin-header .btn {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .admin-header .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(102,126,234,0.1);
        }
        
        .table-container {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(102,126,234,0.15);
        }
        
        .table thead {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            font-weight: 600;
        }
        
        .table th {
            border: none;
            padding: 20px 15px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 20px 15px;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }
        
        .table-hover tbody tr:hover {
            background: linear-gradient(90deg, rgba(102,126,234,0.08), rgba(118,75,162,0.08));
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        
        .action-buttons .btn {
            border-radius: 12px;
            padding: 8px 12px;
            margin: 0 2px;
            border: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-2px);
        }
        
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 20px 20px 0 0;
            padding: 25px 30px;
            border: none;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        .modal-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 25px;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 1rem;
            height: 55px;
            transition: all 0.3s ease;
            background: #fafbfc;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118,75,162,0.15);
            background: white;
            transform: translateY(-2px);
        }
        
        .form-floating > label {
            padding-left: 20px;
            font-weight: 500;
            color: #666;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.6);
        }
        
        .btn-secondary {
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 500;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .admin-header h2 {
                font-size: 1.8rem;
            }
            .table-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Header -->
    <header class="admin-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2><i class="bi bi-shield-lock-fill me-2"></i>Admin Management Dashboard</h2>
            <p class="mb-0 opacity-90" style="font-size: 1rem;">Manage administrators and coordinators for disaster response</p>
        </div>
        <button id="btnAdd" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#adminModal">
            <i class="bi bi-plus-lg me-2"></i>Add New Admin
        </button>
        <a href="/disaster-management-/php/logout.php" class="btn btn-danger">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </header>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card text-center">
                <i class="bi bi-people-fill display-4 text-primary mb-3"></i>
                <h3 id="totalAdmins"><?= $total_admins ?></h3>
                <p class="text-muted mb-0">Total Admins</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center">
                <i class="bi bi-star-fill display-4 text-warning mb-3"></i>
                <h3><?= $super_admins ?></h3>
                <p class="text-muted mb-0">Super Admins</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center">
                <i class="bi bi-person-check-fill display-4 text-success mb-3"></i>
                <h3><?= $coordinators ?></h3>
                <p class="text-muted mb-0">Coordinators</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center">
                <i class="bi bi-database-fill display-4 text-info mb-3"></i>
                <h3><?= $data_managers ?></h3>
                <p class="text-muted mb-0">Data Managers</p>
            </div>
        </div>
    </div>

    <!-- Admin Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th class="text-center">Actions</th>
</tr>
</thead>

<tbody id="adminTableBody">
<?php $i = 1; foreach ($admins as $admin): ?>
    <tr>
        <th scope="row" class="fw-bold"><?= $i++ ?></th>

        <td>
            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle fs-4 text-primary me-2"></i>
                <div>
                    <div class="fw-semibold"><?= htmlspecialchars($admin['name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($admin['email']) ?></small>
                </div>
            </div>
        </td>

        <td><span class="badge bg-light text-dark px-3 py-2"><?= htmlspecialchars($admin['email']) ?></span></td>

        <td>
            <?php
                $role = $admin['role'];
                $badge = "bg-info";

                if ($role == 'Admin' || $role == 'admin') $badge = "bg-danger";
                if ($role == 'Coordinator' || $role == 'coordinator') $badge = "bg-success";
                if ($role == 'Data Manager' || $role == 'data_manager') $badge = "bg-info";
            ?>
            <span class="badge fs-6 px-3 py-2 fw-semibold <?= $badge ?>">
                <?= htmlspecialchars($role) ?>
            </span>
        </td>

        <td class="text-center action-buttons">
            <button class="btn btn-outline-primary btn-sm me-2 btn-edit"
        data-name="<?= htmlspecialchars($admin['name']) ?>"
        data-email="<?= htmlspecialchars($admin['email']) ?>"
        data-role="<?= htmlspecialchars($admin['role']) ?>">
    <i class="bi bi-pencil-square"></i>
</button>
            <a href="/disaster-management-/php/delete_admin.php?email=<?= urlencode($admin['email']) ?>"
                onclick="return confirm('Are you sure you want to delete this admin?');"
                    class="btn btn-outline-danger btn-sm">
               <i class="bi bi-trash"></i>
            </a>

        </td>
    </tr>
<?php endforeach; ?>
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- Admin Add/Edit Modal -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="adminForm" class="modal-content" method="POST" action="add_admin.php">
      <div class="modal-header">
        <h5 class="modal-title" id="adminModalLabel">
            <i class="bi bi-plus-circle-fill me-2"></i>Add New Admin
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="adminId" />
          <div class="row">
              <div class="col-md-6">
                  <div class="form-floating">
                      <input type="text" class="form-control" name="name" id="adminName" placeholder="Full Name" required />
                      <label for="adminName">Full Name <span class="text-danger">*</span></label>
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-floating">
                      <input type="email" class="form-control" name="email" id="adminEmail" placeholder="Email" required />
                      <label for="adminEmail">Email Address <span class="text-danger">*</span></label>
                  </div>
              </div>
          </div>
          
          <div class="row">
              <div class="col-md-6">
                  <div class="form-floating">
                      <select class="form-select" name="role" id="adminRole" required>
                          <option value="" disabled selected>Select role</option>
                          <option value="admin">🔐 Super Admin</option>
                          <option value="Coordinator">📋 Coordinator</option>
                          <option value="Data Manager">📊 Data Manager</option>
                      </select>
                      <label for="adminRole">Role <span class="text-danger">*</span></label>
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-floating">
                      <input type="password" class="form-control" name="password" id="adminPassword" placeholder="Password" minlength="6" required />
                      <label for="adminPassword">Password <span class="text-danger">*</span></label>
                  </div>
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-2"></i>Cancel
        </button>
        <button id="saveAdminBtn" type="submit" class="btn btn-primary px-5">
            <i class="bi bi-check-lg me-2"></i>Save Admin
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

const adminTableBody = document.getElementById('adminTableBody');
const adminModal = new bootstrap.Modal(document.getElementById('adminModal'));
const adminForm = document.getElementById('adminForm');
const adminModalLabel = document.getElementById('adminModalLabel');
const totalAdminsEl = document.getElementById('totalAdmins');

const adminIdInput = document.getElementById('adminId');
const adminNameInput = document.getElementById('adminName');
const adminEmailInput = document.getElementById('adminEmail');
const adminRoleInput = document.getElementById('adminRole');
const adminPasswordInput = document.getElementById('adminPassword');

// Enable Edit Button
document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', function () {
        const name = this.getAttribute('data-name');
        const email = this.getAttribute('data-email');
        const role = this.getAttribute('data-role');

        // Fill modal values
        adminNameInput.value = name;
        adminEmailInput.value = email;
        adminRoleInput.value = role;
        adminPasswordInput.removeAttribute("required"); // Password not required for update
        adminPasswordInput.placeholder = "Leave empty to keep current password";

        adminForm.action = "/disaster-management-/php/update_admin.php";

        // Change modal title & button text
        adminModalLabel.innerHTML = `<i class="bi bi-pencil-fill me-2"></i>Edit Admin`;
        saveAdminBtn.innerHTML = `<i class="bi bi-check-lg me-2"></i>Update Admin`;

        adminModal.show();
    });
});

</script>

</body>
</html>
