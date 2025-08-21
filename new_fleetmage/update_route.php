<?php
include 'config.php';

$route_id = intval($_POST['route_id']);
$route_name_th = $conn->real_escape_string($_POST['route_name_th']);
$type_route = $conn->real_escape_string($_POST['type_route']);
$distance_km = floatval($_POST['distance_km']);

// ลบ comma ก่อน WHERE
$sql = "UPDATE route SET 
          route_name_th='$route_name_th',
          type_route='$type_route',
          distance_km='$distance_km'
        WHERE route_id=$route_id";

if($conn->query($sql)){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>$conn->error]);
}
?>
