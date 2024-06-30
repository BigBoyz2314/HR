<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_REQUEST["id"];
        $payable = $_REQUEST["payable"];
        $paid = $_REQUEST["paid"];
        $remaining = $_REQUEST["remaining"];
        $pay = $_REQUEST["pay"];
        $fname = $_REQUEST["fname"];
        $year = $_REQUEST["year"];
        $month = $_REQUEST["month"];
    }

    $pai = $paid + $pay;
    
    $remain = $remaining - $pay;

    echo $remaining;

        $sql ="UPDATE `salary1` SET `remaining`='$remain', `paid`='$pai', `updated_at` = current_timestamp() WHERE `fname` = '$fname' AND `employeeID` = '$id' AND `year` = $year AND `month` = $month";
        
        if(mysqli_query($conn, $sql)){

            $stmt = "SELECT * FROM salary1 WHERE employeeID= $id";
            $result = $conn->query($stmt);
            $row = $result->fetch_assoc();
            $eid = $row['employeeID'];
            $fname = $row['fname'];
            $mname = $row['mname'];
            $lname = $row['lname'];
            $month = $row['month'];
            $year = $row['year'];
            $desig = $row['designation'];
            $dept = $row['department'];
            $gender = $row['gender'];
            $paydays = $row['pay_days'];
            $totaldays = $row['total_days'];
            $basic = $row['basic_salary'];
            $allowance = $row['allowance'];
            $deduction = $row['deduction'];
            $gross = $row['gross_salary'];                                    
            $payable = $row['payable'];                             
            $absent = $row['absent'];                                 
            $paid = $row['paid'];
            $remaining = $row['remaining'];

            $sql1 ="INSERT IGNORE INTO `salary_log1`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$paydays','$absent','$pay','$paid','$remaining',current_timestamp(),current_timestamp(),'$name')";
           
           mysqli_query($conn, $sql1);

            header('Location: view-salary.php?month='. $month .'&year='. $year .'&action=paid');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>