<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: login.php");
    exit();
}

// Prevent browser caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_supply') {
            $stmt = $pdo->prepare("INSERT INTO supplies (item_id, quantity, supplier, order_date, expected_delivery) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['item_id'], $_POST['quantity'], $_POST['supplier'], $_POST['order_date'], $_POST['expected_delivery']]);
        } elseif ($_POST['action'] == 'receive_supply') {
            $stmt = $pdo->prepare("UPDATE supplies SET received_quantity = ?, received_date = CURDATE() WHERE id = ?");
            $stmt->execute([$_POST['received_quantity'], $_POST['supply_id']]);

            // Update item quantity
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE id = (SELECT item_id FROM supplies WHERE id = ?)");
            $stmt->execute([$_POST['received_quantity'], $_POST['supply_id']]);
        }
    }
    header("Location: supply_management.php");
    exit();
}

// Fetch items for dropdown
$stmt = $pdo->query("SELECT id, name FROM items ORDER BY name");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch supplies
$stmt = $pdo->query("SELECT s.*, i.name as item_name FROM supplies s JOIN items i ON s.item_id = i.id ORDER BY s.order_date DESC");
$supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .table thead th {
            background-color: #19376D;
            color: #E0E7FF;
            border-color: #576CBC;
        }
        .table {
            color: #E0E7FF;
        }
        .table td, .table th {
            border-color: #576CBC;
        }
        thead th {
            position: sticky;
            top: 0;
            z-index: 10; /* Ensure header stays above other content */
            background-color: #0d6efd; /* Match your theme */
            color: white; /* Header text color */
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
            <a class="nav-link" href="reports.php">
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
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Supply Order</h5>
                    </div>
                    <div class="card-body">
                        <form action="supply_management.php" method="POST">
                            <input type="hidden" name="action" value="add_supply">
                            <div class="mb-3">
                                <label for="item_id" class="form-label">Item</label>
                                <select class="form-select" id="item_id" name="item_id" required>
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                            </div>
                            <div class="mb-3">
                                <label for="supplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" required>
                            </div>
                            <div class="mb-3">
                                <label for="order_date" class="form-label">Order Date</label>
                                <input type="date" class="form-control" id="order_date" name="order_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="expected_delivery" class="form-label">Expected Delivery Date</label>
                                <input type="date" class="form-control" id="expected_delivery" name="expected_delivery" required>
                            </div><br>
                            <button type="submit" class="btn btn-primary">Add Supply Order</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8 mb-4">
                <div class="card" style="height: 565px;">
                    <div class="card-header">
                        <h5 class="mb-0">Supply Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-primary table-hover table-striped" data-bs-theme="dark">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Supplier</th>
                                        <th>Order Date</th>
                                        <th>Expected Delivery</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($supplies as $supply): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($supply['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['supplier']); ?></td>
                                        <td><?php echo htmlspecialchars($supply['order_date']); ?></td><td><?php echo htmlspecialchars($supply['expected_delivery']); ?></td>
                                        <td>
                                            <?php
                                            if ($supply['received_quantity'] !== null) {
                                                echo 'Received';
                                            } elseif (strtotime($supply['expected_delivery']) < time()) {
                                                echo 'Overdue';
                                            } else {
                                                echo 'Pending';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($supply['received_quantity'] === null): ?>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#receiveModal<?php echo $supply['id']; ?>">
                                                Receive
                                            </button>
                                            <?php endif; ?>
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

    <?php foreach ($supplies as $supply): ?>
    <?php if ($supply['received_quantity'] === null): ?>
    <div class="modal fade" id="receiveModal<?php echo $supply['id']; ?>" tabindex="-1" aria-labelledby="receiveModalLabel<?php echo $supply['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: black;" id="receiveModalLabel<?php echo $supply['id']; ?>">Receive Supply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="supply_management.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="receive_supply">
                        <input type="hidden" name="supply_id" value="<?php echo $supply['id']; ?>">
                        <div class="mb-3">
                            <label for="received_quantity<?php echo $supply['id']; ?>" class="form-label" style="color: black;">Received Quantity</label>
                            <input style="border-color: black;" type="number" class="form-control" id="received_quantity<?php echo $supply['id']; ?>" name="received_quantity" max="<?php echo $supply['quantity']; ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Receive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>

