<?php
include 'config.php';

// รับ JSON จาก JS
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['plans'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลแผนที่ส่งมา']);
    exit;
}

$plans     = $data['plans'];
$startDate = $data['startDate'] ?? '';
$endDate   = $data['endDate'] ?? '';
$todayOnly = !empty($data['todayOnly']);

if ($todayOnly) {
    $startDate = $endDate = date('Y-m-d');
}

// ตรวจสอบวันที่
if (!$startDate || !$endDate) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกวันที่']);
    exit;
}

$current = strtotime($startDate);
$endTime = strtotime($endDate);

while ($current <= $endTime) {
    $planDate = date('Y-m-d', $current);

    foreach ($plans as $p) {
        $plan_name  = $conn->real_escape_string($p['name']);
        $route      = $conn->real_escape_string($p['route']);
        $plan_type  = $conn->real_escape_string($p['type']);
        $junction1  = $conn->real_escape_string($p['junction1']);
        $junction2  = $conn->real_escape_string($p['junction2']);
        $distance   = floatval($p['distance']);
        $duration   = $conn->real_escape_string($p['duration']);
        $quetime    = !empty($p['time']) ? $conn->real_escape_string($p['time']) : null;

        // ตรวจสอบว่ามี record นี้แล้วหรือยัง
        $check_sql = "SELECT 1 FROM plan_schedule 
                      WHERE plan_name='$plan_name' 
                        AND route_number='$route' 
                        AND plan_date='$planDate' 
                        LIMIT 1";
        $check_res = $conn->query($check_sql);

        if ($check_res && $check_res->num_rows == 0) {
            $insert_sql = "INSERT INTO plan_schedule 
                (plan_name, route_number, quetime, plan_type, junction1, junction2, total_distance, total_time, plan_date, created_at)
                VALUES (
                    '$plan_name',
                    '$route',
                    " . ($quetime ? "'$quetime'" : "NULL") . ",
                    '$plan_type',
                    '$junction1',
                    '$junction2',
                    $distance,
                    '$duration',
                    '$planDate',
                    NOW()
                )";
            $conn->query($insert_sql);
        }
    }

    $current = strtotime("+1 day", $current);
}

echo json_encode(['success' => true, 'message' => 'บันทึกแผนเรียบร้อย']);
$conn->close();
