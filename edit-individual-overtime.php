<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

require_once('includes/gen-salary-helper.php');

date_default_timezone_set('Asia/Karachi');

$emp_id = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
$month  = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year   = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1 || $month > 12) $month = intval(date('m'));
if ($year < 2000 || $year > 2100) $year = intval(date('Y'));

// Fetch Employee Record
$empQuery = $conn->query("SELECT sNo, employeeID, fname, mname, lname, basic_salary, days_working, working_hours, overtime_rate, department, designation, shift FROM employees WHERE employeeID = $emp_id OR sNo = $emp_id LIMIT 1");

if (!$empQuery || $empQuery->num_rows === 0) {
    header("Location: add-overtime.php");
    exit;
}

$emp = $empQuery->fetch_assoc();
$real_emp_id = $emp['employeeID'];
$sNo = !empty($emp['sNo']) ? $emp['sNo'] : $real_emp_id;
$fullName = trim($emp['fname'] . ' ' . $emp['mname'] . ' ' . $emp['lname']);
$basic = floatval($emp['basic_salary']);
$dWork = !empty($emp['days_working']) ? intval($emp['days_working']) : 30;
$wHours = !empty($emp['working_hours']) ? floatval($emp['working_hours']) : 8.00;

// Compute Effective Overtime Hourly Rate
$customRate = floatval($emp['overtime_rate'] ?? 0);
$defaultRate = ($dWork * $wHours > 0) ? round($basic / ($dWork * $wHours), 2) : 0.00;
$effectiveRate = ($customRate > 0) ? $customRate : $defaultRate;

$month_name = date('F', mktime(0, 0, 0, $month, 10));
$msg = '';
$msg_type = '';

// Handle POST: Add More Hours OR Direct Edit Hours
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action_type'])) {
    $action_type = trim($_POST['action_type']);
    $remarks     = trim($_POST['remarks'] ?? '');

    // Fetch existing overtime record for this month
    $existing_hrs = 0.0;
    $otCheck = $conn->query("SELECT hours FROM overtime WHERE employeeID = $real_emp_id AND month = $month AND year = $year LIMIT 1");
    if ($otCheck && $r = $otCheck->fetch_assoc()) {
        $existing_hrs = floatval($r['hours']);
    }

    if ($action_type === 'add_more') {
        $add_hrs = floatval($_POST['add_hours'] ?? 0);
        if ($add_hrs > 0) {
            $new_hrs = $existing_hrs + $add_hrs;
            $new_amount = round($new_hrs * $effectiveRate, 2);

            $stmt = $conn->prepare("INSERT INTO overtime (employeeID, month, year, hours, rate, amount, remarks, updated_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) 
                                    ON DUPLICATE KEY UPDATE hours = VALUES(hours), rate = VALUES(rate), amount = VALUES(amount), remarks = VALUES(remarks), updated_at = NOW()");
            $stmt->bind_param("iiiddss", $real_emp_id, $month, $year, $new_hrs, $effectiveRate, $new_amount, $remarks);
            $stmt->execute();
            $stmt->close();

            autoGenerateSalarySheet($conn, $month, $year, $_SESSION['name'] ?? 'Admin');
            $msg = "Added +{$add_hrs} overtime hours. Total is now {$new_hrs} hours (PKR " . number_format($new_amount, 2) . ").";
            $msg_type = "success";
        } else {
            $msg = "Please enter valid additional hours to add.";
            $msg_type = "error";
        }
    } elseif ($action_type === 'set_total') {
        $set_hrs = floatval($_POST['set_hours'] ?? 0);
        $new_amount = round($set_hrs * $effectiveRate, 2);

        if ($set_hrs > 0) {
            $stmt = $conn->prepare("INSERT INTO overtime (employeeID, month, year, hours, rate, amount, remarks, updated_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) 
                                    ON DUPLICATE KEY UPDATE hours = VALUES(hours), rate = VALUES(rate), amount = VALUES(amount), remarks = VALUES(remarks), updated_at = NOW()");
            $stmt->bind_param("iiiddss", $real_emp_id, $month, $year, $set_hrs, $effectiveRate, $new_amount, $remarks);
            $stmt->execute();
            $stmt->close();
        } else {
            $conn->query("DELETE FROM overtime WHERE employeeID = $real_emp_id AND month = $month AND year = $year");
        }

        autoGenerateSalarySheet($conn, $month, $year, $_SESSION['name'] ?? 'Admin');
        $msg = "Updated total overtime to {$set_hrs} hours (PKR " . number_format($new_amount, 2) . ").";
        $msg_type = "success";
    }
}

// Fetch Current Overtime Record
$current_hrs = 0.0;
$current_amount = 0.0;
$current_remarks = '';
$otRes = $conn->query("SELECT * FROM overtime WHERE employeeID = $real_emp_id AND month = $month AND year = $year LIMIT 1");
if ($otRes && $otRow = $otRes->fetch_assoc()) {
    $current_hrs = floatval($otRow['hours']);
    $current_amount = floatval($otRow['amount']);
    $current_remarks = $otRow['remarks'];
}

// All active employees for dropdown sorted by employee code ascending
$allEmps = $conn->query("SELECT employeeID, sNo, employee_code, fname, mname, lname FROM employees WHERE status = 'Active' ORDER BY CAST(COALESCE(NULLIF(sNo, 0), NULLIF(employee_code, ''), employeeID) AS UNSIGNED) ASC, employeeID ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Overtime - <?php echo htmlspecialchars($fullName); ?> - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("select.searchable-select").forEach(function(el) {
                new TomSelect(el, { create: false });
            });
        });
    </script>
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
            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-extrabold text-xl border border-amber-200">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 text-amber-600 text-xs font-bold uppercase tracking-wider">
                                <span>Individual Overtime &bull; <?php echo $month_name . ' ' . $year; ?></span>
                            </div>
                            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight"><?php echo htmlspecialchars($fullName); ?></h1>
                            <p class="text-xs text-slate-500 font-medium">Staff ID: <span class="font-bold text-amber-700">#<?php echo $sNo; ?></span> &bull; <?php echo htmlspecialchars($emp['designation'] ?: 'Staff'); ?> &bull; <?php echo htmlspecialchars($emp['department'] ?: 'General'); ?></p>
                        </div>
                    </div>

                    <!-- Switch Staff Dropdown -->
                    <form action="edit-individual-overtime.php" method="get" class="flex flex-wrap items-center gap-3">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <div class="w-64">
                            <select name="emp_id" onchange="this.form.submit()" class="searchable-select w-full bg-white">
                                <?php if ($allEmps && $allEmps->num_rows > 0): ?>
                                    <?php while($eItem = $allEmps->fetch_assoc()): ?>
                                        <?php 
                                        $itemCode = !empty($eItem['sNo']) ? $eItem['sNo'] : $eItem['employeeID'];
                                        $eFull = trim($eItem['fname'] . ' ' . $eItem['mname'] . ' ' . $eItem['lname']);
                                        $sel = ($eItem['employeeID'] == $real_emp_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $eItem['employeeID']; ?>" <?php echo $sel; ?>>#<?php echo $itemCode; ?> - <?php echo htmlspecialchars($eFull); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <a href="add-overtime.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition border border-slate-200">
                            Back
                        </a>
                    </form>
                </div>

                <?php if (!empty($msg)): ?>
                    <div class="p-4 rounded-xl text-sm font-bold flex items-center justify-between <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                        <span><?php echo htmlspecialchars($msg); ?></span>
                        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Current Overtime Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-extrabold text-xl">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-amber-700 font-mono"><?php echo number_format($current_hrs, 1); ?> <span class="text-xs font-bold">Hrs</span></div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Recorded OT</div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-extrabold text-xl">
                            <i class="fa-solid fa-calculator"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-indigo-900 font-mono">PKR <?php echo number_format($effectiveRate, 2); ?></div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hourly Rate <?php echo $customRate > 0 ? '(Custom)' : '(Formula)'; ?></div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-extrabold text-xl">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-emerald-700 font-mono">PKR <?php echo number_format($current_amount, 2); ?></div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Calculated Payout</div>
                        </div>
                    </div>
                </div>

                <!-- Actions: Add More vs Set Total -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Form 1: Add Additional Overtime Hours -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <div class="flex items-center space-x-2 text-slate-900 font-extrabold text-sm border-b border-slate-100 pb-3">
                            <i class="fa-solid fa-circle-plus text-amber-500"></i>
                            <span>Add Additional Overtime Hours</span>
                        </div>
                        <p class="text-xs text-slate-500">Append extra hours to existing total. (e.g. Current: <?php echo $current_hrs; ?> hrs + 2 hrs = <?php echo $current_hrs + 2; ?> hrs).</p>

                        <form action="edit-individual-overtime.php?emp_id=<?php echo $real_emp_id; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" method="post" class="space-y-4">
                            <input type="hidden" name="action_type" value="add_more">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hours to Add (+)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-amber-600 font-bold text-sm">+</span>
                                    <input type="number" step="0.5" min="0.5" max="100" name="add_hours" required placeholder="2.0" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Remarks / Reason</label>
                                <input type="text" name="remarks" value="<?php echo htmlspecialchars($current_remarks); ?>" placeholder="e.g. Weekend Special Project" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-amber-500">
                            </div>

                            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-extrabold text-xs shadow-md shadow-amber-500/25 transition">
                                Add Hours to Total
                            </button>
                        </form>
                    </div>

                    <!-- Form 2: Direct Edit / Set Total Overtime Hours -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <div class="flex items-center space-x-2 text-slate-900 font-extrabold text-sm border-b border-slate-100 pb-3">
                            <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
                            <span>Direct Edit / Set Total Overtime</span>
                        </div>
                        <p class="text-xs text-slate-500">Set exact total overtime hours for <?php echo $month_name . ' ' . $year; ?> directly.</p>

                        <form action="edit-individual-overtime.php?emp_id=<?php echo $real_emp_id; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" method="post" class="space-y-4">
                            <input type="hidden" name="action_type" value="set_total">

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exact Total Hours</label>
                                <input type="number" step="0.5" min="0" max="300" name="set_hours" value="<?php echo $current_hrs; ?>" required placeholder="0.0" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Remarks / Reason</label>
                                <input type="text" name="remarks" value="<?php echo htmlspecialchars($current_remarks); ?>" placeholder="e.g. Monthly Overtime Log" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-md transition">
                                Save Total Overtime
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </main>
    </div>

</body>
</html>
