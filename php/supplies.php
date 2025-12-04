<?php
session_start();

// Prevent unauthorized access
if (!isset($_SESSION['coord_email'])) {
    header("Location: /disaster-management-/html/coordinator.html");
    exit;
}

// Database connection
$conn = pg_connect("host=localhost dbname=disastermanagement user=postgres password=venda");
if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

// Fetch all supplies
$query = "SELECT supply_id, donation_type, fname, pick_address, additional 
          FROM supplies ORDER BY supply_id DESC";
$result = pg_query($conn, $query);
$supplies = pg_fetch_all($result);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Supplies & Donations Management | Coordinator Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

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
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            color: var(--dark);
            padding: 40px;
        }

        /* Background Elements */
        .bg-element {
            position: fixed;
            border-radius: 50%;
            z-index: -1;
            opacity: 0.05;
            background: var(--primary);
        }

        .bg-element-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
        }

        .bg-element-2 {
            width: 200px;
            height: 200px;
            bottom: -80px;
            left: -80px;
        }

        .bg-element-3 {
            width: 150px;
            height: 150px;
            top: 30%;
            left: 10%;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px 40px;
            border-radius: var(--radius);
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
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

        .page-header h2 {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header h2 i {
            color: var(--accent);
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            position: relative;
            z-index: 2;
        }

        .btn-action {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .btn-add {
            background: var(--accent);
            color: white;
        }

        .btn-add:hover {
            background: #e61473;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(247, 37, 133, 0.3);
        }

        /* Search Section */
        .search-section {
            background: var(--card-bg);
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border: 1px solid var(--light-gray);
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-container {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid var(--light-gray);
            border-radius: 50px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--light);
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        /* Filter Buttons */
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border-radius: 50px;
            border: 2px solid var(--light-gray);
            background: white;
            color: var(--gray);
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 25px;
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
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Supplies Table */
        .table-container {
            background: var(--card-bg);
            padding: 35px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--light-gray);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-header h4 {
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header h4 i {
            color: var(--primary);
        }

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
            transform: scale(1.01);
        }

        .table td {
            padding: 18px 15px;
            vertical-align: middle;
            border-color: var(--light-gray);
        }

        /* Badges */
        .type-badge {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .address-badge {
            background: rgba(114, 9, 183, 0.1);
            color: var(--secondary);
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-edit {
            background: #ffab00;
            color: white;
        }

        .btn-edit:hover {
            background: #cc8800;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 171, 0, 0.3);
        }

        .btn-delete {
            background: #ff4d4d;
            color: white;
        }

        .btn-delete:hover {
            background: #cc0000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 77, 77, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: var(--shadow-hover);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 25px 30px;
            border: none;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.4rem;
        }

        .modal-body {
            padding: 30px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid var(--light-gray);
            padding: 14px 20px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 50px;
            padding: 14px 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            
            .page-header {
                padding: 25px;
            }
            
            .page-header h2 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .table-container {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
            
            .search-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container {
                min-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .table td, .table th {
                padding: 12px 8px;
                font-size: 0.9rem;
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

        .page-header,
        .search-section,
        .stats-grid,
        .table-container {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .search-section { animation-delay: 0.1s; }
        .stats-grid { animation-delay: 0.2s; }
        .table-container { animation-delay: 0.3s; }
    </style>
</head>

<body>
    <!-- Background Elements -->
    <div class="bg-element bg-element-1"></div>
    <div class="bg-element bg-element-2"></div>
    <div class="bg-element bg-element-3"></div>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h2>
                <i class="fas fa-boxes"></i>
                Supplies & Donations
            </h2>
            <p>Manage inventory, track donations, and coordinate supply distribution for disaster relief</p>
        </div>
        
        <div class="action-buttons">
            <a href="coordinator_dashboard.php" class="btn-action btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <button class="btn-action btn-add" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                <i class="fas fa-plus"></i>
                Add New Supply
            </button>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-section">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   id="searchBox" 
                   class="search-input" 
                   placeholder="Search supplies by type, name, or address...">
        </div>
        
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="food">Food</button>
            <button class="filter-btn" data-filter="medical">Medical</button>
            <button class="filter-btn" data-filter="clothing">Clothing</button>
            <button class="filter-btn" data-filter="shelter">Shelter</button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-number"><?= count($supplies) ?></div>
            <div class="stat-label">Total Supplies</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="stat-number">
                <?= count(array_filter($supplies, fn($s) => stripos($s['donation_type'], 'food') !== false)) ?>
            </div>
            <div class="stat-label">Food Items</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-briefcase-medical"></i>
            </div>
            <div class="stat-number">
                <?= count(array_filter($supplies, fn($s) => stripos($s['donation_type'], 'medical') !== false)) ?>
            </div>
            <div class="stat-label">Medical Supplies</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-number">
                <?= count(array_filter($supplies, fn($s) => stripos($s['donation_type'], 'shelter') !== false)) ?>
            </div>
            <div class="stat-label">Shelter Items</div>
        </div>
    </div>

    <!-- Supplies Table -->
    <div class="table-container">
        <div class="table-header">
            <h4>
                <i class="fas fa-clipboard-list"></i>
                Inventory Management
            </h4>
            <div class="text-muted">
                Showing <?= count($supplies) ?> item(s)
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Donor</th>
                        <th>Location</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="suppliesTable">
                    <?php if ($supplies): ?>
                        <?php foreach ($supplies as $s): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $s['supply_id'] ?></strong>
                                </td>
                                <td>
                                    <span class="type-badge">
                                        <?= htmlspecialchars($s['donation_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($s['fname']) ?></div>
                                </td>
                                <td>
                                    <span class="address-badge">
                                        <?= htmlspecialchars($s['pick_address'] ?: 'Pickup required') ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($s['additional'] ?: 'No additional details') ?></small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="action-btn btn-edit">
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </button>
                                        <button class="action-btn btn-delete">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h4 class="mt-3 mb-2">No Supplies Found</h4>
                                <p>Add your first supply item to get started.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Supply Modal -->
    <div class="modal fade" id="addSupplyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="process_supply.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Add New Supply Item
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="">Select category</option>
                                <option value="Food">🍎 Food</option>
                                <option value="Clothing">👕 Clothing</option>
                                <option value="Medical">🏥 Medical</option>
                                <option value="Shelter Kits">🏠 Shelter Kits</option>
                                <option value="Water">💧 Water</option>
                                <option value="Other">📦 Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select class="form-select" name="unit">
                                <option value="Units">Units</option>
                                <option value="Kgs">Kilograms</option>
                                <option value="Liters">Liters</option>
                                <option value="Boxes">Boxes</option>
                                <option value="Packets">Packets</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Donor Name</label>
                            <input type="text" class="form-control" name="donor_name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Received Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="received_date" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Storage Location</label>
                            <input type="text" class="form-control" name="storage_location" 
                                   placeholder="e.g., Warehouse A, Shelf 3">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Save Supply Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search Filter
        const searchBox = document.getElementById("searchBox");
        searchBox.addEventListener("keyup", function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll("#suppliesTable tr:not(.empty-state)");
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const isVisible = text.includes(filter);
                row.style.display = isVisible ? "" : "none";
                
                if (isVisible) visibleCount++;
            });

            // Update showing count
            const showingElement = document.querySelector('.table-header .text-muted');
            if (showingElement) {
                showingElement.textContent = `Showing ${visibleCount} item(s)`;
            }
        });

        // Category Filter
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const rows = document.querySelectorAll("#suppliesTable tr:not(.empty-state)");
                let visibleCount = 0;

                rows.forEach(row => {
                    const type = row.querySelector('.type-badge')?.textContent.toLowerCase() || '';
                    const isVisible = filter === 'all' || type.includes(filter);
                    row.style.display = isVisible ? "" : "none";
                    
                    if (isVisible) visibleCount++;
                });

                // Update showing count
                const showingElement = document.querySelector('.table-header .text-muted');
                if (showingElement) {
                    showingElement.textContent = `Showing ${visibleCount} item(s)`;
                }
            });
        });

        // Edit Button
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const id = row.querySelector('td:first-child strong').textContent;
                const type = row.querySelector('.type-badge').textContent;
                const name = row.querySelector('.fw-semibold').textContent;
                alert(`Edit supply #${id} - ${type} from ${name} - Feature coming soon!`);
            });
        });

        // Delete Button
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this supply item? This action cannot be undone.')) {
                    const row = this.closest('tr');
                    const id = row.querySelector('td:first-child strong').textContent;
                    alert(`Supply #${id} marked for deletion - Feature coming soon!`);
                }
            });
        });

        // Add animation on scroll
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

        // Observe all animated elements
        document.querySelectorAll('.page-header, .search-section, .stats-grid, .table-container').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>