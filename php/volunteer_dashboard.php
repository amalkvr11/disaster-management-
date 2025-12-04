<?php
session_start();

// If user not logged in, redirect to login page
if (!isset($_SESSION['volunteer_email'])) {
    header("Location: /disaster-management-/html/volunteerlogin.html");
    exit;
}

$email = $_SESSION['volunteer_email'];

// DB Connection
$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");

if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

// Fetch volunteer data
$query = "SELECT fname, district, phone, email, organization, areavolunteer, address 
          FROM registration WHERE email = $1 LIMIT 1";
$result = pg_query_params($conn, $query, array($email));
$volunteer = pg_fetch_assoc($result);
// Fetch assigned tasks for this volunteer
$task_query = "
    SELECT 
        t.title,
        t.location,
        t.due_date,
        t.priority,
        t.created_at,
        t.description,
        COALESCE(ta.status, 'pending') AS status
    FROM tasks t
    INNER JOIN task_assignments ta ON t.id = ta.task_id
    INNER JOIN registration r ON r.email = ta.volunteer_id
    WHERE r.email = $1
    ORDER BY t.created_at DESC
";

$task_result = pg_query_params($conn, $task_query, array($email));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Volunteer Dashboard | VolunteerHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root {
    --primary: #4361ee;
    --primary-dark: #3a56d4;
    --secondary: #7209b7;
    --accent: #f72585;
    --light: #f8f9fa;
    --dark: #212529;
    --gray: #6c757d;
    --light-gray: #e9ecef;
    --sidebar-bg: #1e293b;
    --card-bg: #ffffff;
    --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    --shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.15);
    --radius: 16px;
    --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #f5f7fa;
    margin: 0;
    overflow-x: hidden;
    color: var(--dark);
}

/* Sidebar */
.sidebar {
    height: 100vh;
    width: 280px;
    background: var(--sidebar-bg);
    position: fixed;
    top: 0;
    left: 0;
    padding: 30px 20px;
    color: white;
    transition: var(--transition);
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header .logo-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.sidebar-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: white;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 15px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    padding: 14px 18px;
    margin: 8px 0;
    border-radius: 12px;
    text-decoration: none;
    transition: var(--transition);
    cursor: pointer;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(5px);
}

.nav-item.active {
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    color: white;
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.nav-item i {
    width: 24px;
    text-align: center;
    font-size: 1.2rem;
}

.nav-item.logout {
    color: #ff6b6b;
    margin-top: auto;
    position: absolute;
    bottom: 30px;
    width: calc(100% - 40px);
}

.nav-item.logout:hover {
    background: rgba(255, 107, 107, 0.1);
}

/* Main Content */
.main-content {
    margin-left: 280px;
    padding: 40px;
    transition: var(--transition);
    min-height: 100vh;
}

/* Top Bar */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--light-gray);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
}

.user-details h4 {
    font-weight: 700;
    margin: 0;
    color: var(--dark);
}

.user-details p {
    color: var(--gray);
    margin: 0;
    font-size: 0.9rem;
}

.notification-bell {
    position: relative;
    color: var(--gray);
    font-size: 1.3rem;
    cursor: pointer;
    transition: var(--transition);
}

.notification-bell:hover {
    color: var(--primary);
}

.notification-bell::after {
    content: '3';
    position: absolute;
    top: -8px;
    right: -8px;
    width: 20px;
    height: 20px;
    background: var(--accent);
    color: white;
    border-radius: 50%;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Welcome Card */
.welcome-card {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    padding: 40px;
    border-radius: var(--radius);
    color: white;
    margin-bottom: 40px;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
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

.welcome-card h2 {
    font-weight: 800;
    font-size: 2.5rem;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.welcome-card p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    position: relative;
    z-index: 2;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid var(--light-gray);
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
}

.stat-card h3 {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--dark);
    margin-bottom: 10px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 5px;
}

.stat-desc {
    color: var(--gray);
    font-size: 0.9rem;
}

/* Content Cards */
.content-card {
    background: var(--card-bg);
    padding: 35px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    border: 1px solid var(--light-gray);
}

.content-card h4 {
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.content-card h4 i {
    color: var(--primary);
}

/* Profile Info */
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.info-item {
    margin-bottom: 20px;
}

.info-label {
    font-weight: 600;
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.1rem;
    color: var(--dark);
    font-weight: 500;
    padding: 12px 20px;
    background: var(--light);
    border-radius: 10px;
    border-left: 4px solid var(--primary);
}

/* Activity Table */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--light-gray);
}

.table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table thead {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.table th {
    border: none;
    padding: 20px 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.table tbody tr {
    transition: var(--transition);
}

.table tbody tr:hover {
    background: rgba(67, 97, 238, 0.05);
}

.table td {
    padding: 18px 15px;
    vertical-align: middle;
    border-color: var(--light-gray);
}

.badge {
    padding: 8px 16px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
}

.badge-success {
    background: rgba(40, 199, 111, 0.1);
    color: #28c76f;
}

.badge-warning {
    background: rgba(255, 171, 0, 0.1);
    color: #ffab00;
}

/* Responsive Design */
@media (max-width: 992px) {
    .sidebar {
        width: 80px;
        padding: 20px 10px;
    }
    
    .sidebar-header h2,
    .nav-item span {
        display: none;
    }
    
    .sidebar-header {
        justify-content: center;
        padding-bottom: 15px;
    }
    
    .nav-item {
        justify-content: center;
        padding: 14px;
    }
    
    .nav-item.logout {
        width: calc(100% - 20px);
        justify-content: center;
    }
    
    .main-content {
        margin-left: 80px;
        padding: 25px;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 20px;
    }
    
    .welcome-card {
        padding: 30px 25px;
    }
    
    .welcome-card h2 {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .top-bar {
        flex-direction: column;
        gap: 20px;
        align-items: flex-start;
    }
}
.sidebar-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header .logo-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.header-text {
    flex: 1;
}

.sidebar-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 5px 0;
    color: white;
    line-height: 1.2;
}

.brand-subtitle {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
    font-weight: 400;
    letter-spacing: 0.5px;
}

/* For mobile view when sidebar is collapsed */
@media (max-width: 992px) {
    .sidebar-header .header-text,
    .brand-subtitle {
        display: none;
    }
    
    .sidebar-header {
        justify-content: center;
        padding-bottom: 15px;
    }
}
/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: var(--primary);
    color: white;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 10px;
    font-size: 1.3rem;
    cursor: pointer;
    box-shadow: var(--shadow);
}

@media (max-width: 576px) {
    .mobile-menu-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-card,
.stat-card,
.content-card {
    animation: fadeIn 0.6s ease-out forwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.content-card:nth-child(1) { animation-delay: 0.4s; }
.content-card:nth-child(2) { animation-delay: 0.5s; }
</style>
</head>

<body>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">
            <i class="fas fa-hands-helping"></i>
        </div>
        <div class="header-text">
            <h2>VolunteerHub</h2>
            <p class="brand-subtitle">By K Disastera</p>
        </div>
    </div>

    <a href="volunteer_dashboard.php" class="nav-item active">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-user"></i>
        <span>My Profile</span>
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-history"></i>
        <span>Activity Log</span>
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-tasks"></i>
        <span>Assigned Tasks</span>
    </a>
    <a href="#" class="nav-item">
        <i class="fas fa-info-circle"></i>
        <span>Help & Support</span>
    </a>
    <a href="/disaster-management-/php/logout.php" class="nav-item logout">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>
<!-- Main Content -->
<div class="main-content" id="mainContent">
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr(htmlspecialchars($volunteer['fname']), 0, 1)) ?>
            </div>
            <div class="user-details">
                <h4><?= htmlspecialchars($volunteer['fname']) ?></h4>
                <p>Volunteer • <?= htmlspecialchars($volunteer['district']) ?> District</p>
            </div>
        </div>
        
        <div class="notification-bell" id="notificationBell">
            <i class="fas fa-bell"></i>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="welcome-card">
        <h2>Welcome back, <?= htmlspecialchars($volunteer['fname']) ?>! 👋</h2>
        <p>You're making a difference in <strong><?= htmlspecialchars($volunteer['areavolunteer']) ?></strong>. Keep up the great work!</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="stat-value">Active</div>
            <h3>Account Status</h3>
            <p class="stat-desc">Verified Volunteer</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-location-dot"></i>
            </div>
            <div class="stat-value"><?= htmlspecialchars($volunteer['district']) ?></div>
            <h3>Operating District</h3>
            <p class="stat-desc">Primary Location</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-value"><?= htmlspecialchars($volunteer['areavolunteer']) ?></div>
            <h3>Volunteering Area</h3>
            <p class="stat-desc">Specialization</p>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="content-card">
        <h4><i class="fas fa-user-circle"></i> Personal Information</h4>
        
        <div class="profile-grid">
            <div class="info-item">
                <div class="info-label">Full Name</div>
                <div class="info-value"><?= htmlspecialchars($volunteer['fname']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Email Address</div>
                <div class="info-value"><?= htmlspecialchars($volunteer['email']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Phone Number</div>
                <div class="info-value"><?= htmlspecialchars($volunteer['phone']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Organization</div>
                <div class="info-value"><?= htmlspecialchars($volunteer['organization'] ?: 'Independent Volunteer') ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">District</div>
                <div class="info-value"><?= htmlspecialchars($volunteer['district']) ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Address</div>
                <div class="info-value"><?= htmlspecialchars($volunteer['address']) ?></div>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="content-card">
        <h4><i class="fas fa-list-check"></i> Recent Activity</h4>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Last Login</div>
                    <div class="info-value"><?= date("d M Y, h:i A") ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Total Hours</div>
                    <div class="info-value">48 hours</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Completed Tasks</div>
                    <div class="info-value">5 tasks</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Activity</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
<?php if (pg_num_rows($task_result) > 0): ?>
    <?php while ($task = pg_fetch_assoc($task_result)): ?>
        <tr>
            <td><?= date("d M Y", strtotime($task['created_at'])) ?></td>

            <td>
                <strong><?= htmlspecialchars($task['title']) ?></strong><br>
                <small><?= htmlspecialchars($task['description']) ?></small>
            </td>

            <td><?= htmlspecialchars($task['location']) ?></td>

            <td>
                <?php
                    $status = strtolower($task['status']);
                    $badgeClass = $status === 'completed' ? "badge-success" : "badge-warning";
                ?>
                <span class="badge <?= $badgeClass ?>">
                    <?= ucfirst($status) ?>
                </span>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="4" class="text-center text-muted">
            No activity recorded yet.
        </td>
    </tr>
<?php endif; ?>
</tbody>

            </table>
        </div>
    </div>

</div>

<script>
// Mobile menu toggle
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

mobileMenuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    mobileMenuToggle.innerHTML = sidebar.classList.contains('active') 
        ? '<i class="fas fa-times"></i>' 
        : '<i class="fas fa-bars"></i>';
});

// Notification bell
const notificationBell = document.getElementById('notificationBell');
notificationBell.addEventListener('click', () => {
    alert('You have 3 new notifications:\n\n1. New task assigned: Food Distribution\n2. Meeting scheduled for tomorrow\n3. Profile update required');
});

// Update active nav item
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');
        
        // Close sidebar on mobile after selection
        if (window.innerWidth <= 576) {
            sidebar.classList.remove('active');
            mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
});

// Auto-update last login time
function updateLastLoginTime() {
    const now = new Date();
    const options = { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true 
    };
    const formattedTime = now.toLocaleDateString('en-US', options);
    
    // Update all elements with last login info
    document.querySelectorAll('.info-value').forEach(el => {
        if (el.textContent.includes('Last Login')) {
            const parent = el.closest('.info-item');
            if (parent) {
                const valueDiv = parent.querySelector('.info-value');
                if (valueDiv) {
                    valueDiv.textContent = formattedTime;
                }
            }
        }
    });
}

// Update time initially and every minute
updateLastLoginTime();
setInterval(updateLastLoginTime, 60000);

// Add animation to elements when they come into view
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all stat cards and content cards
document.querySelectorAll('.stat-card, .content-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});
</script>

</body>
</html>