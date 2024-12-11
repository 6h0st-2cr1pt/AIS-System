<?php
require_once 'db_connect.php';

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
    <title>Sign Up</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f2ff;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="#">Owner Dashboard</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <div class=" ms-3 me-3 fs-9 mt-2 mb-3" style="font-size: small;">
        <div class="row">
          <!-- Form Column -->
          <div class="col-md-6">
            <div class="signup-form">
              <h2 class="text-center mb-4">Create Account</h2>
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
              <p class="text-center mt-3">Already have an account? <a href="index.php">Login</a></p>
            </div>
          </div>
          <!-- Table Column -->
          <div class="col-md-6">
            <div class="table-container">
            <h2 class="text-center mb-4">Account List</h2>
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
              <div class="card-body" style="height: 590px;">
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
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="user.js"></script>
</body>
</html>

