<?php
include 'config.php';

// อ่าน JSON จาก request
$input = json_decode(file_get_contents('php://input'), true);

$plans     = $input['plans'] ?? [];
$startDate = $input['startDate'] ?? '';
$endDate   = $input['endDate'] ?? '';
$todayOnly = isset($input['todayOnly']) && $input['todayOnly'] ? true : false;

if(empty($plans) || !$startDate){
    echo json_encode(['success'=>false,'message'=>'ไม่มีข้อมูลแผนหรือวันที่เริ่มต้น']);
    exit;
}

// ถ้าไม่ได้ส่ง endDate หรือ todayOnly = true
if($todayOnly || !$endDate) $endDate = $startDate;

// แปลงวันที่เป็น array
$period = [];
$current = strtotime($startDate);
$end     = strtotime($endDate);
while($current <= $end){
    $period[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

// key ของ stops ทั้งหมด
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

$conn->begin_transaction();
try {
    foreach($plans as $plan){
        $plan_id      = $plan['plan_id'] ?? 0;
        $plan_name    = $conn->real_escape_string($plan['name'] ?? '');
        $route_number = $conn->real_escape_string($plan['route'] ?? '');
        $quetime      = $conn->real_escape_string($plan['time'] ?? '');
        $plan_type    = $conn->real_escape_string($plan['type'] ?? '');
        $junction1    = $conn->real_escape_string($plan['junction1'] ?? '');
        $junction2    = $conn->real_escape_string($plan['junction2'] ?? '');
        $total_distance = floatval($plan['distance'] ?? 0);
        $total_time     = $conn->real_escape_string($plan['duration'] ?? '');
        $stops_input    = $plan['stops'] ?? [];

        // เตรียม stops สำหรับ insert
        $stop_values = [];
        foreach($stop_columns as $col){
            $val = isset($stops_input[$col]) && $stops_input[$col] ? 1 : 0;
            $stop_values[$col] = $conn->real_escape_string($val);
        }

        // ถ้า plan_id = 0 หรือไม่มีใน plan_route_wide ให้ insert แผนก่อน
        if(!$plan_id){
            $sqlPlan = "INSERT INTO plan_route_wide
                (plan_name, route_number, plan_type, total_distance, total_time, junction1, junction2, " . implode(',', $stop_columns) . ")
                VALUES
                ('$plan_name','$route_number','$plan_type','$total_distance','$total_time','$junction1','$junction2','" . implode("','", $stop_values) . "')
            ";
            if(!$conn->query($sqlPlan)){
                throw new Exception("Insert plan failed: " . $conn->error);
            }
            $plan_id = $conn->insert_id;
        }

        // insert schedule สำหรับทุกวัน
        foreach($period as $date){
            $sqlSchedule = "INSERT INTO plan_schedule
                (plan_id, plan_name, route_number, quetime, plan_type, junction1, junction2, total_distance, total_time, plan_date, " . implode(',', $stop_columns) . ")
                VALUES
                ('$plan_id','$plan_name','$route_number','$quetime','$plan_type','$junction1','$junction2','$total_distance','$total_time','$date','" . implode("','", $stop_values) . "')
            ";
            if(!$conn->query($sqlSchedule)){
                throw new Exception("Insert schedule failed: " . $conn->error);
            }
        }
    }

    $conn->commit();
    echo json_encode(['success'=>true]);
}catch(Exception $e){
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
