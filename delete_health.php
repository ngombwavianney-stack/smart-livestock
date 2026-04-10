<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["error" => "Valid ID is required"]);
    exit;
}

$id = intval($_GET['id']);

$sql = "DELETE FROM animal_health WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Health record deleted successfully"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
