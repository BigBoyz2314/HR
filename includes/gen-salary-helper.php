<?php
require_once('config.php');
require_once('ledger-helper.php');

function autoGenerateSalarySheet($conn, $month, $year, $updatedBy = 'System') {
    $actualDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $maxDayRecorded = $actualDaysInMonth;
    $maxDayRes = $conn->query("SELECT MAX(`day`) AS max_day FROM attendance1 WHERE `month` = $month AND `year` = $year");
    if ($maxDayRes && $mRow = $maxDayRes->fetch_assoc()) {
        if (!empty($mRow['max_day'])) {
            $maxDayRecorded = intval($mRow['max_day']);
        }
    }

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

    $stmt = "SELECT `employeeID`, `sNo`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance` AS profile_allowance FROM employees WHERE status = 'Active'";
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

            $absent_days = 0;
            for ($day_num = 1; $day_num <= $maxDayRecorded; $day_num++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day_num);
                $dayOfWeek = date('D', strtotime($dateStr));
                $isOff = ($dayOfWeek === 'Sun' || in_array($day_num, $holidays));

                if ($isOff) {
                    $prevWorkDay = $day_num - 1;
                    while ($prevWorkDay >= 1 && (date('D', strtotime(sprintf('%04d-%02d-%02d', $year, $month, $prevWorkDay))) === 'Sun' || in_array($prevWorkDay, $holidays))) {
                        $prevWorkDay--;
                    }
                    $nextWorkDay = $day_num + 1;
                    while ($nextWorkDay <= $maxDayRecorded && (date('D', strtotime(sprintf('%04d-%02d-%02d', $year, $month, $nextWorkDay))) === 'Sun' || in_array($nextWorkDay, $holidays))) {
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

            // Check if existing record has adjustments
            $existingRes = $conn->query("SELECT * FROM salary1 WHERE employeeID = '$id' AND month = '$month' AND year = '$year' LIMIT 1");
            $arrears = 0; $ot1 = 0; $ot2 = 0; $lessLoans = 0; $lessAdv = 0; $paid = 0;
            $allowance = $profileAllowance;

            if ($existingRes && $exRow = $existingRes->fetch_assoc()) {
                $arrears = floatval($exRow['arrears'] ?? 0);
                $ot1 = floatval($exRow['ot_1_15'] ?? 0);
                $ot2 = floatval($exRow['ot_16_30'] ?? 0);
                $allowance = floatval($exRow['allowance'] ?? $profileAllowance);
                $lessLoans = floatval($exRow['less_loans'] ?? 0);
                $lessAdv = floatval($exRow['less_advance'] ?? 0);
                $paid = floatval($exRow['paid'] ?? 0);
            }

            $totalDeductions = $lessLoans + $lessAdv;
            $gross = $basic + $allowance;
            $pay = max(0, $earned_basic + $arrears + $ot1 + $ot2 + $allowance - $totalDeductions);
            $remaining = max(0, $pay - $paid);

            $fnameEsc = $conn->real_escape_string($fname);
            $mnameEsc = $conn->real_escape_string($mname);
            $lnameEsc = $conn->real_escape_string($lname);
            $desigEsc = $conn->real_escape_string($desig);
            $deptEsc  = $conn->real_escape_string($dept);

            if ($existingRes && $existingRes->num_rows > 0) {
                $conn->query("UPDATE salary1 SET 
                    fname = '$fnameEsc', lname = '$lnameEsc', basic_salary = '$basic', allowance = '$allowance', 
                    deduction = '$totalDeductions', gross_salary = '$gross', total_days = '$maxDayRecorded', 
                    pay_days = '$dayspayable', absent = '$absent_amount', arrears = '$arrears', 
                    ot_1_15 = '$ot1', ot_16_30 = '$ot2', less_loans = '$lessLoans', less_advance = '$lessAdv', 
                    payable = '$pay', remaining = '$remaining', updated_at = NOW() 
                    WHERE employeeID = '$id' AND month = '$month' AND year = '$year'");
            } else {
                $conn->query("INSERT INTO salary1 (employeeID, fname, mname, lname, designation, department, gender, basic_salary, allowance, deduction, gross_salary, month, year, total_days, pay_days, absent, arrears, ot_1_15, ot_16_30, less_loans, less_advance, payable, paid, remaining, created_at, updated_at) 
                    VALUES ('$id', '$fnameEsc', '$mnameEsc', '$lnameEsc', '$desigEsc', '$deptEsc', '$gender', '$basic', '$allowance', '$totalDeductions', '$gross', '$month', '$year', '$maxDayRecorded', '$dayspayable', '$absent_amount', '$arrears', '$ot1', '$ot2', '$lessLoans', '$lessAdv', '$pay', '0', '$pay', NOW(), NOW())");
            }

            // Sync with central Employee Ledger
            syncSalaryAccrualToLedger($conn, $id, $month, $year, $pay, $updatedBy);
        }
    }
}
?>
