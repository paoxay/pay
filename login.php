<?php
session_start();
require_once 'db.php';

$error = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ດຶງຂໍ້ມູນ user ລວມທັງ role
    $stmt = $conn->prepare("SELECT id, password, username, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // Login ສຳເລັດ -> ເກັບ Session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // ເກັບ role ໄວ້ກວດສອບ
            
            // ກວດສອບ Role ເພື່ອປ່ຽນໜ້າ
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ!";
        }
    } else {
        $error = "ບໍ່ພົບຊື່ຜູ້ໃຊ້ນີ້ໃນລະບົບ!";
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ເຂົ້າສູ່ລະບົບ - Paoxay Pay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            border: none; border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden;
        }
        .card-header { background-color: #fff; border-bottom: none; padding-top: 30px; text-align: center; }
        .logo-icon { font-size: 3rem; color: #764ba2; }
        .form-control { border-radius: 0 10px 10px 0; padding: 12px; background-color: #f8f9fa; border: 1px solid #e9ecef; }
        .form-control:focus { box-shadow: none; border-color: #764ba2; background-color: #fff; }
        .input-group-text { border-radius: 10px 0 0 10px; border: 1px solid #e9ecef; background-color: #fff; color: #6c757d; }
        .btn-login {
            border-radius: 10px; padding: 12px;
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none; font-weight: bold; transition: all 0.3s ease;
        }
        .btn-login:hover { opacity: 0.9; transform: translateY(-2px); }
        .footer-link a { color: #764ba2; text-decoration: none; font-weight: 600; }
        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card p-4">
                    <div class="card-header">
                        <div class="logo-icon mb-2"><i class="bi bi-shield-lock-fill"></i></div>
                        <h3 class="fw-bold text-secondary">Admin & User Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted small">ຊື່ຜູ້ໃຊ້</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small">ລະຫັດຜ່ານ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login text-white">ເຂົ້າສູ່ລະບົບ</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center bg-white border-0 mt-3">
                        <p class="small text-muted mb-0">ຍັງບໍ່ມີບັນຊີ? <span class="footer-link"><a href="register.php">ສະໝັກສະມາຊິກ</a></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>