<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bi_id = isset($_POST['bi_id']) ? (int)$_POST['bi_id'] : 0;

  if ($bi_id > 0) {
    $sql = "DELETE FROM bus_info WHERE bi_id = $bi_id";
    if ($conn->query($sql) === TRUE) {
      echo "OK";
    } else {
      echo "ลบไม่สำเร็จ: " . $conn->error;
    }
  } else {
    echo "ไม่พบรหัสรถ";
  }
}
?>
