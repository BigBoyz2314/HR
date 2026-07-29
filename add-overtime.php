<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: login.php");
    exit;
}
require_once('includes/config.php');
require_once('includes/gen-salary-helper.php');

date_default_timezone_set('Asia/Karachi');

$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1 || $month > 12) $month = intval(date('m'));
if ($year < 2000 || $year > 2100) $year = intval(date('Y'));

$month_name = date('F', mktime(0, 0, 0, $month, 10));
$msg = '';
$msg_type = '';

// Handle POST Save Overtime Hours
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_overtime'])) {
    if (isset($_POST['overtime']) && is_array($_POST['overtime'])) {
        $saved_count = 0;
        foreach ($_POST['overtime'] as $emp_id => $ot_data) {
            $emp_id = intval($emp_id);
            $hours = floatval($ot_data['hours'] ?? 0);
            $rate = floatval($ot_data['rate'] ?? 0);
            $amount = round($hours * $rate, 2);
            $remarks = trim($ot_data['remarks'] ?? '');

            if ($hours > 0 || $amount > 0) {
                $stmt = $conn->prepare("INSERT INTO overtime (employeeID, month, year, hours, rate, amount, remarks, updated_at) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) 
                                        ON DUPLICATE KEY UPDATE hours = VALUES(hours), rate = VALUES(rate), amount = VALUES(amount), remarks = VALUES(remarks), updated_at = NOW()");
                $stmt->bind_param("iiiddss", $emp_id, $month, $year, $hours, $rate, $amount, $remarks);
                $stmt->execute();
                $stmt->close();
                $saved_count++;
            } else {
                // If 0 hours, delete entry for this month
                $conn->query("DELETE FROM overtime WHERE employeeID = $emp_id AND month = $month AND year = $year");
            }
        }

        // Re-sync Salary Sheet automatically for this month
        autoGenerateSalarySheet($conn, $month, $year, $_SESSION['name'] ?? 'Admin');

        $msg = "Successfully saved overtime records for $month_name $year and updated Salary Sheets!";
        $msg_type = "success";
    }
}

// Fetch existing overtime records for this month/year
$existing_ot = [];
$ot_res = $conn->query("SELECT * FROM overtime WHERE month = $month AND year = $year");
if ($ot_res) {
    while ($r = $ot_res->fetch_assoc()) {
        $existing_ot[$r['employeeID']] = $r;
    }
}

// Fetch all active employees sorted by employee code ascending
$emp_sql = "SELECT sNo, employeeID, fname, mname, lname, basic_salary, days_working, working_hours, overtime_rate, department, designation FROM employees WHERE status = 'Active' ORDER BY CAST(COALESCE(NULLIF(sNo, 0), NULLIF(employee_code, ''), employeeID) AS UNSIGNED) ASC, employeeID ASC";
$emp_res = $conn->query($emp_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Overtime Hours - <?php echo $month_name . ' ' . $year; ?> - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true', search: '' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- Page Header & Month Selector -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-amber-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-clock text-amber-500"></i>
                            <span>Payroll & Overtime Processing</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Add Overtime Hours</h1>
                        <p class="text-sm text-slate-500 mt-1">Enter monthly overtime hours for staff. Hourly rates and payout amounts are calculated automatically.</p>
                    </div>

                    <!-- Month & Year Filter -->
                    <form action="add-overtime.php" method="get" class="flex flex-wrap items-center gap-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <select name="month" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 bg-white">
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $sel = ($m == $month) ? 'selected' : '';
                                echo '<option value="' . $m . '" ' . $sel . '>' . date("F", mktime(0, 0, 0, $m, 1)) . '</option>';
                            }
                            ?>
                        </select>
                        <select name="year" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 bg-white">
                            <?php
                            $currY = date('Y');
                            for ($y = $currY - 1; $y <= $currY + 2; $y++) {
                                $sel = ($y == $year) ? 'selected' : '';
                                echo '<option value="' . $y . '" ' . $sel . '>' . $y . '</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow transition">
                            <i class="fa-solid fa-filter mr-1"></i> Filter
                        </button>
                    </form>
                </div>

                <?php if (!empty($msg)): ?>
                <div class="p-4 rounded-xl text-sm font-bold flex items-center justify-between <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                    <span><?php echo htmlspecialchars($msg); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php endif; ?>

                <!-- Form Card -->
                <form action="add-overtime.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" method="post">
                    <input type="hidden" name="save_overtime" value="1">

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                        
                        <!-- Search & Quick Action Toolbar -->
                        <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="relative w-full md:w-80">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                <input type="text" x-model="search" placeholder="Search staff by ID, name..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-amber-500 bg-white">
                            </div>

                            <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-extrabold text-xs shadow-lg shadow-amber-500/25 transition flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Save All Overtime Hours</span>
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto max-h-[calc(100vh-320px)] overflow-y-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-slate-900 text-slate-100 sticky top-0 z-20 shadow-sm">
                                    <tr>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider">ID</th>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider">Employee Name</th>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider text-right">Basic Salary</th>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider text-center">Hourly OT Rate</th>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider text-center w-36">OT Hours</th>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider text-right">Calculated Payout</th>
                                        <th class="py-3.5 px-4 font-extrabold text-xs uppercase tracking-wider">Remarks / Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <?php if ($emp_res && $emp_res->num_rows > 0): ?>
                                        <?php while ($e = $emp_res->fetch_assoc()): ?>
                                            <?php
                                            $e_id = $e['employeeID'];
                                            $sNo = !empty($e['sNo']) ? $e['sNo'] : $e_id;
                                            $fullName = trim($e['fname'] . ' ' . $e['mname'] . ' ' . $e['lname']);
                                            $basic = floatval($e['basic_salary']);
                                            $dWork = !empty($e['days_working']) ? intval($e['days_working']) : 30;
                                            $wHours = !empty($e['working_hours']) ? floatval($e['working_hours']) : 8.00;
                                            
                                            // Compute Hourly Rate
                                            $customRate = floatval($e['overtime_rate'] ?? 0);
                                            $defaultRate = ($dWork * $wHours > 0) ? round($basic / ($dWork * $wHours), 2) : 0.00;
                                            $effectiveRate = ($customRate > 0) ? $customRate : $defaultRate;

                                            // Existing recorded overtime
                                            $rec_hours = isset($existing_ot[$e_id]) ? floatval($existing_ot[$e_id]['hours']) : 0;
                                            $rec_remarks = isset($existing_ot[$e_id]) ? $existing_ot[$e_id]['remarks'] : '';
                                            $rec_amount = round($rec_hours * $effectiveRate, 2);
                                            ?>
                                            <tr x-data="{ hrs: <?php echo $rec_hours; ?>, rate: <?php echo $effectiveRate; ?> }" 
                                                x-show="search === '' || '<?php echo strtolower(addslashes($fullName . ' ' . $sNo)); ?>'.includes(search.toLowerCase())"
                                                class="hover:bg-amber-50/40 transition-colors">
                                                
                                                <td class="py-3 px-4 font-mono font-bold text-amber-700">#<?php echo $sNo; ?></td>
                                                
                                                <td class="py-3 px-4">
                                                    <div class="font-extrabold text-slate-900"><?php echo htmlspecialchars($fullName); ?></div>
                                                    <div class="text-[11px] text-slate-500"><?php echo htmlspecialchars($e['designation'] ?: 'Staff'); ?> &bull; <?php echo htmlspecialchars($e['department'] ?: 'General'); ?></div>
                                                </td>

                                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-700">
                                                    PKR <?php echo number_format($basic, 2); ?>
                                                </td>

                                                <td class="py-3 px-4 text-center">
                                                    <input type="hidden" name="overtime[<?php echo $e_id; ?>][rate]" value="<?php echo $effectiveRate; ?>">
                                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-mono font-extrabold border border-slate-200">
                                                        PKR <?php echo number_format($effectiveRate, 2); ?>/hr
                                                    </span>
                                                    <?php if ($customRate > 0): ?>
                                                        <div class="text-[9px] font-black text-amber-600 uppercase mt-0.5">Custom</div>
                                                    <?php else: ?>
                                                        <div class="text-[9px] font-bold text-slate-400 mt-0.5">Default Formula</div>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="py-3 px-4 text-center">
                                                    <input type="number" step="0.5" min="0" max="300" name="overtime[<?php echo $e_id; ?>][hours]" x-model="hrs" placeholder="0.0" class="w-28 px-3 py-1.5 rounded-xl border border-slate-300 font-mono font-bold text-center text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white">
                                                </td>

                                                <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-700">
                                                    <span x-text="'PKR ' + (hrs * rate).toFixed(2)">PKR <?php echo number_format($rec_amount, 2); ?></span>
                                                </td>

                                                <td class="py-3 px-4">
                                                    <input type="text" name="overtime[<?php echo $e_id; ?>][remarks]" value="<?php echo htmlspecialchars($rec_remarks); ?>" placeholder="e.g. Project Overtime" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-amber-500 bg-white">
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Submit Footer -->
                        <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-medium">Overtime totals automatically sync with the monthly salary statement upon saving.</span>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-extrabold text-xs shadow-lg shadow-amber-500/25 transition">
                                Save All Overtime Hours
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </main>
    </div>

</body>
</html>
