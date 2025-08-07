<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = intval($_POST['em_id'] ?? 0);
    $es_id = intval($_POST['es_id'] ?? 0);

    if ($em_id === 0 || $es_id === 0) {
        exit('ข้อมูลไม่ครบ');
    }

    $stmt = $conn->prepare("UPDATE employee SET es_id = ? WHERE em_id = ? AND et_id = 3");
    $stmt->bind_param("ii", $es_id, $em_id);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
}
?>
