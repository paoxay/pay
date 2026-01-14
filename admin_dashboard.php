<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }

$stmt_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
$total_users = $stmt_users->fetch_assoc()['count'];
$stmt_trans = $conn->query("SELECT COUNT(*) as count FROM transactions");
$total_trans = $stmt_trans->fetch_assoc()['count'];
$stmt_money = $conn->query("SELECT SUM(amount) as total FROM transactions");
$total_volume = $stmt_money->fetch_assoc()['total'];
$users_list = $conn->query("SELECT id, username, role FROM users ORDER BY id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Lao', sans-serif; background-color: #f3f4f6; }
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #1e1e2d; color: white; transition: 0.3s; z-index: 1000; }
        .main-content { margin-left: 250px; padding: 20px; transition: 0.3s; }
        .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: #2b2b40; color: white; }
        .stat-card { background: white; border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 20px; display: flex; align-items: center; justify-content: space-between; }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar p-3">
        <div class="d-flex align-items-center mb-5 px-2"><i class="bi bi-shield-check fs-2 text-primary me-2"></i><h5 class="m-0 fw-bold">Admin Panel</h5></div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="admin_dashboard.php"><i class="bi bi-grid-fill"></i> ພາບລວມ</a>
            <a class="nav-link" href="admin_users.php"><i class="bi bi-people-fill"></i> ຈັດການຜູ້ໃຊ້</a>
            <a class="nav-link" href="admin_transactions.php"><i class="bi bi-cash-stack"></i> ລາຍການທຸລະກຳ</a>
            <a class="nav-link" href="admin_report.php"><i class="bi bi-bar-chart-fill"></i> ລາຍງານສະຫຼຸບ</a>
            <div class="mt-5 border-top border-secondary pt-3"><a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-left"></i> ອອກຈາກລະບົບ</a></div>
        </nav>
    </div>
    <div class="main-content">
        <button class="btn btn-light d-md-none mb-3" onclick="document.querySelector('.sidebar').classList.toggle('show')"><i class="bi bi-list fs-4"></i></button>
        <h4 class="fw-bold mb-4">Dashboard</h4>
        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="stat-card"><div><p class="text-muted small mb-1">ຜູ້ໃຊ້ທັງໝົດ</p><h3 class="fw-bold m-0"><?php echo number_format($total_users); ?></h3></div><div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div><p class="text-muted small mb-1">ລາຍການທັງໝົດ</p><h3 class="fw-bold m-0"><?php echo number_format($total_trans); ?></h3></div><div class="icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-receipt"></i></div></div></div>
            <div class="col-md-4"><div class="stat-card"><div><p class="text-muted small mb-1">ຍອດເງິນໝູນວຽນ</p><h4 class="fw-bold m-0"><?php echo number_format($total_volume); ?> ₭</h4></div><div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-coin"></i></div></div></div>
        </div>
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h5 class="fw-bold mb-3">ລາຍຊື່ຜູ້ໃຊ້ລ່າສຸດ</h5>
            <table class="table table-hover">
                <thead><tr><th>ID</th><th>Username</th><th>Role</th></tr></thead>
                <tbody><?php while($u = $users_list->fetch_assoc()): ?>
                    <tr><td>#<?php echo $u['id']; ?></td><td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><span class="badge bg-<?php echo ($u['role']=='admin')?'danger':'success'; ?>"><?php echo ucfirst($u['role']); ?></span></td></tr>
                <?php endwhile; ?></tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>