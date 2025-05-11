<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_data";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Handle form submissions for adding diet items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['diet_submit'])) {
    try {
        $meal_type = $_POST['meal_type'];
        $food_item = $_POST['food_item'];
        $portion_size = $_POST['portion_size'];
        $calories = $_POST['calories'];
        $carbs = $_POST['carbs'];
        $notes = $_POST['notes'];

        $stmt = $conn->prepare("INSERT INTO diet (meal_type, food_item, portion_size, calories, carbs, notes) VALUES (:meal_type, :food_item, :portion_size, :calories, :carbs, :notes)");
        $stmt->execute([
            'meal_type' => $meal_type,
            'food_item' => $food_item,
            'portion_size' => $portion_size,
            'calories' => $calories,
            'carbs' => $carbs,
            'notes' => $notes
        ]);

        // Redirect to the same page to prevent form resubmission
        header("Location: meal.php?status=success");
        exit;
    } catch(PDOException $e) {
        // Store error message in query parameter
        header("Location: meal.php?status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}

// Check for status messages
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';

if ($status === 'success') {
    echo "<script>alert('Diet item saved successfully!');</script>";
} elseif ($status === 'error') {
    echo "<script>alert('Error saving diet item: $message');</script>";
}

// Fetch diet items for display
$meal_types = ['breakfast', 'lunch', 'dinner', 'snack'];
$diet_items = [];
foreach ($meal_types as $type) {
    $stmt = $conn->prepare("SELECT * FROM diet WHERE meal_type = :meal_type");
    $stmt->execute(['meal_type' => $type]);
    $diet_items[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Predefined food items with nutritional data (per 100g serving)
$food_items = [
    'apple' => ['calories' => 52, 'carbs' => 14, 'portion' => 100],
    'banana' => ['calories' => 89, 'carbs' => 23, 'portion' => 100],
    'chicken_breast' => ['calories' => 165, 'carbs' => 0, 'portion' => 100],
    'brown_rice' => ['calories' => 123, 'carbs' => 26, 'portion' => 100],
    'broccoli' => ['calories' => 35, 'carbs' => 7, 'portion' => 100],
    'salmon' => ['calories' => 206, 'carbs' => 0, 'portion' => 100],
    'oatmeal' => ['calories' => 68, 'carbs' => 12, 'portion' => 100],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiaHealth - Meal Consumption</title>
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
        <li><a href="dashboard.php"><i>📊</i> Dashboard</a></li>
        <li><a href="healthdata.php"><i>📝</i> Health Data Input</a></li>
        <li><a href="ai_analysis.php"><i>📈</i> AI Analysis</a></li>
        <li><a href="meal.php" class="active"><i>❤️</i> Meal</a></li>
        <li><a href="educational.php"><i>📚</i> Education</a></li>
        <li><a href="index.php"><i>🚪</i> Logout</a></li>
    </ul>
    <div class="footer">
        DiaHealth AI Guide © 2025<br>
        Your Personal Health Companion
    </div>
</div>
<div class="main-content">
    <h1 class="page-title">Meal Consumption</h1>
    <div class="card">
        <h2 class="card-title">Your Personalized Meal Consumption Chart!!</h2>
        <p class="card-subtitle">View and manage your daily meal plan for optimal health.</p>

        <div class="tabs">
            <div class="tab active" data-tab="view">View Meal</div>
            <div class="tab" data-tab="add">Add Meal Item</div>
        </div>

        <!-- View Diet Tab -->
        <div id="view-tab" class="tab-content active">
            <?php foreach ($meal_types as $type): ?>
                <h3><?php echo ucfirst($type); ?></h3>
                <?php if (empty($diet_items[$type])): ?>
                    <p>No items added for <?php echo ucfirst($type); ?>.</p>
                <?php else: ?>
                    <table class="diet-table">
                        <thead>
                            <tr>
                                <th>Food Item</th>
                                <th>Portion Size</th>
                                <th>Calories</th>
                                <th>Carbs (g)</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diet_items[$type] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['food_item']); ?></td>
                                    <td><?php echo htmlspecialchars($item['portion_size']); ?></td>
                                    <td><?php echo htmlspecialchars($item['calories']); ?></td>
                                    <td><?php echo htmlspecialchars($item['carbs']); ?></td>
                                    <td><?php echo htmlspecialchars($item['notes'] ?: 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Add Diet Item Tab -->
        <form id="add-tab" class="tab-content" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="meal-type">Meal Type</label>
                    <select id="meal-type" name="meal_type" class="dropdown" required>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                        <option value="snack">Snack</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="food-item">Food Item</label>
                    <select id="food-item" name="food_item" class="dropdown" required>
                        <option value="">Select Food Item</option>
                        <?php foreach ($food_items as $food => $data): ?>
                            <option value="<?php echo $food; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $food)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="portion-multiplier">Portion Multiplier (1-50)</label>
                    <input type="number" id="portion-multiplier" name="portion_multiplier" class="form-control" min="1" max="50" value="1" required>
                </div>
                <div class="form-group">
                    <label for="portion-size">Portion Size (g)</label>
                    <input type="text" id="portion-size" name="portion_size" class="form-control" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="calories">Calories</label>
                    <input type="number" id="calories" name="calories" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="carbs">Carbs (g)</label>
                    <input type="number" id="carbs" name="carbs" class="form-control" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Add any notes about this food item"></textarea>
                </div>
            </div>
            <button type="submit" name="diet_submit" class="btn btn-primary">Save Meal Item</button>
        </form>
    </div>
</div>

<script>
    // Food items nutritional data
    const foodData = <?php echo json_encode($food_items); ?>;

    // Get form elements
    const foodSelect = document.getElementById('food-item');
    const portionMultiplier = document.getElementById('portion-multiplier');
    const portionSizeInput = document.getElementById('portion-size');
    const caloriesInput = document.getElementById('calories');
    const carbsInput = document.getElementById('carbs');

    // Function to update nutritional values
    function updateNutritionalValues() {
        const selectedFood = foodSelect.value;
        const multiplier = parseInt(portionMultiplier.value) || 1;

        if (selectedFood && foodData[selectedFood]) {
            const data = foodData[selectedFood];
            const portion = data.portion * multiplier;
            const calories = data.calories * multiplier;
            const carbs = data.carbs * multiplier;

            portionSizeInput.value = portion + 'g';
            caloriesInput.value = Math.round(calories);
            carbsInput.value = Math.round(carbs);
        } else {
            portionSizeInput.value = '';
            caloriesInput.value = '';
            carbsInput.value = '';
        }
    }

    // Event listeners
    foodSelect.addEventListener('change', updateNutritionalValues);
    portionMultiplier.addEventListener('input', () => {
        // Ensure multiplier is between 1 and 50
        if (portionMultiplier.value < 1) portionMultiplier.value = 1;
        if (portionMultiplier.value > 50) portionMultiplier.value = 50;
        updateNutritionalValues();
    });

    // Tab switching logic
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
        });
    });
</script>
</body>
</html>