<?php
include "db_connect.php";
session_start();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST["name"] ?? '';
    $age = $_POST["age"] ?? '';
    $gender = $_POST["gender"] ?? '';
    $meal = $_POST["meal"] ?? '';
    $drink = $_POST["drink"] ?? '';
    $date = $_POST["date"] ?? '';
    
    // Format the date properly for MySQL
    if (!empty($date)) {
        $formatted_date = date('Y-m-d H:i:s', strtotime($date));
    } else {
        $formatted_date = date('Y-m-d H:i:s'); // Current date and time if none provided
    }
    
    // Prepare SQL statement to prevent SQL injection
    $sql = "INSERT INTO `diet` (`Name`, `Age`, `Gender`, `Meal`, `Drink`, `Date`) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Check if prepare succeeded
    if ($stmt === false) {
        $error_message = "Error preparing statement: " . $conn->error;
    } else {
        // Bind parameters with correct types
        if (!$stmt->bind_param("sissss", $name, $age, $gender, $meal, $drink, $formatted_date)) {
            $error_message = "Binding parameters failed: " . $stmt->error;
        }
        
        // Execute the statement
        if (!$stmt->execute()) {
            $error_message = "Error executing statement: " . $stmt->error;
        } else {
            $success_message = "Diet data saved successfully!";
        }
        
        $stmt->close();
    }
}

// Fetch recent diet data for display
$sql = "SELECT * FROM diet ORDER BY `Date` DESC LIMIT 10";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Data Input | DiabetesMonitor</title>
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
                <li><a href="healthdata.php"><span class="icon">📝</span> Health Data Input</a></li>
                <li><a href="ai_health_analysis.php"><span class="icon">🤖</span> AI Health Analysis</a></li>
                <li class="active"><a href="diet.php"><span class="icon">🍎</span> Personalized Diet Plans</a></li>
                <li><a href="educational.php"><span class="icon">📚</span> Educational Content</a></li>
                <li><a href="#"><span class="icon">📊</span> Dashboard</a></li>
            </ul>
        </aside>
        
        <main>
            <h1>Diet Data Input</h1>
            
            <section id="description-block">
                <strong>Description:</strong> Track your meals and drinks to receive personalized diet recommendations.
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
                <h2>Enter New Diet Data</h2>
                
                <form id="dietDataForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="1" max="120" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="meal">Meal</label>   
                            <textarea id="meal" name="meal" placeholder="Meal Info.." style="height:100px; width:1200px"></textarea>                      
                            <label for="drink">Drink</label>
                            <input type="text" id="drink" name="drink" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="datetime-local" id="date" name="date" required>
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
