<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_REQUEST["empID"];
        $name = $_REQUEST["name1"];
        $oldDesig = $_REQUEST["oldDesig"];
        $oldDept = $_REQUEST["oldDept"];
        $oldDesigID = $_REQUEST["oldDesigID"];
        $oldDeptID = $_REQUEST["oldDeptID"];
        $newDept = $_REQUEST["dept"];
        $newDesig = $_REQUEST["desig"];
        $newbasic = $_REQUEST["basicSalary"];
        $newallow = $_REQUEST["allowance"];
        $newdeduc = $_REQUEST["deduction"];
        $newgross = $_REQUEST["grossSalary"];
    }

    if ($newDesig == "") {
        $newDesig = $oldDesig;
        $DesigID = $oldDesigID;
        $stmt3 = "SELECT * FROM designation WHERE designationID = $DesigID";
        $result3 = $conn->query($stmt3);
        
        if ($result3->num_rows > 0) {
                                    
            while($row = $result3->fetch_assoc()) { 
                $newDesig = $row['name'];
                $newGrade = $row['grade'];
            }
        }
    }
    else {
        $DesigID = $newDesig;
        $stmt2 = "SELECT * FROM designation WHERE designationID = $newDesig";
        $result2 = $conn->query($stmt2);
        
        if ($result2->num_rows > 0) {
                                    
            while($row = $result2->fetch_assoc()) { 
                $newDesig = $row['name'];
                $newGrade = $row['grade'];
            }
        }
    }

    if ($newDept == "") {
        $newDept = $oldDept; 
        $deptID = $oldDeptID; 
    }
    else {
        $deptID = $newDept;
        $stmt1 = "SELECT * FROM department WHERE departmentID = $newDept";
        $result1 = $conn->query($stmt1);
        
        if ($result1->num_rows > 0) {
                                    
            while($row = $result1->fetch_assoc()) { 
                $newDept = $row['name'];
                $deptID = $row['departmentID'];
            }
        }
    }

    $stmt3 = "SELECT * FROM employees WHERE employeeID = '$id'";
    $result3 = $conn->query($stmt3);
    $row = $result3->fetch_assoc();
    $fname = ucwords($row['fname']);
    $mname = ucwords($row['mname']);
    $lname = ucwords($row['lname']);
    $dob = $row['dob'];
    $desig = $row['designation'];
    $grade = $row['grade'];
    $dept = $row['department'];
    $status = $row['status'];
    $mstatus = $row['martital_status'];
    $children = $row['children'];
    $spouse = ucwords($row['spouse_name']);
    $basic = $row['basic_salary'];
    $deduction = $row['deduction'];
    $allowance = $row['allowance'];
    $gross = $row['gross_salary'];
    $joindate = $row['join_date'];
    $leavedate = $row['leave_date'];
    $paddress = ucwords($row['primary_address']);
    $saddress = ucwords($row['secondary_address']);
    $caddress = ucwords($row['current_address']);
    $pnumber = $row['primary_number'];
    $snumber = $row['secondary_number'];
    $bank = ucwords($row['bank_name']);
    $bankacc = $row['bank_account_no'];
    $iban = $row['iban'];
    $cnic = $row['cnic'];
    $passport = $row['passport_no'];
    $manager = ucwords($row['manager_name']);
    $warnings = $row['warnings'];
    $leaves = $row['leaves'];
    $allowleaves = $row['allowed_leaves'];
    $yob = $row['yob'];
    $absents = $row['absents'];
    $presents = $row['presents'];
    $daysworking = $row['days_working'];
    $loan = $row['loan'];
    $loanamount = $row['loan_amount'];
    $starttime = $row['start_time'];
    $endtime = $row['end_time'];
    $shift = $row['shift'];
    $created = $row['created_at'];
    $gender = $row['gender'];
    $workinghours = $row['working_hours'];
    $moj = $row['join_month'];
    $mor = $row['leave_month'];


    
    $sql ="UPDATE `employees` SET `basic_salary`='$newbasic',`gross_salary`='$newgross',`allowance`='$newallow',`deduction`='$newdeduc', `department`='$newDept',`designation`='$newDesig',`grade`='$newGrade',`updated_at` = current_timestamp() WHERE `fname` = '$fname' AND `employeeID` = '$id'";

        
        if(mysqli_query($conn, $sql)){

            $sql1 = "INSERT INTO `employees_log`(`employeeID`, `fname`, `mname`, `lname`, `dob`,`yob`,`gender`, `designation`, `designationID`, `grade`, `department`, `departmentID`, `martital_status`, `status`, `children`, `spouse_name`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `join_date`, `leave_date`, `primary_address`, `secondary_address`, `current_address`, `primary_number`, `secondary_number`, `bank_name`, `bank_account_no`, `iban`, `employee_code`, `manager_name`, `managerID`, `warnings`, `leaves`, `allowed_leaves`, `absents`, `presents`, `days_working`, `loan`, `loan_amount`, `cnic`, `passport_no`, `working_hours`, `start_time`, `end_time`, `shift`, `join_month`, `leave_month`, `created_at`, `updated_at`) VALUES
            ('$id','$fname','$mname','$lname','$dob','$yob','$gender','$newDesig','$DesigID','$newGrade','$newDept','$deptID','$mstatus','$status','$children','$spouse','$newbasic','$newallow','$newdeduc','$newgross','$joindate','$leavedate','$paddress','$saddress','$caddress','$pnumber','$snumber','$bank','$bankacc','$iban','','$manager','','$warnings','$leaves','$allowleaves','$absents','$presents','$daysworking','$loan','$loanamount','$cnic','$passport','$workinghours','$starttime','$endtime','$shift','$moj','$mor', '$created', current_timestamp())";
                
            $sql2 = "UPDATE department SET current_Strength = current_Strength + 1 WHERE name = '$newDept'";
            $sql3 = "UPDATE department SET current_Strength = current_Strength - 1 WHERE name = '$oldDept'";

            mysqli_query($conn, $sql1);
            mysqli_query($conn, $sql2);
            mysqli_query($conn, $sql3);
            header('Location: view-employees.php');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>