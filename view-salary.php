<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

date_default_timezone_set('Asia/Karachi');


// Default to current month/year if not provided
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month & year
if ($month < 1 || $month > 12) $month = intval(date('m'));
if ($year < 2000 || $year > 2100) $year = intval(date('Y'));

$month_name = date('F', mktime(0, 0, 0, $month, 10));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Statement - <?php echo $month_name . ' ' . $year; ?> - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; font-size: 10pt; }
            .print-only { display: block !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #cbd5e1 !important; padding: 4px 6px !important; }
            .custom-scrollbar { overflow: visible !important; }
            .action-col { display: none !important; }
        }
        .print-only { display: none; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" 
      x-data="{ 
          sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true',
          genModal: <?php echo (isset($_GET['action']) && $_GET['action'] == 'gen_modal') ? 'true' : 'false'; ?>,
          adjModal: false,
          adjEmp: '',
          adjSalID: 0,
          adjEmpID: 0,
          adjArrears: 0,
          adjOt1: 0,
          adjOt2: 0,
          adjAllowance: 0,
          adjLoans: 0,
          adjAdvance: 0
      }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <!-- Printable Only Header -->
            <div class="print-only mb-4 text-center border-b pb-2">
                <h1 class="text-xl font-bold">FOOTPRINT HR - SALARY STATEMENT (<?php echo strtoupper($month_name . ' ' . $year); ?>)</h1>
                <p class="text-xs">Generated on: <?php echo date('d-M-Y h:i A'); ?></p>
            </div>

            <!-- Page Header -->
            <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 mb-6">
                <div>
                    <div class="flex items-center space-x-2 text-teal-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Payroll Statement &bull; <?php echo $month_name . ' ' . $year; ?></span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Salary Sheet</h1>
                    <p class="text-sm text-slate-500 mt-1">Detailed monthly breakdown of basic pay, overtime, allowances, loan/advance deductions, and payouts.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <?php if ($_SESSION['role'] == '1'): ?>
                        <button type="button" @click="genModal = true" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs shadow-lg shadow-teal-600/30 transition flex items-center space-x-2 cursor-pointer">
                            <i class="fa-solid fa-calculator"></i>
                            <span>Generate Payroll</span>
                        </button>
                    <?php endif; ?>
                    <button onclick="window.print()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-print text-slate-500"></i>
                        <span>Print</span>
                    </button>
                    <button onclick="exportTableToCSV('table', 'salary_sheet_<?php echo $month . '_' . $year; ?>.csv')" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-download text-slate-500"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Status Alert Notifications -->
            <?php if (isset($_GET['action']) || isset($_GET['msg'])): ?>
                <div class="no-print bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm mb-6">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                        <span class="text-sm font-semibold">
                            <?php 
                            if (isset($_GET['action']) && $_GET['action'] == 'generated') {
                                echo "Salary statement for $month_name $year generated successfully!";
                            } elseif (isset($_GET['action']) && $_GET['action'] == 'updated') {
                                echo "Monthly salary adjustments saved successfully!";
                            } else {
                                echo htmlspecialchars($_GET['msg'] ?? 'Operation completed successfully.');
                            }
                            ?>
                        </span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- Filter Controls -->
            <div class="no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 mb-6">
                <form method="get" action="view-salary.php" class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Select Month</label>
                            <select name="month" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-slate-50 focus:bg-white transition">
                                <?php for($m=1; $m<=12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Select Year</label>
                            <select name="year" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-slate-50 focus:bg-white transition">
                                <?php for($y=2024; $y<=2030; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition">
                                Filter Statement
                            </button>
                        </div>
                    </div>

                    <div class="text-xs font-semibold text-slate-500">
                        Payroll Month: <strong class="text-slate-800"><?php echo $month_name . ' ' . $year; ?></strong>
                    </div>
                </form>
            </div>

            <!-- Data Table Wrapper -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 space-y-4">
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="table" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-3 rounded-l-xl sticky left-0 z-20 bg-slate-900 shadow-[2px_0_5px_rgba(0,0,0,0.15)]">Code</th>
                                <th class="py-3.5 px-3">Employee Name</th>
                                <th class="py-3.5 px-3 text-right">Gross Salary</th>
                                <th class="py-3.5 px-3 text-center">Attendance Days</th>
                                <th class="py-3.5 px-3 text-right">Salary for Month</th>
                                <th class="py-3.5 px-3 text-right">Arrears</th>
                                <th class="py-3.5 px-3 text-right">Overtime</th>
                                <th class="py-3.5 px-3 text-right">Allowance + Advance</th>
                                <th class="py-3.5 px-3 text-right">Less Loans</th>
                                <th class="py-3.5 px-3 text-right">Less Advance</th>
                                <th class="py-3.5 px-3 text-right">Salary Payable</th>
                                <th class="py-3.5 px-3 text-right">Paid</th>
                                <th class="py-3.5 px-3 text-right">Remaining</th>
                                <th class="py-3.5 px-3 text-center rounded-r-xl action-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                            <?php
                            require_once('includes/gen-salary-helper.php');
                            
                            $stmt = "SELECT s.*, e.sNo, e.employee_code, e.allowance AS profile_allowance 
                                     FROM salary1 s 
                                     LEFT JOIN employees e ON s.employeeID = e.employeeID 
                                     WHERE s.month = '$month' AND s.year = '$year' 
                                     ORDER BY CAST(COALESCE(NULLIF(e.sNo, 0), NULLIF(e.employee_code, ''), s.employeeID) AS UNSIGNED) ASC, s.employeeID ASC";
                            $result = $conn->query($stmt);

                            // If no salary records generated yet for this month, auto-generate on-the-fly
                            if (!$result || $result->num_rows == 0) {
                                autoGenerateSalarySheet($conn, $month, $year);
                                $result = $conn->query($stmt);
                            }

                            $totGross = 0; $totEarned = 0; $totArrears = 0; $totOt1 = 0; $totOt2 = 0;
                            $totAllow = 0; $totLoans = 0; $totAdv = 0; $totPayable = 0; $totPaid = 0; $totRem = 0;

                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $fullName = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                    $displayCode = !empty($row['sNo']) ? $row['sNo'] : (!empty($row['employee_code']) ? $row['employee_code'] : sprintf('%04d', $row['employeeID']));
                                    
                                    $basicSalary = floatval($row['basic_salary']);
                                    $profileAllowance = floatval($row['profile_allowance'] ?? 0);
                                    $grossSalary = $basicSalary + $profileAllowance;
                                    $payDays = intval($row['pay_days']);
                                    
                                    $earnedBasic = ($payDays / 30.0) * $basicSalary;
                                    
                                    $arrears = floatval($row['arrears'] ?? 0);
                                    $totalOt = floatval($row['ot_1_15'] ?? 0) + floatval($row['ot_16_30'] ?? 0);
                                    $allowanceAdv = floatval($row['allowance'] ?? 0);
                                    $lessLoans = floatval($row['less_loans'] ?? 0);
                                    $lessAdvance = floatval($row['less_advance'] ?? 0);
                                    
                                    $payable = floatval($row['payable']);
                                    $paid = floatval($row['paid']);
                                    $remaining = floatval($row['remaining']);
                                    $empId = $row['employeeID'];
                                    $salID = $row['id'];

                                    $totGross += $grossSalary;
                                    $totEarned += $earnedBasic;
                                    $totArrears += $arrears;
                                    $totOt1 += $totalOt;
                                    $totAllow += $allowanceAdv;
                                    $totLoans += $lessLoans;
                                    $totAdv += $lessAdvance;
                                    $totPayable += $payable;
                                    $totPaid += $paid;
                                    $totRem += $remaining;
                                    ?>
                                    <tr class="group hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-3 font-mono font-bold text-slate-900 sticky left-0 z-10 bg-white group-hover:bg-slate-50 shadow-[2px_0_5px_rgba(0,0,0,0.05)] whitespace-nowrap"><?php echo htmlspecialchars($displayCode); ?></td>
                                        <td class="py-3 px-3 font-bold text-slate-900 whitespace-nowrap">
                                            <a href="employee-ledger.php?id=<?php echo $empId; ?>" class="hover:text-indigo-600 hover:underline transition-colors" title="Click to view Employee Ledger"><?php echo htmlspecialchars($fullName); ?></a>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap"><?php echo number_format($grossSalary); ?></td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-800 whitespace-nowrap"><?php echo $payDays; ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-slate-700 whitespace-nowrap"><?php echo number_format($earnedBasic); ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-slate-600 whitespace-nowrap"><?php echo $arrears > 0 ? number_format($arrears) : '-'; ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-amber-700 font-bold whitespace-nowrap"><?php echo $totalOt > 0 ? number_format($totalOt) : '-'; ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-emerald-600 font-bold whitespace-nowrap"><?php echo $allowanceAdv > 0 ? number_format($allowanceAdv) : '-'; ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-rose-600 whitespace-nowrap"><?php echo $lessLoans > 0 ? '-' . number_format($lessLoans) : '-'; ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-rose-600 whitespace-nowrap"><?php echo $lessAdvance > 0 ? '-' . number_format($lessAdvance) : '-'; ?></td>
                                        <td class="py-3 px-3 text-right font-mono font-extrabold text-slate-900 whitespace-nowrap"><?php echo number_format($payable); ?></td>
                                        <td class="py-3 px-3 text-right font-mono text-emerald-700 font-bold whitespace-nowrap"><?php echo number_format($paid); ?></td>
                                        <td class="py-3 px-3 text-right font-mono font-bold whitespace-nowrap <?php echo $remaining > 0 ? 'text-rose-600' : ($remaining < 0 ? 'text-indigo-600' : 'text-slate-400'); ?>">
                                            <?php echo number_format($remaining); ?>
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap action-col">
                                            <div class="inline-flex items-center space-x-1.5">
                                                <?php if ($_SESSION['role'] == '1'): ?>
                                                    <!-- Edit Monthly Adjustments Modal Trigger -->
                                                    <button type="button" 
                                                            @click="
                                                                adjModal = true; 
                                                                adjEmp = '<?php echo addslashes($fullName); ?> (Code: <?php echo $displayCode; ?>)';
                                                                adjSalID = <?php echo $salID; ?>;
                                                                adjEmpID = <?php echo $empId; ?>;
                                                                adjArrears = <?php echo $arrears; ?>;
                                                                adjOt1 = <?php echo $totalOt; ?>;
                                                                adjOt2 = 0;
                                                                adjAllowance = <?php echo $allowanceAdv; ?>;
                                                                adjLoans = <?php echo $lessLoans; ?>;
                                                                adjAdvance = <?php echo $lessAdvance; ?>;
                                                            " 
                                                            title="Edit Monthly Adjustments (Overtime, Arrears, Loans, Advances)" 
                                                            class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 transition">
                                                        <i class="fa-solid fa-sliders text-xs"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="14" class="py-8 text-center text-slate-400 font-medium">No salary records generated for ' . $month_name . ' ' . $year . '. Click "Re-Generate Salary" to process.</td></tr>';
                            }
                            ?>
                        </tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <tfoot>
                                <tr class="bg-slate-100 font-bold text-xs text-slate-900 border-t-2 border-slate-300">
                                    <td colspan="2" class="py-3 px-3">Total (<?php echo $result->num_rows; ?> Staff)</td>
                                    <td class="py-3 px-3 text-right font-mono"><?php echo number_format($totGross); ?></td>
                                    <td class="py-3 px-3 text-center">-</td>
                                    <td class="py-3 px-3 text-right font-mono"><?php echo number_format($totEarned); ?></td>
                                    <td class="py-3 px-3 text-right font-mono"><?php echo number_format($totArrears); ?></td>
                                    <td class="py-3 px-3 text-right font-mono text-amber-700"><?php echo number_format($totOt1); ?></td>
                                    <td class="py-3 px-3 text-right font-mono text-emerald-700"><?php echo number_format($totAllow); ?></td>
                                    <td class="py-3 px-3 text-right font-mono text-rose-600"><?php echo number_format($totLoans); ?></td>
                                    <td class="py-3 px-3 text-right font-mono text-rose-600"><?php echo number_format($totAdv); ?></td>
                                    <td class="py-3 px-3 text-right font-mono font-extrabold text-slate-900"><?php echo number_format($totPayable); ?></td>
                                    <td class="py-3 px-3 text-right font-mono text-emerald-700"><?php echo number_format($totPaid); ?></td>
                                    <td class="py-3 px-3 text-right font-mono text-rose-600"><?php echo number_format($totRem); ?></td>
                                    <td class="py-3 px-3 action-col"></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Edit Monthly Adjustments Modal -->
            <div x-cloak
                 x-show="adjModal" 
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                 style="display: none !important;">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-200 max-h-[90vh] overflow-y-auto my-auto" @click.away="adjModal = false">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center space-x-2 text-indigo-600 font-bold text-sm">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Edit Monthly Adjustments</span>
                        </div>
                        <button type="button" @click="adjModal = false" class="text-slate-400 hover:text-slate-600 text-sm"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <p class="text-xs text-slate-500">Updating adjustments for <strong class="text-slate-800" x-text="adjEmp"></strong> for <strong><?php echo $month_name . ' ' . $year; ?></strong>.</p>

                    <form action="includes/save-salary-adjustments.php" method="post" class="space-y-4">
                        <input type="hidden" name="salID" :value="adjSalID">
                        <input type="hidden" name="empID" :value="adjEmpID">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Arrears Payable</label>
                                <input type="number" step="0.01" min="0" name="arrears" :value="adjArrears" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Allowance + Advance</label>
                                <input type="number" step="0.01" min="0" name="allowance" :value="adjAllowance" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-semibold text-emerald-600">
                            </div>
                        </div>

                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center">
                                <span>Overtime</span>
                                <span class="ml-auto text-[9px] text-slate-400 font-semibold">(Auto Computed)</span>
                            </label>
                            <input type="text" readonly :value="'PKR ' + (parseFloat(adjOt1) || 0).toFixed(2)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-600 bg-slate-100 cursor-not-allowed select-none">
                            <input type="hidden" name="ot_1_15" :value="adjOt1">
                            <input type="hidden" name="ot_16_30" value="0">
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium italic -mt-2">
                            <i class="fa-solid fa-circle-info text-slate-400 mr-1"></i>
                            Overtime is managed automatically via Overtime Records and cannot be edited here.
                        </p>

                        <div class="grid grid-cols-2 gap-4 bg-rose-50/50 p-3 rounded-xl border border-rose-100">
                            <div>
                                <label class="block text-[11px] font-bold text-rose-800 uppercase tracking-wider mb-1">Less Loans (Deduction)</label>
                                <input type="number" step="0.01" min="0" name="less_loans" :value="adjLoans" class="w-full px-3 py-2 rounded-xl border border-rose-200 text-xs font-mono font-semibold text-rose-700">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-rose-800 uppercase tracking-wider mb-1">Less Advance (Deduction)</label>
                                <input type="number" step="0.01" min="0" name="less_advance" :value="adjAdvance" class="w-full px-3 py-2 rounded-xl border border-rose-200 text-xs font-mono font-semibold text-rose-700">
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-end space-x-3">
                            <button type="button" @click="adjModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md transition">Save Adjustments</button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <!-- Generate Payroll Modal Dialog -->
    <div x-cloak
         x-show="genModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none !important;">
        
        <div @click.away="genModal = false" 
             class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden my-8 max-h-[90vh] overflow-y-auto my-auto">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-600/30 text-teal-300 border border-teal-500/30 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold">Generate Monthly Payroll</h2>
                        <p class="text-xs text-slate-400">Calculate salary sheets for active staff</p>
                    </div>
                </div>
                <button type="button" @click="genModal = false" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form action="includes/generate-salary-new.php" method="get" class="p-6 space-y-5">
                <div class="bg-teal-50/60 border border-teal-100 rounded-2xl p-4 space-y-3">
                    <div class="flex items-start space-x-2 text-xs text-teal-900 leading-relaxed font-medium">
                        <i class="fa-solid fa-circle-info text-teal-600 mt-0.5 text-sm shrink-0"></i>
                        <span>Select month and year. Generating payroll will process attendance, basic pay, fixed allowances, and logged overtime, while retaining custom manual adjustments.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Select Month *</label>
                        <select name="month" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 text-sm bg-white font-semibold">
                            <?php
                            $currM = $month;
                            for ($i = 1; $i <= 12; $i++) {
                                $sel = ($i == $currM) ? 'selected' : '';
                                echo '<option value="' . $i . '" ' . $sel . '>' . date("F", mktime(0, 0, 0, $i, 1)) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Select Year *</label>
                        <select name="year" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 text-sm bg-white font-semibold">
                            <?php
                            $currY = $year;
                            for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++) {
                                $sel = ($y == $currY) ? 'selected' : '';
                                echo '<option value="' . $y . '" ' . $sel . '>' . $y . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="genModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-xs shadow-md shadow-teal-600/25 transition flex items-center space-x-2">
                        <i class="fa-solid fa-gears"></i>
                        <span>Run Payroll Generation</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Table to CSV Export Script -->
    <script>
        function exportTableToCSV(tableId, filename) {
            var csv = [];
            var rows = document.querySelectorAll("#" + tableId + " tr");
            
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].style.display === "none") continue;
                
                var row = [], cols = rows[i].querySelectorAll("td, th");
                var colCount = cols.length;
                
                for (var j = 0; j < colCount; j++) {
                    var headerText = (rows[0].querySelectorAll("th")[j] ? rows[0].querySelectorAll("th")[j].textContent : "").trim().toLowerCase();
                    if (headerText.includes("action")) continue;

                    var text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, " ").trim();
                    
                    // Clean PKR, #, +, - prefixes for pure data CSV exports
                    if (rows[i].parentElement.tagName.toLowerCase() === 'tbody' || rows[i].parentElement.tagName.toLowerCase() === 'tfoot') {
                        text = text.replace(/^#/, '');
                        text = text.replace(/^PKR\s*/i, '');
                        text = text.replace(/^[+\-]\s*PKR\s*/i, '');
                        text = text.replace(/^[+\-]/, '');
                    }

                    text = '"' + text.replace(/"/g, '""') + '"';
                    row.push(text);
                }
                if (row.length > 0) csv.push(row.join(","));
            }

            var csvFile = new Blob(["\uFEFF" + csv.join("\n")], { type: "text/csv;charset=utf-8;" });
            var downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>