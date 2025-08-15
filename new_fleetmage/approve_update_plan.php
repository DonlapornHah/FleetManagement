<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $plan_id = intval($_POST['plan_id']);
    $plan_type = $_POST['plan_type'] ?? '';

    // ดึงสถานะปัจจุบันของแผน
    $sqlCheck = "SELECT plan_approved FROM plan_route_wide WHERE plan_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $plan_id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($currentApproved);
    $stmtCheck->fetch();
    $stmtCheck->close();

    // ถ้า approved = 1 ให้เปลี่ยนเป็น 0 (ยกเลิกอนุมัติ)  
    // ถ้า approved = 0 ให้เปลี่ยนเป็น 1 (อนุมัติ)
    $newApproved = $currentApproved == 1 ? 0 : 1;

    $sqlUpdate = "UPDATE plan_route_wide SET plan_approved = ?, plan_type = ? WHERE plan_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("isi", $newApproved, $plan_type, $plan_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // ส่งกลับไปยังหน้าหลัก
    header("Location: plan.php");
    exit;
}
?>
