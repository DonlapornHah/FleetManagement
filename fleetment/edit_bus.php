<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bi_id = (int)$_POST['bi_id'];
  $bi_licen = trim($_POST['bi_licen']);
  $status_id = (int)$_POST['status_id'];

  if ($bi_id && $bi_licen !== '') {
    $sql = "UPDATE bus_info SET bi_licen = ?, status_id = ? WHERE bi_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $bi_licen, $status_id, $bi_id);

    if ($stmt->execute()) {
      echo "OK";
    } else {
      echo "ไม่สามารถอัปเดตข้อมูลได้: " . $stmt->error;
    }

    $stmt->close();
  } else {
    echo "ข้อมูลไม่ครบถ้วน";
  }
}
?>
