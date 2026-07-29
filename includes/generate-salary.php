<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}


    $name = $_SESSION['name'];

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $month = $_REQUEST["month"];
        $year = $_REQUEST["year"];
    }

    // Optimized: Fetch all allowances in one query
    $allowances_data = [];
    $allowances_sql = "SELECT `employeeID`, SUM(amount) AS amount FROM allowances WHERE `month` = ? AND `year` = ? GROUP BY `employeeID`";
    $allowances_stmt = $conn->prepare($allowances_sql);
    $allowances_stmt->bind_param("ii", $month, $year);
    $allowances_stmt->execute();
    $allowances_result = $allowances_stmt->get_result();
    while ($row_allow = $allowances_result->fetch_assoc()) {
        $allowances_data[$row_allow['employeeID']] = $row_allow['amount'];
    }
    $allowances_stmt->close();

    // Optimized: Fetch all deductions in one query
    $deductions_data = [];
    $deductions_sql = "SELECT `employeeID`, SUM(amount) AS amount FROM deductions WHERE `month` = ? AND `year` = ? GROUP BY `employeeID`";
    $deductions_stmt = $conn->prepare($deductions_sql);
    $deductions_stmt->bind_param("ii", $month, $year);
    $deductions_stmt->execute();
    $deductions_result = $deductions_stmt->get_result();
    while ($row_ded = $deductions_result->fetch_assoc()) {
        $deductions_data[$row_ded['employeeID']] = $row_ded['amount'];
    }
    $deductions_stmt->close();

    $stmt = "SELECT `employeeID`, `fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary` FROM employees";
    $result = $conn->query($stmt);
    $i = 1;
    $totaldays = cal_days_in_month(CAL_GREGORIAN,$_GET['month'],$_GET['year']);
    $today = date("d M y");
    $thismonth = date("n");
    $thisyear = date("Y");
    $date = date("d");
    $dayspayable = $date;

    if ($thismonth > $month) {
        $date = $totaldays;
        $dayspayable = $date;
    }
    if ($date == 31) {
        $dayspayable = 30;
    }

    if ($result->num_rows > 0) {
        // output data of each row
        
        while($row = $result->fetch_assoc()) { 

            $id = $row['employeeID'];
            $fname = $row['fname'];
            $mname = $row['mname'];
            $lname = $row['lname'];
            $desig = $row['designation'];
            $dept = $row['department'];
            $gender = $row['gender'];
            $basic = $row['basic_salary'];

            // Get data from pre-fetched arrays
            $allowance = isset($allowances_data[$id]) ? $allowances_data[$id] : 0;
            $deduction = isset($deductions_data[$id]) ? $deductions_data[$id] : 0;

            $gross = $basic + $allowance - $deduction;

            $day = 365/12;

            $dpay = $gross / $day;

            
            if ($dayspayable <= 0) {
                $abs = 30;
            }
            elseif ($dayspayable === 30 || 31) {
                $abs = 0;
            }
            else {
                $abs = $day - $dayspayable;
            }
            
            $pay = $dpay * $dayspayable;

            if ($dayspayable <= 0) {
            
                $pay = 0;
            
            }

            if ($dayspayable === 31 || 30) {

                $pay = $dpay * $day;

            }

            $absent = $abs * $dpay;

            $sql ="INSERT INTO `salary`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp())";

            $sql1 ="INSERT IGNORE INTO `salary_log` (`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp(),'$name')";
            
            mysqli_query($conn, $sql);
            mysqli_query($conn, $sql1);
        }

        header('Location: ../salary.php?action=generated&month='. $month .'&year='. $year .'');
    
    }
    


?>