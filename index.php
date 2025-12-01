<?php
session_start();
require_once 'db.php';

// ກວດສອບການ Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- ຈັດການເລື່ອງເດືອນ ---
// ຖ້າບໍ່ມີການເລືອກເດືອນ ໃຫ້ໃຊ້ເດືອນປັດຈຸບັນ
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// ແຍກ ປີ ແລະ ເດືອນ
$parts = explode('-', $selected_month);
$year = $parts[0];
$month = $parts[1];

// --- ດຶງຂໍ້ມູນສະເພາະເດືອນນັ້ນ ແລະ User ນັ້ນ ---
$sql = "SELECT * FROM transactions 
        WHERE user_id = ? 
        AND MONTH(transaction_date) = ? 
        AND YEAR(transaction_date) = ? 
        ORDER BY transaction_date DESC, id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// --- ຄຳນວນຍອດລວມ (ສະເພາະເດືອນນັ້ນ) ---
$total_income = 0;
$total_expense = 0;

$sql_total = "SELECT type, SUM(amount) as total FROM transactions 
              WHERE user_id = ? 
              AND MONTH(transaction_date) = ? 
              AND YEAR(transaction_date) = ? 
              GROUP BY type";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("iss", $user_id, $month, $year);
$stmt_total->execute();
$result_total = $stmt_total->get_result();

if ($result_total->num_rows > 0) {
    while($row = $result_total->fetch_assoc()) {
        if ($row['type'] == 'income') {
            $total_income = $row['total'];
        } else {
            $total_expense = $row['total'];
        }
    }
}
$balance = $total_income - $total_expense;
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລະບົບຈັດການລາຍຮັບ-ລາຍຈ່າຍ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Lao', sans-serif; background-color: #f8f9fa; }
        .balance { color: <?php echo ($balance >= 0) ? 'green' : 'red'; ?>; }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>📝 ບັນທຶກຂອງ: <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <a href="logout.php" class="btn btn-outline-danger">ອອກຈາກລະບົບ</a>
        </div>

        <form action="" method="GET" class="mb-4">
            <div class="row align-items-end">
                <div class="col-auto">
                    <label class="form-label">ເລືອກເດືອນທີ່ຕ້ອງການເບິ່ງ:</label>
                    <input type="month" name="month" class="form-control" value="<?php echo $selected_month; ?>" onchange="this.form.submit()">
                </div>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">ລາຍຮັບ (<?php echo date('m/Y', strtotime($selected_month)); ?>)</h5>
                        <p class="card-text fs-4"><?php echo number_format($total_income, 2); ?> ກີບ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">ລາຍຈ່າຍ (<?php echo date('m/Y', strtotime($selected_month)); ?>)</h5>
                        <p class="card-text fs-4"><?php echo number_format($total_expense, 2); ?> ກີບ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">ຍອດເຫຼືອ</h5>
                        <p class="card-text fs-4 balance"><?php echo number_format($balance, 2); ?> ກີບ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>ເພີ່ມລາຍການໃໝ່</strong></div>
            <div class="card-body">
                <form action="save_transaction.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="description" class="form-label">ລາຍລະອຽດ</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        <div class="col-md-6">
                            <label for="amount_display" class="form-label">ຈຳນວນເງິນ</label>
                            <input type="text" class="form-control" id="amount_display" placeholder="ຕົວຢ່າງ: 150000" inputmode="decimal" required>
                            <input type="hidden" name="amount" id="amount_real">
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">ປະເພດ</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="income">ລາຍຮັບ</option>
                                <option value="expense" selected>ລາຍຈ່າຍ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="transaction_date" class="form-label">ວັນທີ</label>
                            <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">ບັນທຶກລາຍການ</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <h3 class="mt-5">ລາຍການປະຈຳເດືອນ <?php echo date('m/Y', strtotime($selected_month)); ?></h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">ວັນທີ</th>
                        <th>ລາຍລະອຽດ</th>
                        <th class="text-center">ປະເພດ</th>
                        <th class="text-end">ຈຳນວນເງິນ (ກີບ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td class="text-center">
                                    <?php if ($row['type'] == 'income'): ?>
                                        <span class="badge bg-success">ລາຍຮັບ</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">ລາຍຈ່າຍ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end <?php echo ($row['type'] == 'income') ? 'text-success' : 'text-danger'; ?> fw-bold">
                                    <?php echo ($row['type'] == 'income' ? '+' : '-') . number_format($row['amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">-- ບໍ່ມີຂໍ້ມູນໃນເດືອນນີ້ --</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const amountDisplay = document.getElementById('amount_display');
        const amountReal = document.getElementById('amount_real');
        amountDisplay.addEventListener('input', function(e) {
            let rawValue = e.target.value.replace(/[^0-9]/g, '');
            amountReal.value = rawValue;
            if (rawValue) {
                const formattedValue = parseInt(rawValue, 10).toLocaleString('en-US');
                e.target.value = formattedValue;
            } else {
                e.target.value = '';
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>