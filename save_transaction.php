<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['description'];
    $amount = str_replace(',', '', $_POST['amount']); // ລຶບຈຸດອອກກ່ອນບັນທຶກ
    $type = $_POST['type'];
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : NULL; // ຮັບຄ່າ category_id
    $transaction_date = $_POST['transaction_date'];
    $user_id = $_SESSION['user_id'];

    // SQL ໃໝ່ທີ່ມີ category_id
    $stmt = $conn->prepare("INSERT INTO transactions (description, amount, type, category_id, transaction_date, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    
    // bind_param: s=string, i=integer (amount ຄວນເປັນ double/decimal ແຕ່ໃນທີ່ນີ້ໃຊ້ s ຫຼື d ກໍໄດ້, id ເປັນ i)
    $stmt->bind_param("sdsisi", $description, $amount, $type, $category_id, $transaction_date, $user_id);

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