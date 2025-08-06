<?php
session_start();
include 'config.php';

$birthday = $_POST['birthday'];

$sql = "SELECT * FROM users WHERE birthday = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $birthday);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $_SESSION['user'] = $user['fullname'];
    header("Location: welcome.php");
} else {
    echo "ไม่พบผู้ใช้งานวันเกิดนี้ <a href='login.php'>กลับ</a>";
}
?>
