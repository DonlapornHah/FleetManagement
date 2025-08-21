<?php
include 'config.php';

$plan_id = 1;
$sql = "SELECT * FROM plan_route_wide WHERE plan_id = $plan_id";
$result = $conn->query($sql);
$plan = $result->fetch_assoc();

// กำหนดจุดจอดหลักที่จะโชว์
$stops = [
    'stop_bkk_station' => ['label'=>'กรุงเทพ สถานีเดินรถนครชัยแอร์','icon'=>'fa-flag'],
    'stop_bkk_mochit' => ['label'=>'กรุงเทพหมอชิต','icon'=>'fa-bus'],
    'stop_rangsit' => ['label'=>'รังสิต','icon'=>'fa-map-marker-alt'],
    'stop_wangnoi' => ['label'=>'วังน้อย','icon'=>'fa-map-marker-alt'],
    'stop_lamtakong_pump' => ['label'=>'ลำตะคอง (ปั๊ม)','icon'=>'fa-gas-pump'],
    'stop_lamtakong_change' => ['label'=>'ลำตะคอง (เปลี่ยนพ่วง)','icon'=>'fa-exchange-alt'],
    'stop_kk3_station' => ['label'=>'สถานีขอนแก่น','icon'=>'fa-flag-checkered']
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Step Progress Bar On Line</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; text-align:center; }
h2 { margin-bottom: 10px; }
.step-container-wrapper { overflow-x: auto; padding-bottom: 20px; }
.step-container {
    position: relative; display: flex; justify-content: center; align-items: center;
    min-width: 900px; padding: 60px 0;
}
.step-container::before {
    content: '';
    position: absolute; top: 50%; left: 0; width: 100%; height: 6px;
    background: linear-gradient(to right, #007bff, #28a745); border-radius: 3px;
    z-index: 1; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.step {
    position: relative; text-align: center; z-index: 2;
    display: flex; flex-direction: column; align-items: center;
    margin: 0 25px;
}
.step .circle {
    width: 50px; height: 50px; border-radius: 50%; background: #6c757d;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 22px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    transition: all 0.3s; cursor: pointer;
    position: absolute;
    top: 50%; transform: translateY(-50%); /* ชิดเส้นตรงกลาง */
}
.step.start .circle, .step.end .circle {
    width: 70px; height: 70px; font-size: 28px;
}
.step p {
    font-size: 14px; line-height: 1.4; color: #333; text-align: center;
    margin-top: 60px; /* ข้อความอยู่ใต้เส้น */
}
.tooltip-box {
    display: none; background: #fff; padding: 8px 12px; border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15); font-size: 12px;
    position: absolute; top: -50px; left: 50%; transform: translateX(-50%);
    white-space: nowrap; z-index: 10;
}
</style>
</head>
<body>

<h2>เส้นทางเดินรถ : <?= htmlspecialchars($plan['route_number']) ?></h2>
<p>
    ประเภณะแผน: <?= htmlspecialchars($plan['plan_type']) ?> | 
    เวลาออก: <?= htmlspecialchars($plan['quetime']) ?> | 
    ระยะทางรวม: <?= htmlspecialchars($plan['total_distance']) ?> กม.
</p>

<div class="step-container-wrapper">
<div class="step-container">
<?php
$first = true;
$lastKey = array_key_last($stops);
foreach($stops as $field => $info) {
    if(empty($plan[$field])) continue;

    $cls = '';
    if($first) { $cls = 'start'; $first = false; }
    elseif($field === $lastKey) { $cls = 'end'; }

    echo '<div class="step '.$cls.'">';
    echo '<div class="circle"><i class="fas '.$info['icon'].'"></i></div>';
    echo '<p>'.htmlspecialchars($plan[$field]).'<br>('.$info['label'].')</p>';

    // Tooltip
    echo '<div class="tooltip-box">';
    echo 'เวลาออก: '.($plan['quetime'] ?? '-').'<br>';
    echo 'ระยะทางรวม: '.($plan['total_distance'] ?? '-').' กม.<br>';
    echo 'รถ ID: '.($plan['bus_id'] ?? '-');
    echo '</div>';

    echo '</div>';
}
?>
</div>
</div>

<script>
// Tooltip แสดงเมื่อ hover
document.querySelectorAll('.step').forEach(step=>{
    const tooltip = step.querySelector('.tooltip-box');
    const circle = step.querySelector('.circle');
    circle.addEventListener('mouseenter', ()=>{ tooltip.style.display='block'; });
    circle.addEventListener('mouseleave', ()=>{ tooltip.style.display='none'; });
});
</script>

</body>
</html>
