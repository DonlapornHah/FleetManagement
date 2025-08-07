<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = isset($_POST['em_id']) ? (int)$_POST['em_id'] : 0;

    if ($em_id > 0) {
        // ลบพนักงาน (และอาจพิจารณาลบข้อมูลอื่นที่เชื่อมโยงด้วย ถ้ามี foreign key)
        $stmt = $conn->prepare("DELETE FROM employee WHERE em_id = ?");
        $stmt->bind_param("i", $em_id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid em_id";
    }
} else {
    echo "Invalid request method";
}
?>
