<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

require_once '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $home_address = $_POST['home_address'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO USER (username, first_name, last_name, home_address, email, phone_number, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $username, $first_name, $last_name, $home_address, $email, $phone_number, $role, $password);
    
    if ($stmt->execute()) {
        $success = "Account created successfully. You can now login.";
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Fetch all users from the database
$sql = "SELECT id, username, first_name, last_name, email, phone_number, role FROM USER";
$result = $conn->query($sql);

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
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #E0E7FF;
        }
        .sidebar .nav-link:hover {
            background-color: #295F98;
        }
        .bodyf {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .signup-form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<nav class="sidebar d-flex flex-column" id="sidebar"> 
    <div class="sidebar-header mt-0 p-2" style="background-color:#295F98;">
        <h3 class="text-center">AIS System</h3>
    </div><br>
    <h4 class="text-center">Admin</h4>
    <ul class="nav flex-column flex-grow-1 mt-3" style="font-size: 18px;">
        <li class="nav-item">
            <a class="nav-link active" href="account.php">
            <img class="me-2" src="../icons/bxs-user-account.svg" alt="Account" style="width: 30px; height: auto; filter: invert(1);">Create Account
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="accountlist.php">
            <img class="me-2" src="../icons/bxs-spreadsheet.svg" alt="List" style="width: 30px; height: auto; filter: invert(1);">Account List
            </a>
        </li>
    </ul>
    <a class="nav-link mt-auto mb-3 p-2" href="../logout.php">
        <img class="me-2" src="../icons/bx-log-out.svg" alt="Logout" style="width: 30px; height: auto; filter: invert(1);">Logout
    </a>
</nav>
    <div class="me-3 fs-9 mt-2 mb-3 bodyf">
        <div class="row">
        <div class="col-md-4">
    <div class="card text-white" style="background-color: #295F98; font-size: 14px;">
        <div class="card-header" style="background-color: #19376D;">
            <h5 class="mb-0">Create Account</h5>
        </div>
        <div class="card-body">
            <?php if (isset($success)) { ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php } ?>
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="home_address" class="form-label">Home Address</label>
                    <input type="text" class="form-control" id="home_address" name="home_address" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role" id="role_owner" value="Owner" required>
                            <label class="form-check-label" for="role_owner">Owner</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role" id="role_admin" value="Admin" required>
                            <label class="form-check-label" for="role_admin">Admin</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role" id="role_staff" value="Staff" required>
                            <label class="form-check-label" for="role_staff">Staff</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role" id="role_receptionist" value="Receptionist" required>
                            <label class="form-check-label" for="role_receptionist">Receptionist</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            </form>
        </div>
    </div>
</div>

          <!-- Table Column -->
      <div class="col-md-8">
        <div class="card" style="background-color: #295F98; color: #E0E7FF; height: 91.3vh;">
            <div class="card-header text-white mb-3" style="background-color: #19376D;">
                <h5 class="mb-0 text-center">Account List</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
                    </div>
                    <div class="col-md-6">
                        <select id="roleFilter" class="form-select">
                            <option value="">All Roles</option>
                            <option value="Owner">Owner</option>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                            <option value="Receptionist">Receptionist</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["first_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["last_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["phone_number"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No users found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </div>
</div>

        <script src="../js/bootstrap.bundle.min.js"></script>
        <script src="user.js"></script>
</body>
</html> 