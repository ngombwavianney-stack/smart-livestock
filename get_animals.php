<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php";

// Check if created_at column exists, if not use tagId for ordering
$checkColumn = $conn->query("SHOW COLUMNS FROM animals LIKE 'created_at'");
if ($checkColumn->num_rows > 0) {
    $sql = "SELECT * FROM animals ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM animals ORDER BY tagId DESC";
}
$result = $conn->query($sql);

$animals = array();
while ($row = $result->fetch_assoc()) {
    $animals[] = $row;
}

echo json_encode($animals);
$conn->close();
?>
