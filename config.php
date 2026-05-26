<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbCandidates = [];

if (getenv("ARTIZO_DB_NAME")) {
    $dbCandidates[] = getenv("ARTIZO_DB_NAME");
}

$dbCandidates[] = "web_assignment";
$dbCandidates[] = "software_evo_assignment";
$dbCandidates = array_values(array_unique($dbCandidates));

// Create connection
$conn = null;
$lastConnectionError = "";

foreach ($dbCandidates as $dbname) {
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if (!$conn->connect_error) {
            break;
        }

        $lastConnectionError = $conn->connect_error;
        $conn = null;
    } catch (mysqli_sql_exception $exception) {
        $lastConnectionError = $exception->getMessage();
        $conn = null;
    }
}

// Check connection
if ($conn === null || $conn->connect_error) {
    http_response_code(500);
    error_log("Database connection failed: " . $lastConnectionError);
    echo "Database connection failed. Please start MySQL in XAMPP and verify the database configuration.";
    exit();
}
?>
