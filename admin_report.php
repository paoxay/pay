<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }

$filter_month = isset($_GET['month']) ? $_GET['month'] : '';
$where_sql = ""; $params = []; $types = "";
if ($filter_month) { $where_sql = " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ? "; $params[] = $filter_month; $types .= "s"; }

$sql = "SELECT u.username, DATE_FORMAT(t.transaction_date, '%Y-%m') as month_str, SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_income, SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as total_expense FROM transactions t JOIN users u ON t.user_id = u.id WHERE 1=1 $where_sql GROUP BY u.id, month_str ORDER BY month_str DESC, total_expense DESC";
$stmt = $conn->prepare($sql);
if ($filter_month) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$sql_sum = "SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as grand_income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as grand_expense FROM transactions t WHERE 1=1 $where_sql";
$stmt_sum = $conn->prepare($sql_sum);
if ($filter_month) $stmt_sum->bind_param($types, ...$params);
$stmt_sum->execute();
$sum_row = $stmt_sum->get_result()->fetch_assoc();
$grand_balance = ($sum_row['grand_income'] ?? 0) - ($sum_row['grand_expense'] ?? 0);
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <title>ລາຍງານ - Admin</title>
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
            <a class="nav-link" href="admin_transactions.php"><i class="bi bi-cash-stack"></i> ລາຍການທຸລະກຳ</a>
            <a class="nav-link active" href="admin_report.php"><i class="bi bi-bar-chart-fill"></i> ລາຍງານສະຫຼຸບ</a>
            <div class="mt-5 border-top border-secondary pt-3"><a class="nav-link text-danger" href="logout.php">ອອກຈາກລະບົບ</a></div>
        </nav>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold m-0">ລາຍງານສະຫຼຸບ</h4>
            <form class="d-flex gap-2" method="GET">
                <input type="month" name="month" class="form-control" value="<?php echo $filter_month; ?>">
                <button type="submit" class="btn btn-primary">ຄົ້ນຫາ</button>
                <?php if($filter_month): ?><a href="admin_report.php" class="btn btn-outline-secondary">ລ້າງຄ່າ</a><?php endif; ?>
            </form>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card p-3 border-start border-4 border-success"><h5>ລາຍຮັບລວມ</h5><h4 class="text-success">+<?php echo number_format($sum_row['grand_income'] ?? 0); ?></h4></div></div>
            <div class="col-md-4"><div class="card p-3 border-start border-4 border-danger"><h5>ລາຍຈ່າຍລວມ</h5><h4 class="text-danger">-<?php echo number_format($sum_row['grand_expense'] ?? 0); ?></h4></div></div>
            <div class="col-md-4"><div class="card p-3 border-start border-4 border-primary"><h5>ຄົງເຫຼືອໃນລະບົບ</h5><h4 class="text-primary"><?php echo number_format($grand_balance); ?></h4></div></div>
        </div>
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <table class="table table-hover table-bordered">
                <thead class="table-light"><tr><th>Month</th><th>Username</th><th>Income</th><th>Expense</th><th>Balance</th></tr></thead>
                <tbody><?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('m/Y', strtotime($row['month_str'] . '-01')); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td class="text-success">+<?php echo number_format($row['total_income']); ?></td>
                    <td class="text-danger">-<?php echo number_format($row['total_expense']); ?></td>
                    <td class="fw-bold"><?php echo number_format($row['total_income'] - $row['total_expense']); ?></td>
                </tr>
                <?php endwhile; else: ?><tr><td colspan="5" class="text-center py-4">ບໍ່ມີຂໍ້ມູນ</td></tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>