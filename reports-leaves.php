<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$month = isset($_GET["month"]) ? intval($_GET["month"]) : intval(date('m'));
$year = isset($_GET["year"]) ? intval($_GET["year"]) : intval(date('Y'));
$status_filter = isset($_GET["status"]) ? trim($_GET["status"]) : '';
$type_filter = isset($_GET["type"]) ? trim($_GET["type"]) : '';
$month_name = date('F', mktime(0, 0, 0, $month, 1, $year));

$where = ["(MONTH(l.start_date) = $month AND YEAR(l.start_date) = $year) OR (MONTH(l.end_date) = $month AND YEAR(l.end_date) = $year)"];
if (!empty($status_filter)) {
    $stEsc = $conn->real_escape_string($status_filter);
    $where[] = "l.status = '$stEsc'";
}
if (!empty($type_filter)) {
    $tpEsc = $conn->real_escape_string($type_filter);
    $where[] = "l.leave_type = '$tpEsc'";
}

$whereClause = implode(' AND ', $where);

$sql = "SELECT l.*, e.sNo, e.employee_code, e.fname, e.mname, e.lname, e.department, e.designation 
        FROM leaves l
        JOIN employees e ON l.employeeID = e.employeeID
        WHERE $whereClause
        ORDER BY l.start_date DESC, e.sNo ASC";
$res = $conn->query($sql);

$records = [];
$total_approved_days = 0;
$leave_type_counts = [];

if ($res) {
    while ($r = $res->fetch_assoc()) {
        $st = new DateTime($r['start_date']);
        $en = new DateTime($r['end_date']);
        $days = $st->diff($en)->days + 1;
        $r['num_days'] = $days;
        $records[] = $r;

        if ($r['status'] === 'Approved') {
            $total_approved_days += $days;
        }

        $lType = $r['leave_type'];
        if (!isset($leave_type_counts[$lType])) {
            $leave_type_counts[$lType] = 0;
        }
        $leave_type_counts[$lType] += $days;
    }
}

// Excel Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $filename = "Leave_Report_" . $month_name . "_" . $year . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <table border="1">
        <thead>
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <th>Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Status</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $r): ?>
                <?php 
                $code = !empty($r['sNo']) ? $r['sNo'] : (!empty($r['employee_code']) ? $r['employee_code'] : $r['employeeID']);
                $fullName = trim($r['fname'] . ' ' . $r['mname'] . ' ' . $r['lname']);
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($code); ?></td>
                    <td><?php echo htmlspecialchars($fullName); ?></td>
                    <td><?php echo htmlspecialchars($r['department']); ?></td>
                    <td><?php echo htmlspecialchars($r['designation']); ?></td>
                    <td><?php echo htmlspecialchars($r['leave_type']); ?></td>
                    <td><?php echo date('d-M-Y', strtotime($r['start_date'])); ?></td>
                    <td><?php echo date('d-M-Y', strtotime($r['end_date'])); ?></td>
                    <td><?php echo $r['num_days']; ?></td>
                    <td><?php echo htmlspecialchars($r['status']); ?></td>
                    <td><?php echo htmlspecialchars($r['reason']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                <td colspan="7">TOTAL APPROVED LEAVE DAYS</td>
                <td><?php echo $total_approved_days; ?></td>
                <td colspan="2">-</td>
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
    <title>Employee Leave Report - <?php echo $month_name . ' ' . $year; ?></title>
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
                padding: 6px 8px !important;
                font-size: 9.5pt !important;
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
                    <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-plane-arrival"></i>
                        <span>Executive Reports</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Employee Leave Management Report</h1>
                    <p class="text-sm text-slate-500 mt-1">Official register of staff leave applications and approvals for <strong><?php echo $month_name . ' ' . $year; ?></strong>.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form action="reports-leaves.php" method="get" class="flex flex-wrap items-center gap-2">
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
                        <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                            <option value="">All Statuses</option>
                            <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition">Filter</button>
                    </form>

                    <a href="reports-leaves.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>&status=<?php echo urlencode($status_filter); ?>&export=excel" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-1.5">
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
                        <h2 class="text-base font-bold text-indigo-700">Official Staff Leave Management Report</h2>
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
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Applications</span>
                        <h3 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo count($records); ?> Records</h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Approved Leave Days</span>
                        <h3 class="text-2xl font-extrabold text-amber-600 mt-1"><?php echo $total_approved_days; ?> Days</h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Casual / Sick Breakdown</span>
                        <h3 class="text-base font-bold text-emerald-600 mt-1">
                            <?php 
                            $typeStrs = [];
                            foreach ($leave_type_counts as $t => $cnt) {
                                $typeStrs[] = "$t: {$cnt}d";
                            }
                            echo count($typeStrs) > 0 ? implode(' | ', $typeStrs) : 'No leaves recorded';
                            ?>
                        </h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-umbrella-beach"></i>
                    </div>
                </div>
            </div>

            <!-- Leave Register Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 space-y-4 print-card">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 no-print">
                    <h2 class="text-base font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-plane-arrival text-amber-500 mr-2"></i>
                        Leave Register &bull; <?php echo $month_name . ' ' . $year; ?>
                    </h2>
                    <span class="text-xs font-semibold text-slate-500"><?php echo count($records); ?> Entries Found</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-900 text-white font-bold uppercase tracking-wider">
                                <th class="py-3 px-4 rounded-l-xl">Code</th>
                                <th class="py-3 px-4">Employee Name</th>
                                <th class="py-3 px-4">Department / Designation</th>
                                <th class="py-3 px-4">Leave Type</th>
                                <th class="py-3 px-4 text-center">Start Date</th>
                                <th class="py-3 px-4 text-center">End Date</th>
                                <th class="py-3 px-4 text-center">Days</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 rounded-r-xl">Reason / Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (count($records) > 0): ?>
                                <?php foreach ($records as $r): ?>
                                    <?php 
                                    $code = !empty($r['sNo']) ? $r['sNo'] : (!empty($r['employee_code']) ? $r['employee_code'] : $r['employeeID']);
                                    $fullName = trim($r['fname'] . ' ' . $r['mname'] . ' ' . $r['lname']);
                                    $statusBadge = ($r['status'] === 'Approved') ? 'bg-emerald-100 text-emerald-800' : (($r['status'] === 'Rejected') ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800');
                                    ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-700">#<?php echo htmlspecialchars($code); ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-900"><?php echo htmlspecialchars($fullName); ?></td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($r['department'] ?: 'General'); ?> &bull; <?php echo htmlspecialchars($r['designation'] ?: 'Staff'); ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-800"><?php echo htmlspecialchars($r['leave_type']); ?></td>
                                        <td class="py-3 px-4 text-center font-mono"><?php echo date('d-M-Y', strtotime($r['start_date'])); ?></td>
                                        <td class="py-3 px-4 text-center font-mono"><?php echo date('d-M-Y', strtotime($r['end_date'])); ?></td>
                                        <td class="py-3 px-4 text-center font-bold text-slate-900 bg-slate-50 rounded-lg"><?php echo $r['num_days']; ?>d</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase <?php echo $statusBadge; ?>"><?php echo $r['status']; ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 italic"><?php echo htmlspecialchars($r['reason'] ?: '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400 font-bold">No leave applications recorded for <?php echo $month_name . ' ' . $year; ?>.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-900 text-white font-extrabold text-xs">
                                <td colspan="6" class="py-3 px-4 rounded-l-xl">TOTAL APPROVED LEAVE DAYS</td>
                                <td class="py-3 px-4 text-center font-mono text-amber-300"><?php echo $total_approved_days; ?> Days</td>
                                <td colspan="2" class="py-3 px-4 text-right rounded-r-xl">REGISTER SUMMARY</td>
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
                        <span>Prepared By (HR Manager)</span>
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
