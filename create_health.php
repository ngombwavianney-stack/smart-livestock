<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$response = [];

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    $response["error"] = "No data received";
    echo json_encode($response);
    exit;
}

if (!isset($data["tagId"]) || empty($data["tagId"])) {
    $response["error"] = "Tag ID is required";
    echo json_encode($response);
    exit;
}

$tagId = $conn->real_escape_string($data["tagId"]);
$type = isset($data["type"]) ? $conn->real_escape_string($data["type"]) : "Checkup";
$status = isset($data["status"]) ? $conn->real_escape_string($data["status"]) : "Healthy";
$notes = isset($data["notes"]) ? $conn->real_escape_string($data["notes"]) : "";
$temperature = isset($data["temperature"]) ? floatval($data["temperature"]) : null;
$startdate = isset($data["startdate"]) && !empty($data["startdate"]) ? $data["startdate"] : date("Y-m-d");
$enddate = isset($data["enddate"]) && !empty($data["enddate"]) ? $data["enddate"] : null;
$nexteventdate = isset($data["nexteventdate"]) && !empty($data["nexteventdate"]) ? $data["nexteventdate"] : null;
$vetname = isset($data["vetName"]) ? $conn->real_escape_string($data["vetName"]) : "";
$vetcontact = isset($data["vetcontact"]) ? $conn->real_escape_string($data["vetcontact"]) : "";

// Set previous records as not current
$updateStmt = $conn->prepare("UPDATE animal_health SET iscurrent = 0 WHERE tagid = ?");
if ($updateStmt) {
    $updateStmt->bind_param("s", $tagId);
    $updateStmt->execute();
    $updateStmt->close();
}

$sql = "INSERT INTO animal_health (tagid, type, status, notes, temperature, startdate, enddate, nexteventdate, vetname, vetcontact, iscurrent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssdsssss", $tagId, $type, $status, $notes, $temperature, $startdate, $enddate, $nexteventdate, $vetname, $vetcontact);

if ($stmt->execute()) {
    $response["message"] = "Health record added successfully";
    $response["id"] = $stmt->insert_id;
} else {
    $response["error"] = $stmt->error;
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>
