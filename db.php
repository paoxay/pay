<?php
// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล ---
$servername = "localhost"; // หรือ IP ของเซิร์ฟเวอร์ฐานข้อมูล
$username = "root";        // ชื่อผู้ใช้ของฐานข้อมูล (ค่าเริ่มต้นของ XAMPP คือ root)
$password = "";            // รหัสผ่านของฐานข้อมูล (ค่าเริ่มต้นของ XAMPP คือไม่มี)
$dbname = "pay"; // ชื่อฐานข้อมูลที่สร้างไว้

// --- สร้างการเชื่อมต่อ ---
$conn = new mysqli($servername, $username, $password, $dbname);

// --- ตรวจสอบการเชื่อมต่อ ---
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตั้งค่า character set เป็น utf8mb4 เพื่อรองรับภาษาลาว
$conn->set_charset("utf8mb4");
?>