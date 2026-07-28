<?php
require_once('config.php');
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $month = intval($_REQUEST["month"]);
    $year = intval($_REQUEST["year"]);
} else {
    header("Location: ../salary.php");
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

// Retrieve all active/registered employees
$stmt = "SELECT `employeeID`, `sNo`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance` AS profile_allowance FROM employees";
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

        $empAttendance = isset($attendance_data[$id]) ? $attendance_data[$id] : [];
        $monthlyAllowance = isset($allowances_data[$id]) ? $allowances_data[$id] : 0;
        $totalAllowance = $profileAllowance + $monthlyAllowance;
        $deduction = isset($deductions_data[$id]) ? $deductions_data[$id] : 0;

        $absent_days = 0;

        // Iterate recorded calendar days (1 to maxDayRecorded) to check absences & sandwich rules
        for ($day_num = 1; $day_num <= $maxDayRecorded; $day_num++) {
            $isOff = isOffDay($day_num, $month, $year, $holidays);

            if ($isOff) {
                // Find previous working day
                $prevWorkDay = $day_num - 1;
                while ($prevWorkDay >= 1 && isOffDay($prevWorkDay, $month, $year, $holidays)) {
                    $prevWorkDay--;
                }

                // Find next working day
                $nextWorkDay = $day_num + 1;
                while ($nextWorkDay <= $maxDayRecorded && isOffDay($nextWorkDay, $month, $year, $holidays)) {
                    $nextWorkDay++;
                }

                $prevPresent = ($prevWorkDay >= 1) ? in_array($prevWorkDay, $empAttendance) : true;
                $nextPresent = ($nextWorkDay <= $maxDayRecorded) ? in_array($nextWorkDay, $empAttendance) : true;

                // Sandwich Rule: Absent before AND absent after off-day -> Sandwiched Absent
                if (!$prevPresent && !$nextPresent) {
                    $absent_days++;
                }
            } else {
                // Regular Working Day (Monday-Saturday)
                if (!in_array($day_num, $empAttendance)) {
                    $absent_days++;
                }
            }
        }

        // Daily rate is calculated on BASIC SALARY ONLY (30-day basis)
        $daily_rate = $basic / 30.0;

        if ($maxDayRecorded >= $actualDaysInMonth) {
            // Full month evaluation
            $dayspayable = max(0, 30 - $absent_days);
            $absent_amount = $absent_days * $daily_rate;
            $earned_basic = max(0, $basic - $absent_amount);
        } else {
            // Mid-month / partial month evaluation (e.g. 1st to 15th = 15 days)
            $dayspayable = max(0, $maxDayRecorded - $absent_days);
            $absent_amount = $absent_days * $daily_rate;
            $earned_basic = max(0, ($dayspayable / 30.0) * $basic);
        }

        // Fixed Additions / Allowances are added AS IS (100% full amount without absent penalty)
        $gross = $basic + $totalAllowance;
        $pay = max(0, $earned_basic + $totalAllowance - $deduction);

        // Check if salary record exists for the employee and month/year
        $sql_check = "SELECT * FROM `salary1` WHERE employeeID = '$id' AND `month` = $month AND `year` = $year";
        $result_check = $conn->query($sql_check);

        if ($result_check && $result_check->num_rows <= 0) {
            // Insert new salary record
            $sql_insert = "INSERT INTO `salary1`(`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$totalAllowance','$deduction','$gross','$month','$year','$maxDayRecorded','$dayspayable','$absent_amount','$pay','0','$pay',current_timestamp(),current_timestamp())";
        
            $sql_log = "INSERT IGNORE INTO `salary_log1` (`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$totalAllowance','$deduction','$gross','$month','$year','$maxDayRecorded','$dayspayable','$absent_amount','$pay','0','$pay',current_timestamp(),current_timestamp(),'$name')";
            
            mysqli_query($conn, $sql_insert);
            mysqli_query($conn, $sql_log);
        } else {
            // Update existing salary record safely
            $row_existing = $result_check->fetch_assoc();
            $paid_existing = floatval($row_existing['paid']);
            $remaining_update = max(0, $pay - $paid_existing);
            
            $sql_update = "UPDATE `salary1` SET 
                `fname` = '$fname',
                `lname` = '$lname',
                `basic_salary` = '$basic', 
                `allowance` = '$totalAllowance', 
                `deduction` = '$deduction', 
                `gross_salary` = '$gross', 
                `total_days` = '$maxDayRecorded', 
                `pay_days` = '$dayspayable', 
                `absent` = '$absent_amount', 
                `payable` = '$pay', 
                `remaining` = '$remaining_update',
                `updated_at` = current_timestamp()
            WHERE employeeID = '$id' AND `month` = $month AND `year` = $year";
            
            $sql_log_update = "INSERT IGNORE INTO `salary_log1` (`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$totalAllowance','$deduction','$gross','$month','$year','$maxDayRecorded','$dayspayable','$absent_amount','$pay','$paid_existing','$remaining_update',current_timestamp(),current_timestamp(),'$name')";
            
            mysqli_query($conn, $sql_update);
            mysqli_query($conn, $sql_log_update);
        }
    }
}

header("Location: ../view-salary.php?month=$month&year=$year&action=generated");
exit;
?>
