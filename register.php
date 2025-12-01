<?php
require_once 'db.php';

$error = '';   // ສຳລັບເກັບຂໍ້ຄວາມ Error
$success = ''; // ສຳລັບເກັບຂໍ້ຄວາມສຳເລັດ

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ກວດສອບວ່າລະຫັດຜ່ານກົງກັນບໍ່
    if ($password !== $confirm_password) {
        $error = "ລະຫັດຜ່ານທັງສອງຊ່ອງບໍ່ກົງກັນ!";
    } elseif (empty($username) || empty($password)) {
        $error = "ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບຖ້ວນ!";
    } else {
        // ກວດສອບຊື່ຜູ້ໃຊ້ຊ້ຳ
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "ຊື່ຜູ້ໃຊ້ນີ້ມີຢູ່ໃນລະບົບແລ້ວ! ກະລຸນາໃຊ້ຊື່ອື່ນ.";
        } else {
            // ຖ້າບໍ່ຊ້ຳ ໃຫ້ບັນທຶກລົງຖານຂໍ້ມູນ
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // ເຂົ້າລະຫັດ
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "ສະໝັກສະມາຊິກສຳເລັດ!";
            } else {
                $error = "ເກີດຂໍ້ຜິດພາດ: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ສະໝັກສະມາຊິກ - Paoxay Pay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Theme ດຽວກັນກັບ Login */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header {
            background-color: #fff;
            border-bottom: none;
            padding-top: 30px;
            text-align: center;
        }
        .logo-icon {
            font-size: 3rem;
            color: #764ba2;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #764ba2;
            background-color: #fff;
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1px solid #e9ecef;
            background-color: #fff;
            color: #6c757d;
        }
        .form-control {
            border-radius: 0 10px 10px 0;
        }
        .btn-register {
            border-radius: 10px;
            padding: 12px;
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .footer-link a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 600;
        }
        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card register-card p-4">
                    <div class="card-header">
                        <div class="logo-icon mb-2">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h3 class="fw-bold text-secondary">ສະໝັກສະມາຊິກ</h3>
                        <p class="text-muted small">ສ້າງບັນຊີໃໝ່ເພື່ອເລີ່ມຕົ້ນໃຊ້ງານ</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if($success): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div>
                                    <?php echo $success; ?> 
                                    <br><a href="login.php" class="alert-link">ຄິກບ່ອນນີ້ເພື່ອເຂົ້າສູ່ລະບົບ</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label text-muted small">ຊື່ຜູ້ໃຊ້ (Username)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="ຕັ້ງຊື່ຜູ້ໃຊ້ຂອງທ່ານ" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label text-muted small">ລະຫັດຜ່ານ (Password)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="ຕັ້ງລະຫັດຜ່ານ" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label text-muted small">ຢືນຢັນລະຫັດຜ່ານ</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="ປ້ອນລະຫັດຜ່ານອີກຄັ້ງ" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-register text-white">
                                        <i class="bi bi-person-plus me-2"></i> ຢືນຢັນການສະໝັກ
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                    </div>
                    <div class="card-footer text-center bg-white border-0 mt-3">
                        <p class="small text-muted mb-0">ມີບັນຊີຢູ່ແລ້ວແມ່ນບໍ່? 
                            <span class="footer-link"><a href="login.php">ເຂົ້າສູ່ລະບົບ</a></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>