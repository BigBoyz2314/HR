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
    $date = date("d");
    $firstDate = date("Ymd", strtotime($today));
    $secondDates = $_GET['year'] .'-0'. $_GET['month'];
    $secondDate = date("Ym", strtotime($secondDates));

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
            $gross = $row['gross_salary'];                                      
            
            $day = 30;

            if ($_GET['month'] == 2) {

                $day = 28;

            }

            $dpay = $gross / $day;

            $abs = $day - $date;

            if ($_GET['month'] == 2 && $date == 28) {

                $abs = 0;

            }
            elseif ($date == 31) {

                $abs = 0;

            }

            $absent = $abs * $dpay;

            $pay = $dpay * $date;

            if ($date == 31) {

                $pay = $dpay * $day;
                $date = 30;

            }

            else {

                $pay = $dpay * $day;

            }

            $pay = $pay - $absent;

            $sql ="INSERT INTO `salary`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$date','$absent','$pay','','',current_timestamp(),current_timestamp())";
            
            mysqli_query($conn, $sql);
        }

        header('Location: salary.php');
    
    }
    


?>