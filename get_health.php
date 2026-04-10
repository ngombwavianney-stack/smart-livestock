<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

$sql = "SELECT * FROM animal_health ORDER BY id DESC";
$result = $conn->query($sql);

$records = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

echo json_encode($records);
$conn->close();
?>
