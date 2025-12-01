<?php
require_once 'db.php';

// ตรวจสอบว่าข้อมูลถูกส่งมาแบบ POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // รับค่าจากฟอร์ม
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $transaction_date = $_POST['transaction_date'];

    // --- การป้องกัน SQL Injection ด้วย Prepared Statements ---
    // เตรียมคำสั่ง SQL
    $stmt = $conn->prepare("INSERT INTO transactions (description, amount, type, transaction_date) VALUES (?, ?, ?, ?)");
    
    // ผูกค่าตัวแปรกับพารามิเตอร์
    // s = string, d = double (decimal/float), i = integer
    $stmt->bind_param("sdss", $description, $amount, $type, $transaction_date);

    // ทำการ execute คำสั่ง
    if ($stmt->execute()) {
        // หากสำเร็จ ให้กลับไปหน้าแรก
        header("Location: index.php");
        exit();
    } else {
        // หากไม่สำเร็จ ให้แสดงข้อผิดพลาด
        echo "Error: " . $stmt->error;
    }

    // ปิด statement และการเชื่อมต่อ
    $stmt->close();
    $conn->close();
} else {
    // ถ้าไม่ได้เข้ามาด้วย POST method ให้กลับไปหน้าแรก
    header("Location: index.php");
    exit();
}
?>