<?php
include 'config.php';

$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 1;
$levels = [
    1 => 'พัก',    // พัก = level 1
    2 => 'คิว'     // คิว = level 2
];

// ดึงพนักงานแต่ละประเภทตาม et_id
$employee_types = [
    1 => 'main',
    2 => 'ex',
    3 => 'coach'
];

foreach ($employee_types as $et_id => $type) {
    $sql = "SELECT em_id FROM employee WHERE et_id = $et_id ORDER BY RAND()";
    $result = mysqli_query($conn, $sql);
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row['em_id'];
    }

    $half = ceil(count($employees) / 2);
    $level_map = array_fill(0, $half, 1) + array_fill($half, count($employees) - $half, 2); // แบ่งครึ่งเป็นพัก/คิว

    foreach ($employees as $index => $em_id) {
        $level = $index < $half ? 1 : 2;
        $queue = "{$route_id}-{$level}-" . ($index + 1);

        // อัปเดต queue ใหม่
        $update = "UPDATE employee SET em_queue = '$queue' WHERE em_id = $em_id";
        mysqli_query($conn, $update);
    }
}

mysqli_close($conn);
header("Location: index.php"); // กลับไปหน้าเดิม
exit;
