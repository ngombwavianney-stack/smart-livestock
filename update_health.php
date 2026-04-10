<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$response = [];

$input = file_get_contents("php://input");

if (empty($input)) {
    $response["error"] = "No data received";
    echo json_encode($response);
    exit;
}

$data = json_decode($input, true);

if (!$data) {
    $response["error"] = "Invalid JSON data";
    echo json_encode($response);
    exit;
}

if (!isset($data['id']) || empty($data['id'])) {
    $response["error"] = "Record ID is required";
    echo json_encode($response);
    exit;
}

$id = intval($data['id']);
$tagId = $conn->real_escape_string($data['tagId']);
$type = isset($data['type']) ? $conn->real_escape_string($data['type']) : '';
$status = isset($data['status']) ? $conn->real_escape_string($data['status']) : '';
$notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
$temperature = isset($data['temperature']) ? floatval($data['temperature']) : null;
$startdate = (isset($data['startdate']) && !empty($data['startdate'])) ? $data['startdate'] : null;
$enddate = (isset($data['enddate']) && !empty($data['enddate'])) ? $data['enddate'] : null;
$nexteventdate = (isset($data['nexteventdate']) && !empty($data['nexteventdate'])) ? $data['nexteventdate'] : null;
$vetname = isset($data['vetName']) ? $conn->real_escape_string($data['vetName']) : '';
$vetcontact = isset($data['vetcontact']) ? $conn->real_escape_string($data['vetcontact']) : '';
$iscurrent = isset($data['iscurrent']) ? intval($data['iscurrent']) : 1;

$sql = "UPDATE animal_health SET tagid = ?, type = ?, status = ?, notes = ?, temperature = ?, startdate = ?, enddate = ?, nexteventdate = ?, vetname = ?, vetcontact = ?, iscurrent = ? WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssdsssssii", $tagId, $type, $status, $notes, $temperature, $startdate, $enddate, $nexteventdate, $vetname, $vetcontact, $iscurrent, $id);

if ($stmt->execute()) {
    $response["message"] = "Health record updated successfully";
} else {
    $response["error"] = "Database error: " . $stmt->error;
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>
