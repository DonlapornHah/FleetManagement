<?php
include 'config.php';

// อ่าน JSON จาก request
$input = json_decode(file_get_contents('php://input'), true);

// ดึงค่าจาก request
$plan_name    = $conn->real_escape_string($input['plan_name'] ?? '');
$route_number = $conn->real_escape_string($input['route_number'] ?? '');
$plan_type    = $conn->real_escape_string($input['plan_type'] ?? '');
$total_distance = floatval($input['total_distance'] ?? 0);
$total_time     = $conn->real_escape_string($input['total_time'] ?? '');
$junction1      = $conn->real_escape_string($input['junction1'] ?? '');
$junction2      = $conn->real_escape_string($input['junction2'] ?? '');

// อ่านค่า stops จาก request
$stops_input = $input['stops'] ?? [];

// กำหนดค่า default จุดจอด
$stop_columns = [
    'stop_bkk_station','stop_bkk_mochit','stop_rangsit','stop_wangnoi',
    'stop_prademchai','stop_lamtakong_pump','stop_police_khlongphai','stop_lamtakong_change',
    'stop_banmittraphap_change','stop_lanphakdi','stop_korat_bus_station','stop_bansom',
    'stop_taladkae','stop_nontaether','stop_police_sida','stop_sida',
    'stop_sida_station','stop_bualai','stop_police_amphoe_phon','stop_muangphon',
    'stop_amphoe_phon_station','stop_nonsila','stop_banphai','stop_banphai_station',
    'stop_bankeng','stop_meechai_center','stop_banhed','stop_sirindhorn_hospital',
    'stop_dongklang','stop_nongbuadee','stop_thapra','stop_bankudkwang',
    'stop_khonkaen_airstation','stop_jaerongsri','stop_mtec','stop_kk3_station'
];

// เตรียมค่า stops สำหรับ insert
$stop_values = [];
foreach($stop_columns as $col){
    $val = isset($stops_input[$col]) && $stops_input[$col] ? 1 : 0;
    $stop_values[$col] = $conn->real_escape_string($val);
}

// สร้าง SQL
$sql = "INSERT INTO plan_route_wide
(plan_name, route_number, plan_type, total_distance, total_time, junction1, junction2, " . implode(',', $stop_columns) . ")
VALUES
('$plan_name','$route_number','$plan_type','$total_distance','$total_time','$junction1','$junction2','" . implode("','", $stop_values) . "')
";

if($conn->query($sql)){
    $plan_id = $conn->insert_id;
    echo json_encode([
        'success'=>true,
        'plan'=>[
            'plan_id'=>$plan_id,
            'plan_name'=>$plan_name,
            'route_number'=>$route_number,
            'plan_type'=>$plan_type,
            'total_distance'=>$total_distance,
            'total_time'=>$total_time,
            'junction1'=>$junction1,
            'junction2'=>$junction2,
            'stops'=>$stop_values
        ]
    ]);
}else{
    echo json_encode([
        'success'=>false,
        'message'=>$conn->error
    ]);
}
