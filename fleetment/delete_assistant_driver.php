<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = isset($_POST['em_id']) ? (int)$_POST['em_id'] : 0;
    if ($em_id > 0) {
        $stmt = $conn->prepare("DELETE FROM employee WHERE em_id=? AND et_id=2");
        $stmt->bind_param("i", $em_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "ลบไม่สำเร็จ: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ข้อมูลไม่ถูกต้อง";
    }
}
?>
