<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
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
    COUNT(CASE WHEN quantity = 0 THEN 0 END) as out_of_stock_items
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

// Fetch supply management report
$stmt = $pdo->query("
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
    <title>AIS System</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
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
    <h4 class="text-center">Staff</h4>
    <ul class="nav flex-column flex-grow-1 mt-3" style="font-size: 18px;">
        <li class="nav-item">
            <a class="nav-link active" href="inventory.php">
            <img class="me-2" src="../icons/bxs-notepad.svg" alt="Inventory" style="width: 30px; height: auto; filter: invert(1);">Inventory

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="inventorylist.php">
            <img class="me-2" src="../icons/bxs-spreadsheet.svg" alt="List" style="width: 30px; height: auto; filter: invert(1);">Inventory List

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="reports.php">
            <img class="me-2" src="../icons/bxs-report.svg" alt="Reports" style="width: 30px; height: auto; filter: invert(1);">Reports

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="supply_management.php">
            <img class="me-2" src="../icons/bxs-box.svg" alt="Resupply" style="width: 30px; height: auto; filter: invert(1);">Resupply

            </a>
        </li>
    </ul>
    <a class="nav-link mt-auto mb-3 p-2" href="../logout.php">
        <img class="me-2" src="../icons/bx-log-out.svg" alt="Logout" style="width: 30px; height: auto; filter: invert(1);">Logout
    </a>
</nav>

    <div class="content">
        <div class="row m-1">
            <div class="col-md-6 mb-4">
                <div class="card" style="height: 40vh;">
                    <div class="card-header">
                        <h5 class="mb-0">Inventory Summary</h5>
                    </div>
                    <div class="card-body">
                        <p>Total Items: <?php echo $summary['total_items']; ?></p>
                        <p>Total Quantity: <?php echo $summary['total_quantity']; ?></p>
                        <p>Total Value: ₱<?php echo number_format($summary['total_value'], 2); ?></p>
                        <p>Low Stock Items: <?php echo $summary['low_stock_items']; ?></p>
                        <p>Out of Stock Items: <?php echo $summary['out_of_stock_items']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card" style="height: 40vh;">
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
                                    <?php
                                    $displayedItems = []; // Track displayed items
                                    foreach ($action_items as $item):
                                        if (in_array($item['id'], $displayedItems)) continue; // Skip duplicates
                                        $displayedItems[] = $item['id'];
                                    ?>
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

        <div class="row m-1">
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

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>