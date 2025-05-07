<?php
// Add error reporting at the top of the script
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_connect.php";
session_start();

$error = '';
$success = '';

// Debug: Check database connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function validate($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Collect form data
    $username = validate($_POST['username']);
    $email = validate($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation checks
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check for existing username or email
        $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        
        if (!$stmt) {
            $error = "Prepare statement failed: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $error = "Username or email already exists. Please choose another.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Prepare SQL to insert new user
                $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);

                if (!$insert_stmt) {
                    $error = "Insert prepare failed: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($insert_stmt, "sss", $username, $email, $hashed_password);
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $success = "Registration successful! You can now login.";
                        $_SESSION['username'] = $username; // Changed 'name' to 'username' for consistency
                    } else {
                        $error = "Registration failed: " . mysqli_error($conn);
                    }

                    mysqli_stmt_close($insert_stmt);
                }
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Integrated Health Monitoring System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" type="text/css" href="login_style.css">
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-center h-100">
        <div class="card">
            <div class="card-header">
                <h3>Sign Up for <i>Integrated Health Monitoring System</i></h3>
            </div>
            <div class="card-body">
                <?php 
                if (!empty($error)) {
                    echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
                }
                if (!empty($success)) {
                    echo "<div class='alert alert-success'>" . htmlspecialchars($success) . "</div>";
                }
                ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Username" name="username" required>
                    </div>
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" placeholder="Email" name="email" required>
                    </div>
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" class="form-control" placeholder="Password" name="password" required>
                    </div>
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" placeholder="Confirm Password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Sign Up" class="btn float-right login_btn">
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center links">
                    Already have an account? <a href="login.php">Login</a>  
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>