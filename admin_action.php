<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }

if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM transactions WHERE user_id = $id");
    $conn->query("DELETE FROM categories WHERE user_id = $id");
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: admin_users.php"); exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_trans' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM transactions WHERE id = $id");
    header("Location: admin_transactions.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'update_user') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    $new_pass = $_POST['new_password'];
    if (!empty($new_pass)) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $role, $hashed, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $user_id);
    }
    $stmt->execute();
    header("Location: admin_users.php"); exit();
}
?>