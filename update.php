<?php
include("db.php");

$data = json_decode(file_get_contents("php://input"), true);

$sql = "UPDATE animals 
SET name=?, animaltype=?, sex=?, breed=?, birthdate=?, ownerContact=? 
WHERE tagId=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssi",
    $data['name'],
    $data['animaltype'],
    $data['sex'],
    $data['breed'],
    $data['birthdate'],
    $data['ownerContact'],
    $data['tagId']
);

$stmt->execute();

echo json_encode(["message" => "Updated"]);
?>
