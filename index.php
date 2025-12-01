<?php
require_once 'db.php';

// --- ดึงข้อมูลทั้งหมดมาแสดง ---
$sql = "SELECT * FROM transactions ORDER BY transaction_date DESC, id DESC";
$result = $conn->query($sql);

// --- คำนวณยอดรวม ---
$total_income = 0;
$total_expense = 0;

$sql_total = "SELECT type, SUM(amount) as total FROM transactions GROUP BY type";
$result_total = $conn->query($sql_total);
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
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: #f8f9fa;
        }
        .balance {
            color: <?php echo ($balance >= 0) ? 'green' : 'red'; ?>;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h1 class="text-center mb-4">📝 ບັນທຶກລາຍຮັບ-ລາຍຈ່າຍ</h1>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">ລາຍຮັບທັງໝົດ</h5>
                        <p class="card-text fs-4"><?php echo number_format($total_income, 2); ?> ກີບ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">ລາຍຈ່າຍທັງໝົດ</h5>
                        <p class="card-text fs-4"><?php echo number_format($total_expense, 2); ?> ກີບ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">ຍອດເຫຼືອຄົງເຫຼືອ</h5>
                        <p class="card-text fs-4 balance"><?php echo number_format($balance, 2); ?> ກີບ</p>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-4">
            <div class="card-header">
                <strong>ເພີ່ມລາຍການໃໝ່</strong>
            </div>
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

        <h3 class="mt-5">ລາຍການທັງໝົດ</h3>
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
                            <td colspan="4" class="text-center">-- ຍັງບໍ່ມີຂໍ້ມູນລາຍການ --</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ເລືອກເອົາ input 2 ອັນຂອງເຮົາ
        const amountDisplay = document.getElementById('amount_display');
        const amountReal = document.getElementById('amount_real');

        // ເມື່ອມີການພິມໃນຊ່ອງສະແດງຜົນ
        amountDisplay.addEventListener('input', function(e) {
            // 1. ເອົາຄ່າທີ່ພິມເຂົ້າມາ ແລ້ວລຶບທຸກຢ່າງທີ່ບໍ່ແມ່ນຕົວເລກອອກ
            let rawValue = e.target.value.replace(/[^0-9]/g, '');

            // 2. ເກັບຄ່າທີ່ເປັນຕົວເລກແທ້ໆ ໄວ້ໃນ input ທີ່ຊ່ອນຢູ່
            amountReal.value = rawValue;

            // 3. ຈັດຮູບແບບຕົວເລກໃຫ້ມີເຄື່ອງໝາຍຂັ້ນຫຼັກພັນ ແລ້ວສະແດງໃນຊ່ອງທີ່ເຫັນ
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
<?php
$conn->close();
?>