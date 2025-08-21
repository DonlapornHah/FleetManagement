<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['plan_id'])) {
        die("<script>alert('ไม่มีแผนที่เลือก'); window.history.back();</script>");
    }

    $plan_id = intval($_POST['plan_id']);

    // ดึงค่าข้อมูลแผน
    $plan_name    = $conn->real_escape_string($_POST['plan_name'] ?? '');
    $route_number = $conn->real_escape_string($_POST['route_number'] ?? '');
    $plan_type    = $conn->real_escape_string($_POST['plan_type'] ?? '');
    $junction1    = $conn->real_escape_string($_POST['junction1'] ?? '');
    $junction2    = $conn->real_escape_string($_POST['junction2'] ?? '');
    $total_distance = floatval($_POST['total_distance'] ?? 0);
    $total_time   = $conn->real_escape_string($_POST['total_time'] ?? '');
    $quetime      = $conn->real_escape_string($_POST['quetime'] ?? '');

    // รายชื่อคอลัมน์จุดจอด
    $stops_columns = [
        'stop_bkk_station','stop_bkk_mochit','stop_rangsit','stop_wangnoi','stop_prademchai',
        'stop_lamtakong_pump','stop_police_khlongphai','stop_lamtakong_change','stop_banmittraphap_change',
        'stop_lanphakdi','stop_korat_bus_station','stop_bansom','stop_taladkae','stop_nontaether',
        'stop_police_sida','stop_sida','stop_sida_station','stop_bualai','stop_police_amphoe_phon',
        'stop_muangphon','stop_amphoe_phon_station','stop_nonsila','stop_banphai','stop_banphai_station',
        'stop_bankeng','stop_meechai_center','stop_banhed','stop_sirindhorn_hospital','stop_dongklang',
        'stop_nongbuadee','stop_thapra','stop_bankudkwang','stop_khonkaen_airstation','stop_jaerongsri',
        'stop_mtec','stop_kk3_station'
    ];

    // ตั้งค่าเริ่มต้นทุกจุดจอดเป็น 0
    $update_values = [];
    foreach ($stops_columns as $col) {
        $update_values[$col] = 0;
    }

    // ถ้ามี selected_stop ให้เซ็ตเป็น 1
    if (isset($_POST['selected_stop']) && is_array($_POST['selected_stop'])) {
        foreach ($_POST['selected_stop'] as $selected) {
            if (in_array($selected, $stops_columns)) {
                $update_values[$selected] = 1;
            }
        }
    }

    // สร้าง SQL สำหรับจุดจอด
    $set_sql_stops = [];
    foreach ($update_values as $col => $val) {
        $set_sql_stops[] = "`$col` = $val";
    }

    // รวม SQL ของข้อมูลแผนและจุดจอด + quetime
    $set_sql = array_merge([
        "`plan_name` = '$plan_name'",
        "`route_number` = '$route_number'",
        "`plan_type` = '$plan_type'",
        "`junction1` = '$junction1'",
        "`junction2` = '$junction2'",
        "`total_distance` = $total_distance",
        "`total_time` = '$total_time'",
        "`quetime` = '$quetime'"
    ], $set_sql_stops);

    $set_sql_str = implode(", ", $set_sql);

    $sql = "UPDATE plan_route_wide SET $set_sql_str WHERE plan_id = $plan_id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('บันทึกข้อมูลแผนและจุดจอดเรียบร้อยแล้ว');
                window.location.href = 'plan.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('เกิดข้อผิดพลาด: ".$conn->error."');
                window.history.back();
              </script>";
    }
} else {
    die("<script>alert('Invalid request method'); window.history.back();</script>");
}
?>
