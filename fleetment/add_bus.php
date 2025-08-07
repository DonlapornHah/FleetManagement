<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bi_licen = trim($_POST['bi_licen']);
  $br_id = (int)$_POST['br_id'];
  $bt_id = (int)$_POST['bt_id'];
  $status_id = (int)$_POST['status_id'];

  if ($bi_licen !== '' && $br_id > 0 && $bt_id > 0 && $status_id > 0) {
    $stmt = $conn->prepare("INSERT INTO bus_info (bi_licen, br_id, bt_id, status_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $bi_licen, $br_id, $bt_id, $status_id);
    
    if ($stmt->execute()) {
      echo "OK";
    } else {
      echo "เพิ่มรถไม่สำเร็จ: " . $stmt->error;
    }

    $stmt->close();
  } else {
    echo "กรุณากรอกข้อมูลให้ครบถ้วน";
  }
}
?>
