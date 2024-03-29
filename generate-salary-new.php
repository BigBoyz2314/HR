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
    $totaldays = cal_days_in_month(CAL_GREGORIAN,$_GET['month'],$_GET['year']);

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

            $stmt3 = "SELECT COUNT(day) as paydays FROM attendance WHERE `month` = $month AND `year` = $year AND `employeeID` = $id";
            $result3 = $conn->query($stmt3);

            $stmt1 = "SELECT SUM(amount) AS amount FROM allowances where `month` = $month AND `year` = $year AND `employeeID` = $id";
            $stmt2 = "SELECT SUM(amount) AS amount FROM deductions where `month` = $month AND `year` = $year AND `employeeID` = $id";
            $result1 = $conn->query($stmt1);
            $result2 = $conn->query($stmt2);

            
            $stmt4 = "SELECT COUNT(day) as `holidays` FROM holidays WHERE `month` = $month AND `year` = $year";
            $result4 = $conn->query($stmt4);

            $row4 = $result4->fetch_assoc();
            $holidays = $row4['holidays'];

            $row3 = $result3->fetch_assoc();
            $paydays = $row3['paydays'];

            $row1 = $result1->fetch_assoc();
            $allowance = $row1['amount'];
            
            $row2 = $result2->fetch_assoc();
            $deduction = $row2['amount'];

            for ($i=1; $i < $holidays; $i++) { 
                $paydays++;
            }

            if ($paydays == 31) {
                $dayspayable = 30;
            } 
            else {
                $dayspayable = $paydays;
            }

            $gross = $basic + $allowance - $deduction;

            $day = 365/12;

            $dpay = $gross / $day;

            if ($dayspayable <= 0) {
                $abs = 30;
            }
            elseif ($dayspayable == 31 || $dayspayable == 30) {
                $pay = $dpay * $day;
            } 
            else {
                $abs = $day - $dayspayable;
            }
            
            $pay = $dpay * $dayspayable;

            if ($dayspayable <= 0) {
                $pay = 0;
            }

            if ($dayspayable == 31 || $dayspayable == 30) {
                $pay = $dpay * $day;
            }
            if ($dayspayable == 0) {
                $absent = $gross;
            }
            else {
                $absent = $abs * $dpay;
            }

            
            $sql2 = "SELECT * FROM `salary` WHERE employeeID = '$id' AND `month` = $month AND `year` = $year";
            $result5 = $conn->query($sql2);


            if ($result5->num_rows <= 0) {
                // Insert new salary record
                $sql ="INSERT INTO `salary`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
                VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp())";
            
                $sql1 ="INSERT IGNORE INTO `salary_log` (`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
                VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp(),'$name')";
                            
                mysqli_query($conn, $sql);
                mysqli_query($conn, $sql1);
                            
                header('Location: salary.php?action=generated&month='. $month .'&year='. $year .'');
            }
            else {
                // Update existing salary record
                $row5 = $result5->fetch_assoc();
                $payabl = $row5['payable'];
                $pai = $row5['paid'];
                $remain = $row5['remaining'];
            
                $remaining = $payabl - $pai;
            
                $sql3 ="UPDATE `salary` SET `basic_salary` = '$basic', `allowance` = '$allowance', `deduction` = '$deduction', `gross_salary` = '$gross', `total_days` = '$totaldays', `pay_days` = '$dayspayable', `absent` = '$absent', `payable` = '$pay', `paid` = '$pai', `remaining` = '$remaining', `updated_at` = current_timestamp() WHERE employeeID = $id AND `month` = $month AND `year` = $year";
            
                $sql4 ="INSERT IGNORE INTO `salary_log` (`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
                VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','$pai','$remain',current_timestamp(),current_timestamp(),'$name')";
                        
                mysqli_query($conn, $sql3);
                mysqli_query($conn, $sql4);
                            
                header('Location: salary.php?action=updated&month='. $month .'&year='. $year .'');
            }
        }
    }
    


?>