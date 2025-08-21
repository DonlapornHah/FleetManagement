<?php
include 'config.php';

if(!isset($_FILES['file'])) {
    echo json_encode(['success'=>false, 'error'=>'ไม่มีไฟล์อัปโหลด']);
    exit;
}

$file = $_FILES['file']['tmp_name'];

if(!file_exists($file)) {
    echo json_encode(['success'=>false, 'error'=>'ไฟล์ไม่ถูกต้อง']);
    exit;
}

if(($handle = fopen($file, "r")) !== FALSE) {
    $row = 0;
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
        $row++;
        if($row == 1) continue; // ข้าม header

        $route_number = $conn->real_escape_string($data[0]);
        $route_name_th = $conn->real_escape_string($data[1]);
        $type_route = $conn->real_escape_string($data[2]);
        $distance_km = floatval($data[3]);
        $total_time = intval($data[4]);

        // แทรกหรืออัปเดตข้อมูล
        $sql = "INSERT INTO route (route_number, route_name_th, type_route, distance_km, total_time)
                VALUES ('$route_number', '$route_name_th', '$type_route', $distance_km, $total_time)
                ON DUPLICATE KEY UPDATE
                route_name_th='$route_name_th', type_route='$type_route', distance_km=$distance_km, total_time=$total_time";

        $conn->query($sql);
    }
    fclose($handle);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>'อ่านไฟล์ไม่สำเร็จ']);
}
?>
