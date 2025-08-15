<?php
include 'config.php';

if (isset($_GET['plan_id'])) {
    $plan_id = intval($_GET['plan_id']);

    // ลบแผน
    $sql = "DELETE FROM plan_route_wide WHERE plan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();

    header("Location: plan.php?deleted=1");
    exit;
}
?>
