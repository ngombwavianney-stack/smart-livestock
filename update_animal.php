<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

if (!isset($data['tagId']) || empty($data['tagId'])) {
    echo json_encode(["error" => "Valid Tag ID is required"]);
    exit;
}

$tagId = $conn->real_escape_string($data['tagId']);
$animalname = isset($data['animalname']) ? $conn->real_escape_string($data['animalname']) : '';
$animaltype = isset($data['animaltype']) ? $conn->real_escape_string($data['animaltype']) : '';
$sex = isset($data['sex']) ? $conn->real_escape_string($data['sex']) : '';
$breed = isset($data['breed']) ? $conn->real_escape_string($data['breed']) : '';
$birthdate = isset($data['birthdate']) && !empty($data['birthdate']) ? $data['birthdate'] : null;
$ownerContact = isset($data['ownerContact']) ? $conn->real_escape_string($data['ownerContact']) : '';

$sql = "UPDATE animals SET animalname = ?, animaltype = ?, sex = ?, breed = ?, birthdate = ?, ownerContact = ? WHERE tagId = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $animalname, $animaltype, $sex, $breed, $birthdate, $ownerContact, $tagId);

if ($stmt->execute()) {
    echo json_encode(["message" => "Animal updated successfully"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
