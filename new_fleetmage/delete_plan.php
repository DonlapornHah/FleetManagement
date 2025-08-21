<?php
include 'config.php';

if (isset($_GET['plan_id'])) {
    $plan_id = intval($_GET['plan_id']);

    // ลบจาก plan_schedule ก่อน
    $stmt1 = $conn->prepare("DELETE FROM plan_schedule WHERE plan_id = ?");
    $stmt1->bind_param("i", $plan_id);
    $stmt1->execute();
    $stmt1->close();

    // ลบจาก plan_route_wide
    $stmt2 = $conn->prepare("DELETE FROM plan_route_wide WHERE plan_id = ?");
    $stmt2->bind_param("i", $plan_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: plan.php?deleted=1");
    exit;
}
?>
