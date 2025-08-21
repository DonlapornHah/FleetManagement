<?php
include 'config.php';

// รับค่าจากฟอร์ม
$plan_name      = $_POST['plan_name'];
$route_number   = $_POST['route_number'];
$plan_type      = $_POST['plan_type'] ?? '';
$bus_id         = $_POST['bus_id'] ?? '';
$total_distance = $_POST['total_distance'] ?? 0;
$total_time     = $_POST['total_time'] ?? '00:00:00';
$quetime        = $_POST['quetime'] ?? '00:00:00';
$send_point1    = $_POST['junction1'] ?? '';
$send_point2    = $_POST['junction2'] ?? '';
$selected_stops = $_POST['selected_stop'] ?? [];

// รายชื่อจุดจอดทั้งหมด
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

// เตรียมคอลัมน์และค่า
$cols = ['plan_name','route_number','plan_type','bus_id','junction1','junction2','total_distance','total_time','quetime'];
$placeholders = array_fill(0, count($cols), '?');
$values = [$plan_name,$route_number,$plan_type,$bus_id,$send_point1,$send_point2,$total_distance,$total_time,$quetime];
$types = 'ssssssdds'; // s=string, d=double

// เพิ่มคอลัมน์จุดจอด
foreach($all_stops as $stop){
    $cols[] = $stop;
    $placeholders[] = '?';
    $values[] = in_array($stop,$selected_stops) ? '1' : '0';
    $types .= 's';
}

// สร้าง SQL
$sql = "INSERT INTO plan_route_wide (".implode(',', $cols).") VALUES (".implode(',', $placeholders).")";

$stmt = $conn->prepare($sql);
if(!$stmt){
    die("Prepare failed: ".$conn->error);
}

// bind_param แบบ dynamic
$stmt->bind_param($types, ...$values);

if($stmt->execute()){
    // เพิ่มเสร็จแล้ว แจ้ง alert และกลับหน้า plan.php
    echo "<script>
            alert('เพิ่มแผนเรียบร้อยแล้ว!');
            window.location.href='plan.php';
          </script>";
    exit;
}else{
    echo "Error: " . $stmt->error;
}
?>
