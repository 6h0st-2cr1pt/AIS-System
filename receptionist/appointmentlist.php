<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Receptionist') {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "appoint_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = $error_message = '';

// Fetch appointments
$sql = "SELECT * FROM appointments ORDER BY appointment_date DESC";
$result = $conn->query($sql);

// Delete appointment
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_sql = "DELETE FROM appointments WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Appointment deleted successfully!";
        // Refresh the page to update the appointment list
        header("Location: appointmentlist.php");
        exit();
    } else {
        $error_message = "Error deleting appointment: " . $delete_stmt->error;
    }
    
    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>
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
    </style>
</head>
<body>
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky">
        <div class="sidebar-header mt-0 p-2" style="background-color:#295F98;">
            <h3 class="text-center">AIS System</h3>
        </div><br>
        <h4 class="text-center">Receptionist</h4>
        <ul class="nav flex-column flex-grow-1 mt-3" style="font-size: 18px;">
            <li class="nav-item">
                <a class="nav-link" href="appointment.php">
                    <img class="me-2" src="../icons/bxs-calendar-plus.svg" alt="Appointment" style="width: 30px; height: auto; filter: invert(1);">Appointment
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="calendar.php">
                    <img class="me-2" src="../icons/bxs-calendar.svg" alt="Calendar" style="width: 30px; height: auto; filter: invert(1);">Calendar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="appointmentlist.php">
                    <img class="me-2" src="../icons/bxs-calendar-week.svg" alt="List" style="width: 30px; height: auto; filter: invert(1);">Appointment List
                </a>
            </li>
        </ul>
        <a class="nav-link mb-3 p-2" href="../logout.php" style="margin-top: 420px;">
            <img class="me-2" src="../icons/bx-log-out.svg" alt="Logout" style="width: 30px; height: auto; filter: invert(1);">Logout
        </a>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Appointment List -->
            <div class="col-mb-4 mt-3">
                <div class="card" style="height: 97vh;">
                    <div class="card-body">
                        <h5 class="card-title">Appointment List</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Owner</th>
                                        <th>Pet</th>
                                        <th>Pet Type</th> <!-- Added Pet Type Column -->
                                        <th>Address</th> <!-- Added Address Column -->
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row["owner_name"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["pet_name"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["pet_type"]) . "</td>"; // Display Pet Type
                                            echo "<td>" . htmlspecialchars($row["home_address"]) . "</td>"; // Display Address
                                            echo "<td>" . htmlspecialchars($row["service_type"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($row["appointment_date"]) . "</td>";
                                            echo "<td>
                                                  <a href='appointmentlist.php?delete=" . htmlspecialchars($row["id"]) . "' class='btn btn-danger btn-sm'>Delete</a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No appointments found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        if (!empty($success_message)) {
                            echo '<div class="alert alert-success mt-3">' . $success_message . '</div>';
                        }
                        if (!empty($error_message)) {
                            echo '<div class="alert alert-danger mt-3">' . $error_message . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script src="list.js"></script>
</body>
</html>