<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Set timezone to ensure consistent date handling
date_default_timezone_set('UTC'); // Adjust to your timezone

// Fetch the latest glucose data
$sql_latest_glucose = "SELECT blood_glucose_level, time_of_day FROM healthinfo 
                      WHERE blood_glucose_level IS NOT NULL 
                      ORDER BY id DESC LIMIT 1";
$result_latest_glucose = $conn->query($sql_latest_glucose);
$latest_glucose = ($result_latest_glucose->num_rows > 0) ? $result_latest_glucose->fetch_assoc()['blood_glucose_level'] : 'N/A';

// Calculate glucose change from yesterday
$sql_yesterday_glucose = "SELECT AVG(blood_glucose_level) as avg_glucose FROM healthinfo 
                         WHERE blood_glucose_level IS NOT NULL 
                         AND DATE(created_at) = DATE(NOW() - INTERVAL 1 DAY)";
try {
    $result_yesterday_glucose = $conn->query($sql_yesterday_glucose);
    $row = $result_yesterday_glucose->fetch_assoc();
    $yesterday_glucose = ($result_yesterday_glucose->num_rows > 0 && $row['avg_glucose'] !== null) 
        ? $row['avg_glucose'] : 0;
} catch (mysqli_sql_exception $e) {
    error_log("Error in yesterday glucose query: " . $e->getMessage());
    $yesterday_glucose = 0;
}

$glucose_change = 0;
$glucose_change_direction = '';
if ($yesterday_glucose > 0 && $latest_glucose != 'N/A') {
    $glucose_change = round((($latest_glucose - $yesterday_glucose) / $yesterday_glucose) * 100, 1);
    $glucose_change_direction = ($glucose_change >= 0) ? 'higher' : 'lower';
    $glucose_change = abs($glucose_change);
}

// Fetch latest heart rate
$sql_latest_vitals = "SELECT pulse_rate FROM healthinfo 
                     WHERE pulse_rate IS NOT NULL 
                     ORDER BY id DESC LIMIT 1";
$result_latest_vitals = $conn->query($sql_latest_vitals);
$latest_heart_rate = ($result_latest_vitals->num_rows > 0) ? $result_latest_vitals->fetch_assoc()['pulse_rate'] : 'N/A';

// Calculate heart rate change
$sql_yesterday_heart = "SELECT AVG(pulse_rate) as avg_heart FROM healthinfo 
                       WHERE pulse_rate IS NOT NULL 
                       AND DATE(created_at) = DATE(NOW() - INTERVAL 1 DAY)";
try {
    $result_yesterday_heart = $conn->query($sql_yesterday_heart);
    $row = $result_yesterday_heart->fetch_assoc();
    $yesterday_heart = ($result_yesterday_heart->num_rows > 0 && $row['avg_heart'] !== null) 
        ? $row['avg_heart'] : 0;
} catch (mysqli_sql_exception $e) {
    error_log("Error in yesterday heart rate query: " . $e->getMessage());
    $yesterday_heart = 0;
}

$heart_change = 0;
$heart_change_direction = '';
if ($yesterday_heart > 0 && $latest_heart_rate != 'N/A') {
    $heart_change = round((($latest_heart_rate - $yesterday_heart) / $yesterday_heart) * 100, 1);
    $heart_change_direction = ($heart_change >= 0) ? 'higher' : 'lower';
    $heart_change = abs($heart_change);
}

// Count insulin doses today
$sql_insulin_doses = "SELECT COUNT(*) as dose_count FROM healthinfo 
                     WHERE insulin_dose IS NOT NULL 
                     AND DATE(created_at) = CURDATE()";
try {
    $result_insulin = $conn->query($sql_insulin_doses);
    $insulin_doses = ($result_insulin->num_rows > 0) ? $result_insulin->fetch_assoc()['dose_count'] : 0;
} catch (mysqli_sql_exception $e) {
    error_log("Error in insulin doses query: " . $e->getMessage());
    $insulin_doses = 0;
}

// Calculate total carb intake today
$sql_carbs = "SELECT SUM(carbs) as total_carbs FROM diet 
             WHERE DATE(created_at) = CURDATE()";
try {
    $result_carbs = $conn->query($sql_carbs);
    $row = $result_carbs->fetch_assoc();
    $total_carbs = ($result_carbs->num_rows > 0 && $row['total_carbs'] !== null) 
        ? $row['total_carbs'] : 0;
} catch (mysqli_sql_exception $e) {
    error_log("Error in carbs query: " . $e->getMessage());
    $total_carbs = 0;
}

// Calculate carb change
$sql_yesterday_carbs = "SELECT SUM(carbs) as yesterday_carbs FROM diet 
                       WHERE DATE(created_at) = DATE(NOW() - INTERVAL 1 DAY)";
try {
    $result_yesterday_carbs = $conn->query($sql_yesterday_carbs);
    $row = $result_yesterday_carbs->fetch_assoc();
    $yesterday_carbs = ($result_yesterday_carbs->num_rows > 0 && $row['yesterday_carbs'] !== null) 
        ? $row['yesterday_carbs'] : 0;
} catch (mysqli_sql_exception $e) {
    error_log("Error in yesterday carbs query: " . $e->getMessage());
    $yesterday_carbs = 0;
}

$carb_change = 0;
$carb_change_direction = '';
if ($yesterday_carbs > 0) {
    $carb_change = round((($total_carbs - $yesterday_carbs) / $yesterday_carbs) * 100, 1);
    $carb_change_direction = ($carb_change >= 0) ? 'higher' : 'lower';
    $carb_change = abs($carb_change);
}

// Get glucose readings for today
$sql_glucose_readings = "SELECT blood_glucose_level, TIME(created_at) as reading_time 
                        FROM healthinfo 
                        WHERE blood_glucose_level IS NOT NULL 
                        AND DATE(created_at) = CURDATE() 
                        ORDER BY created_at ASC";
try {
    $result_glucose_readings = $conn->query($sql_glucose_readings);
    $glucose_data = [];
    $glucose_times = [];
    if ($result_glucose_readings->num_rows > 0) {
        while ($row = $result_glucose_readings->fetch_assoc()) {
            $glucose_data[] = $row['blood_glucose_level'];
            $glucose_times[] = date('ga', strtotime($row['reading_time']));
        }
    } else {
        // Sample data to ensure the graph renders
        $glucose_data = [120.0, 130.0, 145.0, 140.0, 135.0];
        $glucose_times = ['8:00am', '10:00am', '12:00pm', '2:00pm', '4:00pm'];
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error in glucose readings query: " . $e->getMessage());
    // Sample data in case of error
    $glucose_data = [120.0, 130.0, 145.0, 140.0, 135.0];
    $glucose_times = ['8:00am', '10:00am', '12:00pm', '2:00pm', '4:00pm'];
}

// Calculate average, highest, and lowest glucose
$avg_glucose = !empty($glucose_data) ? round(array_sum($glucose_data) / count($glucose_data)) : 0;
$highest_glucose = !empty($glucose_data) ? max($glucose_data) : 0;
$lowest_glucose = !empty($glucose_data) ? min($glucose_data) : 0;

// Get today's meals
$sql_meals = "SELECT meal_type, food_item, carbs, created_at 
             FROM diet 
             WHERE DATE(created_at) = CURDATE() 
             ORDER BY created_at ASC";
try {
    $result_meals = $conn->query($sql_meals);
    $meals = [];
    if ($result_meals->num_rows > 0) {
        while ($row = $result_meals->fetch_assoc()) {
            $meals[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error in meals query: " . $e->getMessage());
    $meals = [];
}

// Organize meals by type
$meals_by_type = [
    'breakfast' => [],
    'lunch' => [],
    'dinner' => [],
    'snack' => []
];

foreach ($meals as $meal) {
    $type = strtolower($meal['meal_type']);
    if (isset($meals_by_type[$type])) {
        $meals_by_type[$type][] = $meal;
    }
}

// Get AI insights (simplified version - in a real app, these would be generated by AI)
$insights = [
    [
        'type' => 'warning',
        'title' => 'High glucose after breakfast',
        'description' => 'Your readings show elevated glucose levels 1-2 hours after breakfast.'
    ],
    [
        'type' => 'success',
        'title' => 'Good progress on evening levels',
        'description' => 'Your evening glucose levels have improved over the past week.'
    ],
    [
        'type' => 'info',
        'title' => 'Physical activity impact',
        'description' => 'Walking for 20 minutes after meals helps lower your glucose levels.'
    ],
    [
        'type' => 'danger',
        'title' => 'Potential low glucose risk',
        'description' => 'Be cautious of dropping levels during your afternoon activities.'
    ]
];

// Get medications (simplified version)
$medications = [
    [
        'name' => 'Metformin',
        'dose' => '500mg',
        'time' => '8:00 AM',
        'taken' => true
    ],
    [
        'name' => 'Lisinopril',
        'dose' => '10mg',
        'time' => '8:00 AM',
        'taken' => true
    ],
    [
        'name' => 'Metformin',
        'dose' => '500mg',
        'time' => '1:00 PM',
        'taken' => true
    ],
    [
        'name' => 'Metformin',
        'dose' => '500mg',
        'time' => '8:00 PM',
        'taken' => false
    ]
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DiaHealth</title>
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
        <li><a href="dashboard.php" class="active"><i>📊</i> Dashboard</a></li>
        <li><a href="healthdata.php"><i>📝</i> Health Data Input</a></li>
        <li><a href="ai_analysis.php"><i>📈</i> AI Analysis</a></li>
        <li><a href="meal.php"><i>❤️</i> Meal</a></li>
        <li><a href="educational.php"><i>📚</i> Education</a></li>
        <li><a href="index.php"><i>🚪</i> Logout</a></li>
    </ul>
    <div class="footer">
        DiaHealth AI Guide © 2025<br>
        Your Personal Health Companion
    </div>
</div>

<div class="main-content">
    <h1 class="page-title">Welcome to Your Health Dashboard</h1>
    
    <!-- Key Metrics Row -->
    <div class="metrics-row">
        <div class="metric-card">
            <div class="metric-title">Blood Glucose</div>
            <div class="metric-value"><?php echo htmlspecialchars($latest_glucose); ?> <span class="unit">mg/dL</span></div>
            <?php if ($glucose_change > 0): ?>
            <div class="metric-change <?php echo $glucose_change_direction == 'higher' ? 'up' : 'down'; ?>">
                <?php echo ($glucose_change_direction == 'higher' ? '↑' : '↓') . ' ' . htmlspecialchars($glucose_change); ?>% <?php echo htmlspecialchars($glucose_change_direction); ?> from yesterday
            </div>
            <?php endif; ?>
            <div class="metric-icon glucose-icon">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M12,12m-8,0a8,8 0 1,0 16,0a8,8 0 1,0 -16,0" />
                    <path fill="white" d="M12,7v10 M7,12h10" />
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">Heart Rate</div>
            <div class="metric-value"><?php echo htmlspecialchars($latest_heart_rate); ?> <span class="unit">bpm</span></div>
            <?php if ($heart_change > 0): ?>
            <div class="metric-change <?php echo $heart_change_direction == 'higher' ? 'up' : 'down'; ?>">
                <?php echo ($heart_change_direction == 'higher' ? '↑' : '↓') . ' ' . htmlspecialchars($heart_change); ?>% <?php echo htmlspecialchars($heart_change_direction); ?> from yesterday
            </div>
            <?php endif; ?>
            <div class="metric-icon heart-icon">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M12,21.35l-1.45-1.32C5.4,15.36,2,12.28,2,8.5C2,5.42,4.42,3,7.5,3c1.74,0,3.41,0.81,4.5,2.09C13.09,3.81,14.76,3,16.5,3C19.58,3,22,5.42,22,8.5c0,3.78-3.4,6.86-8.55,11.54L12,21.35z" />
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-title">Carb Intake</div>
            <div class="metric-value"><?php echo htmlspecialchars($total_carbs); ?> <span class="unit">g</span></div>
            <?php if ($carb_change > 0): ?>
            <div class="metric-change <?php echo $carb_change_direction == 'higher' ? 'up' : 'down'; ?>">
                <?php echo ($carb_change_direction == 'higher' ? '↑' : '↓') . ' ' . htmlspecialchars($carb_change); ?>% <?php echo htmlspecialchars($carb_change_direction); ?> from yesterday
            </div>
            <?php endif; ?>
            <div class="metric-icon carb-icon">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M8.1,13.34L3.91,9.16C2.35,7.59 2.35,5.06 3.91,3.5L10.93,10.5L8.1,13.34M14.88,11.53L13.41,13L20.29,19.88L18.88,21.29L12,14.41L5.12,21.29L3.71,19.88L13.47,10.12C12.76,8.59 13.26,6.44 14.85,4.85C16.76,2.93 19.5,2.57 20.96,4.03C22.43,5.5 22.07,8.24 20.15,10.15C18.56,11.74 16.41,12.24 14.88,11.53Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Two column layout -->
    <div class="dashboard-row">
        <!-- Left column -->
        <div class="dashboard-col">
            <!-- Glucose chart -->
            <div class="dashboard-card glucose-chart-card">
                <h2 class="card-title">Blood Glucose Levels</h2>
                <p class="card-subtitle">Today's readings</p>
                <div class="glucose-chart-container">
                    <canvas id="glucoseChart"></canvas>
                </div>
                <div class="glucose-stats">
                    <div class="glucose-stat">
                        <span class="stat-label">Average</span>
                        <span class="stat-value"><?php echo htmlspecialchars($avg_glucose); ?> mg/dL</span>
                    </div>
                    <div class="glucose-stat">
                        <span class="stat-label">Highest</span>
                        <span class="stat-value"><?php echo htmlspecialchars($highest_glucose); ?> mg/dL</span>
                    </div>
                    <div class="glucose-stat">
                        <span class="stat-label">Lowest</span>
                        <span class="stat-value"><?php echo htmlspecialchars($lowest_glucose); ?> mg/dL</span>
                    </div>
                </div>
            </div>

            <!-- AI Insights -->
            <div class="dashboard-card">
                <h2 class="card-title">AI Health Insights</h2>
                <div class="insights-container">
                    <?php foreach ($insights as $insight): ?>
                    <div class="insight-item insight-<?php echo htmlspecialchars($insight['type']); ?>">
                        <div class="insight-icon">
                            <?php if ($insight['type'] == 'warning'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12,2L1,21H23M12,6L19.53,19H4.47M11,10V14H13V10M11,16V18H13V16" />
                            </svg>
                            <?php elseif ($insight['type'] == 'success'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z" />
                            </svg>
                            <?php elseif ($insight['type'] == 'info'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M13,9H11V7H13M13,17H11V11H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                            </svg>
                            <?php elseif ($insight['type'] == 'danger'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M3.27,1.44L2,2.72L4.05,4.77C2.75,5.37 1.5,6.11 0.38,7C4.41,12.06 12,21.5 12,21.5L15.91,16.63L19.23,19.95L20.5,18.68M12,3C10.6,3 9.21,3.17 7.86,3.5L9.56,5.19C10.37,5.07 11.18,5 12,5C15.07,5 18.09,5.86 20.71,7.45L16.76,12.38L18.18,13.8C20.08,11.43 22,9 23.65,7C20.32,4.41 16.22,3 12,3Z" />
                            </svg>
                            <?php endif; ?>
                        </div>
                        <div class="insight-content">
                            <h3 class="insight-title"><?php echo htmlspecialchars($insight['title']); ?></h3>
                            <p class="insight-description"><?php echo htmlspecialchars($insight['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-card">
                <h2 class="card-title">Quick Actions</h2>
                <div class="quick-actions">
                    <a href="healthdata.php" class="quick-action">
                        <div class="quick-action-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                            </svg>
                        </div>
                        <span>Add Health Data</span>
                    </a>
                    <a href="meal.php" class="quick-action">
                        <div class="quick-action-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M8.1,13.34L3.91,9.16C2.35,7.59 2.35,5.06 3.91,3.5L10.93,10.5L8.1,13.34M14.88,11.53L13.41,13L20.29,19.88L18.88,21.29L12,14.41L5.12,21.29L3.71,19.88L13.47,10.12C12.76,8.59 13.26,6.44 14.85,4.85C16.76,2.93 19.5,2.57 20.96,4.03C22.43,5.5 22.07,8.24 20.15,10.15C18.56,11.74 16.41,12.24 14.88,11.53Z" />
                            </svg>
                        </div>
                        <span>Log Meal</span>
                    </a>
                    <a href="ai_analysis.php" class="quick-action">
                        <div class="quick-action-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12,16A3,3 0 0,1 9,13C9,11.88 9.61,10.9 10.5,10.39L20.21,4.77L14.68,14.35C14.18,15.33 13.17,16 12,16M12,3C7.03,3 3,7.03 3,12C3,16.97 7.03,21 12,21C16.97,21 21,16.97 21,12C21,7.03 16.97,3 12,3M12,5C15.87,5 19,8.13 19,12C19,15.87 15.87,19 12,19C8.13,19 5,15.87 5,12C5,8.13 8.13,5 12,5Z" />
                            </svg>
                        </div>
                        <span>AI Analysis</span>
                    </a>
                    <a href="educational.php" class="quick-action">
                        <div class="quick-action-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12,3L1,9L12,15L21,10.09V17H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z" />
                            </svg>
                        </div>
                        <span>Learn More</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="dashboard-col">
            <!-- Today's Meals -->
            <div class="dashboard-card">
                <h2 class="card-title">Today's Meals</h2>
                <p class="card-subtitle">Last updated <?php echo count($meals) > 0 ? '2h ago' : 'N/A'; ?></p>
                <div class="meals-container">
                    <?php if (!empty($meals_by_type['breakfast'])): ?>
                    <div class="meal-item">
                        <div class="meal-time">8:30 AM</div>
                        <div class="meal-content">
                            <h3 class="meal-title">Breakfast</h3>
                            <p class="meal-description">
                                <?php 
                                $breakfast_items = array_map(function($meal) {
                                    return htmlspecialchars($meal['food_item']);
                                }, $meals_by_type['breakfast']);
                                echo implode(', ', $breakfast_items);
                                ?>
                            </p>
                            <div class="meal-carbs">
                                <?php 
                                $breakfast_carbs = array_sum(array_column($meals_by_type['breakfast'], 'carbs'));
                                echo htmlspecialchars($breakfast_carbs); 
                                ?> g carbs
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meals_by_type['lunch'])): ?>
                    <div class="meal-item">
                        <div class="meal-time">12:15 PM</div>
                        <div class="meal-content">
                            <h3 class="meal-title">Lunch</h3>
                            <p class="meal-description">
                                <?php 
                                $lunch_items = array_map(function($meal) {
                                    return htmlspecialchars($meal['food_item']);
                                }, $meals_by_type['lunch']);
                                echo implode(', ', $lunch_items);
                                ?>
                            </p>
                            <div class="meal-carbs">
                                <?php 
                                $lunch_carbs = array_sum(array_column($meals_by_type['lunch'], 'carbs'));
                                echo htmlspecialchars($lunch_carbs); 
                                ?> g carbs
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meals_by_type['snack'])): ?>
                    <div class="meal-item">
                        <div class="meal-time">4:00 PM</div>
                        <div class="meal-content">
                            <h3 class="meal-title">Snack</h3>
                            <p class="meal-description">
                                <?php 
                                $snack_items = array_map(function($meal) {
                                    return htmlspecialchars($meal['food_item']);
                                }, $meals_by_type['snack']);
                                echo implode(', ', $snack_items);
                                ?>
                            </p>
                            <div class="meal-carbs">
                                <?php 
                                $snack_carbs = array_sum(array_column($meals_by_type['snack'], 'carbs'));
                                echo htmlspecialchars($snack_carbs); 
                                ?> g carbs
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($meals_by_type['dinner'])): ?>
                    <div class="meal-item">
                        <div class="meal-time">7:00 PM</div>
                        <div class="meal-content">
                            <h3 class="meal-title">Dinner</h3>
                            <p class="meal-description">
                                <?php 
                                $dinner_items = array_map(function($meal) {
                                    return htmlspecialchars($meal['food_item']);
                                }, $meals_by_type['dinner']);
                                echo implode(', ', $dinner_items);
                                ?>
                            </p>
                            <div class="meal-carbs">
                                <?php 
                                $dinner_carbs = array_sum(array_column($meals_by_type['dinner'], 'carbs'));
                                echo htmlspecialchars($dinner_carbs); 
                                ?> g carbs
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($meals)): ?>
                    <div class="no-data">No meal data available for today</div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="meal.php" class="btn btn-outline">View All</a>
                </div>
            </div>

            <!-- Today's Medications -->
            <div class="dashboard-card">
                <h2 class="card-title">Today's Medications</h2>
                <div class="medications-header">
                    <span class="medication-count"><?php echo htmlspecialchars(count(array_filter($medications, function($med) { return $med['taken']; }))); ?> of <?php echo htmlspecialchars(count($medications)); ?> taken</span>
                </div>
                <div class="medications-container">
                    <?php foreach ($medications as $med): ?>
                    <div class="medication-item">
                        <div class="medication-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                            </svg>
                        </div>
                        <div class="medication-content">
                            <h3 class="medication-name"><?php echo htmlspecialchars($med['name']); ?></h3>
                            <p class="medication-dose"><?php echo htmlspecialchars($med['dose']); ?></p>
                        </div>
                        <div class="medication-time"><?php echo htmlspecialchars($med['time']); ?></div>
                        <div class="medication-status <?php echo $med['taken'] ? 'taken' : ''; ?>">
                            <?php if ($med['taken']): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z" />
                            </svg>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>
// Glucose chart data
const glucoseData = {
    labels: <?php echo json_encode($glucose_times); ?>,
    datasets: [{
        label: 'Blood Glucose (mg/dL)',
        data: <?php echo json_encode($glucose_data); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 2,
        tension: 0.4,
        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
        pointRadius: 4,
        fill: true
    }]
};

// Chart configuration
window.addEventListener('DOMContentLoaded', (event) => {
    const ctx = document.getElementById('glucoseChart').getContext('2d');
    const glucoseChart = new Chart(ctx, {
        type: 'line',
        data: glucoseData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    suggestedMin: 50,
                    suggestedMax: 250,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        color: '#666'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#666'
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>