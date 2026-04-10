<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

if (!isset($_GET['tagId']) || empty($_GET['tagId'])) {
    echo json_encode(["error" => "Tag ID required"]);
    exit;
}

$tagId = $conn->real_escape_string($_GET['tagId']);

// Get animal info
$sql = "SELECT * FROM animals WHERE tagId = '$tagId'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $animal = $result->fetch_assoc();
    
    // Get latest health status
    $healthSql = "SELECT status, temperature FROM animal_health WHERE tagid = '$tagId' ORDER BY id DESC LIMIT 1";
    $healthResult = $conn->query($healthSql);
    
    $isSick = false;
    $temperature = null;
    
    if ($healthResult && $healthResult->num_rows > 0) {
        $health = $healthResult->fetch_assoc();
        $isSick = ($health['status'] == 'Sick' || $health['status'] == 'Critical');
        $temperature = $health['temperature'];
    }
    
    echo json_encode([
        "success" => true,
        "tagId" => $animal['tagId'],
        "name" => $animal['animalname'],
        "type" => $animal['animaltype'],
        "isSick" => $isSick,
        "temperature" => $temperature
    ]);
} else {
    echo json_encode(["error" => "Animal not found", "tagId" => $tagId]);
}

$conn->close();
?>
