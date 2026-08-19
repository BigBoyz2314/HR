<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$empID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch active employees list sorted by employee code ascending
$empListRes = $conn->query("SELECT employeeID, sNo, fname, mname, lname, employee_code, department FROM employees ORDER BY CAST(COALESCE(NULLIF(sNo, 0), NULLIF(employee_code, ''), employeeID) AS UNSIGNED) ASC, employeeID ASC");

// If no employee selected, default to first employee
if ($empID <= 0 && $empListRes && $empListRes->num_rows > 0) {
    $firstRow = $empListRes->fetch_assoc();
    $empID = $firstRow['employeeID'];
    $empListRes->data_seek(0);
}

// Fetch selected employee details
$selectedEmp = null;
if ($empID > 0) {
    $eStmt = $conn->query("SELECT * FROM employees WHERE employeeID = '$empID'");
    if ($eStmt && $eStmt->num_rows > 0) {
        $selectedEmp = $eStmt->fetch_assoc();
    }
}

// Fetch ledger entries and compute running balance if needed
$ledgerEntries = [];
$totalDebit = 0;
$totalCredit = 0;
$runningBalance = 0;

if ($empID > 0) {
    $lRes = $conn->query("SELECT * FROM employee_ledger WHERE employeeID = '$empID' ORDER BY trans_date ASC, id ASC");
    if ($lRes && $lRes->num_rows > 0) {
        while ($lRow = $lRes->fetch_assoc()) {
            $debit = floatval($lRow['debit']);
            $credit = floatval($lRow['credit']);
            
            $totalDebit += $debit;
            $totalCredit += $credit;
            
            // Credit increases payable, Debit decreases payable (paying off)
            $runningBalance += ($credit - $debit);
            $lRow['calculated_balance'] = $runningBalance;
            $ledgerEntries[] = $lRow;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Ledger & Advance Payments - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("select.searchable-select").forEach(function(el) {
                new TomSelect(el, {
                    create: false
                });
            });
        });
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true', showPayModal: false }">
    
    <!-- Top Header Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Payroll Accounting</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Employee Ledger & Payments</h1>
                    <p class="text-sm text-slate-500 mt-1">Track accrued salary, payments, advance salary, and print thermal vouchers.</p>
                </div>

                <?php if ($empID > 0 && $_SESSION['role'] == '1'): ?>
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="showPayModal = true" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition flex items-center space-x-2">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <span>+ Pay Advance / Salary</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3 text-xs font-bold">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
                        <span><?php echo htmlspecialchars($_GET['msg']); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Employee Selection & Summary Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                
                <!-- Employee Selector -->
                <div class="lg:col-span-1 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Select Employee</label>
                    <form action="employee-ledger.php" method="get">
                        <select name="id" onchange="this.form.submit()" class="searchable-select w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs font-semibold bg-white transition">
                            <?php
                            if ($empListRes && $empListRes->num_rows > 0) {
                                while($eRow = $empListRes->fetch_assoc()) {
                                    $eName = trim($eRow['fname'] . ' ' . $eRow['mname'] . ' ' . $eRow['lname']);
                                    $eCode = !empty($eRow['employee_code']) ? $eRow['employee_code'] : sprintf('%04d', $eRow['employeeID']);
                                    $sel = ($eRow['employeeID'] == $empID) ? 'selected' : '';
                                    echo '<option value="' . $eRow['employeeID'] . '" ' . $sel . '>#' . htmlspecialchars($eCode) . ' - ' . htmlspecialchars($eName) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </form>

                    <?php if ($selectedEmp): ?>
                        <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs">
                            <div class="flex justify-between text-slate-500">
                                <span>Department:</span>
                                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($selectedEmp['department'] ?: 'General'); ?></span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Basic Salary:</span>
                                <span class="font-bold text-slate-800">PKR <?php echo number_format($selectedEmp['basic_salary']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3 Summary Metrics -->
                <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <!-- Total Accrued (Credit) -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Salary Accrued</span>
                            <h3 class="text-xl font-extrabold text-slate-900 mt-1">PKR <?php echo number_format(round($totalCredit)); ?></h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-file-invoice"></i>
                        </div>
                    </div>

                    <!-- Total Paid (Debit) -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Paid / Advances</span>
                            <h3 class="text-xl font-extrabold text-emerald-600 mt-1">PKR <?php echo number_format(round($totalDebit)); ?></h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                    </div>

                    <!-- Net Balance -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Net Balance</span>
                            <h3 class="text-xl font-extrabold mt-1 <?php echo $runningBalance > 0 ? 'text-amber-600' : ($runningBalance < 0 ? 'text-rose-600' : 'text-emerald-600'); ?>">
                                PKR <?php echo number_format(round(abs($runningBalance))); ?>
                            </h3>
                            <span class="text-[10px] font-semibold text-slate-500">
                                <?php echo $runningBalance > 0 ? '(Payable to Employee)' : ($runningBalance < 0 ? '(Advance Overpaid)' : '(Settled)'); ?>
                            </span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Ledger Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-base font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-list-check text-indigo-600 mr-2"></i>
                        Statement of Account Ledger
                    </h2>
                    <span class="text-xs font-semibold text-slate-500"><?php echo count($ledgerEntries); ?> Transactions</span>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4 rounded-l-xl whitespace-nowrap">Date & Time</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Period</th>
                                <th class="py-3.5 px-4 whitespace-nowrap">Transaction Type</th>
                                <th class="py-3.5 px-4">Description / Notes</th>
                                <th class="py-3.5 px-4 text-right whitespace-nowrap">Debit (Paid)</th>
                                <th class="py-3.5 px-4 text-right whitespace-nowrap">Credit (Accrued)</th>
                                <th class="py-3.5 px-4 text-right whitespace-nowrap">Running Balance</th>
                                <th class="py-3.5 px-4 text-center rounded-r-xl whitespace-nowrap">Voucher</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                            <?php
                            if (count($ledgerEntries) > 0) {
                                foreach ($ledgerEntries as $entry) {
                                    $dateStr = date('d-M-Y h:i A', strtotime($entry['trans_date']));
                                    $periodStr = ($entry['month'] > 0) ? date('M Y', mktime(0,0,0,$entry['month'],1, $entry['year'])) : '-';
                                    $isDisbursement = ($entry['type'] === 'Salary Payment' || $entry['type'] === 'Advance Payment');
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 text-slate-600 font-mono whitespace-nowrap"><?php echo $dateStr; ?></td>
                                        <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap"><?php echo $periodStr; ?></td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <?php if ($entry['type'] == 'Advance Payment'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px] uppercase whitespace-nowrap select-none">Advance Salary</span>
                                            <?php elseif ($entry['type'] == 'Salary Payment'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase whitespace-nowrap select-none">Salary Paid</span>
                                            <?php elseif (strpos($entry['type'], 'Overtime') !== false): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] uppercase whitespace-nowrap select-none">Overtime Accrued</span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px] uppercase whitespace-nowrap select-none">Salary Accrued</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600"><?php echo htmlspecialchars($entry['description'] ?: '-'); ?></td>
                                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                            <?php echo floatval($entry['debit']) > 0 ? 'PKR ' . number_format(round($entry['debit'])) : '-'; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono font-bold text-blue-600">
                                            <?php echo floatval($entry['credit']) > 0 ? 'PKR ' . number_format(round($entry['credit'])) : '-'; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                            PKR <?php echo number_format(round($entry['calculated_balance'])); ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <?php if ($isDisbursement): ?>
                                                <a href="print-voucher.php?id=<?php echo $entry['id']; ?>" target="_blank" class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] border border-indigo-200 transition">
                                                    <i class="fa-solid fa-receipt text-[10px]"></i>
                                                    <span>Print Voucher</span>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" class="py-8 text-center text-slate-400 font-medium">No ledger transactions recorded for this employee yet.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal for Advance / Salary Payment -->
    <?php if ($selectedEmp): ?>
        <div x-cloak x-show="showPayModal" style="display: none !important;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto my-auto" @click.away="showPayModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-extrabold text-slate-900 flex items-center">
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-600 mr-2"></i>
                        Record Payment / Advance
                    </h3>
                    <button @click="showPayModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <form action="includes/pay-advance.php" method="post" class="space-y-4">
                    <input type="hidden" name="employeeID" value="<?php echo $empID; ?>">

                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Employee</label>
                            <div class="text-xs font-extrabold text-slate-900 truncate">
                                <?php echo htmlspecialchars(trim($selectedEmp['fname'].' '.$selectedEmp['lname'])); ?>
                            </div>
                            <span class="text-[10px] font-semibold text-slate-500">#<?php echo htmlspecialchars($selectedEmp['employee_code'] ?: sprintf('%04d', $selectedEmp['employeeID'])); ?></span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Current Net Balance</label>
                            <div class="text-xs font-extrabold font-mono <?php echo $runningBalance > 0 ? 'text-amber-600' : ($runningBalance < 0 ? 'text-rose-600' : 'text-emerald-600'); ?>">
                                PKR <?php echo number_format(abs($runningBalance), 2); ?>
                            </div>
                            <span class="text-[10px] font-bold <?php echo $runningBalance > 0 ? 'text-amber-600' : ($runningBalance < 0 ? 'text-rose-600' : 'text-emerald-600'); ?>">
                                <?php echo $runningBalance > 0 ? '(Payable to Employee)' : ($runningBalance < 0 ? '(Advance Overpaid)' : '(Settled)'); ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Type *</label>
                        <select name="type" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold bg-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Advance Payment">Advance Salary Payment</option>
                            <option value="Salary Payment">Regular Salary Payment</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Month</label>
                            <select name="month" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                <?php
                                for($m=1; $m<=12; $m++) {
                                    $sel = ($m == date('n')) ? 'selected' : '';
                                    echo '<option value="'.$m.'" '.$sel.'>'.date('F', mktime(0,0,0,$m,1)).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Year</label>
                            <input type="number" name="year" value="<?php echo date('Y'); ?>" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Amount (PKR) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="5000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 font-extrabold text-slate-900 text-base">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Mode</label>
                        <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Notes / Description</label>
                        <input type="text" name="notes" placeholder="e.g. Advance requested for medical expense" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <div class="pt-2 flex justify-end space-x-3">
                        <button type="button" @click="showPayModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 text-white font-bold text-xs shadow-md">Record & Print Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>
