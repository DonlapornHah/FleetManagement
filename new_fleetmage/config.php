<?php
// config.php

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$host = 'localhost';       // โฮสต์ MySQL
$db   = 'fleetment';       // ชื่อฐานข้อมูล
$user = 'root';            // ชื่อผู้ใช้
$pass = '12345678';                // รหัสผ่าน
$charset = 'utf8mb4';      // ชุดอักขระ

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $user, $pass, $db);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตั้งค่า charset
$conn->set_charset($charset);
?>
