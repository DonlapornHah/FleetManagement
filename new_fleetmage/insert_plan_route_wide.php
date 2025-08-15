<?php
include 'config.php';

$plan_name = $_POST['plan_name'];
$route_number = $_POST['route_number'];
$plan_type = $_POST['plan_type'] ?? '';
$bus_id = $_POST['bus_id'] ?? '';
$total_distance = $_POST['total_distance'] ?? '';
$total_time = $_POST['total_time'] ?? '';
$selected_stops = $_POST['selected_stop'] ?? [];

// เตรียมคอลัมน์จุดจอด
$stops_cols = [];
foreach($selected_stops as $stop){
    $stops_cols[$stop] = '1';
}

// ค่า default สำหรับทุกจุดจอด
$all_stops = [
    'stop_bkk_station','stop_bkk_mochit','stop_rangsit','stop_wangnoi','stop_prademchai',
    'stop_lamtakong_pump','stop_police_khlongphai','stop_lamtakong_change','stop_banmittraphap_change',
    'stop_lanphakdi','stop_korat_bus_station','stop_bansom','stop_taladkae','stop_nontaether',
    'stop_police_sida','stop_sida','stop_sida_station','stop_bualai','stop_police_amphoe_phon',
    'stop_muangphon','stop_amphoe_phon_station','stop_nonsila','stop_banphai','stop_banphai_station',
    'stop_bankeng','stop_meechai_center','stop_banhed','stop_sirindhorn_hospital','stop_dongklang',
    'stop_nongbuadee','stop_thapra','stop_bankudkwang','stop_khonkaen_airstation','stop_jaerongsri',
    'stop_mtec','stop_kk3_station'
];

$cols_sql = "plan_name, route_number, plan_type, bus_id, total_distance, total_time";
$vals_sql = "?, ?, ?, ?, ?, ?";

$values = [$plan_name, $route_number, $plan_type, $bus_id, $total_distance, $total_time];

foreach($all_stops as $stop){
    $cols_sql .= ", $stop";
    $vals_sql .= ", ?";
    $values[] = isset($stops_cols[$stop]) ? '1' : '0';
}

// Insert
$sql = "INSERT INTO plan_route_wide 
(plan_name, route_number, plan_type, send_point1, send_point2, total_distance, total_time)
VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssddd", $plan_name, $route_number, $plan_type, $send_point1, $send_point2, $total_distance, $total_time);
$stmt->execute();


if($stmt->execute()){
    header("Location: plan.php"); // กลับไปหน้าเดิม
    exit;
}else{
    echo "Error: " . $stmt->error;
}
