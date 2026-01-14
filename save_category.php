<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['category_name']);
    $type = $_POST['category_type']; // income ຫຼື expense
    $user_id = $_SESSION['user_id'];

    if (!empty($name) && !empty($type)) {
        // ກວດສອບວ່າຊື່ຊ້ຳບໍ່
        $check = $conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ?");
        $check->bind_param("iss", $user_id, $name, $type);
        $check->execute();
        if ($check->get_result()->num_rows == 0) {
            // ບັນທຶກລົງ Database
            $stmt = $conn->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $name, $type);
            $stmt->execute();
            $stmt->close();
        }
        $check->close();
    }
}
// ກັບໄປໜ້າ index
header("Location: index.php");
exit();
?>