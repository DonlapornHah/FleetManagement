<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = isset($_POST['em_id']) ? (int)$_POST['em_id'] : 0;
    $em_name = trim($_POST['em_name'] ?? '');
    $em_surname = trim($_POST['em_surname'] ?? '');
    $es_id = isset($_POST['es_id']) ? (int)$_POST['es_id'] : 1;

    // ตรวจสอบชื่อและนามสกุล
    if ($em_name === '' || $em_surname === '') {
        exit('ชื่อและนามสกุลห้ามว่าง');
    }

    if ($em_id > 0) {
        // แก้ไข
        $stmt = $conn->prepare("UPDATE employee SET em_name=?, em_surname=?, es_id=? WHERE em_id=? AND et_id=2");
        $stmt->bind_param("ssii", $em_name, $em_surname, $es_id, $em_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "แก้ไขไม่สำเร็จ: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // เพิ่มใหม่
        $et_id = 2; // พนักงานขับรถพ่วง
        $stmt = $conn->prepare("INSERT INTO employee (em_name, em_surname, et_id, es_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $em_name, $em_surname, $et_id, $es_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "เพิ่มไม่สำเร็จ: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
