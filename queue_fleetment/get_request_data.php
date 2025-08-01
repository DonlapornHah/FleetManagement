<?php
include 'config.php';
$route = [2, 3, 4];
$sql = "SELECT * FROM queue_request WHERE br_id IN (" . implode(',', $route) . ")";
$result = mysqli_query($conn, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[$row['br_id']] = json_decode($row['qr_request'], true);
}

header('Content-Type: application/json');
echo json_encode($data);
