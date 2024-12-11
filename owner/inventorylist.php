<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

// Prevent browser caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Database connection settings
$host = 'localhost'; // Change if your database is hosted elsewhere
$db = 'inv_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Fetch items from the database
$stmt = $pdo->query("SELECT * FROM items");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique categories for the filter dropdown
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM items");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
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
            overflow-x: hidden;
            padding-left: 250px; /* Add padding to the left to avoid overlap with sidebar */
        }
        .navbar {
            background-color: #19376D !important;
            position: fixed;
            width: calc(100% - 250px); /* Full width minus sidebar */
            left: 250px; /* Align with the sidebar */
            z-index: 1000; /* Ensure it's above other content */
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
        .card {
            background-color: #295F98;
            color: #E0E7FF;
            margin-top: 70px; /* Space for the navbar */
            margin-left: 250px; /* Space for the sidebar */
            height: calc(100vh - 70px); /* Full height minus navbar */
            overflow-y: auto; /* Allow scrolling within the card */
        }
        .card-header {
            background-color: #19376D !important;
            color: #E0E7FF;
        }
        .form-control, .form-check-input {
            background-color: #0B2447;
            border-color: #576CBC;
            color: #E0E7FF;
        }
        .form-control:focus, .form-check-input:focus {
            background-color: #19376D;
            border-color: #A5D7E8;
            color: #E0E7FF;
            box-shadow: 0 0 0 0.25rem rgba(165, 215, 232, 0.25);
        }
        .btn-primary {
            background-color: #576CBC;
            border-color: #576CBC;
        }
        .btn-primary:hover {
            background-color: #A5D7E8;
            border-color: #A5D7E8;
        }
        .table {
            color: #E0E7FF;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(30, 62, 98, 0.7);
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #1E3E62;
        }
        .table thead th {
            background-color: #19376D;
            color: #E0E7FF;
            border-color: #576CBC;
        }
        .table td, .table th {
            border-color: #576CBC;
        }
        .table-responsive {
            position: relative;
            max-height: calc(100vh - 150px); /* Adjust table height */
            overflow-y: auto;
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
        <h4 class="text-center">Owner</h4>
        <ul class="nav flex-column flex-grow-1 mt-3" style="font-size: 18px;">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
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
                <a class="nav-link active" href="inventorylist.php">
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

    <div class="container">
        <div class="col">
            <div class="card m-4">
                <div class="card-header">
                    <h5 class="mb-0">Inventory Items</h5>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search inventory...">
                        </div>
                        <div class="col-md-4">
                            <select id="categoryFilter" class="form-select" aria-label="Category filter">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="statusFilter" class="form-select" aria-label="Status filter">
                                <option value="">All Statuses</option>
                                <option value="In Stock">In Stock</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: calc(100vh - 150px); overflow-y: auto;">
                        <table class="table table-sm table-primary table-hover table-striped" data-bs-theme="dark" id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Expiration Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>₱<?php echo htmlspecialchars($item['unit_price']); ?></td>
                                    <td><?php echo htmlspecialchars($item['expiration_date']); ?></td>
                                    <td>
                                        <?php
                                        if($item['quantity'] == 0) {
                                            echo '<span class="badge bg-danger">Out of Stock</span>';
                                        } elseif($item['quantity'] <= 10) {
                                            echo '<span class="badge bg-warning">Low Stock</span>';
                                        } else {
                                            echo '<span class="badge bg-success">In Stock</span>';
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
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="filter.js"></script>
</body>
</html>
