<?php
// Start session
session_start();

// Debugging: Log session data
file_put_contents('debug.log', "Dashboard.php - Session ID: " . session_id() . ", Session Data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Check if user is logged in
if (!isset($_SESSION['name'])) {
    file_put_contents('debug.log', "Dashboard.php - No session name, redirecting to login\n", FILE_APPEND);
    header("Location: login.php");
    exit();
}

// Include database connection
include "db_connect.php";

// Log healthinfo and diet table structures for debugging
$result = $conn->query("DESCRIBE healthinfo");
if ($result) {
    file_put_contents('debug.log', "Dashboard.php - Healthinfo structure: " . print_r($result->fetch_all(MYSQLI_ASSOC), true) . "\n", FILE_APPEND);
}
$result = $conn->query("DESCRIBE diet");
if ($result) {
    file_put_contents('debug.log', "Dashboard.php - Diet structure: " . print_r($result->fetch_all(MYSQLI_ASSOC), true) . "\n", FILE_APPEND);
}

// Fetch user health data
$username = $_SESSION['name'];
$sql_health = "SELECT h.id, h.blood_glucose_level, h.time_of_day, h.activity_type, h.symptom_name 
               FROM healthinfo h 
               JOIN users u ON h.user_id = u.id 
               WHERE u.username = ? 
               ORDER BY h.time_of_day DESC LIMIT 5";
$stmt_health = $conn->prepare($sql_health);
if (!$stmt_health) {
    file_put_contents('debug.log', "Dashboard.php - Health SQL Error: " . $conn->error . "\n", FILE_APPEND);
    die("Database error: Unable to prepare health statement.");
}
$stmt_health->bind_param("s", $username);
$stmt_health->execute();
$health_result = $stmt_health->get_result();

// Fetch meal data (no user_id filter due to schema limitation)
$sql_meal = "SELECT id, meal_type, food_item, portion_size, calories, carbs 
             FROM diet 
             ORDER BY id DESC LIMIT 5";
$stmt_meal = $conn->prepare($sql_meal);
if (!$stmt_meal) {
    file_put_contents('debug.log', "Dashboard.php - Meal SQL Error: " . $conn->error . "\n", FILE_APPEND);
    die("Database error: Unable to prepare meal statement.");
}
$stmt_meal->execute();
$meal_result = $stmt_meal->get_result();

// Fetch summary data
$sql_summary = "SELECT AVG(h.blood_glucose_level) as avg_glucose 
                FROM healthinfo h 
                JOIN users u ON h.user_id = u.id 
                WHERE u.username = ?";
$stmt_summary = $conn->prepare($sql_summary);
if (!$stmt_summary) {
    file_put_contents('debug.log', "Dashboard.php - Summary SQL Error: " . $conn->error . "\n", FILE_APPEND);
    die("Database error: Unable to prepare summary statement.");
}
$stmt_summary->bind_param("s", $username);
$stmt_summary->execute();
$summary_result = $stmt_summary->get_result();
$summary = $summary_result->fetch_assoc();

// Fetch total calories from diet (no user filter)
$sql_calories = "SELECT SUM(calories) as total_calories FROM diet";
$result_calories = $conn->query($sql_calories);
$calories = $result_calories->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | DiaHealth</title>
    <link rel="stylesheet" href="styles1.css">
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
            <li><a href="#" class="active"><i>📊</i> Dashboard</a></li>
            <li><a href="healthdata.php"><i>📝</i> Health Data Input</a></li>
            <li><a href="ai_analysis.php"><i>📈</i> AI Analysis</a></li>
            <li><a href="meal.php"><i>❤️</i> Meal</a></li>
            <li><a href="educational.php"><i>📚</i> Education</a></li>
        </ul>
        <div class="footer">
            DiaHealth AI Guide © 2025<br>
            Your Personal Health Companion
        </div>
    </div>

    <div class="main-content">
        <main>
            <section class="description-block">
                <strong>Welcome <?php 
                    if(isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
                        echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                    } else {
                        echo htmlspecialchars($_SESSION['name']); 
                    }
                ?>!</strong>
                View a summary of your health and meal data, and monitor your progress.
            </section>

            <h1>Your Health Dashboard</h1>
            
            <section id="health-data-section">
                <h2>Your Recent Health Data</h2>
                <p><strong>Note:</strong> Showing latest 5 entries. Some columns (Age, Blood Pressure) are unavailable.</p>
                <table class="diet-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Blood Glucose (mg/dL)</th>
                            <th>Time of Day</th>
                            <th>Activity Type</th>
                            <th>Symptom Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($health_result && $health_result->num_rows > 0) {
                            while($row = $health_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["blood_glucose_level"] ?? "N/A") . "</td>";
                                echo "<td>" . htmlspecialchars($row["time_of_day"] ?? "N/A") . "</td>";
                                echo "<td>" . htmlspecialchars($row["activity_type"] ?? "N/A") . "</td>";
                                echo "<td>" . htmlspecialchars($row["symptom_name"] ?? "N/A") . "</td>";
                                echo "<td>
                                    <button class='action-btn edit-btn' onclick='editRecord(" . $row["id"] . ")'>Edit</button>
                                    <button class='action-btn delete-btn' onclick='deleteRecord(" . $row["id"] . ")'>Delete</button>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No health data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="meal-data-section">
                <h2>Your Recent Meal Data</h2>
                <p><strong>Note:</strong> Showing latest 5 entries. Full data available in Meal Consumption.</p>
                <table class="diet-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Meal Type</th>
                            <th>Food Item</th>
                            <th>Portion Size</th>
                            <th>Calories</th>
                            <th>Carbs (g)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($meal_result && $meal_result->num_rows > 0) {
                            while($row = $meal_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td>" . htmlspecialchars(ucfirst($row["meal_type"])) . "</td>";
                                echo "<td>" . htmlspecialchars($row["food_item"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["portion_size"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["calories"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["carbs"]) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No meal data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="summary-section">
                <h2>Health & Meal Summary</h2>
                <div id="summary-content">
                    <?php
                    if ($summary['avg_glucose'] !== null) {
                        echo "<p><strong>Average Blood Glucose:</strong> " . round($summary['avg_glucose'], 1) . " mg/dL</p>";
                    } else {
                        echo "<p><strong>Average Blood Glucose:</strong> No data available</p>";
                    }
                    if ($calories['total_calories'] !== null) {
                        echo "<p><strong>Total Calories Consumed:</strong> " . round($calories['total_calories']) . " kcal</p>";
                    } else {
                        echo "<p><strong>Total Calories Consumed:</strong> No data available</p>";
                    }
                    ?>
                    <p>Track more data to get personalized insights!</p>
                </div>
            </section>

            <section id="quick-actions">
                <h2>Quick Actions</h2>
                <div class="button-center">
                    <a href="healthdata.php" class="submit-btn">Add New Health Data</a>
                    <a href="meal.php" class="submit-btn" style="margin-left: 10px;">Add New Meal Data</a>
                    <a href="ai_analysis.php" class="submit-btn" style="margin-left: 10px;">Analyze My Health</a>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Functions for edit and delete actions
        function editRecord(id) {
            window.location.href = "edit_health_data.php?id=" + id;
        }
        
        function deleteRecord(id) {
            if(confirm("Are you sure you want to delete this record?")) {
                window.location.href = "delete_health_data.php?id=" + id;
            }
        }
    </script>
</body>
</html>
<?php
$stmt_health->close();
$stmt_meal->close();
$stmt_summary->close();
$conn->close();
?>