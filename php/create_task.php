<?php
session_start();

if (!isset($_SESSION['coord_email'])) {
    header("Location: coordinator.html");
    exit;
}

$coord_email = $_SESSION['coord_email'];

// DB connection
$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");
if (!$conn) {
    die("DB Connection Failed: " . pg_last_error());
}

// Fetch volunteers
$vol_query = "SELECT userid, email, fname, district FROM registration ORDER BY fname ASC";
$vol_result = pg_query($conn, $vol_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task | KDM</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f3f7fa 0%, #eef2ff 100%);
            min-height: 100vh;
            color: var(--dark);
            padding: 30px;
        }
        
        /* Background bubbles */
        .bg-bubble {
            position: fixed;
            border-radius: 50%;
            background: #4361ee;
            opacity: 0.07;
            z-index: -1;
        }
        .b1 { 
            width: 200px; 
            height: 200px; 
            top: -50px; 
            left: -50px; 
        }
        .b2 { 
            width: 150px; 
            height: 150px; 
            bottom: -40px; 
            right: -40px; 
        }
        .b3 { 
            width: 120px; 
            height: 120px; 
            top: 20%; 
            right: 15%; 
            background: #7209b7; 
            opacity: 0.08; 
            border-radius: 40% 60% 60% 40%; 
        }
        
        .form-container {
            max-width: 850px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            animation: fadeUp 0.8s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes fadeUp {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .header-section h2 {
            font-weight: 700;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .header-section p {
            color: #6c757d;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            padding: 14px 16px;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .form-control::placeholder {
            color: #999;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .priority-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 5px;
        }
        
        .priority-badge {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .priority-badge:hover {
            transform: translateY(-2px);
        }
        
        .priority-badge.active {
            color: white;
        }
        
        .priority-badge[data-value="Low"]:hover,
        .priority-badge[data-value="Low"].active {
            background: #28a745;
            border-color: #28a745;
        }
        
        .priority-badge[data-value="Normal"]:hover,
        .priority-badge[data-value="Normal"].active {
            background: #17a2b8;
            border-color: #17a2b8;
        }
        
        .priority-badge[data-value="High"]:hover,
        .priority-badge[data-value="High"].active {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .priority-badge[data-value="Critical"]:hover,
        .priority-badge[data-value="Critical"].active {
            background: #dc3545;
            border-color: #dc3545;
        }
        
        /* Volunteer selection styling */
        .volunteer-selection {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
            background: #fff;
        }
        
        .volunteer-option {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .volunteer-option:hover {
            background: #f8f9fa;
            border-color: var(--primary);
        }
        
        .volunteer-option.selected {
            background: rgba(67, 97, 238, 0.1);
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .volunteer-name {
            font-weight: 500;
        }
        
        .volunteer-district {
            font-size: 0.85rem;
            color: #6c757d;
            background: #f1f3f5;
            padding: 3px 8px;
            border-radius: 20px;
        }
        
        .selected-volunteers {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            min-height: 50px;
            padding: 10px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .selected-tag {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .selected-tag i {
            cursor: pointer;
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .selected-tag i:hover {
            opacity: 1;
        }
        
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }
        
        .submit-btn:active {
            transform: translateY(-1px);
        }
        
        /* Custom scrollbar */
        .volunteer-selection::-webkit-scrollbar {
            width: 8px;
        }
        
        .volunteer-selection::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .volunteer-selection::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            
            .form-container {
                padding: 30px 25px;
            }
            
            .header-section h2 {
                font-size: 1.8rem;
            }
            
            .priority-badges {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .form-container {
                padding: 25px 20px;
            }
            
            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Background bubbles -->
<div class="bg-bubble b1"></div>
<div class="bg-bubble b2"></div>
<div class="bg-bubble b3"></div>

<div class="form-container">
    <div class="header-section">
        <div class="header-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <h2>Create & Assign Task</h2>
        <p>Create new tasks and assign them to volunteers for disaster management operations</p>
    </div>

    <form method="POST" action="/disaster-management-/php/process_task.php" id="taskForm">
        <!-- Task Title -->
        <div class="mb-4">
            <label class="form-label">
                <i class="fas fa-heading"></i> Task Title
            </label>
            <input type="text" name="title" class="form-control" required 
                   placeholder="Enter task name (e.g., Emergency Relief Distribution)">
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="form-label">
                <i class="fas fa-align-left"></i> Description
            </label>
            <textarea name="description" class="form-control" rows="4" required 
                      placeholder="Describe the task details, objectives, and requirements..."></textarea>
        </div>

        <!-- Priority & Due Date -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">
                    <i class="fas fa-flag"></i> Priority
                </label>
                <div class="priority-badges" id="prioritySelection">
                    <div class="priority-badge" data-value="Low">Low</div>
                    <div class="priority-badge" data-value="Normal">Normal</div>
                    <div class="priority-badge active" data-value="High">High</div>
                    <div class="priority-badge" data-value="Critical">Critical</div>
                </div>
                <input type="hidden" name="priority" id="priorityInput" value="High">
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    <i class="fas fa-calendar-alt"></i> Due Date
                </label>
                <input type="date" name="due_date" class="form-control" required 
                       min="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <!-- Location -->
        <div class="mb-4">
            <label class="form-label">
                <i class="fas fa-map-marker-alt"></i> Location
            </label>
            <input type="text" name="location" class="form-control" required 
                   placeholder="Enter task location (e.g., Central Relief Camp, District HQ)">
        </div>

        <!-- Assign to Volunteers -->
        <div class="mb-4">
            <label class="form-label">
                <i class="fas fa-users"></i> Assign to Volunteers
            </label>
            <div class="volunteer-selection" id="volunteerSelection">
                <?php while ($row = pg_fetch_assoc($vol_result)): ?>
                    <div class="volunteer-option" data-id="<?= $row['email'] ?>">
                        <span class="volunteer-name"><?= htmlspecialchars($row['fname']) ?></span>
                        <span class="volunteer-district"><?= htmlspecialchars($row['district']) ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            <small class="text-muted mt-2 d-block">Click to select/deselect volunteers. Selected volunteers will appear below.</small>
            
            <div class="selected-volunteers" id="selectedVolunteers">
                <!-- Selected volunteers will appear here -->
            </div>
            <input type="hidden" name="volunteers" id="volunteersInput">
        </div>

        <input type="hidden" name="created_by" value="<?= $coord_email ?>">

        <button type="submit" class="submit-btn">
            <i class="fas fa-plus-circle"></i> Create Task
        </button>
    </form>
</div>

<script>
    // Priority selection
    const priorityBadges = document.querySelectorAll('.priority-badge');
    const priorityInput = document.getElementById('priorityInput');
    
    priorityBadges.forEach(badge => {
        badge.addEventListener('click', () => {
            // Remove active class from all badges
            priorityBadges.forEach(b => b.classList.remove('active'));
            // Add active class to clicked badge
            badge.classList.add('active');
            // Update hidden input value
            priorityInput.value = badge.getAttribute('data-value');
        });
    });
    
    // Volunteer selection
    const volunteerOptions = document.querySelectorAll('.volunteer-option');
    const selectedVolunteersDiv = document.getElementById('selectedVolunteers');
    const volunteersInput = document.getElementById('volunteersInput');
    let selectedVolunteers = [];
    
    volunteerOptions.forEach(option => {
        option.addEventListener('click', () => {
            const volunteerId = option.getAttribute('data-id');
            const volunteerName = option.querySelector('.volunteer-name').textContent;
            const volunteerDistrict = option.querySelector('.volunteer-district').textContent;
            
            if (option.classList.contains('selected')) {
                // Remove from selected
                option.classList.remove('selected');
                selectedVolunteers = selectedVolunteers.filter(id => id !== volunteerId);
            } else {
                // Add to selected
                option.classList.add('selected');
                selectedVolunteers.push(volunteerId);
            }
            
            // Update selected volunteers display
            updateSelectedVolunteersDisplay();
            
            // Update hidden input
            volunteersInput.value = selectedVolunteers.join(",");
        });
    });
    
    function updateSelectedVolunteersDisplay() {
        if (selectedVolunteers.length === 0) {
            selectedVolunteersDiv.innerHTML = '<div class="text-muted">No volunteers selected yet</div>';
            return;
        }
        
        let html = '';
        selectedVolunteers.forEach((id, index) => {
            const option = document.querySelector(`.volunteer-option[data-id="${id}"]`);
            const name = option.querySelector('.volunteer-name').textContent;
            const district = option.querySelector('.volunteer-district').textContent;
            
            html += `
                <div class="selected-tag">
                    ${name}
                    <i class="fas fa-times" onclick="removeVolunteer('${id}', ${index})"></i>
                </div>
            `;
        });
        
        selectedVolunteersDiv.innerHTML = html;
    }
    
    function removeVolunteer(id, index) {
        // Remove from array
        selectedVolunteers.splice(index, 1);
        
        // Remove selected class from option
        const option = document.querySelector(`.volunteer-option[data-id="${id}"]`);
        if (option) {
            option.classList.remove('selected');
        }
        
        // Update display
        updateSelectedVolunteersDisplay();
        
        // Update hidden input
        volunteersInput.value = JSON.stringify(selectedVolunteers);
    }
    
    // Form validation
    document.getElementById('taskForm').addEventListener('submit', function(e) {
        if (selectedVolunteers.length === 0) {
            e.preventDefault();
            alert('Please select at least one volunteer for this task.');
            return;
        }
        
        const dueDate = document.querySelector('input[name="due_date"]').value;
        const today = new Date().toISOString().split('T')[0];
        
        if (dueDate < today) {
            e.preventDefault();
            alert('Due date cannot be in the past. Please select a future date.');
            return;
        }
    });
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="due_date"]').setAttribute('min', today);
</script>

</body>
</html>