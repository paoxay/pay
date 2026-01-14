<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <title>ຈັດການຜູ້ໃຊ້ - Admin</title>
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
            <a class="nav-link active" href="admin_users.php"><i class="bi bi-people-fill"></i> ຈັດການຜູ້ໃຊ້</a>
            <a class="nav-link" href="admin_transactions.php"><i class="bi bi-cash-stack"></i> ລາຍການທຸລະກຳ</a>
            <a class="nav-link" href="admin_report.php"><i class="bi bi-bar-chart-fill"></i> ລາຍງານສະຫຼຸບ</a>
            <div class="mt-5 border-top border-secondary pt-3"><a class="nav-link text-danger" href="logout.php">ອອກຈາກລະບົບ</a></div>
        </nav>
    </div>
    <div class="main-content">
        <h4 class="fw-bold mb-4">ຈັດການຜູ້ໃຊ້</h4>
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Action</th></tr></thead>
                <tbody><?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><span class="badge bg-<?php echo ($u['role']=='admin')?'danger':'success'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-light text-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $u['id']; ?>"><i class="bi bi-pencil-square"></i></button>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <a href="admin_action.php?action=delete_user&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('ຢືນຢັນການລົບ?')"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">ແກ້ໄຂ: <?php echo $u['username']; ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div><form action="admin_action.php" method="POST"><div class="modal-body"><input type="hidden" name="action" value="update_user"><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><div class="mb-3"><label>Role</label><select class="form-select" name="role"><option value="user" <?php echo ($u['role']=='user')?'selected':''; ?>>User</option><option value="admin" <?php echo ($u['role']=='admin')?'selected':''; ?>>Admin</option></select></div><div class="mb-3"><label>Password ໃໝ່ (ຖ້າຈະປ່ຽນ)</label><input type="password" class="form-control" name="new_password"></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">ບັນທຶກ</button></div></form></div></div></div>
                <?php endwhile; ?></tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>