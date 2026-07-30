<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


// Validate and sanitize GET parameters
$year = isset($_GET["year"]) ? intval($_GET["year"]) : date('Y');
$month = isset($_GET["month"]) ? intval($_GET["month"]) : date('n');

if ($year < 2000 || $year > 2100) $year = date('Y');
if ($month < 1 || $month > 12) $month = date('n');

$month_name = date('F', mktime(0, 0, 0, $month, 1, $year));
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$first_day = new DateTime("$year-$month-01");
$last_day = new DateTime("$year-$month-$days_in_month");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Attendance Summary Sheet - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="js/tableHTMLExport.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <div class="mx-auto space-y-6">
                
                <!-- Page Header Section -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Attendance Summary Grid</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Monthly Attendance Sheet</h1>
                        <p class="text-sm text-slate-500 mt-1">Status summary grid showing daily attendance records for <strong><?php echo $month_name . ' ' . $year; ?></strong>.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="view-attendance-times.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition inline-flex items-center space-x-2">
                            <i class="fa-solid fa-clock"></i>
                            <span>Punch In/Out Times View</span>
                        </a>
                        <button class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-md flex items-center space-x-2 export-btn">
                            <i class="fa-solid fa-file-excel"></i>
                            <span>Export Excel</span>
                        </button>
                        <button class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition shadow-md flex items-center space-x-2" id="browserPrint">
                            <i class="fa-solid fa-print"></i>
                            <span>Print PDF</span>
                        </button>
                    </div>
                </div>

                <!-- Filter & Search Bar -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <form action="view-attendance.php" method="get" class="flex flex-wrap items-center gap-4 flex-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Month</label>
                            <select name="month" class="px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs font-semibold bg-white">
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    $sel = ($i == $month) ? 'selected' : '';
                                    echo '<option value="' . $i . '" ' . $sel . '>' . date("F", mktime(0, 0, 0, $i, 1)) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Year</label>
                            <select name="year" class="px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs font-semibold bg-white">
                                <?php
                                $currY = date('Y');
                                for ($y = $currY - 1; $y <= $currY + 2; $y++) {
                                    $sel = ($y == $year) ? 'selected' : '';
                                    echo '<option value="' . $y . '" ' . $sel . '>' . $y . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="flex items-end pt-5">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-1.5">
                                <i class="fa-solid fa-filter"></i>
                                <span>Filter</span>
                            </button>
                        </div>
                    </form>

                    <!-- Search Input -->
                    <div class="relative max-w-xs w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input type="text" id="searc" class="w-full pl-9 pr-8 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs transition" placeholder="Search by staff ID or name...">
                        <button id="clearSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                            <i class="fa-solid fa-xmark text-slate-400 hover:text-slate-600 text-xs cursor-pointer"></i>
                        </button>
                    </div>
                </div>

                <!-- Legend Bar -->
                <div class="p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-sm space-y-1.5">
                    <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Calendar Cell Legend:</h3>
                    <div class="flex flex-wrap gap-3.5 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-green-100 border border-green-300 rounded flex items-center justify-center text-[10px] font-bold text-green-800 whitespace-nowrap select-none">P</span>
                            <span>Present (Machine)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-sky-100 border border-sky-300 rounded flex items-center justify-center text-[10px] font-bold text-sky-950 whitespace-nowrap select-none">P(M)</span>
                            <span class="font-bold text-slate-800">Present (Manual)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-red-200 border border-red-300 rounded flex items-center justify-center text-[10px] font-bold text-red-900 whitespace-nowrap select-none">A</span>
                            <span>Absent</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-rose-200 border border-rose-400 rounded flex items-center justify-center text-[10px] font-bold text-rose-950 whitespace-nowrap select-none">A(M)</span>
                            <span class="font-bold text-slate-800">Absent (Manual)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-red-300 border border-red-400 rounded flex items-center justify-center text-[10px] font-bold text-red-900 whitespace-nowrap select-none">A*</span>
                            <span>Absent (Sandwich)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-blue-200 border border-blue-300 rounded flex items-center justify-center text-[10px] font-bold text-blue-900 whitespace-nowrap select-none">H</span>
                            <span>Holiday</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-yellow-200 border border-yellow-300 rounded flex items-center justify-center text-[10px] font-bold text-yellow-900 whitespace-nowrap select-none">W</span>
                            <span>Weekend</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-slate-200 border border-slate-300 rounded flex items-center justify-center text-[10px] font-bold text-slate-600 whitespace-nowrap select-none">NJ</span>
                            <span>Did Not Join</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-purple-200 border border-purple-300 rounded flex items-center justify-center text-[10px] font-bold text-purple-900 whitespace-nowrap select-none">I</span>
                            <span>Inactive</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="min-w-[1.4rem] px-1 h-5 bg-amber-200 border border-amber-300 rounded flex items-center justify-center text-[10px] font-bold text-amber-900 whitespace-nowrap select-none">L</span>
                            <span>On Leave (Approved)</span>
                        </div>
                    </div>
                </div>

                <!-- Calendar Grid Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-400 overflow-hidden">
                    <div class="overflow-x-auto max-h-[calc(100vh-270px)] overflow-y-auto">
                        <table class="w-full text-center text-xs md:text-sm border-separate border-spacing-0" id="table">
                            <thead class="bg-slate-900 text-slate-100 sticky top-0 z-30 shadow-sm">
                                <tr>
                                    <th class="py-3.5 px-2 font-extrabold border-b-2 border-r border-slate-700 whitespace-nowrap text-sm col-sr bg-slate-900 text-center select-none">Sr.</th>
                                    <th class="py-3.5 px-2 font-extrabold border-b-2 border-r border-slate-700 whitespace-nowrap text-sm col-id bg-slate-900 text-center select-none">ID</th>
                                    <th class="py-3.5 px-3 font-extrabold border-b-2 border-r border-slate-700 whitespace-nowrap text-left text-sm col-name bg-slate-900 select-none shadow-[4px_0_8px_-2px_rgba(0,0,0,0.3)]">Employee Name</th>
                                    <?php
                                    $current_day = clone $first_day;
                                    while ($current_day <= $last_day) {
                                        $day_of_week = $current_day->format('D');
                                        $day_number = $current_day->format('j');
                                        $is_weekend = ($day_of_week == 'Sun');
                                        $header_class = $is_weekend ? 'bg-slate-800 text-amber-300' : 'bg-slate-900';
                                        echo '<th class="py-2.5 px-1.5 font-extrabold border-b-2 border-r border-slate-700 text-center min-w-[42px] sticky top-0 z-30 ' . $header_class . ' select-none">' . $day_of_week . '<br><span class="text-xs font-bold">' . $day_number . '</span></th>';
                                        $current_day->modify('+1 day');
                                    }
                                    ?>
                                    <th class="py-3.5 px-3 font-extrabold text-center whitespace-nowrap bg-indigo-950 text-indigo-200 text-sm border-b-2 border-r border-slate-700 min-w-[115px] w-[115px] sticky top-0 z-30 select-none">Total Present</th>
                                    <th class="py-3.5 px-3 font-extrabold text-center whitespace-nowrap bg-rose-950 text-rose-200 text-sm border-b-2 border-slate-700 min-w-[115px] w-[115px] sticky top-0 z-30 select-none">Total Absent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch holidays once
                                $holidays = [];
                                $holidays_sql = "SELECT `day` FROM `holidays` WHERE `month` = ? AND `year` = ?";
                                $holidays_stmt = $conn->prepare($holidays_sql);
                                $holidays_stmt->bind_param("ii", $month, $year);
                                $holidays_stmt->execute();
                                $holidays_result = $holidays_stmt->get_result();
                                while ($holiday_row = $holidays_result->fetch_assoc()) {
                                    $holidays[] = (int) $holiday_row['day'];
                                }
                                $holidays_stmt->close();

                                // Fetch all attendance records for the month
                                $attendance_data = [];
                                $attendance_sql = "SELECT `employeeID`, `day`, `updated_by` FROM `attendance1` WHERE `month` = ? AND `year` = ? ORDER BY `employeeID`, `day` ASC";
                                $attendance_stmt = $conn->prepare($attendance_sql);
                                $attendance_stmt->bind_param("ii", $month, $year);
                                $attendance_stmt->execute();
                                $attendance_result = $attendance_stmt->get_result();
                                while ($attendance_row = $attendance_result->fetch_assoc()) {
                                    $emp_id = $attendance_row['employeeID'];
                                    $day_num = (int)$attendance_row['day'];
                                    if (!isset($attendance_data[$emp_id])) {
                                        $attendance_data[$emp_id] = [];
                                    }
                                    $attendance_data[$emp_id][$day_num] = $attendance_row;
                                }
                                $attendance_stmt->close();

                                // Fetch all approved leaves for the month
                                $leaves_data = [];
                                $leaves_sql = "SELECT `employeeID`, `start_date`, `end_date`, `leave_type` FROM `leaves` WHERE `status` = 'Approved' AND ((MONTH(`start_date`) = ? AND YEAR(`start_date`) = ?) OR (MONTH(`end_date`) = ? AND YEAR(`end_date`) = ?))";
                                $leaves_stmt = $conn->prepare($leaves_sql);
                                $leaves_stmt->bind_param("iiii", $month, $year, $month, $year);
                                $leaves_stmt->execute();
                                $leaves_res = $leaves_stmt->get_result();
                                while ($lRow = $leaves_res->fetch_assoc()) {
                                    $empIdL = (int)$lRow['employeeID'];
                                    $stDate = new DateTime($lRow['start_date']);
                                    $enDate = new DateTime($lRow['end_date']);
                                    while ($stDate <= $enDate) {
                                        if ((int)$stDate->format('m') === $month && (int)$stDate->format('Y') === $year) {
                                            $dayN = (int)$stDate->format('j');
                                            $leaves_data[$empIdL][$dayN] = $lRow['leave_type'];
                                        }
                                        $stDate->modify('+1 day');
                                    }
                                }
                                $leaves_stmt->close();

                                // Fetch all employees with join_date, leave_date, and status
                                $sql = "SELECT `sNo`, `fname`, `mname`, `lname`, `join_date`, `leave_date`, `status` FROM `employees` ORDER BY `sNo` ASC";
                                $result = $conn->query($sql);

                                $total_present = 0;
                                $total_absent = 0;

                                if ($result && $result->num_rows > 0) {
                                    $row_index = 0;
                                    $sr_no = 0;
                                    while ($row = $result->fetch_assoc()) {
                                        $sr_no++;
                                        $employee_id = $row['sNo'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $join_date = $row['join_date'];
                                        $leave_date = $row['leave_date'];
                                        $emp_status = $row['status'];

                                        $row_bg_sticky = ($row_index % 2 == 0) ? 'bg-white' : 'bg-slate-100';
                                        $row_class = ($row_index % 2 == 0) ? 'bg-white' : 'bg-slate-50/60';
                                        
                                        echo "<tr class='group $row_class hover:bg-indigo-50/40 transition-colors'>";
                                        echo "<td class='py-3 px-2 border-b border-r border-slate-400 font-mono font-bold text-slate-500 text-sm text-center col-sr $row_bg_sticky group-hover:bg-indigo-100 transition-colors'>$sr_no</td>";
                                        echo "<td class='py-3 px-2 border-b border-r border-slate-400 font-mono font-bold text-indigo-600 text-sm text-center col-id $row_bg_sticky group-hover:bg-indigo-100 transition-colors'>$employee_id</td>";
                                        echo "<td class='py-3 px-3 border-b border-r border-slate-400 text-left font-bold text-slate-900 whitespace-nowrap text-xs md:text-sm col-name $row_bg_sticky group-hover:bg-indigo-100 transition-colors shadow-[4px_0_8px_-2px_rgba(0,0,0,0.08)]'>";
                                        echo "<a href='edit-individual-attendance.php?emp_id=$employee_id&month=$month&year=$year' class='hover:underline text-indigo-700 hover:text-indigo-950 transition flex items-center justify-between gap-1.5 group/link' title='Click to edit individual attendance for $fname $lname'>";
                                        echo "<span>$fname $mname $lname</span>";
                                        echo "<i class='fa-solid fa-user-pen text-xs text-indigo-400 group-hover/link:text-indigo-700 opacity-60 group-hover/link:opacity-100 transition'></i>";
                                        echo "</a>";
                                        echo "</td>";

                                        $employee_attendance = isset($attendance_data[$employee_id]) ? $attendance_data[$employee_id] : [];
                                        $emp_leaves = isset($leaves_data[$employee_id]) ? $leaves_data[$employee_id] : [];
                                        $absent_count = 0;
                                        $present_count = 0;

                                        // Build day status array for the month
                                        $day_status = [];
                                        $current_day = clone $first_day;
                                        while ($current_day <= $last_day) {
                                            $day_number = (int)$current_day->format('j');
                                            $day_of_week = $current_day->format('D');
                                            $current_date_str = $current_day->format('Y-m-d');
                                            
                                            $is_sunday = ($day_of_week == 'Sun');
                                            $is_holiday = in_array($day_number, $holidays);

                                            $not_joined = (!empty($join_date) && $join_date !== '0000-00-00' && $current_date_str < $join_date);
                                            $is_inactive = (!empty($leave_date) && $leave_date !== '0000-00-00' && $current_date_str > $leave_date) ||
                                                           ($emp_status === 'Inactive' && !empty($leave_date) && $leave_date !== '0000-00-00' && $current_date_str > $leave_date);

                                            if (isset($employee_attendance[$day_number])) {
                                                $attRecord = $employee_attendance[$day_number];
                                                $upBy = strtolower(trim($attRecord['updated_by'] ?? ''));
                                                if ($upBy === 'manual_absent') {
                                                    $day_status[$day_number] = 'absent_manual';
                                                } elseif (!empty($upBy) && $upBy !== 'csv_import') {
                                                    $day_status[$day_number] = 'present_manual';
                                                } else {
                                                    $day_status[$day_number] = 'present';
                                                }
                                            } elseif (isset($emp_leaves[$day_number])) {
                                                $lType = $emp_leaves[$day_number];
                                                $day_status[$day_number] = ($lType === 'Unpaid Leave') ? 'unpaid_leave' : 'leave';
                                            } elseif ($is_holiday) {
                                                $day_status[$day_number] = 'holiday';
                                            } elseif ($is_sunday) {
                                                $day_status[$day_number] = 'weekend';
                                            } elseif ($not_joined) {
                                                $day_status[$day_number] = 'not_joined';
                                            } elseif ($is_inactive) {
                                                $day_status[$day_number] = 'inactive';
                                            } else {
                                                $day_status[$day_number] = 'absent';
                                            }
                                            $current_day->modify('+1 day');
                                        }

                                        // Apply sandwich & service boundary policies for weekends (Sunday) & holidays:
                                        // 1. If employee is present on EITHER preceding OR succeeding working day -> Weekend/Holiday remains Present (W/H).
                                        // 2. If employee is absent on BOTH sides -> Sandwich Absent (A*).
                                        // 3. If surrounding days are Not Joined (NJ) or Inactive (I) without presence -> NJ or I.
                                        $days = array_keys($day_status);
                                        for ($i = 0; $i < count($days); $i++) {
                                            if ($day_status[$days[$i]] === 'weekend' || $day_status[$days[$i]] === 'holiday') {
                                                $block_start = $i;
                                                while ($i + 1 < count($days) && ($day_status[$days[$i + 1]] === 'weekend' || $day_status[$days[$i + 1]] === 'holiday')) {
                                                    $i++;
                                                }
                                                $block_end = $i;

                                                $before_status = ($block_start > 0) ? $day_status[$days[$block_start - 1]] : 'not_joined';
                                                $after_status = ($block_end + 1 < count($days)) ? $day_status[$days[$block_end + 1]] : 'inactive';

                                                $is_present_before = ($before_status === 'present' || $before_status === 'present_manual');
                                                $is_present_after = ($after_status === 'present' || $after_status === 'present_manual');

                                                if ($is_present_before || $is_present_after) {
                                                    // Remains weekend (W) or holiday (H) - Counted as Present
                                                    continue;
                                                } elseif ($before_status === 'not_joined' && $after_status === 'not_joined') {
                                                    for ($k = $block_start; $k <= $block_end; $k++) {
                                                        $day_status[$days[$k]] = 'not_joined';
                                                    }
                                                } elseif ($before_status === 'inactive' && $after_status === 'inactive') {
                                                    for ($k = $block_start; $k <= $block_end; $k++) {
                                                        $day_status[$days[$k]] = 'inactive';
                                                    }
                                                } elseif ($before_status === 'not_joined' && !$is_present_after) {
                                                    for ($k = $block_start; $k <= $block_end; $k++) {
                                                        $day_status[$days[$k]] = 'not_joined';
                                                    }
                                                } elseif ($after_status === 'inactive' && !$is_present_before) {
                                                    for ($k = $block_start; $k <= $block_end; $k++) {
                                                        $day_status[$days[$k]] = 'inactive';
                                                    }
                                                } else {
                                                    // Absent on both sides -> Sandwich Absent
                                                    for ($k = $block_start; $k <= $block_end; $k++) {
                                                        $day_status[$days[$k]] = 'absent_sandwich';
                                                    }
                                                }
                                            }
                                        }

                                        // Render table cells
                                        $current_day = clone $first_day;
                                        while ($current_day <= $last_day) {
                                            $day_number = (int)$current_day->format('j');
                                            $status = $day_status[$day_number];
                                            if ($status === 'present') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-green-100 text-green-800 font-extrabold text-sm whitespace-nowrap select-none' title='Present (Machine)'>P</td>";
                                                $present_count++;
                                            } elseif ($status === 'present_manual') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-sky-100 text-sky-950 font-extrabold text-sm whitespace-nowrap select-none' title='Present (Manual Entry)'>P(M)</td>";
                                                $present_count++;
                                            } elseif ($status === 'leave') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-amber-200 text-amber-900 font-extrabold text-sm whitespace-nowrap select-none' title='On Leave (Approved Paid Leave)'>L</td>";
                                                $present_count++;
                                            } elseif ($status === 'unpaid_leave') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-amber-100 text-amber-900 font-extrabold text-sm whitespace-nowrap select-none' title='Unpaid Leave (Deducted)'>L*</td>";
                                                $absent_count++;
                                            } elseif ($status === 'not_joined') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-slate-200 text-slate-600 font-extrabold text-sm whitespace-nowrap select-none' title='Did Not Join'>NJ</td>";
                                            } elseif ($status === 'inactive') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-purple-200 text-purple-900 font-extrabold text-sm whitespace-nowrap select-none' title='Inactive'>I</td>";
                                            } elseif ($status === 'holiday') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-blue-200 text-blue-900 font-extrabold text-sm whitespace-nowrap select-none' title='Holiday (Counted Present)'>H</td>";
                                                $present_count++;
                                            } elseif ($status === 'weekend') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-yellow-200 text-yellow-900 font-extrabold text-sm whitespace-nowrap select-none' title='Weekend (Counted Present)'>W</td>";
                                                $present_count++;
                                            } elseif ($status === 'absent_sandwich') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-red-300 text-red-900 font-extrabold text-sm whitespace-nowrap select-none' title='Absent (Sandwich)'>A*</td>";
                                                $absent_count++;
                                            } elseif ($status === 'absent_manual') {
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-rose-200 text-rose-950 font-extrabold text-sm whitespace-nowrap select-none' title='Absent (Manual)'>A(M)</td>";
                                                $absent_count++;
                                            } else { // 'absent'
                                                echo "<td class='py-2 px-1 border-b border-r border-slate-400 bg-red-200 text-red-900 font-extrabold text-sm whitespace-nowrap select-none' title='Absent'>A</td>";
                                                $absent_count++;
                                            }
                                            $current_day->modify('+1 day');
                                        }

                                        echo "<td class='py-3 px-3 font-extrabold text-center border-b border-r border-slate-400 bg-indigo-50/80 text-indigo-900 font-mono text-sm whitespace-nowrap min-w-[115px] w-[115px]'>$present_count</td>";
                                        echo "<td class='py-3 px-3 font-extrabold text-center border-b border-slate-400 bg-rose-50/80 text-rose-900 font-mono text-sm whitespace-nowrap min-w-[115px] w-[115px]'>$absent_count</td>";
                                        echo "</tr>";
                                        
                                        $total_present += $present_count;
                                        $total_absent += $absent_count;
                                        
                                        $row_index++;
                                    }
                                    
                                    // Add totals row
                                    echo "<tr class='bg-slate-200 font-extrabold text-slate-900 border-t-2 border-slate-500'>";
                                    echo "<td colspan='3' class='py-3 px-4 border-b border-r border-slate-400 text-left font-extrabold sticky left-0 z-20 bg-slate-200 min-w-[240px]'>Total Summary</td>";
                                    
                                    $current_day = clone $first_day;
                                    while ($current_day <= $last_day) {
                                        echo "<td class='py-2 px-1 border-b border-r border-slate-400'></td>";
                                        $current_day->modify('+1 day');
                                    }
                                    
                                    echo "<td class='py-3 px-3 font-extrabold text-center border-b border-r border-slate-400 bg-indigo-100 text-indigo-950 font-mono text-sm'>$total_present</td>";
                                    echo "<td class='py-3 px-3 font-extrabold text-center border-b border-slate-400 bg-rose-100 text-rose-950 font-mono text-sm'>$total_absent</td>";
                                    echo "</tr>";
                                } else {
                                    echo "<tr><td colspan='" . ($days_in_month + 4) . "' class='py-8 text-center text-slate-500'><i class='fa-solid fa-inbox text-3xl mb-2 block text-slate-300'></i>No employees found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <script>
        $(document).ready(function(){
            // Print Preview PDF
            function printData() {
                var divToPrint = document.getElementById("table");
                var newWin = window.open("", "Print-Window");
                newWin.document.write('<!DOCTYPE html><html><head><title>Print Preview - Monthly Attendance Sheet</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"><style>body { padding: 15px; font-family: sans-serif; } table { font-size: 11px; width: 100%; border-collapse: collapse; } th, td { border: 1px solid #cbd5e1; padding: 3px; text-align: center; } th { background-color: #0f172a; color: white; } .bg-green-100 { background-color: #dcfce7; } .bg-sky-100 { background-color: #e0f2fe; } .bg-red-200 { background-color: #fecdd3; } .bg-red-300 { background-color: #fca5a5; } .bg-blue-200 { background-color: #bfdbfe; } .bg-yellow-200 { background-color: #fef08a; } .bg-slate-200 { background-color: #e2e8f0; } .bg-purple-200 { background-color: #e9d5ff; } @media print { @page { size: landscape; margin: 0.5cm; } }</style></head><body><h3 style="text-align: center; margin-bottom: 15px;">Monthly Attendance Sheet - <?php echo $month_name . " " . $year; ?></h3>');
                newWin.document.write(divToPrint.outerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                setTimeout(function() {
                    newWin.print();
                    newWin.close();
                }, 250);
            }
            
            document.querySelector('#browserPrint').addEventListener('click', printData);

            // Export to Excel / CSV
            $(".export-btn").click(function(){  
                $("#table").tableHTMLExport({
                    type: 'csv',
                    filename: 'monthly-attendance-summary-<?php echo $month_name . "-" . $year; ?>.csv',
                });
            });

            // Live Search Filter
            $("#searc").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                var hasResults = false;
                
                $("#table tbody tr").filter(function() {
                    var matches = $(this).text().toLowerCase().indexOf(value) > -1;
                    $(this).toggle(matches);
                    if (matches) hasResults = true;
                });
                
                if (value.length > 0) {
                    $("#clearSearch").removeClass("hidden");
                } else {
                    $("#clearSearch").addClass("hidden");
                }
            });

            // Clear search
            $("#clearSearch").on("click", function() {
                $("#searc").val("");
                $("#searc").trigger("keyup");
            });
        });
    </script>
</body>
</html>
