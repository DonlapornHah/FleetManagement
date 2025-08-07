<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = intval($_POST['em_id'] ?? 0);
    $name = trim($_POST['em_name'] ?? '');
    $surname = trim($_POST['em_surname'] ?? '');
    $es_id = intval($_POST['es_id'] ?? 1);
    $main_route = intval($_POST['main_route'] ?? 0);  // เพิ่มรับค่า main_route

    // Debug เช็คค่าที่รับมา
    file_put_contents('debug_log.txt', "name=$name, surname=$surname, em_id=$em_id, es_id=$es_id, main_route=$main_route\n", FILE_APPEND);

    if ($name === '' || $surname === '') {
        exit("ชื่อและนามสกุลห้ามว่าง");
    }
    if (!in_array($es_id, [1,3])) {
        exit("สถานะไม่ถูกต้อง");
    }
    if ($main_route <= 0) {
        exit("กรุณาเลือกสายรถประจำ");
    }

    if ($em_id > 0) {
        // อัปเดตข้อมูล
        $stmt = $conn->prepare("UPDATE employee SET em_name = ?, em_surname = ?, es_id = ?, main_route = ? WHERE em_id = ? AND et_id = 2");
        $stmt->bind_param("ssiii", $name, $surname, $es_id, $main_route, $em_id);
    } else {
        $et_id = 2;
        // เพิ่มข้อมูลใหม่
        $stmt = $conn->prepare("INSERT INTO employee (em_name, em_surname, et_id, es_id, main_route) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiii", $name, $surname, $et_id, $es_id, $main_route);
    }

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}

?>
