<?php
include 'config.php';

$route_id = intval($_GET['route_id']);
$sql = "SELECT * FROM route WHERE route_id = $route_id LIMIT 1";
$result = $conn->query($sql);

if($result && $result->num_rows > 0){
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error'=>'ไม่พบข้อมูล']);
}
?>
