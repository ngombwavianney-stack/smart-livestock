<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

if (!isset($_GET['tagId']) || empty($_GET['tagId'])) {
    echo json_encode(["error" => "Valid Tag ID is required"]);
    exit;
}

$tagId = $conn->real_escape_string($_GET['tagId']);

// First delete health records
$healthDelete = $conn->prepare("DELETE FROM animal_health WHERE tagid = ?");
$healthDelete->bind_param("s", $tagId);
$healthDelete->execute();
$healthDelete->close();

// Then delete animal
$sql = "DELETE FROM animals WHERE tagId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tagId);

if ($stmt->execute()) {
    echo json_encode(["message" => "Animal and related health records deleted successfully"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
