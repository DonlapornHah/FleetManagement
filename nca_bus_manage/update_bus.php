<?php
include 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' || $action === 'update') {
  $id = $_POST['bi_id'] ?? null;
  $license = $_POST['licenseplate'];
  $model = $_POST['model'];
  $capacity = $_POST['capacity'];
  $type = $_POST['bus_type'];
  $subtype = $_POST['bus_sub_type'];
  $start = $_POST['start_location'];
  $end = $_POST['end_location'];
  $status = $_POST['status'];

  if ($action === 'add') {
    $sql = "INSERT INTO bus_info (bi_licenseplate, bi_model, bi_capacity, bt_id, bs_id)
            VALUES ('$license', '$model', '$capacity', '$type', '$status')";
  } else {
    $sql = "UPDATE bus_info SET 
              bi_licenseplate='$license',
              bi_model='$model',
              bi_capacity='$capacity',
              bt_id='$type',
              bs_id='$status'
            WHERE bi_id='$id'";
  }

  mysqli_query($conn, $sql);
  header("Location: manage_buses.php");
  exit;

} elseif ($action === 'delete') {
  $id = $_GET['id'];
  mysqli_query($conn, "DELETE FROM bus_info WHERE bi_id='$id'");
  header("Location: manage_buses.php");
  exit;
}
?>
