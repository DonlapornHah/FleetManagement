<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $license = trim($_POST['license'] ?? '');
    $route_id = (int)($_POST['route_id'] ?? 0);

    if ($fullname && $license && $route_id > 0) {
        // แยกชื่อกับนามสกุล (หากกรอกมาแบบ "ชื่อ นามสกุล")
        $name_parts = explode(' ', $fullname, 2);
        $em_name = $name_parts[0];
        $em_surname = $name_parts[1] ?? '';

        // ตรวจสอบว่ามีทะเบียนรถนี้หรือยัง
        $stmt = $conn->prepare("SELECT bi_id FROM bus_info WHERE bi_licen = ?");
        $stmt->bind_param("s", $license);
        $stmt->execute();
        $stmt->bind_result($bi_id);
        if ($stmt->fetch()) {
            $stmt->close();
        } else {
            // ยังไม่มีรถนี้ → เพิ่มใหม่
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO bus_info (bi_licen, br_id) VALUES (?, ?)");
            $stmt->bind_param("si", $license, $route_id);
            $stmt->execute();
            $bi_id = $stmt->insert_id;
            $stmt->close();
        }

        // เพิ่มพนักงาน
        $stmt = $conn->prepare("INSERT INTO employee (em_name, em_surname, main_car, et_id, es_id) VALUES (?, ?, ?, 1, 1)");
        $stmt->bind_param("ssi", $em_name, $em_surname, $bi_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ข้อมูลไม่ครบถ้วน";
    }
} else {
    echo "Invalid request method";
}
?>
