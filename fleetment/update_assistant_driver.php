<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Method not allowed');
}

$em_id = intval($_POST['em_id'] ?? 0);
$es_id = intval($_POST['es_id'] ?? 0);

if ($em_id <= 0 || $es_id == 0) {
    exit('ข้อมูลไม่ครบถ้วน');
}

// กรณีอัปเดตสถานะเฉพาะ (ajax)
if (!isset($_POST['em_name']) && !isset($_POST['em_surname']) && !isset($_POST['main_route'])) {
    $stmt = $conn->prepare("UPDATE employee SET es_id = ? WHERE em_id = ?");
    $stmt->bind_param("ii", $es_id, $em_id);
    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

// กรณีเพิ่ม / แก้ไขข้อมูลเต็ม
$main_route = intval($_POST['main_route'] ?? 0);
$em_name = trim($_POST['em_name'] ?? '');
$em_surname = trim($_POST['em_surname'] ?? '');

if ($em_id === 0) {
    // เพิ่มใหม่
    if ($em_name === '' || $em_surname === '' || $es_id == 0 || $main_route == 0) {
        exit('ข้อมูลไม่ครบถ้วน');
    }

    $stmt = $conn->prepare("INSERT INTO employee (em_name, em_surname, et_id, es_id, main_route) VALUES (?, ?, 2, ?, ?)");
    $stmt->bind_param("ssii", $em_name, $em_surname, $es_id, $main_route);
    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
} else {
    // แก้ไข
    if ($em_name === '' || $em_surname === '' || $es_id == 0 || $main_route == 0) {
        exit('ข้อมูลไม่ครบถ้วน');
    }

    $stmt = $conn->prepare("UPDATE employee SET em_name = ?, em_surname = ?, es_id = ?, main_route = ? WHERE em_id = ?");
    $stmt->bind_param("ssiii", $em_name, $em_surname, $es_id, $main_route, $em_id);
    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}
?>
