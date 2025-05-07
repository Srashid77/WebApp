<?php
include "db_connect.php";
session_start();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST["name"] ?? '';
    $age = $_POST["age"] ?? '';
    $systolic = $_POST["systolic"] ?? '';
    $diastolic = $_POST["diastolic"] ?? '';
    $bloodSugar = $_POST["bloodSugar"] ?? '';
    $meal = $_POST["meal"] ?? '';
    $date = $_POST["date"] ?? '';
    $insulin = $_POST["insulin"] ?? '';
    $physicalActivity = $_POST["activity"] ?? '';
    $nutritionHabits = $_POST["habits"] ?? '';

    // Format the date properly for MySQL
    $formatted_date = !empty($date) 
        ? date('Y-m-d H:i:s', strtotime($date)) 
        : date('Y-m-d H:i:s');

    // Prepare SQL statement
    $sql = "INSERT INTO `healthinfo` (
                `Name`, `Age`, `Blood Pressure (Systolic)`, `Blood Pressure (Diastolic)`, 
                `Blood Sugar Level`, `Meal`, `Date`, `Insulin Level`, `Physical Activity`, `Nutrition Habits`
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $error_message = "Error preparing statement: " . $conn->error;
    } else {
        if (!$stmt->bind_param("siiisssiss", $name, $age, $systolic, $diastolic, $bloodSugar, $meal, $formatted_date, $insulin, $physicalActivity, $nutritionHabits)) {
            $error_message = "Binding parameters failed: " . $stmt->error;
        }

        if (!$stmt->execute()) {
            $error_message = "Error executing statement: " . $stmt->error;
        } else {
            $success_message = "Health data saved successfully!";
        }

        $stmt->close();
    }
}

// Fetch recent health data for display
$sql = "SELECT * FROM healthinfo ORDER BY `Date` DESC LIMIT 10";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Data Input | DiabetesMonitor</title>
    <link rel="stylesheet" href="styles1.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">DiabetesMonitor</div>
        </nav>
    </header>
    
    <div id="app-container">
        <aside>
            <ul>
                <li class="active"><a href="healthdata.php"><span class="icon">📝</span> Health Data Input</a></li>
                <li><a href="ai_health_analysis.php"><span class="icon">🤖</span> AI Health Analysis</a></li>
                <li><a href="diet.php"><span class="icon">🍎</span> Personalized Diet Plans</a></li>
                <li><a href="educational.php"><span class="icon">📚</span> Educational Content</a></li>
                <li><a href="#"><span class="icon">📊</span> Dashboard</a></li>
            </ul>
        </aside>
        
        <main>
            <h1>Health Data Input</h1>
            
            <section id="description-block">
                <strong>Description:</strong> Track your health metrics to receive personalized insights and recommendations.
            </section>
            
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <section id="data-input-section">
                <h2>Enter New Health Data</h2>
                
                <form id="healthDataForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="1" max="120" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bloodPressure">Blood Pressure (mmHg)</label>
                            <div class="blood-pressure-inputs">
                                <input type="number" id="systolic" name="systolic" placeholder="Systolic" min="70" max="250" required>
                                <span>/</span>
                                <input type="number" id="diastolic" name="diastolic" placeholder="Diastolic" min="40" max="150" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="bloodSugar">Blood Sugar Level (mg/dL)</label>
                            <input type="number" id="bloodSugar" name="bloodSugar" min="1" max="600" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="meal">Meal</label>   
                            <input type="text" id="meal" name="meal" required>                       
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="datetime-local" id="date" name="date" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="insulin">Insulin Level</label>   
                            <input type="number" id="insulin" name="insulin" min="1" max="600" required>                       
                        </div>
                        
                        <div class="form-group">
                            <label for="activity">Physical Activity</label>
                            <input type="text" id="activity" name="activity">
                        </div>
                    </div>
                    <div class="form-row">
                    <div class="form-group">
                    <label for="habits">Diet and Nutrition Habits</label>
                   <textarea id="habits" name="habits" rows="8" cols="130" placeholder="Describe your diet and nutrition habits..."></textarea>
                    </div>
                    </div>
                    <button type="submit" class="submit-btn">Save Diet Data</button>            
                </form>
            </section>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?>
