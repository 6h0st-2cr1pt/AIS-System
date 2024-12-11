<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: login.php");
    exit();
}

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
    try {
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
    // Update item
    $stmt = $pdo->prepare("UPDATE items SET name = ?, category = ?, quantity = ?, unit_price = ?, expiration_date = ? WHERE id = ?");
    $stmt->execute([$_POST['item_name'], $_POST['category'], $_POST['quantity'], $_POST['unit_price'], $_POST['expiration_date'], $_POST['edit_id']]);
    } else {
    // Add new item
    $stmt = $pdo->prepare("INSERT INTO items (name, category, quantity, unit_price, expiration_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['item_name'], $_POST['category'], $_POST['quantity'], $_POST['unit_price'], $_POST['expiration_date']]);
    }
    // Redirect to the same page to prevent form resubmission
    header("Location: inventory.php");
    exit();
    } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    }
    }

// Fetch items from the database
$stmt = $pdo->query("SELECT * FROM items");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete action
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: inventory.php");
    exit();
}
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
            transition: margin-left 0.3s ease;
        }
        .top-navbar {
            background-color: #19376D;
            position: fixed;
            width: calc(100% - 250px);
            left: 250px;
            top: 0;
            z-index: 1000;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .content {
                margin-left: 0;
            }
            .top-navbar {
                width: 100%;
                left: 0;
            }
        }
        .card {
            background-color: #295F98;
            color: #E0E7FF;
            height: 91vh;
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
    <div class="content" id="content">
        <div class="container mb-4">
            <div class="row justify-content-center">
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">INVENTORY FORM</h5>
                        </div>
                        <div class="card-body">
                            <form action="inventory.php" method="POST">
                                <input type="hidden" name="edit_id" id="edit_id" value="">
                                <div class="mb-3">
                                    <label for="item_name" class="form-label">ITEM NAME</label>
                                    <input type="text" class="form-control" id="item_name" name="item_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">CATEGORY</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category" value="Food" id="food" required>
                                            <label class="form-check-label" for="food">Food</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category" value="Hygiene" id="hygiene">
                                            <label class="form-check-label" for="hygiene">Hygiene</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category" value="Accessories" id="accessories">
                                            <label class="form-check-label" for="accessories">Accessories</label>
                                        </div>
                                    </div><br>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category" value="Vaccine" id="vaccine">
                                            <label class="form-check-label" for="vaccine">Vaccine</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category" value="Medicine" id="medicine">
                                            <label class="form-check-label" for="medicine">Medicine</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">QUANTITY</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="number_of_damage" class="form-label">NUMBER OF DAMAGED ITEMS</label>
                                    <input type="number" class="form-control" id="number_of_damage" name="number_of_damage" required>
                                </div>
                                <div class="mb-3">
                                    <label for="unit_price" class="form-label">UNIT PRICE</label>
                                    <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                                </div>
                                <div class="mb-3">
                                    <label for="expiration_date" class="form-label">EXPIRATION DATE</label>
                                    <input type="date" class="form-control" id="expiration_date" name="expiration_date">
                                    <h6>Set Expiration Date If Available</h6>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                                    <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;">Cancel</button>
                                    <button type="reset" class="btn btn-primary" id="clearBtn">Clear</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Inventory Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 595px; overflow-y: auto;">
                                <table class="table table-sm table-primary table-hover table-striped" data-bs-theme="dark">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Expiration Date</th>
                                            <th>Status</th>
                                            <th style="text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['id']); ?></td>
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
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $item['id']; ?>">Edit</button>
                                                <button class="btn btn-sm btn-danger delete-btn" onclick="location.href='inventory.php?delete_id=<?php echo $item['id']; ?>'">Delete</button>
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
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select all edit buttons
        const editButtons = document.querySelectorAll('.edit-btn');
        const form = document.querySelector('form');
        const cancelBtn = document.getElementById('cancelBtn');
        const submitBtn = document.getElementById('submitBtn');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Fetch the data attributes of the selected row
                const id = this.dataset.id;
                const row = this.closest('tr');
                const name = row.cells[1].textContent;
                const category = row.cells[2].textContent;
                const quantity = row.cells[3].textContent;
                const unitPrice = row.cells[4].textContent.replace('₱', '');
                const expirationDate = row.cells[5].textContent;

                // Populate the form fields
                document.getElementById('edit_id').value = id;
                document.getElementById('item_name').value = name;
                document.getElementById('quantity').value = quantity;
                document.getElementById('unit_price').value = unitPrice;
                document.getElementById('expiration_date').value = expirationDate;

                // Set the correct category radio button
                const categoryRadio = document.querySelector(`input[name="category"][value="${category}"]`);
                if (categoryRadio) {
                    categoryRadio.checked = true;
                }

                // Show the Cancel button and change Submit button text
                cancelBtn.style.display = 'inline-block';
                submitBtn.textContent = 'Update';
            });
        });

        // Reset form when cancel button is clicked
        cancelBtn.addEventListener('click', function () {
            form.reset();
            document.getElementById('edit_id').value = '';
            cancelBtn.style.display = 'none';
            submitBtn.textContent = 'Submit';
        });
    });
</script>



</body>
</html>

