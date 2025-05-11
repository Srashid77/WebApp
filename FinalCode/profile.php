<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Adjust as needed
$password = ""; // Adjust as needed
$dbname = "health_data";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable strict error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Fetch user information
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT username, user_type, firstname, lastname, email, gender FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

// Handle form submission
$errors = [];
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($firstname)) {
        $errors[] = "First name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($gender) || !in_array($gender, ['Male', 'Female', 'Other'])) {
        $errors[] = "Valid gender is required.";
    }
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Check if email is already used by another user
    $sql_check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check_email);
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $errors[] = "This email is already in use.";
    }

    // If no errors, update the database
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // Update with password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE users SET firstname = ?, lastname = ?, email = ?, gender = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("sssssi", $firstname, $lastname, $email, $gender, $hashed_password, $user_id);
            } else {
                // Update without password
                $sql_update = "UPDATE users SET firstname = ?, lastname = ?, email = ?, gender = ? WHERE id = ?";
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("ssssi", $firstname, $lastname, $email, $gender, $user_id);
            }
            $stmt->execute();
            $success_message = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $conn->prepare($sql_user);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result_user = $stmt->get_result();
            $user = $result_user->fetch_assoc();
        } catch (mysqli_sql_exception $e) {
            $errors[] = "Error updating profile: " . $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - DiaHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles2.css">
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <div class="logo-icon">
            <span class="heart-icon">♡</span>
        </div>
        DiaHealth
    </div>
    <div class="nav-header">Main Navigation</div>
    <ul class="nav-links">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i>📊</i> Dashboard</a></li>
        <li><a href="healthdata.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'healthdata.php' ? 'active' : ''; ?>"><i>📝</i> Health Data Input</a></li>
        <li><a href="ai_analysis.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ai_analysis.php' ? 'active' : ''; ?>"><i>📈</i> AI Analysis</a></li>
        <li><a href="meal.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'meal.php' ? 'active' : ''; ?>"><i>❤️</i> Meal</a></li>
        <li><a href="educational.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'educational.php' ? 'active' : ''; ?>"><i>📚</i> Education</a></li>
        <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>"><i>👤</i> Profile</a></li>
        <li><a href="index.php"><i>🚪</i> Logout</a></li>
    </ul>
    <div class="footer">
        DiaHealth AI Guide © 2025<br>
        Your Personal Health Companion
    </div>
</div>

<div class="main-content">
    <h1 class="page-title">Your Profile</h1>

    <!-- Display errors or success message -->
    <?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
    <div class="success-message">
        <p><?php echo htmlspecialchars($success_message); ?></p>
    </div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="dashboard-card">
        <h2 class="card-title">Profile Information</h2>
        <form method="POST" class="profile-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <input type="text" id="user_type" name="user_type" value="<?php echo htmlspecialchars($user['user_type'] ?? '');，各?>" disabled>
            </div>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password" placeholder="Enter new password">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>