<?php
include 'config.php';

$bus_id = intval($_GET['bus_id']);
$sql = "
SELECT b.*, r.route_name_th, t.bt_id, t.bt_name
FROM bus_info b
LEFT JOIN route r ON b.br_id = r.route_number
LEFT JOIN bus_type t ON b.bus_type_id = t.bt_id
WHERE b.bus_id = $bus_id
";
$result = $conn->query($sql);

if($result && $result->num_rows > 0){
    $row = $result->fetch_assoc();
    echo json_encode($row);
}else{
    echo json_encode(['error'=>'ไม่พบข้อมูลรถ']);
}
