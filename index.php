<?php
session_start();
require_once 'db.php';

//
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- ກວດສອບ ແລະ ເພີ່ມໝວດໝູ່ເລີ່ມຕົ້ນ (Auto) ---
$chk_cat = $conn->query("SELECT id FROM categories WHERE user_id = $user_id LIMIT 1");
if ($chk_cat->num_rows == 0) {
    $conn->query("INSERT INTO categories (user_id, name, type) VALUES 
    ($user_id, 'ລາຍຈ່າຍຈຳເປັນ', 'expense'),
    ($user_id, 'ລາຍຈ່າຍສິ້ນເປືອງ', 'expense'),
    ($user_id, 'ອາຫານ & ເຄື່ອງດື່ມ', 'expense'),
    ($user_id, 'ເງິນເດືອນ', 'income')");
}

// --- ຈັດການເດືອນ ---
// ຖ້າມີການສົ່ງຄ່າ month ມາ ແລະ ບໍ່ເປັນຄ່າຫວ່າງ, ໃຫ້ໃຊ້ຄ່ານັ້ນ. ຖ້າບໍ່, ໃຫ້ໃຊ້ເດືອນປັດຈຸບັນ.
$selected_month = (!empty($_GET['month'])) ? $_GET['month'] : date('Y-m');

// ກວດສອບຄວາມຖືກຕ້ອງຂອງຮູບແບບ (ຕ້ອງມີຂີດ -)
if (strpos($selected_month, '-') === false) {
    $selected_month = date('Y-m');
}

$parts = explode('-', $selected_month);
$year = $parts[0];
$month = $parts[1];
// --- ດຶງຂໍ້ມູນລາຍການ ---
$sql = "SELECT t.*, c.name as category_name 
        FROM transactions t 
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
        AND MONTH(t.transaction_date) = ? 
        AND YEAR(t.transaction_date) = ? 
        ORDER BY t.transaction_date DESC, t.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// --- ຄຳນວນຍອດ ---
$total_income = 0;
$total_expense = 0;
$sql_total = "SELECT type, SUM(amount) as total FROM transactions 
              WHERE user_id = ? AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ? 
              GROUP BY type";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("iss", $user_id, $month, $year);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
while($row = $result_total->fetch_assoc()) {
    if ($row['type'] == 'income') $total_income = $row['total'];
    else $total_expense = $row['total'];
}
$balance = $total_income - $total_expense;

// --- ດຶງຂໍ້ມູນໝວດໝູ່ ---
$cats_income = $conn->query("SELECT * FROM categories WHERE user_id = $user_id AND type='income'");
$cats_expense = $conn->query("SELECT * FROM categories WHERE user_id = $user_id AND type='expense'");
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Paoxay Pay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6C63FF;
            --income-color: #10B981;
            --expense-color: #EF4444;
        }
        body { font-family: 'Noto Sans Lao', sans-serif; background-color: #F8F9FA; padding-bottom: 80px; }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0 0 25px 25px;
            padding: 20px 20px 40px 20px;
            margin-bottom: -30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .balance-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
            margin: 0 15px;
        }
        .balance-amount { font-size: 2rem; font-weight: bold; color: #333; }
        
        .trans-date-header { 
            font-size: 0.9rem; 
            color: #666; 
            margin: 20px 0 8px 0; 
            font-weight: 700;
            background-color: #e9ecef;
            display: inline-block;
            padding: 2px 10px;
            border-radius: 15px;
        }
        .trans-item {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .trans-icon {
            width: 45px; height: 45px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-right: 15px;
        }
        .bg-icon-income { background-color: #D1FAE5; color: var(--income-color); }
        .bg-icon-expense { background-color: #FEE2E2; color: var(--expense-color); }
        
        .fab-btn {
            position: fixed; bottom: 20px; right: 20px;
            width: 60px; height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.4);
            font-size: 2rem; display: flex; align-items: center; justify-content: center;
        }
        
        /* ປັບແຕ່ງ Input Month ໃຫ້ເບິ່ງງ່າຍ */
        .month-selector {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 5px 10px;
            display: inline-flex;
            align-items: center;
        }
        .month-display {
            font-weight: bold;
            font-size: 1.1rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>

    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <small>ສະບາຍດີ,</small>
                <h5 class="fw-bold m-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h5>
            </div>
            <a href="logout.php" class="text-white fs-4"><i class="bi bi-box-arrow-right"></i></a>
        </div>
        
        <div class="month-selector">
            <input type="month" class="form-control form-control-sm border-0" 
                   style="width: 40px; opacity: 0.5; cursor: pointer;"
                   value="<?php echo $selected_month; ?>" 
                   onchange="location.href='?month='+this.value">
            <span class="month-display">
                ປະຈຳເດືອນ: <?php echo date('m/Y', strtotime($selected_month)); ?>
            </span>
        </div>
    </div>

    <div class="balance-card">
        <p class="text-muted small mb-1">ຍອດເງິນຄົງເຫຼືອ</p>
        <div class="balance-amount"><?php echo number_format($balance); ?> ₭</div>
        <div class="row mt-3 pt-3 border-top">
            <div class="col-6 border-end">
                <div class="text-success small mb-1">ລາຍຮັບ</div>
                <div class="fw-bold"><?php echo number_format($total_income); ?></div>
            </div>
            <div class="col-6">
                <div class="text-danger small mb-1">ລາຍຈ່າຍ</div>
                <div class="fw-bold"><?php echo number_format($total_expense); ?></div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        
        <?php if ($result->num_rows > 0): ?>
            <?php 
            $current_date = '';
            while($row = $result->fetch_assoc()): 
                // ສະແດງວັນທີເປັນຕົວເລກ (d/m/Y)
                $date_num = date('d/m/Y', strtotime($row['transaction_date']));
                
                if ($current_date != $row['transaction_date']) {
                    echo '<div class="trans-date-header"><i class="bi bi-calendar3 me-1"></i> ' . $date_num . '</div>';
                    $current_date = $row['transaction_date'];
                }
            ?>
            <div class="trans-item">
                <div class="d-flex align-items-center">
                    <div class="trans-icon <?php echo ($row['type'] == 'income') ? 'bg-icon-income' : 'bg-icon-expense'; ?>">
                        <i class="bi <?php echo ($row['type'] == 'income') ? 'bi-wallet2' : 'bi-bag'; ?>"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['description']); ?></div>
                        <div class="small text-muted" style="font-size: 0.8rem;">
                            <?php echo $row['category_name'] ? $row['category_name'] : '-'; ?>
                        </div>
                    </div>
                </div>
                <div class="fw-bold <?php echo ($row['type'] == 'income') ? 'text-success' : 'text-danger'; ?>">
                    <?php echo ($row['type'] == 'income' ? '+' : '-') . number_format($row['amount']); ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1"></i>
                <p>ບໍ່ມີຂໍ້ມູນໃນເດືອນ <?php echo date('m/Y', strtotime($selected_month)); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <button class="fab-btn" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        <i class="bi bi-plus-lg"></i>
    </button>

    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">ເພີ່ມລາຍການໃໝ່</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-pills nav-fill mb-3 gap-2">
                        <li class="nav-item">
                            <button class="nav-link active bg-danger bg-opacity-10 text-danger w-100 fw-bold" id="expense-tab" onclick="setType('expense')">ລາຍຈ່າຍ</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link bg-success bg-opacity-10 text-success w-100 fw-bold" id="income-tab" onclick="setType('income')">ລາຍຮັບ</button>
                        </li>
                    </ul>

                    <form action="save_transaction.php" method="POST">
                        <input type="hidden" name="type" id="typeInput" value="expense">
                        
                        <div class="mb-3">
                            <label class="small text-muted">ຈຳນວນເງິນ</label>
                            <input type="text" class="form-control fs-2 fw-bold text-center text-primary" name="amount" id="amount" placeholder="0" inputmode="decimal" required style="border: none; background: #f8f9fa;">
                        </div>

                        <div class="mb-3">
                            <label class="small text-muted">ລາຍລະອຽດ</label>
                            <input type="text" class="form-control" name="description" required placeholder="ຂຽນລາຍລະອຽດ...">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="small text-muted">ໝວດໝູ່</label>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="small text-decoration-none fw-bold">
                                    + ເພີ່ມໝວດໝູ່
                                </a>
                            </div>
                            <select class="form-select" id="cat_expense" name="category_id">
                                <option value="">-- ເລືອກໝວດໝູ່ --</option>
                                <?php foreach($cats_expense as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select d-none" id="cat_income" name="category_id_income" disabled>
                                <option value="">-- ເລືອກໝວດໝູ່ --</option>
                                <?php foreach($cats_income as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="small text-muted">ວັນທີ (ເດືອນ/ວັນ/ປີ)</label>
                            <input type="date" class="form-control" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 rounded-4 fw-bold" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">ບັນທຶກ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">ເພີ່ມໝວດໝູ່</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="save_category.php" method="POST">
                        <div class="mb-2">
                            <input type="text" class="form-control" name="category_name" required placeholder="ຊື່ໝວດໝູ່...">
                        </div>
                        <div class="mb-3">
                            <select class="form-select" name="category_type">
                                <option value="expense">ລາຍຈ່າຍ</option>
                                <option value="income">ລາຍຮັບ</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-sm rounded-pill">ບັນທຶກ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto Format Number (ໃສ່ຈຸດໃຫ້ເງິນ)
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value.length > 0) {
                e.target.value = Number(value).toLocaleString('en-US');
            }
        });

        // Toggle Type Function
        function setType(type) {
            document.getElementById('typeInput').value = type;
            const expenseSelect = document.getElementById('cat_expense');
            const incomeSelect = document.getElementById('cat_income');
            const expTab = document.getElementById('expense-tab');
            const incTab = document.getElementById('income-tab');
            
            if(type === 'income') {
                expenseSelect.classList.add('d-none'); expenseSelect.disabled = true;
                incomeSelect.classList.remove('d-none'); incomeSelect.disabled = false;
                incomeSelect.name = 'category_id'; 
                
                expTab.classList.remove('active', 'bg-danger', 'text-white');
                incTab.classList.add('active', 'bg-success', 'text-white');
            } else {
                incomeSelect.classList.add('d-none'); incomeSelect.disabled = true;
                expenseSelect.classList.remove('d-none'); expenseSelect.disabled = false;
                expenseSelect.name = 'category_id'; 
                
                incTab.classList.remove('active', 'bg-success', 'text-white');
                expTab.classList.add('active', 'bg-danger', 'text-white');
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>