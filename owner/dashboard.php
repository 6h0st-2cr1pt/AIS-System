<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

// Database connections
$host = 'localhost';
$user = 'root';
$pass = '';

// Inventory Database
$inv_db = 'inv_db';
try {
    $inv_pdo = new PDO("mysql:host=$host;dbname=$inv_db", $user, $pass);
    $inv_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the inventory database: " . $e->getMessage());
}

// Appointment Database
$appoint_db = 'appoint_db';
try {
    $appoint_pdo = new PDO("mysql:host=$host;dbname=$appoint_db", $user, $pass);
    $appoint_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the appointment database: " . $e->getMessage());
}

// Fetch inventory data
$inv_summary = $inv_pdo->query("SELECT 
    COUNT(*) as total_items,
    SUM(quantity) as total_quantity,
    SUM(quantity * unit_price) as total_value,
    COUNT(CASE WHEN quantity <= 10 THEN 1 END) as low_stock_items,
    COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock_items
FROM items")->fetch(PDO::FETCH_ASSOC);

$categories = $inv_pdo->query("SELECT category, COUNT(*) as count FROM items GROUP BY category")->fetchAll(PDO::FETCH_ASSOC);

$action_items = $inv_pdo->query("
    SELECT DISTINCT id, name, quantity, expiration_date 
    FROM items 
    WHERE (quantity <= 10 
    OR (expiration_date IS NOT NULL AND expiration_date <> '' AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)))
    ORDER BY quantity ASC, expiration_date ASC
    LIMIT 5;
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch appointment data
$total_appointments = $appoint_pdo->query("SELECT COUNT(*) as total FROM appointments")->fetch(PDO::FETCH_ASSOC)['total'];
$upcoming_appointments = $appoint_pdo->query("SELECT COUNT(*) as upcoming FROM appointments WHERE appointment_date >= CURDATE()")->fetch(PDO::FETCH_ASSOC)['upcoming'];
$recent_appointments = $appoint_pdo->query("SELECT * FROM appointments ORDER BY appointment_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Fetch appointment types for chart
$appointment_types = $appoint_pdo->query("SELECT service_type, COUNT(*) as count FROM appointments GROUP BY service_type")->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for charts
$categoryLabels = json_encode(array_column($categories, 'category'));
$categoryCounts = json_encode(array_column($categories, 'count'));
$appointmentTypeLabels = json_encode(array_column($appointment_types, 'service_type'));
$appointmentTypeCounts = json_encode(array_column($appointment_types, 'count'));


// Fetch supply management report
$stmt = $inv_pdo->query("
    SELECT s.*, i.name as item_name 
    FROM supplies s 
    JOIN items i ON s.item_id = i.id 
    WHERE s.received_quantity IS NULL 
    ORDER BY s.expected_delivery ASC
");
$pending_supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combined Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #0B2447;
            color: #E0E7FF;
        }
        .sidebar {
            background-color: #19376D;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding-top: 0px;
        }
        .sidebar .nav-link {
            color: #E0E7FF;
        }
        .sidebar .nav-link:hover {
            background-color: #295F98;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            background-color: #295F98;
            color: #E0E7FF;
        }
        .card-header {
            background-color: #19376D;
        }
        .table thead th {
            background-color: #19376D;
            color: #E0E7FF;
            border-color: #576CBC;
        }
    </style>
</head>
<body>
    <nav class="sidebar d-flex flex-column" id="sidebar"> 
        <div class="sidebar-header mt-0 p-2" style="background-color:#295F98;">
            <h3 class="text-center">AIS System</h3>
        </div><br>
        <h4 class="text-center">Owner</h4>
        <ul class="nav flex-column flex-grow-1 mt-3" style="font-size: 18px;">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <img class="me-2" src="../icons/bxs-dashboard.svg" alt="Dashboard" style="width: 30px; height: auto; filter: invert(1);">Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="calendar.php">
                    <img class="me-2" src="../icons/bxs-calendar.svg" alt="Inventory" style="width: 30px; height: auto; filter: invert(1);">Calendar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="appointmentlist.php">
                    <img class="me-2" src="../icons/bxs-spreadsheet.svg" alt="Appointments" style="width: 30px; height: auto; filter: invert(1);">Appointment List
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="inventorylist.php">
                    <img class="me-2" src="../icons/bxs-notepad.svg" alt="Appointments" style="width: 30px; height: auto; filter: invert(1);">Inventory List
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="generalreports.php">
                    <img class="me-2" src="../icons/bxs-report.svg" alt="Reports" style="width: 30px; height: auto; filter: invert(1);">General Reports
                </a>
            </li>
        </ul>
        <a class="nav-link mt-auto mb-3 p-2" href="../logout.php">
            <img class="me-2" src="../icons/bx-log-out.svg" alt="Logout" style="width: 30px; height: auto; filter: invert(1);">Logout
        </a>
    </nav>
    <div class="content">       
        <!-- Inventory Statistics Bar -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-secondary">
            <div class="card-body">
                <h6 class="card-title">Total Items</h6>
                <p class="card-text display-6"><?php echo $inv_summary['total_items']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success">
            <div class="card-body">
                <h6 class="card-title">In Stock</h6>
                <p class="card-text display-6"><?php echo ($inv_summary['total_items'] - $inv_summary['low_stock_items'] - $inv_summary['out_of_stock_items']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning">
            <div class="card-body">
                <h6 class="card-title">Low Stock</h6>
                <p class="card-text display-6"><?php echo $inv_summary['low_stock_items']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger">
            <div class="card-body">
                <h6 class="card-title">Out of Stock</h6>
                <p class="card-text display-6"><?php echo $inv_summary['out_of_stock_items']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card" style="background-color: #6c757d;">
            <div class="card-body">
                <h6 class="card-title">Expired Items</h6>
                <p class="card-text display-6">7</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-primary">
            <div class="card-body">
                <h6 class="card-title">Damage Items</h6>
                <p class="card-text display-6">2</p>
            </div>
        </div>
    </div>
</div>
        <!-- Inventory and Appointment Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Items</h5>
                        <p class="card-text display-4"><?php echo $inv_summary['total_items']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Items</h5>
                        <p class="card-text display-4"><?php echo $inv_summary['low_stock_items']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Appointments</h5>
                        <p class="card-text display-4"><?php echo $total_appointments; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Appointments</h5>
                        <p class="card-text display-4"><?php echo $upcoming_appointments; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Inventory Category Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Appointment Types</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="appointmentTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row">
            <div class="col-md-6">
                <div class="card" style="height: 46vh;">
                    <div class="card-header">
                        <h5 class="mb-0">Items Needing Action</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Quantity</th>
                                        <th>Expiration Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($action_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['expiration_date']); ?></td>
                                        <td>
                                            <?php
                                            if ($item['quantity'] == 0) {
                                                echo '<span class="badge bg-danger">Out of Stock</span>';
                                            } elseif ($item['quantity'] <= 10) {
                                                echo '<span class="badge bg-warning">Low Stock</span>';
                                            }
                                            if ($item['expiration_date'] && strtotime($item['expiration_date']) <= strtotime('+30 days')) {
                                                echo '<span class="badge bg-warning">Expiring Soon</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="height: 46vh;">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Appointments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Owner</th>
                                        <th>Pet</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['owner_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12 mb-4">
                <div class="card" style="height: 46vh;">
                    <div class="card-header">
                        <h5 class="mb-0">Supply Management Report</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Supplier</th>
                                        <th>Order Date</th>
                                        <th>Expected Delivery</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_supplies as $supply): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($supply['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['supplier']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['order_date']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['expected_delivery']); ?></td>
                                        <td>
                                            <?php
                                            if (strtotime($supply['expected_delivery']) < time()) {
                                                echo '<span class="badge bg-danger">Overdue</span>';
                                            } else {
                                                echo '<span class="badge bg-warning">Pending</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Category Distribution Chart
    var ctx = document.getElementById('categoryDistributionChart').getContext('2d');
    var categoryDistributionChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo $categoryLabels; ?>,
            datasets: [{
                data: <?php echo $categoryCounts; ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#FFFFFF' // Set legend font color to white
                    }
                }
            }
        }
    });

    // Appointment Types Chart
    var appointmentCtx = document.getElementById('appointmentTypesChart').getContext('2d');
    var appointmentTypesChart = new Chart(appointmentCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo $appointmentTypeLabels; ?>,
            datasets: [{
                data: <?php echo $appointmentTypeCounts; ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#FFFFFF' // Set legend font color to white
                    }
                }
            }
        }
    });
</script>
</body>
</html>

