<?php
require_once('config.php');

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect them to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the user has admin role (role 1), if not redirect to index.php
if ($_SESSION['role'] != '1') {
    header("location: index.php");
    exit;
}

$name = $_SESSION['name'];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $month = $_REQUEST["month"];
    $year = $_REQUEST["year"];
}

// Retrieve all employees
$stmt = "SELECT * FROM employees";
$result = $conn->query($stmt);
$totaldays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['sNo'];
        $fname = $row['fname'];
        $mname = $row['mname'];
        $lname = $row['lname'];
        $desig = $row['designation'];
        $dept = $row['department'];
        $gender = $row['gender'];
        $basic = $row['basic_salary'];

        // Fetch attendance, allowances, and deductions
        $stmt3 = "SELECT `day` FROM attendance1 WHERE `month` = $month AND `year` = $year AND `employeeID` = $id";
        $result3 = $conn->query($stmt3);

        $stmt1 = "SELECT SUM(amount) AS amount FROM allowances WHERE `month` = $month AND `year` = $year AND `employeeID` = $id";
        $stmt2 = "SELECT SUM(amount) AS amount FROM deductions WHERE `month` = $month AND `year` = $year AND `employeeID` = $id";
        $result1 = $conn->query($stmt1);
        $result2 = $conn->query($stmt2);

        $attendance_days = [];
        while ($row3 = $result3->fetch_assoc()) {
            $attendance_days[] = (int)$row3['day'];
        }

        $row1 = $result1->fetch_assoc();
        $allowance = $row1['amount'];

        $row2 = $result2->fetch_assoc();
        $deduction = $row2['amount'];

        // Fetch holidays in the month
        $holidays = [];
        $stmt4 = "SELECT `day` FROM holidays WHERE `month` = $month AND `year` = $year";
        $result4 = $conn->query($stmt4);
        while ($row4 = $result4->fetch_assoc()) {
            $holidays[] = (int)$row4['day'];
        }

        // Calculate the number of Sundays in the month
        $first_day = new DateTime("$year-$month-01");
        $last_day = new DateTime("$year-$month-$totaldays");
        $current_day = clone $first_day;
        $sundays = 0;
        $absent_days = 0;
        $paydays = 0;

        while ($current_day <= $last_day) {
            $day_num = (int)$current_day->format('j');
            if ($current_day->format('D') == 'Sun') {
                $prev_day = (clone $current_day)->modify('-1 day');
                $next_day = (clone $current_day)->modify('+1 day');

                $prev_day_in_attendance = in_array((int)$prev_day->format('j'), $attendance_days);
                $next_day_in_attendance = in_array((int)$next_day->format('j'), $attendance_days);

                if (!$prev_day_in_attendance && !$next_day_in_attendance) {
                    $absent_days++;
                } else {
                    $sundays++;
                    $paydays++;
                }
            } else {
                if (in_array($day_num, $attendance_days) || in_array($day_num, $holidays)) {
                    $paydays++;
                } else {
                    $absent_days++;
                }
            }
            $current_day->modify('+1 day');
        }

        // Calculate gross salary, payable amount, and absent days
        $gross = $basic + $allowance - $deduction;
        $day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dpay = $gross / $day_count; // Use the total days in the month for calculation

        $dayspayable = $paydays;
        $absent = $absent_days * $dpay;

        if ($dayspayable > $totaldays) {
            $dayspayable = $totaldays;
        }

        $pay = $dpay * $dayspayable;

        if ($dayspayable <= 0) {
            $pay = 0;
        }

        // Check if salary record exists for the employee and month/year
        $sql_check = "SELECT * FROM `salary1` WHERE employeeID = '$id' AND `month` = $month AND `year` = $year";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows <= 0) {
            // Insert new salary record
            $sql_insert = "INSERT INTO `salary1`(`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp())";
        
            $sql_log = "INSERT IGNORE INTO `salary_log1` (`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp(),'$name')";
            
            mysqli_query($conn, $sql_insert);
            mysqli_query($conn, $sql_log);
            
            header('Location: salary.php?action=generated&month='. $month .'&year='. $year .'');
        } else {
            // Update existing salary record
            $row_existing = $result_check->fetch_assoc();
            $payable_existing = $row_existing['payable'];
            $paid_existing = $row_existing['paid'];
            $remaining_existing = $row_existing['remaining'];
            
            $remaining_update = $payable_existing - $paid_existing;
            
            $sql_update = "UPDATE `salary1` SET 
                `basic_salary` = '$basic', 
                `allowance` = '$allowance', 
                `deduction` = '$deduction', 
                `gross_salary` = '$gross', 
                `total_days` = '$totaldays', 
                `pay_days` = '$dayspayable', 
                `absent` = '$absent', 
                `payable` = '$pay', 
                `paid` = '$paid_existing', 
                `remaining` = '$remaining_update', 
                `updated_at` = current_timestamp() 
                WHERE `employeeID` = '$id' AND `month` = '$month' AND `year` = '$year'";
            
            $sql_log_update = "INSERT IGNORE INTO `salary_log1` (`employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
                VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','$paid_existing','$remaining_update',current_timestamp(),current_timestamp(),'$name')";
                        
            mysqli_query($conn, $sql_update);
            mysqli_query($conn, $sql_log_update);
                            
            header('Location: salary.php?action=updated&month='. $month .'&year='. $year .'');
        }
    }
} else {
    echo "No employees found.";
}

$conn->close();
?>
