<?php
$servername = "localhost";     // ใช้ localhost เมื่อรันบน XAMPP
$username = "root";            // ค่า default ของ XAMPP คือ root
$password = "";                // ค่า default ของ XAMPP คือ รหัสผ่านว่าง
$dbname = "higherlevelaudtion";  // ชื่อฐานข้อมูลที่คุณสร้างใน phpMyAdmin ของ XAMPP

// เชื่อมต่อฐานข้อมูล
$conn = mysqli_connect($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// กำหนด charset ให้รองรับ UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>
