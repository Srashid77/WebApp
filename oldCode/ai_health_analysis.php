<?php
include "db_connect.php";
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

// Handle AJAX request for AI analysis
if (isset($_GET['action']) && $_GET['action'] === 'analyze' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM healthinfo WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        if ($record) {
            $feedback = analyzeDiabetesRisk($record);
            echo json_encode(['success' => true, 'feedback' => $feedback]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Record not found']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit();
}

// Function to perform simple AI analysis for diabetes risk
function analyzeDiabetesRisk($record) {
    $bloodSugar = (float)$record['Blood Sugar Level'];
    $insulin = (float)$record['Insulin Level'];
    $age = (int)$record['Age'];
    $physicalActivity = $record['Physical Activity'];
    $nutritionHabits = $record['Nutrition Habits'];

    $riskFactors = 0;
    $feedback = '';

    if ($bloodSugar > 126) {
        $riskFactors++;
        $feedback .= "High blood sugar level ({$bloodSugar} mg/dL) indicates potential diabetes. ";
    }

    if ($insulin < 5 || $insulin > 25) {
        $riskFactors++;
        $feedback .= "Abnormal insulin level ({$insulin} mU/L) may suggest insulin issues. ";
    }

    if ($age > 45) {
        $riskFactors++;
        $feedback .= "Age ({$age}) increases diabetes risk. ";
    }

    if (empty($physicalActivity) || stripos($physicalActivity, 'none') !== false) {
        $riskFactors++;
        $feedback .= "Low physical activity increases diabetes risk. ";
    }

    if (stripos($nutritionHabits, 'high sugar') !== false || stripos($nutritionHabits, 'processed') !== false) {
        $riskFactors++;
        $feedback .= "Unhealthy nutrition habits increase diabetes risk. ";
    }

    if ($riskFactors >= 3) {
        $feedback = "High risk of diabetes detected. " . $feedback . "Consult a healthcare professional.";
    } elseif ($riskFactors >= 1) {
        $feedback = "Moderate risk of diabetes. " . $feedback . "Monitor your health and consult a doctor.";
    } else {
        $feedback = "No immediate diabetes concern. Continue healthy habits.";
    }

    return $feedback;
}

// Handle delete request
if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $sql = "DELETE FROM healthinfo WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $success_message = "Record deleted successfully.";
        } else {
            $error_message = "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Database error: " . $conn->error;
    }
}

// Fetch all records for the table
$sql = "SELECT * FROM healthinfo ORDER BY `Date` DESC";
$result = $conn->query($sql);
if (!$result) {
    $error_message = "Error fetching records: " . $conn->error;
}

// Check if ID is provided for editing
$row = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Fetch the record to edit
    $sql = "SELECT * FROM healthinfo WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error_message = "Database error: " . $conn->error;
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result_edit = $stmt->get_result();
        $row = $result_edit->fetch_assoc();
        if (!$row) {
            $error_message = "Record not found for editing.";
        }
        $stmt->close();
    }
}

// Handle form submission for updating the record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update']) && empty($error_message)) {
    // Sanitize and validate inputs
    $name = filter_var($_POST["name"] ?? '', FILTER_SANITIZE_STRING);
    $age = filter_var($_POST["age"] ?? '', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 120]]);
    $systolic = filter_var($_POST["systolic"] ?? '', FILTER_VALIDATE_INT, ["options" => ["min_range" => 70, "max_range" => 250]]);
    $diastolic = filter_var($_POST["diastolic"] ?? '', FILTER_VALIDATE_INT, ["options" => ["min_range" => 40, "max_range" => 150]]);
    $bloodSugar = filter_var($_POST["bloodSugar"] ?? '', FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 1, "max_range" => 600]]);
    $meal = filter_var($_POST["meal"] ?? '', FILTER_SANITIZE_STRING);
    $date = $_POST["date"] ?? '';
    $insulin = filter_var($_POST["insulin"] ?? '', FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 1, "max_range" => 600]]);
    $activity = filter_var($_POST["activity"] ?? '', FILTER_SANITIZE_STRING);
    $habits = filter_var($_POST["habits"] ?? '', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$name || $age === false || $systolic === false || $diastolic === false || $bloodSugar === false || !$meal || !$insulin) {
        $error_message = "Please fill all required fields with valid values.";
    } else {
        // Format the date properly for MySQL
        $formatted_date = !empty($date) 
            ? date('Y-m-d H:i:s', strtotime($date)) 
            : date('Y-m-d H:i:s');

        // Prepare SQL statement
        $sql = "UPDATE healthinfo SET 
                    `Name` = ?, `Age` = ?, `Blood Pressure (Systolic)` = ?, `Blood Pressure (Diastolic)` = ?, 
                    `Blood Sugar Level` = ?, `Meal` = ?, `Date` = ?, `Insulin Level` = ?, 
                    `Physical Activity` = ?, `Nutrition Habits` = ?
                WHERE ID = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "Error preparing statement: " . $conn->error;
        } else {
            // Bind parameters
            $stmt->bind_param("siiddssdsii", $name, $age, $systolic, $diastolic, $bloodSugar, $meal, $formatted_date, $insulin, $activity, $habits, $id);

            if ($stmt->execute()) {
                $success_message = "Record updated successfully.";
                // Refresh the table data after update
                $sql = "SELECT * FROM healthinfo ORDER BY `Date` DESC";
                $result = $conn->query($sql);
            } else {
                $error_message = "Error updating record: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Health Analysis | DiabetesMonitor</title>
    <link rel="stylesheet" href="styles1.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            margin-right: 5px;
        }
        .edit-btn {
            background-color: #2196F3;
            color: white;
            text-decoration: none;
        }
        .edit-btn:hover {
            background-color: #1976D2;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        .ai-analysis-section {
            margin-top: 20px;
        }
        .ai-analysis-section label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .ai-analysis-section input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .loading {
            display: none;
            color: #666;
            margin-top: 5px;
        }
        .error {
            color: #f44336;
            margin-top: 5px;
        }
    </style>
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
                <li class="active"><a href="ai_health_analysis.php"><span class="icon">🤖</span> AI Health Analysis</a></li>
                <li><a href="diet.php"><span class="icon">🍎</span> Personalized Diet Plans</a></li>
                <li><a href="educational.php"><span class="icon">📚</span> Educational Content</a></li>
                <li><a href="#"><span class="icon">📊</span> Dashboard</a></li>
            </ul>
        </aside>
        
        <main>
            <h1>AI Health Analysis</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <section id="data-table-section">
                <h2>Health Data Records</h2>
                <form id="tableForm" method="POST" action="">
                    <table>
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Blood Pressure (Sys/Dia)</th>
                                <th>Blood Sugar Level</th>
                                <th>Meal</th>
                                <th>Date</th>
                                <th>Insulin Level</th>
                                <th>Physical Activity</th>
                                <th>Nutrition Habits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($record = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <input type="radio" name="selected_id" value="<?php echo $record['ID']; ?>" class="record-radio">
                                        </td>
                                        <td><?php echo htmlspecialchars($record['ID']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Age']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Blood Pressure (Systolic)']) . '/' . htmlspecialchars($record['Blood Pressure (Diastolic)']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Blood Sugar Level']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Meal']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Date']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Insulin Level']); ?></td>
                                        <td><?php echo htmlspecialchars($record['Physical Activity']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($record['Nutrition Habits'], 0, 50)) . (strlen($record['Nutrition Habits']) > 50 ? '...' : ''); ?></td>
                                        <td>
                                            <a href="?id=<?php echo $record['ID']; ?>" class="action-btn edit-btn">Edit</a>
                                            <button type="submit" name="delete_id" value="<?php echo $record['ID']; ?>" class="action-btn delete-btn">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12">No records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
                <div class="ai-analysis-section">
                    <label for="ai_feedback">AI Health Analysis</label>
                    <input type="text" id="ai_feedback" name="ai_feedback" value="" readonly>
                    <div id="loading" class="loading">Analyzing...</div>
                    <div id="ai_error" class="error"></div>
                </div>
            </section>
            
            <?php if (isset($row)): ?>
                <section id="data-input-section">
                    <h2>Edit Health Data</h2>
                    <form id="healthDataForm" method="POST" action="">
                        <input type="hidden" name="update" value="1">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['Name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="number" id="age" name="age" min="1" max="120" value="<?php echo htmlspecialchars($row['Age']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bloodPressure">Blood Pressure (mmHg)</label>
                                <div class="blood-pressure-inputs">
                                    <input type="number" id="systolic" name="systolic" placeholder="Systolic" min="70" max="250" value="<?php echo htmlspecialchars($row['Blood Pressure (Systolic)']); ?>" required>
                                    <span>/</span>
                                    <input type="number" id="diastolic" name="diastolic" placeholder="Diastolic" min="40" max="150" value="<?php echo htmlspecialchars($row['Blood Pressure (Diastolic)']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bloodSugar">Blood Sugar Level (mg/dL)</label>
                                <input type="number" id="bloodSugar" name="bloodSugar" min="1" max="600" step="0.1" value="<?php echo htmlspecialchars($row['Blood Sugar Level']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="meal">Meal</label>
                                <input type="text" id="meal" name="meal" value="<?php echo htmlspecialchars($row['Meal']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="datetime-local" id="date" name="date" value="<?php echo !empty($row['Date']) ? date('Y-m-d\TH:i', strtotime($row['Date'])) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="insulin">Insulin Level</label>
                                <input type="number" id="insulin" name="insulin" min="1" max="600" step="0.1" value="<?php echo htmlspecialchars($row['Insulin Level']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="activity">Physical Activity</label>
                                <input type="text" id="activity" name="activity" value="<?php echo htmlspecialchars($row['Physical Activity']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="habits">Nutrition Habits</label>
                            <textarea id="habits" name="habits" rows="8" cols="130"><?php echo htmlspecialchars($row['Nutrition Habits']); ?></textarea>
                        </div>
                        <div class="button-center">
                            <button type="submit" class="submit-btn">Update Health Data</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Handle radio button selection for AI analysis
        document.querySelectorAll('.record-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const id = this.value;
                const feedbackInput = document.getElementById('ai_feedback');
                const loading = document.getElementById('loading');
                const errorDiv = document.getElementById('ai_error');

                // Clear previous feedback and errors
                feedbackInput.value = '';
                errorDiv.textContent = '';
                loading.style.display = 'block';

                // AJAX request for AI analysis
                fetch(`?action=analyze&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.style.display = 'none';
                        if (data.success) {
                            feedbackInput.value = data.feedback;
                        } else {
                            errorDiv.textContent = 'Error: ' + data.error;
                        }
                    })
                    .catch(error => {
                        loading.style.display = 'none';
                        errorDiv.textContent = 'Error: Failed to fetch analysis';
                    });
            });
        });

        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this record?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_id';
                    input.value = this.value;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Client-side validation for edit form
        const editForm = document.getElementById('healthDataForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const name = document.getElementById('name').value.trim();
                const age = parseInt(document.getElementById('age').value);
                const systolic = parseInt(document.getElementById('systolic').value);
                const diastolic = parseInt(document.getElementById('diastolic').value);
                const bloodSugar = parseFloat(document.getElementById('bloodSugar').value);
                const meal = document.getElementById('meal').value.trim();
                const insulin = parseFloat(document.getElementById('insulin').value);

                let errors = [];
                if (!name) errors.push('Name is required.');
                if (isNaN(age) || age < 1 || age > 120) errors.push('Age must be between 1 and 120.');
                if (isNaN(systolic) || systolic < 70 || systolic > 250) errors.push('Systolic BP must be between 70 and 250.');
                if (isNaN(diastolic) || diastolic < 40 || diastolic > 150) errors.push('Diastolic BP must be between 40 and 150.');
                if (isNaN(bloodSugar) || bloodSugar < 1 || bloodSugar > 600) errors.push('Blood Sugar must be between 1 and 600.');
                if (!meal) errors.push('Meal is required.');
                if (isNaN(insulin) || insulin < 1 || insulin > 600) errors.push('Insulin Level must be between 1 and 600.');

                if (errors.length > 0) {
                    e.preventDefault();
                    alert('Please fix the following errors:\n- ' + errors.join('\n- '));
                }
            });
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>