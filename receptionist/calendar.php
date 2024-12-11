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

// Get the month and year from the query string, default to current month and year
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Fetch appointments for the selected month
$selected_month = sprintf('%04d-%02d', $year, $month);
$sql = "SELECT appointment_date, COUNT(*) as appointment_count FROM appointments WHERE DATE_FORMAT(appointment_date, '%Y-%m') = ? GROUP BY appointment_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$result = $stmt->get_result();

$appointments = array();
while ($row = $result->fetch_assoc()) {
    $appointments[$row['appointment_date']] = $row['appointment_count'];
}

$stmt->close();
$conn->close();

// Function to get the name of the month
function getMonthName($month) {
    return date('F', mktime(0, 0, 0, $month, 1, 2000));
}

// Calculate previous and next month/year
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13) {
    $next_month = 1;
    $next_year++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Calendar</title>
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
        .calendar {
            background-color: #295F98;
            border-radius: 5px;
            padding: 20px;
        }
        .calendar .table td {
            height: 100px;
            vertical-align: top;
        }
        .appointment-count {
            font-weight: bold;
            color: #0B2447;
        }
        .month-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
                    <a class="nav-link" href="appointment.php">
                        <img class="me-2" src="../icons/bxs-calendar-plus.svg" alt="Appointment" style="width: 30px; height: auto; filter: invert(1);">Appointment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="calendar.php">
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
        
    <div class="row">
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4"><br>
            <div class="card calendar">
                <div class="card-body">
                    <div class="month-nav">
                        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-primary">&lt; Previous</a>
                        <h5 class="card-title" style="font-size: 48px; color: #ffffff;"><?php echo getMonthName($month) . ' ' . $year; ?></h5>
                        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-primary">Next &gt;</a>
                    </div>
                    <table class="table table-bordered bg-primary" style="border-width:4px">
                        <thead>
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $firstDay = strtotime("$year-$month-01");
                            $lastDay = strtotime('last day of ' . date('F Y', $firstDay));
                            $currentDay = $firstDay;
                            $weekDay = date('w', $currentDay);
                            echo "<tr>";
                            for ($i = 0; $i < $weekDay; $i++) {
                                echo "<td></td>";
                            }
                            while ($currentDay <= $lastDay) {
                                if ($weekDay == 7) {
                                    echo "</tr><tr>";
                                    $weekDay = 0;
                                }
                                $currentDate = date('Y-m-d', $currentDay);
                                $appointmentCount = isset($appointments[$currentDate]) ? $appointments[$currentDate] : 0;
                                echo "<td>";
                                echo date('j', $currentDay);
                                if ($appointmentCount > 0) {
                                    echo "<br><span class='appointment-count'>$appointmentCount appointments</span>";
                                }
                                echo "</td>";
                                $currentDay = strtotime('+1 day', $currentDay);
                                $weekDay++;
                            }
                            while ($weekDay < 7) {
                                echo "<td></td>";
                                $weekDay++;
                            }
                            echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>

