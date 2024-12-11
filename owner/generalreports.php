<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

// Database connection details
$host = 'localhost';
$db = 'inv_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Fetch inventory summary
$stmt = $pdo->query("SELECT 
    COUNT(*) as total_items,
    SUM(quantity) as total_quantity,
    SUM(quantity * unit_price) as total_value,
    COUNT(CASE WHEN quantity <= 10 THEN 1 END) as low_stock_items,
    COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock_items
FROM items");
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch items needing action
$stmt = $pdo->query("
    SELECT DISTINCT id, name, quantity, expiration_date 
    FROM items 
    WHERE (quantity <= 10 
    OR (expiration_date IS NOT NULL AND expiration_date <> '' AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)))
    ORDER BY quantity ASC, expiration_date ASC;
");
$action_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch category distribution
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM items GROUP BY category");
$category_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch top 5 most valuable items
$stmt = $pdo->query("SELECT name, quantity * unit_price as total_value FROM items ORDER BY total_value DESC LIMIT 5");
$top_valuable_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIS System - General Report</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
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
        .table {
            color: #E0E7FF;
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
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card" style="height: 90vh;">
                <div class="card-header">
                    <h5 class="mb-0">Inventory Summary</h5>
                </div>
                <div class="card-body" style=" font-size: 30px;">
                    <p>Total Items: <?php echo $summary['total_items']; ?></p>
                    <p>Total Quantity: <?php echo $summary['total_quantity']; ?></p>
                    <p>Total Value: ₱<?php echo number_format($summary['total_value'], 2); ?></p>
                    <p>Low Stock Items: <?php echo $summary['low_stock_items']; ?></p>
                    <p>Out of Stock Items: <?php echo $summary['out_of_stock_items']; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card" style="height: 90vh;">
                <div class="card-header">
                    <h5 class="mb-0">Category Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card" style="height: 90vh;">
                <div class="card-header">
                    <h5 class="mb-0">Top 5 Most Valuable Items</h5>
                </div>
                <div class="card-body">
                    <canvas id="valueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card" style="height: 90vh;">
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
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    // Category Distribution Chart
    var ctx = document.getElementById('categoryChart').getContext('2d');
    var categoryChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($category_distribution, 'category')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($category_distribution, 'count')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'white' // Set legend font color to white
                    }
                },
                title: {
                    display: true,
                    text: 'Category Distribution',
                    color: 'white' // Title color
                }
            }
        }
    });

    // Top 5 Most Valuable Items Chart
    var ctx2 = document.getElementById('valueChart').getContext('2d');
    var valueChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($top_valuable_items, 'name')); ?>,
            datasets: [{
                label: 'Total Value',
                data: <?php echo json_encode(array_column($top_valuable_items, 'total_value')); ?>,
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'white' // Set legend font color to white
                    }
                },
                title: {
                    display: true,
                    text: 'Top 5 Most Valuable Items',
                    color: 'white' // Title color
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '₱' + value.toFixed(2);
                        },
                        color: 'white' // Y-axis ticks color
                    }
                },
                x: {
                    ticks: {
                        color: 'white' // X-axis ticks color
                    }
                }
            }
        }
    });
</script>
</body>
</html>