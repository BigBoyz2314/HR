<?php
    require_once('config.php');
?>

<?php

    // Initialize the session
    session_start();

    // Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }

    if ($_SESSION['role'] != '1') {
        header("location: index.php");
        exit;
    }

    $name = $_SESSION['name'];

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $month = $_REQUEST["month"];
        $year = $_REQUEST["year"];
    }

    $stmt = "SELECT * FROM employees";
    $result = $conn->query($stmt);
    $i = 1;
    $totaldays = cal_days_in_month(CAL_GREGORIAN,$_GET['month'],$_GET['year']);
    $today = date("d M y");
    $thismonth = date("n");
    $thisyear = date("Y");
    $date = date("d");
    $dayspayable = $date;

    if ($thismonth > $month) {
        $date = date(30);
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
            $allowance = $row['allowance'];
            $deduction = $row['deduction'];

            $stmt1 = "SELECT SUM(amount) AS amount FROM allowances where `month` = $month AND `year` = $year AND `employeeID` = $id";
            $stmt2 = "SELECT SUM(amount) AS amount FROM deductions where `month` = $month AND `year` = $year AND `employeeID` = $id";
            $result1 = $conn->query($stmt1);
            $result2 = $conn->query($stmt2);

            $row1 = $result1->fetch_assoc();
            $allowance = $row1['amount'];
            
            $row2 = $result2->fetch_assoc();
            $deduction = $row2['amount'];

            $gross = $basic + $allowance - $deduction;

            $day = 365/12;

            $dpay = $gross / $day;

            $abs = $day - $date;

            if ($date == 31 || 30) {

                $abs = 0;
            
            }

            $absent = $abs * $dpay;

            $pay = $dpay * $date;

            if ($date == 31 || 30) {

                $pay = $dpay * $day;

            }

            $pay = $pay - $absent;

            $sql ="INSERT IGNORE INTO `salary`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp())";

            $sql1 ="INSERT IGNORE INTO `salary_log` (`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp(),'$name')";
            
            mysqli_query($conn, $sql);
            mysqli_query($conn, $sql1);
        }

        header('Location: salary.php?action=generated&month='. $month .'&year='. $year .'');
    
    }
    


?>