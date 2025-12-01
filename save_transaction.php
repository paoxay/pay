<?php
session_start(); // 1. ເພີ່ມ session_start
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $transaction_date = $_POST['transaction_date'];
    $user_id = $_SESSION['user_id']; // 2. ຮັບຄ່າ user_id

    // 3. ເພີ່ມ user_id ເຂົ້າໃນ SQL Insert
    $stmt = $conn->prepare("INSERT INTO transactions (description, amount, type, transaction_date, user_id) VALUES (?, ?, ?, ?, ?)");
    
    // 4. ເພີ່ມ 'i' (integer) ແລະ $user_id ໃນ bind_param
    $stmt->bind_param("sdssi", $description, $amount, $type, $transaction_date, $user_id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>