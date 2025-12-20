<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "ລະຫັດຜ່ານໃໝ່ທັງສອງຊ່ອງບໍ່ກົງກັນ!";
    } elseif (empty($username) || empty($new_password)) {
        $error = "ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບຖ້ວນ!";
    } else {
        // ກວດສອບວ່າຊື່ຜູ້ໃຊ້ນີ້ມີຢູ່ບໍ່
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows == 0) {
            $error = "ບໍ່ພົບຊື່ຜູ້ໃຊ້ນີ້ໃນລະບົບ!";
        } else {
            // ອັບເດດລະຫັດຜ່ານໃໝ່
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_password, $username);
            
            if ($update_stmt->execute()) {
                $success = "ປ່ຽນລະຫັດຜ່ານສຳເລັດ! ກະລຸນາລຶບໄຟລ໌ນີ້ຖິ້ມ.";
            } else {
                $error = "ເກີດຂໍ້ຜິດພາດ: " . $conn->error;
            }
            $update_stmt->close();
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
    <title>ຣີເຊັດລະຫັດຜ່ານ - Paoxay Pay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold mt-2">ຕັ້ງລະຫັດຜ່ານໃໝ່</h3>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                            <br><a href="login.php" class="alert-link">ກັບໄປໜ້າເຂົ້າສູ່ລະບົບ</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">ຊື່ຜູ້ໃຊ້ (Username)</label>
                                <input type="text" class="form-control" name="username" required placeholder="ໃສ່ຊື່ຜູ້ໃຊ້ທີ່ຕ້ອງການປ່ຽນລະຫັດ">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ລະຫັດຜ່ານໃໝ່</label>
                                <input type="password" class="form-control" name="new_password" required placeholder="ຕັ້ງລະຫັດຜ່ານໃໝ່">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">ຢືນຢັນລະຫັດຜ່ານໃໝ່</label>
                                <input type="password" class="form-control" name="confirm_password" required placeholder="ໃສ່ລະຫັດຜ່ານອີກຄັ້ງ">
                            </div>
                            <button type="submit" class="btn btn-danger w-100">ປ່ຽນລະຫັດຜ່ານ</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>