<?php
include 'config.php';

// ดึงข้อมูลสายรถทั้งหมด
$sql = "SELECT route_number, route_name_th, type_route, distance_km, total_time FROM route ORDER BY route_number ASC";
$result = $conn->query($sql);

// ตั้ง header ให้ดาวน์โหลดเป็น CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=routes.csv');

$output = fopen('php://output', 'w');

// หัวตาราง
fputcsv($output, ['เลขสาย', 'ชื่อสายเดินรถ', 'ประเภท', 'ระยะทาง(กม.)', 'เวลา(นาที)']);

// ข้อมูล
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        fputcsv($output, [$row['route_number'], $row['route_name_th'], $row['type_route'], $row['distance_km'], $row['total_time']]);
    }
}
fclose($output);
exit;
?>
