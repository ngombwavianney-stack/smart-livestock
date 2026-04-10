<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

// Get the most recent health record (from ESP32 scans)
$sql = "SELECT h.*, a.animalname 
        FROM animal_health h 
        LEFT JOIN animals a ON h.tagid = a.tagId 
        ORDER BY h.id DESC LIMIT 1";
        
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "tagId" => $row['tagid'],
        "scan_time" => $row['createdat'],
        "animal_name" => $row['animalname'],
        "status" => $row['status'],
        "temperature" => $row['temperature']
    ]);
} else {
    echo json_encode(["error" => "No scans found"]);
}

$conn->close();
?>
