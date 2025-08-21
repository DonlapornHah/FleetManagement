<?php
include 'config.php';

$br_id           = intval($_POST['br_id']);
$bus_number      = $_POST['bus_number'];
$full_bus_number = $_POST['full_bus_number'];
$license_plate   = $_POST['license_plate'];
$engine_number   = $_POST['engine_number'];
$chassis_number  = $_POST['chassis_number'];
$in_service      = intval($_POST['in_service']);
$notes           = $_POST['notes'];

// insert
$sql = "
INSERT INTO bus_info (br_id, bus_number, full_bus_number, license_plate, engine_number, chassis_number, in_service, notes)
VALUES ($br_id, '$bus_number', '$full_bus_number', '$license_plate', '$engine_number', '$chassis_number', $in_service, '$notes')
";

if($conn->query($sql)){
    $bus_id = $conn->insert_id;

    // ดึงข้อมูลล่าสุด (ป้องกัน JOIN ซ้ำ)
    $sql2 = "
    SELECT 
        b.bus_id,
        b.bus_number,
        b.full_bus_number,
        b.license_plate,
        b.engine_number,
        b.chassis_number,
        b.bus_type_id,
        b.in_service,
        b.notes,
        r.route_name_th,
        t.bt_name,
        -- ดึงคนขับหลักคนเดียว
        (SELECT CONCAT(d.first_name,' ',d.last_name)
         FROM drivers d 
         WHERE d.bus_id = b.bus_id
         ORDER BY d.id ASC LIMIT 1) AS main_driver_name
    FROM bus_info b
    LEFT JOIN route r ON b.br_id = r.route_number
    LEFT JOIN bus_type t ON b.bus_type_id = t.bt_id
    WHERE b.bus_id = $bus_id
    ";
    $res = $conn->query($sql2);
    $row = $res->fetch_assoc();

    echo json_encode(array_merge(['status'=>'success'], $row));
}else{
    echo json_encode(['status'=>'error','message'=>$conn->error]);
}
