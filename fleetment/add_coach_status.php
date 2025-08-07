<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id   = intval($_POST['em_id'] ?? 0);
    $name    = trim($_POST['em_name'] ?? '');
    $surname = trim($_POST['em_surname'] ?? '');
    $es_id   = intval($_POST['es_id'] ?? 0);
    $route   = intval($_POST['main_route'] ?? 0);

    if ($name === '' || $surname === '' || $es_id === 0 || $route === 0) {
        exit('กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    if ($em_id > 0) {
        // แก้ไขข้อมูลโค๊ช
        $stmt = $conn->prepare("UPDATE employee SET em_name=?, em_surname=?, es_id=?, main_route=? WHERE em_id=? AND et_id=3");
        $stmt->bind_param("ssiii", $name, $surname, $es_id, $route, $em_id);
    } else {
        // เพิ่มโค๊ชใหม่
        $stmt = $conn->prepare("INSERT INTO employee (em_name, em_surname, et_id, es_id, main_route) VALUES (?, ?, 3, ?, ?)");
        $stmt->bind_param("ssii", $name, $surname, $es_id, $route);
    }

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
}
?>
