<?php
session_start();
require_once 'db.php';

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

// --- ຈັດການເດືອນ (Default: ປັດຈຸບັນ) ---
$selected_month = (!empty($_GET['month'])) ? $_GET['month'] : date('Y-m');
// ຖ້າ format ຜິດ ໃຫ້ໃຊ້ເດືອນປັດຈຸບັນ
if (strpos($selected_month, '-') === false) $selected_month = date('Y-m');

$parts = explode('-', $selected_month);
$year = $parts[0];
$month = $parts[1];

// --- 1. ດຶງຂໍ້ມູນລາຍການ (Transaction List) ---
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

// --- 2. ຄຳນວນຍອດລວມ ---
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

// --- 3. ຂໍ້ມູນສຳລັບກາບຟິກ (Chart Data) ---
// ດຶງຍອດລາຍຈ່າຍ ແຍກຕາມໝວດໝູ່
$sql_chart = "SELECT c.name, SUM(t.amount) as total 
              FROM transactions t 
              JOIN categories c ON t.category_id = c.id
              WHERE t.user_id = ? 
              AND t.type = 'expense'
              AND MONTH(t.transaction_date) = ? 
              AND YEAR(t.transaction_date) = ? 
              GROUP BY t.category_id 
              ORDER BY total DESC";
$stmt_chart = $conn->prepare($sql_chart);
$stmt_chart->bind_param("iss", $user_id, $month, $year);
$stmt_chart->execute();
$result_chart = $stmt_chart->get_result();

$chart_labels = [];
$chart_data = [];
while($row_c = $result_chart->fetch_assoc()){
    $chart_labels[] = $row_c['name'];
    $chart_data[] = $row_c['total'];
}

// --- ດຶງຂໍ້ມູນໝວດໝູ່ (Dropdown) ---
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --income-color: #10B981;
            --expense-color: #EF4444;
        }
        body { font-family: 'Noto Sans Lao', sans-serif; background-color: #F3F4F6; padding-bottom: 80px; }
        
        /* Header */
        .header-section {
            background: var(--primary-gradient);
            color: white;
            border-radius: 0 0 30px 30px;
            padding: 20px 20px 50px 20px;
            margin-bottom: -40px;
            box-shadow: 0 4px 20px rgba(118, 75, 162, 0.3);
        }

        /* Month Picker Style Customization */
        .month-picker-wrapper {
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 8px 15px;
            display: inline-flex;
            align-items: center;
            backdrop-filter: blur(5px);
            cursor: pointer;
            transition: 0.2s;
        }
        .month-picker-wrapper:hover { background: rgba(255,255,255,0.3); }
        .flatpickr-input { background: transparent; border: none; color: white; font-weight: bold; width: 100px; text-align: center; cursor: pointer; }
        .flatpickr-input:focus { outline: none; }

        /* Cards */
        .balance-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
            margin: 0 20px 20px 20px;
            position: relative;
        }
        .balance-amount { font-size: 2.2rem; font-weight: 800; color: #333; letter-spacing: -1px; }

        /* Chart Section */
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin: 0 20px 20px 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Transactions */
        .trans-date-header { 
            font-size: 0.85rem; color: #888; margin: 15px 0 5px 0; font-weight: 600; padding-left: 10px;
        }
        .trans-item {
            background: white;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
        }
        .trans-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-right: 15px;
            flex-shrink: 0;
        }
        .bg-icon-income { background-color: #ECFDF5; color: var(--income-color); }
        .bg-icon-expense { background-color: #FEF2F2; color: var(--expense-color); }

        /* FAB */
        .fab-btn {
            position: fixed; bottom: 25px; right: 25px;
            width: 65px; height: 65px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white; border: none;
            box-shadow: 0 8px 20px rgba(108, 99, 255, 0.4);
            font-size: 2rem; display: flex; align-items: center; justify-content: center;
            z-index: 1000;
            transition: transform 0.2s;
        }
        .fab-btn:active { transform: scale(0.9); }

        /* Custom Modal */
        .modal-content { border-radius: 24px; border: none; }
        .modal-header { border-bottom: none; padding-bottom: 0; }
        .form-control-custom {
            border: 2px solid #F3F4F6;
            border-radius: 12px;
            padding: 12px;
            font-size: 1rem;
        }
        .form-control-custom:focus { border-color: #764ba2; box-shadow: none; }
    </style>
</head>
<body>

    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <small class="opacity-75">ບັນຊີຂອງ</small>
                <h4 class="fw-bold m-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            </div>
            <a href="logout.php" class="text-white opacity-75"><i class="bi bi-box-arrow-right fs-4"></i></a>
        </div>
        
        <div class="d-flex justify-content-center">
            <div class="month-picker-wrapper">
                <i class="bi bi-calendar-month me-2"></i>
                <input type="text" id="monthPicker" value="<?php echo $selected_month; ?>" readonly>
                <i class="bi bi-chevron-down ms-2 small"></i>
            </div>
        </div>
    </div>

    <div class="balance-card">
        <p class="text-muted small mb-1 text-uppercase fw-bold ls-1">ຍອດເງິນຄົງເຫຼືອ</p>
        <div class="balance-amount"><?php echo number_format($balance); ?> ₭</div>
        <div class="row mt-4 pt-3 border-top">
            <div class="col-6 border-end">
                <div class="text-success small mb-1 fw-bold">ລາຍຮັບ</div>
                <div class="fw-bold"><?php echo number_format($total_income); ?></div>
            </div>
            <div class="col-6">
                <div class="text-danger small mb-1 fw-bold">ລາຍຈ່າຍ</div>
                <div class="fw-bold"><?php echo number_format($total_expense); ?></div>
            </div>
        </div>
    </div>

    <?php if(count($chart_data) > 0): ?>
    <div class="chart-container">
        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-pie-chart-fill me-2"></i>ສະຫຼຸບລາຍຈ່າຍ</h6>
        <div style="position: relative; height: 200px;">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <div class="container pb-5">
        <h6 class="fw-bold mb-3 text-secondary ps-2">ລາຍການເຄື່ອນໄຫວ</h6>
        
        <?php if ($result->num_rows > 0): ?>
            <?php 
            $current_date = '';
            while($row = $result->fetch_assoc()): 
                $date_display = date('d/m/Y', strtotime($row['transaction_date']));
                
                if ($current_date != $row['transaction_date']) {
                    echo '<div class="trans-date-header">' . $date_display . '</div>';
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
                        <div class="small text-muted">
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
                <i class="bi bi-journal-x fs-1 opacity-50"></i>
                <p class="mt-2">ບໍ່ມີຂໍ້ມູນໃນເດືອນນີ້</p>
            </div>
        <?php endif; ?>
    </div>

    <button class="fab-btn" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        <i class="bi bi-plus-lg"></i>
    </button>

    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">ເພີ່ມລາຍການໃໝ່</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="d-flex justify-content-center my-3">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type_opt" id="opt_expense" autocomplete="off" checked onclick="setType('expense')">
                            <label class="btn btn-outline-danger" for="opt_expense">ລາຍຈ່າຍ</label>

                            <input type="radio" class="btn-check" name="type_opt" id="opt_income" autocomplete="off" onclick="setType('income')">
                            <label class="btn btn-outline-success" for="opt_income">ລາຍຮັບ</label>
                        </div>
                    </div>

                    <form action="save_transaction.php" method="POST">
                        <input type="hidden" name="type" id="typeInput" value="expense">
                        
                        <div class="mb-3">
                            <label class="small text-muted mb-1">ຈຳນວນເງິນ</label>
                            <input type="text" class="form-control fs-1 fw-bold text-center text-primary border-0 bg-light" name="amount" id="amount" placeholder="0" inputmode="decimal" required>
                        </div>

                        <div class="mb-3">
                            <label class="small text-muted mb-1">ລາຍລະອຽດ</label>
                            <input type="text" class="form-control form-control-custom" name="description" required placeholder="ຂຽນລາຍລະອຽດ...">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="small text-muted mb-1">ໝວດໝູ່</label>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="small text-decoration-none fw-bold text-primary">
                                    + ເພີ່ມໝວດໝູ່
                                </a>
                            </div>
                            <select class="form-select form-control-custom" id="cat_expense" name="category_id">
                                <option value="">-- ເລືອກໝວດໝູ່ --</option>
                                <?php foreach($cats_expense as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select form-control-custom d-none" id="cat_income" name="category_id_income" disabled>
                                <option value="">-- ເລືອກໝວດໝູ່ --</option>
                                <?php foreach($cats_income as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="small text-muted mb-1">ວັນທີ</label>
                            <input type="text" class="form-control form-control-custom bg-white" id="datePicker" name="transaction_date" required placeholder="ເລືອກວັນທີ">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 rounded-4 fw-bold shadow-sm" style="background: var(--primary-gradient); border: none;">ບັນທຶກລາຍການ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">ເພີ່ມໝວດໝູ່</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="save_category.php" method="POST">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-custom" name="category_name" required placeholder="ຊື່ໝວດໝູ່...">
                        </div>
                        <div class="mb-3">
                            <select class="form-select form-control-custom" name="category_type">
                                <option value="expense">ລາຍຈ່າຍ</option>
                                <option value="income">ລາຍຮັບ</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-sm rounded-pill py-2">ບັນທຶກ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/la.js"></script>

    <script>
        // 1. ຕັ້ງຄ່າ Flatpickr ສຳລັບເລືອກເດືອນ (Month Picker)
        flatpickr("#monthPicker", {
            plugins: [
                new monthSelectPlugin({
                    shorthand: true, 
                    dateFormat: "Y-m", // ສົ່ງຄ່າເປັນ 2024-01
                    altFormat: "F Y", // ສະແດງຜົນເປັນ January 2024
                    theme: "dark"
                })
            ],
            locale: "la", // ພາສາລາວ
            disableMobile: "true", // ບັງຄັບໃຊ້ theme ຂອງ library ແທນຂອງມືຖື
            onChange: function(selectedDates, dateStr, instance) {
                // ເມື່ອເລືອກເດືອນແລ້ວ ໃຫ້ reload ໜ້າເວັບ
                window.location.href = "?month=" + dateStr;
            }
        });

        // 2. ຕັ້ງຄ່າ Flatpickr ສຳລັບວັນທີທົ່ວໄປ (Date Picker)
        flatpickr("#datePicker", {
            dateFormat: "Y-m-d",
            defaultDate: "today",
            locale: "la",
            disableMobile: "true",
            altInput: true,
            altFormat: "j F Y"
        });

        // 3. Format ຕົວເລກເງິນ
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (!isNaN(value) && value.length > 0) {
                e.target.value = Number(value).toLocaleString('en-US');
            }
        });

        // 4. Chart.js (ກາບຟິກ)
        <?php if(count($chart_data) > 0): ?>
        const ctx = document.getElementById('expenseChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { 
                            usePointStyle: true,
                            font: { family: "'Noto Sans Lao', sans-serif" }
                        }
                    }
                },
                cutout: '70%', // ເຮັດໃຫ້ຮູທາງກາງກວ້າງຂຶ້ນ
            }
        });
        <?php endif; ?>

        // 5. Toggle Income/Expense Types
        function setType(type) {
            document.getElementById('typeInput').value = type;
            const expenseSelect = document.getElementById('cat_expense');
            const incomeSelect = document.getElementById('cat_income');
            
            if(type === 'income') {
                expenseSelect.classList.add('d-none'); expenseSelect.disabled = true;
                incomeSelect.classList.remove('d-none'); incomeSelect.disabled = false;
                incomeSelect.name = 'category_id'; 
            } else {
                incomeSelect.classList.add('d-none'); incomeSelect.disabled = true;
                expenseSelect.classList.remove('d-none'); expenseSelect.disabled = false;
                expenseSelect.name = 'category_id'; 
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>