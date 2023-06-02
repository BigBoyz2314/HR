<?php
    require_once('config.php');
?>

<?php

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
    $date = date(31);
    $dayspayable = $date;
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
            $cAllowance = $row['c_allowance'];
            $eobi = $row['eobi'];
            $deduction = $row['deduction'];
            $gross = $row['gross_salary'];                                      
            
            $day = 365/12;

            $dpay = $gross / $day;

            $abs = $day - $date;

            if ($date == 31) {

                $abs = 0;
            
            }

            $absent = $abs * $dpay;

            $pay = $dpay * $date;

            if ($date == 31) {

                $pay = $dpay * $day;

            }

            $pay = $pay - $absent;

            $sql ="INSERT INTO `salary`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `c_allowance`, `eobi`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$cAllowance','$eobi','$deduction','$gross','$month','$year','$totaldays','$dayspayable','$absent','$pay','','$pay',current_timestamp(),current_timestamp())";
            
            mysqli_query($conn, $sql);
        }

        header('Location: salary.php');
    
    }
    


?>