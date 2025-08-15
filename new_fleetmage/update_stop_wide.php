<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['plan_id'])) {
        die("<script>alert('ไม่มีแผนที่เลือก'); window.history.back();</script>");
    }

    $plan_id = intval($_POST['plan_id']);

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

    $update_values = [];
    foreach ($stops_columns as $col) {
        $update_values[$col] = 0;
    }

    if (isset($_POST['selected_stop']) && is_array($_POST['selected_stop'])) {
        foreach ($_POST['selected_stop'] as $selected) {
            if (in_array($selected, $stops_columns)) {
                $update_values[$selected] = 1;
            }
        }
    }

    $set_sql = [];
    foreach ($update_values as $col => $val) {
        $set_sql[] = "`$col` = $val";
    }
    $set_sql_str = implode(", ", $set_sql);

    $sql = "UPDATE plan_route_wide SET $set_sql_str WHERE plan_id = $plan_id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('บันทึกจุดจอดเรียบร้อยแล้ว');
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
