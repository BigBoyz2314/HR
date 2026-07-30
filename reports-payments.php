<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$month = isset($_GET["month"]) ? intval($_GET["month"]) : intval(date('m'));
$year = isset($_GET["year"]) ? intval($_GET["year"]) : intval(date('Y'));
$type_filter = isset($_GET["type"]) ? trim($_GET["type"]) : '';
$month_name = date('F', mktime(0, 0, 0, $month, 1, $year));

$where = ["l.debit > 0", "l.type IN ('Salary Payment', 'Advance Payment')", "(l.month = $month AND l.year = $year OR (l.month = 0 AND MONTH(l.trans_date) = $month AND YEAR(l.trans_date) = $year))"];
if (!empty($type_filter)) {
    $tpEsc = $conn->real_escape_string($type_filter);
    $where[] = "l.type = '$tpEsc'";
}

$whereClause = implode(' AND ', $where);

$sql = "SELECT l.*, e.sNo, e.employee_code, e.fname, e.mname, e.lname, e.department, e.designation 
        FROM employee_ledger l
        JOIN employees e ON l.employeeID = e.employeeID
        WHERE $whereClause
        ORDER BY l.trans_date ASC, l.id ASC";
$res = $conn->query($sql);

$records = [];
$total_paid = 0.0;
$total_salary_paid = 0.0;
$total_advance_paid = 0.0;

if ($res) {
    while ($r = $res->fetch_assoc()) {
        $records[] = $r;
        $d = floatval($r['debit']);
        $total_paid += $d;
        if ($r['type'] === 'Salary Payment') {
            $total_salary_paid += $d;
        } elseif ($r['type'] === 'Advance Payment') {
            $total_advance_paid += $d;
        }
    }
}

// Excel Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $filename = "Payment_Report_" . $month_name . "_" . $year . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <table border="1">
        <thead>
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <th>Date & Time</th>
                <th>Voucher #</th>
                <th>Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Payment Type</th>
                <th>Description / Mode</th>
                <th>Paid Amount (PKR)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $r): ?>
                <?php 
                $code = !empty($r['sNo']) ? $r['sNo'] : (!empty($r['employee_code']) ? $r['employee_code'] : $r['employeeID']);
                $fullName = trim($r['fname'] . ' ' . $r['mname'] . ' ' . $r['lname']);
                ?>
                <tr>
                    <td><?php echo date('d-M-Y h:i A', strtotime($r['trans_date'])); ?></td>
                    <td>#VCH-<?php echo sprintf('%05d', $r['id']); ?></td>
                    <td>#<?php echo htmlspecialchars($code); ?></td>
                    <td><?php echo htmlspecialchars($fullName); ?></td>
                    <td><?php echo htmlspecialchars($r['department']); ?></td>
                    <td><?php echo htmlspecialchars($r['type']); ?></td>
                    <td><?php echo htmlspecialchars($r['description']); ?></td>
                    <td><?php echo number_format(round($r['debit'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="7">TOTAL DISBURSEMENTS FOR PERIOD</td>
                <td><?php echo number_format(round($total_paid)); ?></td>
            </tr>
        </tfoot>
    </table>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment & Disbursement Report - <?php echo $month_name . ' ' . $year; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .print-only { display: none; }
        @media print {
            .no-print, header, aside, button, form, nav { display: none !important; }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                height: auto !important;
                min-height: 100% !important;
                overflow: visible !important;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                font-size: 11pt !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            div, main, section, article {
                background: transparent !important;
                background-color: transparent !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                position: static !important;
                box-shadow: none !important;
                border: none !important;
            }
            .print-only { display: block !important; }
            .print-card { padding: 0 !important; margin: 0 !important; background: transparent !important; }
            
            /* Sharp rectangular tables */
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px !important;
                margin-bottom: 20px !important;
                border: 1px solid #000000 !important;
                border-radius: 0 !important;
            }
            tr {
                page-break-inside: avoid !important;
                background: transparent !important;
                background-color: transparent !important;
            }
            th, td {
                border: 1px solid #333333 !important;
                border-radius: 0 !important;
                padding: 5px 7px !important;
                font-size: 11pt !important;
                background: transparent !important;
                background-color: transparent !important;
                color: #000000 !important;
            }
            th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                border-radius: 0 !important;
                text-transform: uppercase !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            tfoot tr, tfoot td {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                font-weight: 800 !important;
                border-top: 2px solid #000000 !important;
                border-radius: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .rounded-l-xl, .rounded-r-xl, .rounded-xl, .rounded-lg, .rounded-full, .rounded {
                border-radius: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 12mm 18mm 12mm;
                @bottom-right {
                    content: "Pg. " counter(page) " / " counter(pages);
                    font-family: 'Segoe UI', sans-serif;
                    font-size: 9pt;
                    font-weight: bold;
                    color: #000000;
                }
                @bottom-left {
                    content: "Footprint HR Portal - Official Executive Report";
                    font-family: 'Segoe UI', sans-serif;
                    font-size: 8pt;
                    color: #475569;
                }
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 no-print">
                <div>
                    <div class="flex items-center space-x-2 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Executive Reports</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Payment & Disbursement Report</h1>
                    <p class="text-sm text-slate-500 mt-1">Official register of cash/bank salary and advance payouts for <strong><?php echo $month_name . ' ' . $year; ?></strong>.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form action="reports-payments.php" method="get" class="flex flex-wrap items-center gap-2">
                        <select name="month" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $sel = ($m == $month) ? 'selected' : '';
                                echo '<option value="' . $m . '" ' . $sel . '>' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>';
                            }
                            ?>
                        </select>
                        <select name="year" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <?php
                            $currY = intval(date('Y'));
                            for ($y = $currY - 1; $y <= $currY + 2; $y++) {
                                $sel = ($y == $year) ? 'selected' : '';
                                echo '<option value="' . $y . '" ' . $sel . '>' . $y . '</option>';
                            }
                            ?>
                        </select>
                        <select name="type" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <option value="">All Payment Types</option>
                            <option value="Salary Payment" <?php echo $type_filter === 'Salary Payment' ? 'selected' : ''; ?>>Salary Payment</option>
                            <option value="Advance Payment" <?php echo $type_filter === 'Advance Payment' ? 'selected' : ''; ?>>Advance Payment</option>
                        </select>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition">Filter</button>
                    </form>

                    <a href="reports-payments.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>&type=<?php echo urlencode($type_filter); ?>&export=excel" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Export Excel</span>
                    </a>

                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-print"></i>
                        <span>Print Report</span>
                    </button>
                </div>
            </div>

            <!-- Print Official Header -->
            <div class="print-only mb-6 border-b-2 border-slate-900 pb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-black uppercase text-slate-900">Footprint HR Portal</h1>
                        <h2 class="text-base font-bold text-emerald-700">Official Payment & Disbursement Register Report</h2>
                        <p class="text-xs text-slate-600">Period: <strong><?php echo $month_name . ' ' . $year; ?></strong> | Printed On: <strong><?php echo date('d-M-Y h:i A'); ?></strong> | By: <strong><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></strong></p>
                    </div>
                    <div class="text-right">
                        <img src="images/Footprint+Logo.png" alt="Logo" class="h-10 w-auto">
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 no-print">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Disbursements</span>
                        <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">PKR <?php echo number_format(round($total_paid)); ?></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Salary Paid</span>
                        <h3 class="text-2xl font-extrabold text-indigo-600 mt-1">PKR <?php echo number_format(round($total_salary_paid)); ?></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Advances Paid</span>
                        <h3 class="text-2xl font-extrabold text-purple-600 mt-1">PKR <?php echo number_format(round($total_advance_paid)); ?></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-handshake-simple"></i>
                    </div>
                </div>
            </div>

            <!-- Payment Register Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 space-y-4 print-card">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 no-print">
                    <h2 class="text-base font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-receipt text-emerald-600 mr-2"></i>
                        Disbursement Register &bull; <?php echo $month_name . ' ' . $year; ?>
                    </h2>
                    <span class="text-xs font-semibold text-slate-500"><?php echo count($records); ?> Transactions</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm md:text-base">
                        <thead>
                            <tr class="bg-slate-900 text-white font-bold uppercase tracking-wider">
                                <th class="py-3 px-3 rounded-l-xl text-center w-[50px]">Sr.</th>
                                <th class="py-3 px-4">Date & Time</th>
                                <th class="py-3 px-4">Voucher #</th>
                                <th class="py-3 px-4">Code</th>
                                <th class="py-3 px-4">Employee Name</th>
                                <th class="py-3 px-4">Department</th>
                                <th class="py-3 px-4 text-center">Type</th>
                                <th class="py-3 px-4">Description / Mode</th>
                                <th class="py-3 px-4 text-right rounded-r-xl">Paid Amount (PKR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (count($records) > 0): ?>
                                <?php $sr_no = 0; foreach ($records as $r): $sr_no++; ?>
                                    <?php 
                                    $code = !empty($r['sNo']) ? $r['sNo'] : (!empty($r['employee_code']) ? $r['employee_code'] : $r['employeeID']);
                                    $fullName = trim($r['fname'] . ' ' . $r['mname'] . ' ' . $r['lname']);
                                    $typeBadge = ($r['type'] === 'Salary Payment') ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800';
                                    ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-3 font-mono font-bold text-slate-500 text-center"><?php echo $sr_no; ?></td>
                                        <td class="py-3 px-4 font-mono text-slate-600"><?php echo date('d-M-Y h:i A', strtotime($r['trans_date'])); ?></td>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-800">#VCH-<?php echo sprintf('%05d', $r['id']); ?></td>
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-700">#<?php echo htmlspecialchars($code); ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-900"><?php echo htmlspecialchars($fullName); ?></td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($r['department'] ?: 'General'); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase <?php echo $typeBadge; ?>"><?php echo htmlspecialchars($r['type']); ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($r['description'] ?: '-'); ?></td>
                                        <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-700">PKR <?php echo number_format(round($r['debit'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 font-bold">No disbursement transactions recorded for <?php echo $month_name . ' ' . $year; ?>.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-900 text-white font-extrabold text-xs">
                                <td colspan="7" class="py-3 px-4 rounded-l-xl">TOTAL DISBURSEMENTS FOR PERIOD</td>
                                <td class="py-3 px-4 text-right font-mono text-emerald-300 rounded-r-xl">PKR <?php echo number_format(round($total_paid)); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Print Authorization Signatures -->
            <div class="print-only pt-12 mt-8 border-t border-slate-300">
                <div class="grid grid-cols-3 gap-8 text-center text-xs font-bold text-slate-800">
                    <div>
                        <div class="border-b border-slate-900 mb-2 pb-8"></div>
                        <span>Prepared By (Accountant)</span>
                    </div>
                    <div>
                        <div class="border-b border-slate-900 mb-2 pb-8"></div>
                        <span>Verified By (Finance)</span>
                    </div>
                    <div>
                        <div class="border-b border-slate-900 mb-2 pb-8"></div>
                        <span>Approved By (Director)</span>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
