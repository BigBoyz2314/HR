<?php
require_once('config.php');
require_once('ledger-helper.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Admin';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_REQUEST["month"], $_REQUEST["year"])) {
    $month = intval($_REQUEST["month"]);
    $year = intval($_REQUEST["year"]);
} else {
    header("Location: ../view-salary.php");
    exit;
}

// Calendar days in the month
$actualDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Find max day recorded in attendance1 for this month to handle mid-month payroll generation
$maxDayRecorded = $actualDaysInMonth;
$maxDayRes = $conn->query("SELECT MAX(`day`) AS max_day FROM attendance1 WHERE `month` = $month AND `year` = $year");
if ($maxDayRes && $mRow = $maxDayRes->fetch_assoc()) {
    if (!empty($mRow['max_day'])) {
        $maxDayRecorded = intval($mRow['max_day']);
    }
}

// 1. Fetch Gazette Holidays for this month
$holidays = [];
$holidays_sql = "SELECT `day` FROM holidays WHERE `month` = ? AND `year` = ?";
$holidays_stmt = $conn->prepare($holidays_sql);
$holidays_stmt->bind_param("ii", $month, $year);
$holidays_stmt->execute();
$holidays_result = $holidays_stmt->get_result();
while ($rowH = $holidays_result->fetch_assoc()) {
    $holidays[] = (int)$rowH['day'];
}
$holidays_stmt->close();

// Helper function to check if a day is an Off Day (Sunday or Gazette Holiday)
function isOffDay($dayNum, $month, $year, $holidays) {
    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayNum);
    $dayOfWeek = date('D', strtotime($dateStr));
    if ($dayOfWeek === 'Sun' || in_array($dayNum, $holidays)) {
        return true;
    }
    return false;
}

// 2. Fetch all attendance records in one query
$attendance_data = [];
$attendance_sql = "SELECT `employeeID`, `day` FROM attendance1 WHERE `month` = ? AND `year` = ?";
$attendance_stmt = $conn->prepare($attendance_sql);
$attendance_stmt->bind_param("ii", $month, $year);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();
while ($rowA = $attendance_result->fetch_assoc()) {
    $emp_id = $rowA['employeeID'];
    if (!isset($attendance_data[$emp_id])) {
        $attendance_data[$emp_id] = [];
    }
    $attendance_data[$emp_id][] = (int)$rowA['day'];
}
$attendance_stmt->close();

$hasMonthAttendance = count($attendance_data) > 0;

// 3. Fetch Allowances
$allowances_data = [];
$allowances_sql = "SELECT `employeeID`, SUM(amount) AS amount FROM allowances WHERE `month` = ? AND `year` = ? GROUP BY `employeeID`";
$allowances_stmt = $conn->prepare($allowances_sql);
$allowances_stmt->bind_param("ii", $month, $year);
$allowances_stmt->execute();
$allowances_result = $allowances_stmt->get_result();
while ($row_allow = $allowances_result->fetch_assoc()) {
    $allowances_data[$row_allow['employeeID']] = floatval($row_allow['amount']);
}
$allowances_stmt->close();

// 4. Fetch Deductions
$deductions_data = [];
$deductions_sql = "SELECT `employeeID`, SUM(amount) AS amount FROM deductions WHERE `month` = ? AND `year` = ? GROUP BY `employeeID`";
$deductions_stmt = $conn->prepare($deductions_sql);
$deductions_stmt->bind_param("ii", $month, $year);
$deductions_stmt->execute();
$deductions_result = $deductions_stmt->get_result();
while ($row_ded = $deductions_result->fetch_assoc()) {
    $deductions_data[$row_ded['employeeID']] = floatval($row_ded['amount']);
}
$deductions_stmt->close();

// 5. Fetch Overtime
$overtime_data = [];
$ot_sql = "SELECT `employeeID`, SUM(amount) AS amount FROM overtime WHERE `month` = ? AND `year` = ? GROUP BY `employeeID`";
$ot_stmt = $conn->prepare($ot_sql);
$ot_stmt->bind_param("ii", $month, $year);
$ot_stmt->execute();
$ot_result = $ot_stmt->get_result();
while ($row_ot = $ot_result->fetch_assoc()) {
    $overtime_data[$row_ot['employeeID']] = floatval($row_ot['amount']);
}
$ot_stmt->close();

// Retrieve all active employees
$stmt = "SELECT `employeeID`, `sNo`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance` AS profile_allowance, `join_date`, `leave_date` FROM employees WHERE status = 'Active'";
$result = $conn->query($stmt);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['employeeID'];
        $fname = $row['fname'];
        $mname = $row['mname'];
        $lname = $row['lname'];
        $desig = $row['designation'];
        $dept = $row['department'];
        $gender = $row['gender'];
        $basic = floatval($row['basic_salary']);
        $profileAllowance = floatval($row['profile_allowance'] ?? 0);
        $joinDateStr = $row['join_date'] ?? '';
        $leaveDateStr = $row['leave_date'] ?? '';

        $empAttendance = isset($attendance_data[$id]) ? $attendance_data[$id] : [];
        $monthlyAllowance = isset($allowances_data[$id]) ? $allowances_data[$id] : 0;
        $totalAllowance = $profileAllowance + $monthlyAllowance;
        $deduction = isset($deductions_data[$id]) ? $deductions_data[$id] : 0;
        $otAmount = isset($overtime_data[$id]) ? $overtime_data[$id] : 0;

        $absent_days = 0;

        if ($hasMonthAttendance) {
            // Iterate recorded calendar days (1 to maxDayRecorded)
            for ($day_num = 1; $day_num <= $maxDayRecorded; $day_num++) {
                $curDayStr = sprintf('%04d-%02d-%02d', $year, $month, $day_num);

                // Ignore days before joining or after leaving
                if (!empty($joinDateStr) && $curDayStr < $joinDateStr) {
                    continue;
                }
                if (!empty($leaveDateStr) && $leaveDateStr != '0000-00-00' && $curDayStr > $leaveDateStr) {
                    continue;
                }

                $isOff = isOffDay($day_num, $month, $year, $holidays);

                if ($isOff) {
                    $prevWorkDay = $day_num - 1;
                    while ($prevWorkDay >= 1 && isOffDay($prevWorkDay, $month, $year, $holidays)) {
                        $prevWorkDay--;
                    }

                    $nextWorkDay = $day_num + 1;
                    while ($nextWorkDay <= $maxDayRecorded && isOffDay($nextWorkDay, $month, $year, $holidays)) {
                        $nextWorkDay++;
                    }

                    $prevPresent = ($prevWorkDay >= 1) ? in_array($prevWorkDay, $empAttendance) : true;
                    $nextPresent = ($nextWorkDay <= $maxDayRecorded) ? in_array($nextWorkDay, $empAttendance) : true;

                    if (!$prevPresent && !$nextPresent) {
                        $absent_days++;
                    }
                } else {
                    if (!in_array($day_num, $empAttendance)) {
                        $absent_days++;
                    }
                }
            }
        }

        $daily_rate = $basic / 30.0;

        if ($maxDayRecorded >= $actualDaysInMonth) {
            $dayspayable = max(0, 30 - $absent_days);
            $absent_amount = $absent_days * $daily_rate;
            $earned_basic = max(0, $basic - $absent_amount);
        } else {
            $dayspayable = max(0, $maxDayRecorded - $absent_days);
            $absent_amount = $absent_days * $daily_rate;
            $earned_basic = max(0, ($dayspayable / 30.0) * $basic);
        }

        // Check if salary record already exists to preserve custom adjustments
        $sql_check = "SELECT * FROM `salary1` WHERE employeeID = '$id' AND `month` = $month AND `year` = $year LIMIT 1";
        $result_check = $conn->query($sql_check);

        $arrears = 0.0;
        $ot1 = $otAmount;
        $ot2 = 0.0;
        $lessLoans = 0.0;
        $lessAdv = 0.0;
        $paid_existing = 0.0;

        if ($result_check && $result_check->num_rows > 0) {
            $row_existing = $result_check->fetch_assoc();
            $arrears = floatval($row_existing['arrears'] ?? 0);
            if ($ot1 == 0) {
                $ot1 = floatval($row_existing['ot_1_15'] ?? 0);
            }
            $ot2 = floatval($row_existing['ot_16_30'] ?? 0);
            $lessLoans = floatval($row_existing['less_loans'] ?? 0);
            $lessAdv = floatval($row_existing['less_advance'] ?? 0);
            $paid_existing = floatval($row_existing['paid'] ?? 0);
        }

        $totalDeductions = $deduction + $lessLoans + $lessAdv;
        $gross = $basic + $totalAllowance;
        $pay = max(0, $earned_basic + $totalAllowance + $arrears + $ot1 + $ot2 - $totalDeductions);
        $remaining_update = max(0, $pay - $paid_existing);

        $fnameEsc = $conn->real_escape_string($fname);
        $mnameEsc = $conn->real_escape_string($mname);
        $lnameEsc = $conn->real_escape_string($lname);
        $desigEsc = $conn->real_escape_string($desig);
        $deptEsc  = $conn->real_escape_string($dept);

        if ($result_check && $result_check->num_rows > 0) {
            $sql_update = "UPDATE `salary1` SET 
                `fname` = '$fnameEsc',
                `mname` = '$mnameEsc',
                `lname` = '$lnameEsc',
                `designation` = '$desigEsc',
                `department` = '$deptEsc',
                `basic_salary` = '$basic', 
                `allowance` = '$totalAllowance', 
                `deduction` = '$totalDeductions', 
                `gross_salary` = '$gross', 
                `total_days` = '$maxDayRecorded', 
                `pay_days` = '$dayspayable', 
                `absent` = '$absent_amount', 
                `arrears` = '$arrears',
                `ot_1_15` = '$ot1',
                `ot_16_30` = '$ot2',
                `less_loans` = '$lessLoans',
                `less_advance` = '$lessAdv',
                `payable` = '$pay', 
                `remaining` = '$remaining_update',
                `updated_at` = current_timestamp()
            WHERE employeeID = '$id' AND `month` = $month AND `year` = $year";
            
            $sql_log_update = "INSERT IGNORE INTO `salary_log1` (`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fnameEsc','$mnameEsc','$lnameEsc','$desigEsc','$deptEsc','$gender','$basic','$totalAllowance','$totalDeductions','$gross','$month','$year','$maxDayRecorded','$dayspayable','$absent_amount','$pay','$paid_existing','$remaining_update',current_timestamp(),current_timestamp(),'$name')";
            
            $conn->query($sql_update);
            $conn->query($sql_log_update);
        } else {
            $sql_insert = "INSERT INTO `salary1`(`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `arrears`, `ot_1_15`, `ot_16_30`, `less_loans`, `less_advance`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fnameEsc','$mnameEsc','$lnameEsc','$desigEsc','$deptEsc','$gender','$basic','$totalAllowance','$totalDeductions','$gross','$month','$year','$maxDayRecorded','$dayspayable','$absent_amount','$arrears','$ot1','$ot2','$lessLoans','$lessAdv','$pay','0','$pay',current_timestamp(),current_timestamp())";
        
            $sql_log = "INSERT IGNORE INTO `salary_log1` (`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fnameEsc','$mnameEsc','$lnameEsc','$desigEsc','$deptEsc','$gender','$basic','$totalAllowance','$totalDeductions','$gross','$month','$year','$maxDayRecorded','$dayspayable','$absent_amount','$pay','0','$pay',current_timestamp(),current_timestamp(),'$name')";
            
            $conn->query($sql_insert);
            $conn->query($sql_log);
        }

        // Sync with central Employee Ledger
        if (function_exists('syncSalaryAccrualToLedger')) {
            $otTotal = $ot1 + $ot2;
            syncSalaryAccrualToLedger($conn, $id, $month, $year, $pay, $name, $otTotal);
        }
    }
}

header("Location: ../view-salary.php?month=$month&year=$year&action=generated");
exit;
?>
