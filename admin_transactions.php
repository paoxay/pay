<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }
$sql = "SELECT t.*, u.username, c.name as category_name FROM transactions t JOIN users u ON t.user_id = u.id LEFT JOIN categories c ON t.category_id = c.id ORDER BY t.transaction_date DESC, t.id DESC LIMIT 50";
$trans = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <title>ລາຍການທຸລະກຳ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Lao', sans-serif; background-color: #f3f4f6; }
        .sidebar { width: 250px; height: 100vh; position: fixed; top: 0; left: 0; background: #1e1e2d; color: white; transition: 0.3s; z-index: 1000; }
        .main-content { margin-left: 250px; padding: 20px; transition: 0.3s; }
        .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: #2b2b40; color: white; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar p-3">
        <div class="d-flex align-items-center mb-5 px-2"><h5 class="m-0 fw-bold text-white">Admin Panel</h5></div>
        <nav class="nav flex-column">
            <a class="nav-link" href="admin_dashboard.php"><i class="bi bi-grid-fill"></i> ພາບລວມ</a>
            <a class="nav-link" href="admin_users.php"><i class="bi bi-people-fill"></i> ຈັດການຜູ້ໃຊ້</a>
            <a class="nav-link active" href="admin_transactions.php"><i class="bi bi-cash-stack"></i> ລາຍການທຸລະກຳ</a>
            <a class="nav-link" href="admin_report.php"><i class="bi bi-bar-chart-fill"></i> ລາຍງານສະຫຼຸບ</a>
            <div class="mt-5 border-top border-secondary pt-3"><a class="nav-link text-danger" href="logout.php">ອອກຈາກລະບົບ</a></div>
        </nav>
    </div>
    <div class="main-content">
        <h4 class="fw-bold mb-4">ລາຍການເຄື່ອນໄຫວທັງໝົດ</h4>
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <table class="table table-hover align-middle">
                <thead><tr><th>Date</th><th>User</th><th>Description</th><th>Amount</th><th>Action</th></tr></thead>
                <tbody><?php while($row = $trans->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($row['transaction_date'])); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['username']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['description']); ?> <small class="text-muted">(<?php echo $row['category_name']; ?>)</small></td>
                    <td class="<?php echo ($row['type']=='income')?'text-success':'text-danger'; ?> fw-bold"><?php echo number_format($row['amount']); ?></td>
                    <td><a href="admin_action.php?action=delete_trans&id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('ລົບ?')"><i class="bi bi-trash"></i></a></td>
                </tr>
                <?php endwhile; ?></tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>