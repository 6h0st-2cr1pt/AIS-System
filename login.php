<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM USER WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            switch ($user['role']) {
                case 'Owner':
                    header("Location: owner/dashboard.php");
                    break;
                case 'Admin':
                    header("Location: admin/account.php");
                    break;
                case 'Staff':
                    header("Location: staff/inventory.php");
                    break;
                case 'Receptionist':
                    header("Location: receptionist/appointment.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f2ff;
        }
        .login-form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-mh-100" style="background-image: url('image/wallpaperflare.com_wallpaper\ \(9\).jpg'); background-repeat: no-repeat; background-size: cover;">
    <div class="container mt-5" style="width: 50%;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-form p-4 shadow-sm rounded-5" style="background-color: rgba(184, 241, 255, 0.7);">
                    <div class="text-center mb-4">
                        <img src="logo/free-dog-walking-logo-4-1.png" alt="Dog" style="width: auto; height: 150px;">
                    </div>
                    <h1 class="text-center mb-4" style="opacity: 1; font-family: 'Brush Script MT', cursive; font-size: 60px;">Login</h1>
                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php } ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required style="opacity: 1; background-color: rgb(199, 245, 255); border-color: Black; border: solid;">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required style="opacity: 1; background-color: rgb(199, 245, 255); border-color: Black; border: solid;">
                        </div><br>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form><br>
                </div>
            </div>
        </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>

