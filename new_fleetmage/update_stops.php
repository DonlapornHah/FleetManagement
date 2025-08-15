<?php
include 'config.php';

$plan_id = $_POST['plan_id'];
$selected_stops = $_POST['stops'] ?? [];

$stops = ['กท','สบ','ปากช่อง',/*...*/'ชร'];
$mandatory_stops = ['กท','นว','ชม'];

// ดึง total_distance, total_time เดิม
$res = $conn->query("SELECT total_distance, total_time FROM route_plan WHERE plan_id=$plan_id");
$row = $res->fetch_assoc();
$total_distance = $row['total_distance'];
$time_parts = explode(":", $row['total_time']);
$total_duration_min = intval($time_parts[0])*60 + intval($time_parts[1]);

// ดึง stop_info
$stop_info = [];
$info_res = $conn->query("SELECT * FROM stop_info");
while($r = $info_res->fetch_assoc()){
    $stop_info[$r['stop_code']] = $r;
}

// อัปเดทแต่ละ stop
$update_cols = [];
foreach($stops as $stop){
    $is_selected = in_array($stop, $selected_stops) || in_array($stop, $mandatory_stops);
    $col = 'stop_'.$stop;
    $update_cols[] = "$col = ".($is_selected ? 1 : 0);

    if($is_selected && isset($stop_info[$stop])){
        $total_distance += $stop_info[$stop]['distance_km'];
        $total_duration_min += $stop_info[$stop]['stop_time_min'];
    }
}

$total_time_str = sprintf("%02d:%02d", floor($total_duration_min/60), $total_duration_min%60);

// อัปเดทฐานข้อมูล
$update_sql = "UPDATE route_plan SET ".implode(',', $update_cols).", total_distance=$total_distance, total_time='$total_time_str' WHERE plan_id=$plan_id";
if($conn->query($update_sql)){
    echo "บันทึกสำเร็จ";
}else{
    echo "เกิดข้อผิดพลาด: ".$conn->error;
}
?>
