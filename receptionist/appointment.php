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

// Initialize messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']); // Clear messages

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_name = $_POST['owner_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $home_address = $_POST['home_address'];
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $pet_type_other = $pet_type === 'Other' ? $_POST['pet_type_other'] : '';
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $service_type = $_POST['service_type'];
    $appointment_date = $_POST['appointment_date'];

    $sql = "INSERT INTO appointments (owner_name, contact_number, email, home_address, pet_name, pet_type, pet_type_other, breed, age, service_type, appointment_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $owner_name, $contact_number, $email, $home_address, $pet_name, $pet_type, $pet_type_other, $breed, $age, $service_type, $appointment_date);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Appointment added successfully!";
        // Redirect to the same page to prevent resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    $stmt->close();
}

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
        $_SESSION['success_message'] = "Appointment deleted successfully!";
        header("Location: appointment.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error deleting appointment: " . $delete_stmt->error;
    }
    
    $delete_stmt->close();
}

// Handle edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $owner_name = $_POST['owner_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $home_address = $_POST['home_address'];
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $pet_type_other = $pet_type === 'Other' ? $_POST['pet_type_other'] : '';
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $service_type = $_POST['service_type'];
    $appointment_date = $_POST['appointment_date'];

    $sql = "UPDATE appointments SET owner_name = ?, contact_number = ?, email = ?, home_address = ?, pet_name = ?, pet_type = ?, pet_type_other = ?, breed = ?, age = ?, service_type = ?, appointment_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", $owner_name, $contact_number, $email, $home_address, $pet_name, $pet_type, $pet_type_other, $breed, $age, $service_type, $appointment_date, $edit_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Appointment updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating appointment: " . $stmt->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch appointment for editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM appointments WHERE id = ?";
    $edit_stmt = $conn->prepare($edit_sql);
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
    }
    $edit_stmt->close();
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
            overflow-x: hidden;
            height: 100vh;
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
    <div class="sidebar-header mt-0 p-2" style="background-color:#295F98;">
        <h3 class="text-center">AIS System</h3>
    </div><br>
    <h4 class="text-center">Receptionist</h4>
    <ul class="nav flex-column flex-grow-1 mt-3" style="font-size: 18px;">
        <li class="nav-item">
            <a class="nav-link active" href="appointment.php">
                <img class="me-2" src="../icons/bxs-calendar-plus.svg" alt="Appointment" style="width: 30px; height: auto; filter: invert(1);">Appointment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="calendar.php">
                <img class="me-2" src="../icons/bxs-calendar.svg" alt="Calendar" style="width: 30px; height: auto; filter: invert(1);">Calendar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="appointmentlist.php">
                <img class="me-2" src="../icons/bxs-calendar-week.svg" alt="List" style="width: 30px; height: auto; filter: invert(1);">Appointment List
            </a>
        </li>
    </ul>
    <a class="nav-link mb-3 p-2" href="../logout.php" style="margin-top: 420px;">
        <img class="me-2" src="../icons/bx-log-out.svg" alt="Logout" style="width: 30px; height: auto; filter: invert(1);">Logout
    </a>
</nav>

        <div class="row" style="height: 100vh;">
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-3">

                <div class="row ms-2">
                    <!-- Appointment Form -->
                    <div class="col-md-6 mb-4">
                        <div class="card" style="height: 94vh;">
                            <div class="card-header">
                                <h5 class="mb0">New Appointment</h5>
                            </div>
                            <div class="card-body">
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="owner_name" class="form-label">Owner's Name</label>
                                            <input type="text" class="form-control" id="owner_name" name="owner_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="contact_number" class="form-label">Contact Number</label>
                                            <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="home_address" class="form-label">Home Address</label>
                                            <input type="text" class="form-control" id="home_address" name="home_address" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="pet_name" class="form-label">Pet Name</label>
                                            <input type="text" class="form-control" id="pet_name" name="pet_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Pet Type</label>
                                            <div class="d-flex gap-3 rounded-3" style="border-style: solid; padding: 4px; justify-content: center;">
                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input" type="radio" name="pet_type" id="pet_type_cat" value="Cat" required>
                                                    <label class="form-check-label ms-2" for="pet_type_cat">Cat</label>
                                                </div>
                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input" type="radio" name="pet_type" id="pet_type_dog" value="Dog">
                                                    <label class="form-check-label ms-2" for="pet_type_dog">Dog</label>
                                                </div>
                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input" type="radio" name="pet_type" id="pet_type_other" value="Other">
                                                    <label class="form-check-label ms-2" for="pet_type_other">Other</label>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control mt-2" id="pet_type_other_specify" name="pet_type_other" placeholder="Please specify">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="breed" class="form-label">Breed</label>
                                            <input type="text" class="form-control" id="breed" name="breed" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="age" class="form-label">Age</label>
                                            <input type="number" class="form-control" id="age" name="age" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Service Type</label>
                                        <div class="d-flex gap-3 rounded-3" style="border-style: solid; padding: 4px; justify-content: center;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="service_type" id="service_type_surgery" value="Surgery" required>
                                                <label class="form-check-label" for="service_type_surgery">Surgery</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="service_type" id="service_type_checkup" value="Check Up">
                                                <label class="form-check-label" for="service_type_checkup">Check Up</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="service_type" id="service_type_grooming" value="Grooming">
                                                <label class="form-check-label" for="service_type_grooming">Grooming</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="service_type" id="service_type_vaccination" value="Vaccination">
                                                <label class="form-check-label" for="service_type_vaccination">Vaccination</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="appointment_date" class="form-label">Appointment Date</label>
                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                    </div><br>
                                    <button type="submit" class="btn btn-primary">Submit Appointment</button>
                                </form><br>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment List -->
                    <div class="col-md-6 mb-4">
                        <div class="card" style="height: 94vh;">
                            <div class="card-header">
                                <h5 class="mb0">Appointment List</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Owner</th>
                                                <th>Pet</th>
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
                                                    echo "<td>" . htmlspecialchars($row["service_type"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["appointment_date"]) . "</td>";
                                                    echo "<td>
                                                            <a href='appointment.php?delete=" . $row["id"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this appointment?\")'>Delete</a>
                                                          </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5'>No appointments found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="appoint.js"></script>
</body>
</html>

