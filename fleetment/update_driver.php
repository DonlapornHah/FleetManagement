<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $em_id = isset($_POST['em_id']) ? (int)$_POST['em_id'] : 0;
    $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;
    $driver_name = isset($_POST['driver_name']) ? trim($_POST['driver_name']) : '';
    $license = isset($_POST['license']) ? trim($_POST['license']) : '';

    if ($em_id > 0 && $driver_name !== '' && $license !== '' && in_array($status_id, [1,2,3,4])) {
        // แยกชื่อ-นามสกุล (ถ้ามี 2 คำขึ้นไป)
        $nameParts = explode(' ', $driver_name, 2);
        $em_name = $nameParts[0];
        $em_surname = isset($nameParts[1]) ? $nameParts[1] : '';

        // เริ่ม transaction
        $conn->begin_transaction();

        // อัปเดต employee ชื่อ, นามสกุล, สถานะ
        $stmt1 = $conn->prepare("UPDATE employee SET em_name = ?, em_surname = ?, es_id = ? WHERE em_id = ?");
        $stmt1->bind_param('ssii', $em_name, $em_surname, $status_id, $em_id);
        $res1 = $stmt1->execute();

        // อัปเดตทะเบียนรถประจำ (bus_info.bi_licen) ของรถที่พนักงานขับอยู่
        // ดึง bi_id จาก employee.main_car
        $stmt2 = $conn->prepare("SELECT main_car FROM employee WHERE em_id = ?");
        $stmt2->bind_param('i', $em_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $bi_id = 0;
        if ($row = $result->fetch_assoc()) {
            $bi_id = (int)$row['main_car'];
        }
        $stmt2->close();

        $res2 = true;
        if ($bi_id > 0) {
            $stmt3 = $conn->prepare("UPDATE bus_info SET bi_licen = ? WHERE bi_id = ?");
            $stmt3->bind_param('si', $license, $bi_id);
            $res2 = $stmt3->execute();
            $stmt3->close();
        }

        if ($res1 && $res2) {
            $conn->commit();
            echo "OK";
        } else {
            $conn->rollback();
            echo "Error updating data";
        }

        $stmt1->close();
    } else {
        echo "Invalid input";
    }
} else {
    echo "Invalid request method";
}
?>
