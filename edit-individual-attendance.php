<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Sanitize & validate inputs
$emp_id = isset($_GET['emp_id']) ? trim($_GET['emp_id']) : '';
$year = isset($_GET["year"]) ? intval($_GET["year"]) : date('Y');
$month = isset($_GET["month"]) ? intval($_GET["month"]) : date('n');

if ($year < 2000 || $year > 2100) $year = date('Y');
if ($month < 1 || $month > 12) $month = date('n');

$month_name = date('F', mktime(0, 0, 0, $month, 1, $year));
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$first_day = new DateTime("$year-$month-01");
$last_day = new DateTime("$year-$month-$days_in_month");

// Fetch employee details & assigned shift times
$emp_info = null;
$shift_start = '09:00';
$shift_end = '17:00';
$shift_name = 'General Shift';

if (!empty($emp_id)) {
    $emp_stmt = $conn->prepare("SELECT e.`sNo`, e.`fname`, e.`mname`, e.`lname`, e.`join_date`, e.`leave_date`, e.`status`, e.`shift_id`, s.start_time, s.end_time, s.shift_name 
                                FROM `employees` e 
                                LEFT JOIN `shifts` s ON e.shift_id = s.id 
                                WHERE e.`sNo` = ? OR e.`employeeID` = ? LIMIT 1");
    $emp_stmt->bind_param("ss", $emp_id, $emp_id);
    $emp_stmt->execute();
    $emp_res = $emp_stmt->get_result();
    if ($emp_res && $emp_res->num_rows > 0) {
        $emp_info = $emp_res->fetch_assoc();
        $emp_id = $emp_info['sNo']; // standard ID
        if (!empty($emp_info['start_time']) && $emp_info['start_time'] !== '00:00:00') {
            $shift_start = date('H:i', strtotime($emp_info['start_time']));
        }
        if (!empty($emp_info['end_time']) && $emp_info['end_time'] !== '00:00:00') {
            $shift_end = date('H:i', strtotime($emp_info['end_time']));
        }
        if (!empty($emp_info['shift_name'])) {
            $shift_name = $emp_info['shift_name'];
        }
    }
    $emp_stmt->close();
}

if (!$emp_info) {
    die("<div style='padding:40px; font-family:sans-serif; text-align:center;'><h2>Employee Not Found</h2><p><a href='view-attendance.php'>Return to Attendance Grid</a></p></div>");
}

$msg = '';
$msg_type = '';

// Handle POST Save Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $updated_count = 0;
    
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $day_num => $times) {
            $day_num = intval($day_num);
            if ($day_num < 1 || $day_num > $days_in_month) continue;

            $t_in_raw = trim($times['time_in'] ?? '');
            $t_out_raw = trim($times['time_out'] ?? '');
            $is_manual_absent = isset($times['manual_absent']) && ($times['manual_absent'] === '1' || $times['manual_absent'] === 1);

            $date_str = sprintf('%04d-%02d-%02d', $year, $month, $day_num);

            $time_in_val = !empty($t_in_raw) ? date('H:i:s', strtotime($t_in_raw)) : '00:00:00';
            $time_out_val = !empty($t_out_raw) ? date('H:i:s', strtotime($t_out_raw)) : '00:00:00';

            // Check if record exists
            $check_stmt = $conn->prepare("SELECT `id`, `time_in`, `time_out`, `updated_by` FROM `attendance1` WHERE `employeeID` = ? AND `day` = ? AND `month` = ? AND `year` = ?");
            $check_stmt->bind_param("siii", $emp_id, $day_num, $month, $year);
            $check_stmt->execute();
            $check_res = $check_stmt->get_result();

            if ($is_manual_absent) {
                if ($check_res && $check_res->num_rows > 0) {
                    $rec = $check_res->fetch_assoc();
                    $att_id = $rec['id'];
                    $up_stmt = $conn->prepare("UPDATE `attendance1` SET `time_in` = '00:00:00', `time_out` = '00:00:00', `updated_at` = NOW(), `updated_by` = 'manual_absent' WHERE `id` = ?");
                    $up_stmt->bind_param("i", $att_id);
                    $up_stmt->execute();
                    $up_stmt->close();
                    $updated_count++;
                } else {
                    $ins_stmt = $conn->prepare("INSERT INTO `attendance1` (`employeeID`, `unit`, `date`, `day`, `month`, `year`, `time_in`, `time_out`, `created_at`, `updated_at`, `updated_by`) VALUES (?, '', ?, ?, ?, ?, '00:00:00', '00:00:00', NOW(), NOW(), 'manual_absent')");
                    $ins_stmt->bind_param("ssiii", $emp_id, $date_str, $day_num, $month, $year);
                    $ins_stmt->execute();
                    $ins_stmt->close();
                    $updated_count++;
                }
            } elseif ($time_in_val !== '00:00:00' || $time_out_val !== '00:00:00') {
                if ($check_res && $check_res->num_rows > 0) {
                    $rec = $check_res->fetch_assoc();
                    $att_id = $rec['id'];
                    $old_in = (!empty($rec['time_in']) && $rec['time_in'] !== '00:00:00') ? date('H:i:s', strtotime($rec['time_in'])) : '00:00:00';
                    $old_out = (!empty($rec['time_out']) && $rec['time_out'] !== '00:00:00') ? date('H:i:s', strtotime($rec['time_out'])) : '00:00:00';

                    if ($time_in_val !== $old_in || $time_out_val !== $old_out || $rec['updated_by'] === 'manual_absent') {
                        $up_stmt = $conn->prepare("UPDATE `attendance1` SET `time_in` = ?, `time_out` = ?, `updated_at` = NOW(), `updated_by` = 'manual' WHERE `id` = ?");
                        $up_stmt->bind_param("ssi", $time_in_val, $time_out_val, $att_id);
                        $up_stmt->execute();
                        $up_stmt->close();
                        $updated_count++;
                    }
                } else {
                    $ins_stmt = $conn->prepare("INSERT INTO `attendance1` (`employeeID`, `unit`, `date`, `day`, `month`, `year`, `time_in`, `time_out`, `created_at`, `updated_at`, `updated_by`) VALUES (?, '', ?, ?, ?, ?, ?, ?, NOW(), NOW(), 'manual')");
                    $ins_stmt->bind_param("ssiiiss", $emp_id, $date_str, $day_num, $month, $year, $time_in_val, $time_out_val);
                    $ins_stmt->execute();
                    $ins_stmt->close();
                    $updated_count++;
                }
            } else {
                if ($check_res && $check_res->num_rows > 0) {
                    $del_stmt = $conn->prepare("DELETE FROM `attendance1` WHERE `employeeID` = ? AND `day` = ? AND `month` = ? AND `year` = ?");
                    $del_stmt->bind_param("siii", $emp_id, $day_num, $month, $year);
                    $del_stmt->execute();
                    $del_stmt->close();
                    $updated_count++;
                }
            }
            $check_stmt->close();
        }
        $msg = "Successfully updated attendance records for $month_name $year!";
        $msg_type = "success";
    }
}

// Fetch holidays for the month
$holidays = [];
$holidays_sql = "SELECT `day` FROM `holidays` WHERE `month` = ? AND `year` = ?";
$holidays_stmt = $conn->prepare($holidays_sql);
$holidays_stmt->bind_param("ii", $month, $year);
$holidays_stmt->execute();
$holidays_result = $holidays_stmt->get_result();
while ($h_row = $holidays_result->fetch_assoc()) {
    $holidays[] = (int) $h_row['day'];
}
$holidays_stmt->close();

// Fetch existing attendance records for employee for this month
$emp_attendance = [];
$att_stmt = $conn->prepare("SELECT `day`, `time_in`, `time_out`, `updated_by` FROM `attendance1` WHERE `employeeID` = ? AND `month` = ? AND `year` = ? ORDER BY `day` ASC");
$att_stmt->bind_param("sii", $emp_id, $month, $year);
$att_stmt->execute();
$att_res = $att_stmt->get_result();
while ($r = $att_res->fetch_assoc()) {
    $emp_attendance[(int)$r['day']] = $r;
}
$att_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Attendance Entry - <?php echo htmlspecialchars($emp_info['fname'] . ' ' . $emp_info['lname']); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
            
            <div class="max-w-5xl mx-auto space-y-6">
                
                <!-- Success / Notification Alert -->
                <?php if (!empty($msg)): ?>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                            <span class="font-bold text-sm"><?php echo $msg; ?></span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Employee Header Section -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-extrabold text-xl shadow-md">
                            <?php echo strtoupper(substr($emp_info['fname'], 0, 1) . substr($emp_info['lname'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-mono text-xs font-extrabold border border-indigo-200">ID: <?php echo htmlspecialchars($emp_info['sNo']); ?></span>
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">Shift: <?php echo htmlspecialchars($shift_name); ?> (<?php echo $shift_start . ' - ' . $shift_end; ?>)</span>
                                <?php if ($emp_info['status'] === 'Active'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">Active</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 text-xs font-bold border border-purple-200">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <h1 class="text-2xl font-extrabold text-slate-900 mt-1"><?php echo htmlspecialchars($emp_info['fname'] . ' ' . $emp_info['mname'] . ' ' . $emp_info['lname']); ?></h1>
                            <p class="text-xs text-slate-500 mt-0.5">Individual Daily Attendance Management for <strong><?php echo $month_name . ' ' . $year; ?></strong></p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="view-attendance.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition inline-flex items-center space-x-2">
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Summary Grid View</span>
                        </a>
                        <a href="view-attendance-times.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition inline-flex items-center space-x-2">
                            <i class="fa-solid fa-clock"></i>
                            <span>Punch Times View</span>
                        </a>
                    </div>
                </div>

                <!-- Month / Year Selector Bar & Presets -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <form action="edit-individual-attendance.php" method="get" class="flex flex-wrap items-center gap-4 flex-1">
                        <input type="hidden" name="emp_id" value="<?php echo htmlspecialchars($emp_id); ?>">
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
                                <span>Change Period</span>
                            </button>
                        </div>
                    </form>

                    <!-- Fill Presets -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="markAllPresent()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Mark All Working Days Present</span>
                        </button>
                        <button type="button" onclick="clearAllHours()" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-xs border border-rose-300 transition flex items-center space-x-1">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Clear All</span>
                        </button>
                    </div>
                </div>

                <!-- Attendance Legend Bar -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
                    <span class="font-extrabold text-slate-700 uppercase tracking-wider text-[11px] flex items-center">
                        <i class="fa-solid fa-tags text-indigo-600 mr-2"></i>
                        Attendance Legend:
                    </span>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold text-[11px] inline-flex items-center gap-1"><i class="fa-solid fa-fingerprint text-emerald-600"></i> Present (Machine)</span>
                        <span class="px-2.5 py-1 rounded-lg bg-sky-100 border border-sky-400 text-sky-950 font-bold text-[11px] inline-flex items-center gap-1"><i class="fa-solid fa-user-pen text-sky-600"></i> Present (Manual)</span>
                        <span class="px-2.5 py-1 rounded-lg bg-rose-100 border border-rose-400 text-rose-950 font-bold text-[11px] inline-flex items-center gap-1"><i class="fa-solid fa-user-xmark text-rose-600"></i> Absent (Manual)</span>
                        <span class="px-2.5 py-1 rounded-lg bg-red-100 border border-red-300 text-red-800 font-bold text-[11px] inline-flex items-center gap-1"><i class="fa-solid fa-user-minus text-red-500"></i> Absent</span>
                        <span class="px-2.5 py-1 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-900 font-bold text-[11px] inline-flex items-center gap-1"><i class="fa-solid fa-sun text-amber-500"></i> Weekend (Sun)</span>
                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 border border-blue-300 text-blue-900 font-bold text-[11px] inline-flex items-center gap-1"><i class="fa-solid fa-umbrella text-blue-500"></i> Holiday</span>
                    </div>
                </div>

                <!-- Main Form Table -->
                <form action="edit-individual-attendance.php?emp_id=<?php echo htmlspecialchars($emp_id); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" method="post" class="space-y-6">
                    <input type="hidden" name="save_attendance" value="1">

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-300 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs md:text-sm border-collapse">
                                <thead class="bg-slate-900 text-slate-100">
                                    <tr>
                                        <th class="py-3.5 px-4 font-extrabold border border-slate-700 w-24">Date</th>
                                        <th class="py-3.5 px-4 font-extrabold border border-slate-700 w-24">Day</th>
                                        <th class="py-3.5 px-4 font-extrabold border border-slate-700">Status Badge</th>
                                        <th class="py-3.5 px-4 font-extrabold border border-slate-700 w-44">Time In (Punch In)</th>
                                        <th class="py-3.5 px-4 font-extrabold border border-slate-700 w-44">Time Out (Punch Out)</th>
                                        <th class="py-3.5 px-4 font-extrabold border border-slate-700 text-center w-40">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $current_day = clone $first_day;
                                    while ($current_day <= $last_day) {
                                        $day_num = (int)$current_day->format('j');
                                        $day_of_week = $current_day->format('D');
                                        $date_str = $current_day->format('Y-m-d');
                                        $display_date = $current_day->format('d M Y');

                                        $is_sunday = ($day_of_week === 'Sun');
                                        $is_holiday = in_array($day_num, $holidays);

                                        $join_date = $emp_info['join_date'];
                                        $leave_date = $emp_info['leave_date'];
                                        $emp_status = $emp_info['status'];

                                        $not_joined = (!empty($join_date) && $join_date !== '0000-00-00' && $date_str < $join_date);
                                        $is_inactive = (!empty($leave_date) && $leave_date !== '0000-00-00' && $date_str > $leave_date) ||
                                                       ($emp_status === 'Inactive' && !empty($leave_date) && $leave_date !== '0000-00-00' && $date_str > $leave_date);

                                        $is_working_day = (!$is_sunday && !$is_holiday && !$not_joined && !$is_inactive);

                                        $att_record = isset($emp_attendance[$day_num]) ? $emp_attendance[$day_num] : null;

                                        $t_in_val = '';
                                        $t_out_val = '';
                                        $entry_type = '';

                                        if ($att_record) {
                                            if (!empty($att_record['time_in']) && $att_record['time_in'] !== '00:00:00') {
                                                $t_in_val = date('H:i', strtotime($att_record['time_in']));
                                            }
                                            if (!empty($att_record['time_out']) && $att_record['time_out'] !== '00:00:00') {
                                                $t_out_val = date('H:i', strtotime($att_record['time_out']));
                                            }
                                            $up_by = strtolower(trim($att_record['updated_by'] ?? ''));
                                            if ($up_by === 'manual_absent') {
                                                $entry_type = 'manual_absent';
                                            } elseif (!empty($up_by) && $up_by !== 'csv_import') {
                                                $entry_type = 'manual';
                                            } else {
                                                $entry_type = 'machine';
                                            }
                                        }

                                        $is_present = (!empty($t_in_val) || !empty($t_out_val));

                                        $row_bg = ($day_num % 2 == 0) ? 'bg-white' : 'bg-slate-50/60';
                                        if ($is_sunday) $row_bg = 'bg-yellow-50/60';

                                        echo "<tr class='$row_bg hover:bg-indigo-50/40 border-b border-slate-400 transition-colors' data-working='" . ($is_working_day ? "1" : "0") . "'>";
                                        echo "<td class='py-3 px-4 font-mono font-bold text-slate-900 border border-slate-400'>$display_date</td>";
                                        
                                        if ($is_sunday) {
                                            echo "<td class='py-3 px-4 font-extrabold text-amber-700 border border-slate-400'><i class='fa-solid fa-sun mr-1'></i>$day_of_week</td>";
                                        } else {
                                            echo "<td class='py-3 px-4 font-bold text-slate-700 border border-slate-400'>$day_of_week</td>";
                                        }

                                        // Status badge cell
                                        echo "<td class='py-3 px-4 border border-slate-400'>";
                                        if (!empty($t_in_val) || !empty($t_out_val)) {
                                            if ($entry_type === 'manual') {
                                                echo "<span class='px-2.5 py-1 rounded-lg bg-sky-100 border border-sky-400 text-sky-950 font-bold text-xs inline-flex items-center gap-1'><i class='fa-solid fa-user-pen text-sky-600'></i> Present (Manual)</span>";
                                            } else {
                                                echo "<span class='px-2.5 py-1 rounded-lg bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold text-xs inline-flex items-center gap-1'><i class='fa-solid fa-fingerprint text-emerald-600'></i> Present (Machine)</span>";
                                            }
                                        } elseif ($entry_type === 'manual_absent') {
                                            echo "<span class='px-2.5 py-1 rounded-lg bg-rose-100 border border-rose-400 text-rose-950 font-bold text-xs inline-flex items-center gap-1'><i class='fa-solid fa-user-xmark text-rose-600'></i> Absent (Manual)</span>";
                                        } elseif ($not_joined) {
                                            echo "<span class='px-2.5 py-1 rounded-lg bg-slate-200 border border-slate-300 text-slate-700 font-bold text-xs'>Did Not Join</span>";
                                        } elseif ($is_inactive) {
                                            echo "<span class='px-2.5 py-1 rounded-lg bg-purple-200 border border-purple-300 text-purple-900 font-bold text-xs'>Inactive</span>";
                                        } elseif ($is_holiday) {
                                            echo "<span class='px-2.5 py-1 rounded-lg bg-blue-100 border border-blue-300 text-blue-900 font-bold text-xs'>Holiday</span>";
                                        } elseif ($is_sunday) {
                                            echo "<span class='px-2.5 py-1 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-900 font-bold text-xs'>Weekend</span>";
                                        } else {
                                            echo "<span class='px-2.5 py-1 rounded-lg bg-red-100 border border-red-300 text-red-800 font-bold text-xs'>Absent</span>";
                                        }
                                        echo "</td>";

                                        // Time In Input
                                        echo "<td class='py-2.5 px-4 border border-slate-400'>";
                                        echo "<input type='time' name='attendance[$day_num][time_in]' value='$t_in_val' class='time-in-field w-full px-3 py-1.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono text-xs font-bold text-slate-800 bg-white' placeholder='HH:MM'>";
                                        echo "</td>";

                                        // Time Out Input
                                        echo "<td class='py-2.5 px-4 border border-slate-400'>";
                                        echo "<input type='time' name='attendance[$day_num][time_out]' value='$t_out_val' class='time-out-field w-full px-3 py-1.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono text-xs font-bold text-slate-800 bg-white' placeholder='HH:MM'>";
                                        echo "</td>";

                                        // Quick Row Action (Shows Mark Present if Absent, Mark Absent if Present)
                                        $hidePresent = $is_present ? 'hidden' : '';
                                        $hideAbsent  = !$is_present ? 'hidden' : '';

                                        echo "<td class='py-2.5 px-4 border border-slate-400 text-center whitespace-nowrap action-cell'>";
                                        echo "<input type='hidden' name='attendance[$day_num][manual_absent]' value='" . ($entry_type === 'manual_absent' ? '1' : '0') . "' class='manual-absent-input'>";
                                        echo "<button type='button' onclick='markRowPresent(this)' class='btn-mark-present px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition $hidePresent' title='Auto-fill shift time ($shift_start - $shift_end)'><i class='fa-solid fa-check mr-1.5'></i>Mark Present</button>";
                                        echo "<button type='button' onclick='markRowAbsent(this)' class='btn-mark-absent px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition $hideAbsent' title='Clear punch times & mark Absent (Manual)'><i class='fa-solid fa-user-xmark mr-1.5'></i>Mark Absent</button>";
                                        echo "</td>";

                                        echo "</tr>";

                                        $current_day->modify('+1 day');
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="sticky bottom-4 bg-slate-900/90 backdrop-blur p-4 rounded-2xl shadow-xl flex items-center justify-between text-white border border-slate-700">
                        <div class="flex items-center space-x-2 text-xs">
                            <i class="fa-solid fa-circle-info text-amber-400 text-sm"></i>
                            <span class="text-slate-300 font-medium">Use <strong>Mark Present</strong> on Absent days or <strong>Mark Absent</strong> on Present days to toggle attendance status. Shift: <strong><?php echo $shift_start . ' - ' . $shift_end; ?></strong>.</span>
                        </div>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-lg transition flex items-center space-x-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Save Attendance Changes</span>
                        </button>
                    </div>

                </form>

            </div>

        </main>
    </div>

    <script>
        const defaultShiftStart = "<?php echo $shift_start; ?>";
        const defaultShiftEnd = "<?php echo $shift_end; ?>";

        function markRowPresent(btn) {
            const row = btn.closest('tr');
            if (row) {
                const inField = row.querySelector('.time-in-field');
                const outField = row.querySelector('.time-out-field');
                const absentInput = row.querySelector('.manual-absent-input');
                if (inField && outField) {
                    inField.value = defaultShiftStart;
                    outField.value = defaultShiftEnd;
                }
                if (absentInput) absentInput.value = '0';
                
                const presentBtn = row.querySelector('.btn-mark-present');
                const absentBtn = row.querySelector('.btn-mark-absent');
                if (presentBtn) presentBtn.classList.add('hidden');
                if (absentBtn) absentBtn.classList.remove('hidden');
            }
        }

        function markRowAbsent(btn) {
            const row = btn.closest('tr');
            if (row) {
                const inField = row.querySelector('.time-in-field');
                const outField = row.querySelector('.time-out-field');
                const absentInput = row.querySelector('.manual-absent-input');
                if (inField) inField.value = '';
                if (outField) outField.value = '';
                if (absentInput) absentInput.value = '0';

                const presentBtn = row.querySelector('.btn-mark-present');
                const absentBtn = row.querySelector('.btn-mark-absent');
                if (presentBtn) presentBtn.classList.remove('hidden');
                if (absentBtn) absentBtn.classList.add('hidden');
            }
        }

        function markAllPresent() {
            document.querySelectorAll('tr[data-working="1"]').forEach(row => {
                const inField = row.querySelector('.time-in-field');
                const outField = row.querySelector('.time-out-field');
                const absentInput = row.querySelector('.manual-absent-input');
                if (inField && outField) {
                    inField.value = defaultShiftStart;
                    outField.value = defaultShiftEnd;
                    if (absentInput) absentInput.value = '0';

                    const presentBtn = row.querySelector('.btn-mark-present');
                    const absentBtn = row.querySelector('.btn-mark-absent');
                    if (presentBtn) presentBtn.classList.add('hidden');
                    if (absentBtn) absentBtn.classList.remove('hidden');
                }
            });
        }

        function clearAllHours() {
            if (confirm('Clear all entered punch times and mark all days absent for this month?')) {
                document.querySelectorAll('tr').forEach(row => {
                    const inField = row.querySelector('.time-in-field');
                    const outField = row.querySelector('.time-out-field');
                    const absentInput = row.querySelector('.manual-absent-input');
                    if (inField) inField.value = '';
                    if (outField) outField.value = '';
                    if (absentInput) absentInput.value = '0';

                    const presentBtn = row.querySelector('.btn-mark-present');
                    const absentBtn = row.querySelector('.btn-mark-absent');
                    if (presentBtn) presentBtn.classList.remove('hidden');
                    if (absentBtn) absentBtn.classList.add('hidden');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.time-in-field, .time-out-field').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    if (row) {
                        const inVal = row.querySelector('.time-in-field').value;
                        const outVal = row.querySelector('.time-out-field').value;
                        const absentInput = row.querySelector('.manual-absent-input');
                        const presentBtn = row.querySelector('.btn-mark-present');
                        const absentBtn = row.querySelector('.btn-mark-absent');
                        
                        if (inVal || outVal) {
                            if (absentInput) absentInput.value = '0';
                            if (presentBtn) presentBtn.classList.add('hidden');
                            if (absentBtn) absentBtn.classList.remove('hidden');
                        } else {
                            if (presentBtn) presentBtn.classList.remove('hidden');
                            if (absentBtn) absentBtn.classList.add('hidden');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
