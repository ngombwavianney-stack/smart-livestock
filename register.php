<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['tagId'])) {
    echo json_encode(["error" => "No tag ID received"]);
    exit;
}

$tagId = $data['tagId'];
file_put_contents("last_tag.txt", $tagId);

echo json_encode(["success" => true, "tagId" => $tagId, "timestamp" => date("Y-m-d H:i:s")]);
?>
