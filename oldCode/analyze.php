<?php
include "db_connect.php";
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// If no data is provided
if (!isset($data['id'])) {
    echo json_encode(['result' => 'No data selected for analysis']);
    exit;
}

$id = $data['id'];

// Fetch selected health data from the database
$sql = "SELECT * FROM healthinfo WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

// If no record found
if ($result->num_rows === 0) {
    echo json_encode(['result' => 'Data not found for the selected ID']);
    exit;
}

// Fetch the row data
$row = $result->fetch_assoc();

// Prepare the data for OpenAI prompt
$bloodSugarLevel = $row['Blood Sugar Level'];
$bloodPressureSystolic = $row['Blood Pressure (Systolic)'];
$bloodPressureDiastolic = $row['Blood Pressure (Diastolic)'];
$age = $row['Age'];
$meal = $row['Meal'];
$name = $row['Name'];

// Create a prompt for the OpenAI model
$prompt = "
Analyze the following health data and determine whether the person is at risk of diabetes or not:
Name: $name
Age: $age
Blood Sugar Level: $bloodSugarLevel mg/dL
Blood Pressure (Systolic): $bloodPressureSystolic mmHg
Blood Pressure (Diastolic): $bloodPressureDiastolic mmHg
Meal: $meal

Based on the information above, provide a recommendation on whether this person has diabetes or not. Include any relevant health advice.
";

// OpenAI API Key
$apiKey = "your-openai-api-key";  // Replace with your OpenAI API key

// OpenAI API request
$openaiUrl = "https://api.openai.com/v1/completions";
$data = [
    'model' => 'gpt-3.5-turbo',  // or use 'gpt-4' if available
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant who provides medical advice based on health data.'],
        ['role' => 'user', 'content' => $prompt],
    ],
    'temperature' => 0.7,
    'max_tokens' => 100
];

// cURL request to OpenAI API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $openaiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

// Parse the response from OpenAI
$responseData = json_decode($response, true);
$recommendation = $responseData['choices'][0]['message']['content'] ?? "Unable to analyze data";

// Return the recommendation
echo json_encode(['result' => $recommendation]);

// Close the connection
$conn->close();
?>
