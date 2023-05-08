<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fname = $_REQUEST["fName"];
        $mname = $_REQUEST["mName"];
        $lname = $_REQUEST["lName"];
        $dob = $_REQUEST["dob"];
        $mStatus = $_REQUEST["mStatus"];
        $children = $_REQUEST["children"];
        $spousename = $_REQUEST["spouseName"];
        $cnic = $_REQUEST["cnic"];
        $passport = $_REQUEST["passport"];
        $paddress = $_REQUEST["pAddress"];
        $saddress = $_REQUEST["sAddress"];
        $caddress = $_REQUEST["cAddress"];
        $pnumber = $_REQUEST["pNumber"];
        $snumber = $_REQUEST["sNumber"];
        $basicsalary = $_REQUEST["basicSalary"];
        $allowence = $_REQUEST["allowence"];
        $deduction = $_REQUEST["deduction"];
        $grosssalary = $_REQUEST["grossSalary"];
        $designation = $_REQUEST["designation"];
        $department = $_REQUEST["department"];
        $joindate = $_REQUEST["joinDate"];
        $leavedate = $_REQUEST["leaveDate"];
        $bank = $_REQUEST["bank"];
        $bankacc = $_REQUEST["bankAcc"];
        $iban = $_REQUEST["iban"];
        $manager = $_REQUEST["manager"];
        $warnings = $_REQUEST["warnings"];
        $leaves = $_REQUEST["leaves"];
        $allowleaves = $_REQUEST["allowLeaves"];
        $absents = $_REQUEST["absents"];
        $presents = $_REQUEST["presents"];
        $daysworking = $_REQUEST["daysWorking"];
        $loan = $_REQUEST["loan"];
        $loanamount = $_REQUEST["loanAmount"];
        $workinghours = $_REQUEST["workingHours"];
        $starttime = $_REQUEST["startTime"];
        $endtime = $_REQUEST["endTime"];
        $shift = $_REQUEST["shift"];
        $status = $_REQUEST["status"];
        $gender = $_REQUEST["gender"];
        
    }
        $stmt1 = "SELECT * FROM department WHERE departmentID = $department";
        $result1 = $conn->query($stmt1);
        
        if ($result1->num_rows > 0) {
                                    
            while($row = $result1->fetch_assoc()) { 
                $dept = $row['name'];
            }
        }

        $stmt2 = "SELECT * FROM designation WHERE designationID = $designation";
        $result2 = $conn->query($stmt2);
        
        if ($result2->num_rows > 0) {
                                    
            while($row = $result2->fetch_assoc()) { 
                $desig = $row['name'];
                $grade = $row['grade'];
            }
        }

        $yob = date('Y', strtotime($dob));
        $moj = date('m', strtotime($joindate));
        $mor = date('m', strtotime($leavedate));

        $sql = "INSERT INTO `employees`(`employeeID`, `fname`, `mname`, `lname`, `dob`,`yob`,`gender`, `designation`, `designationID`, `grade`, `department`, `departmentID`, `martital_status`, `status`, `children`, `spouse_name`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `join_date`, `leave_date`, `primary_address`, `secondary_address`, `current_address`, `primary_number`, `secondary_number`, `bank_name`, `bank_account_no`, `iban`, `employee_code`, `manager_name`, `managerID`, `warnings`, `leaves`, `allowed_leaves`, `absents`, `presents`, `days_working`, `loan`, `loan_amount`, `cnic`, `passport_no`, `working_hours`, `start_time`, `end_time`, `shift`, `join_month`, `leave_month`, `created_at`) VALUES
         ('','$fname','$mname','$lname','$dob','$yob','$gender','$desig','$designation','$grade','$dept','$department','$mStatus','$status','$children','$spousename','$basicsalary','$allowence','$deduction','$grosssalary','$joindate','$leavedate','$paddress','$saddress','$caddress','$pnumber','$snumber','$bank','$bankacc','$iban','','$manager','','$warnings','$leaves','$allowleaves','$absents','$presents','$daysworking','$loan','$loanamount','$cnic','$passport','$workinghours','$starttime','$endtime','$shift','$moj','$mor', current_timestamp())";


// "INSERT INTO employees VALUES ('', '$fname', '$mname', '$lname',  '$dob', $designation, '', '', $department, '', '$mStatus', '$status', '$children', '$spousename', '$basicsalary', '$allowence', '$deduction', '$grosssalary', '$joindate', '$leavedate', '$paddress', '$saddress', '$caddress', '$pnumber', '$snumber', '$bank', '$bankacc', '$iban', '$manager', '', '$warnings', '$leaves', '$allowleaves', '$absents', '$presents', '$daysworking', '$loan', '$loanamount', '$cnic', '$passport', '$workinghours', '$starttime', '$endtime', '$shift', current_timestamp())";

        if(mysqli_query($conn, $sql)){
            $last_id = $conn->insert_id;
            
            $sql1 = "INSERT INTO `employees_log`(`employeeID`, `fname`, `mname`, `lname`, `dob`,`yob`,`gender`, `designation`, `designationID`, `grade`, `department`, `departmentID`, `martital_status`, `status`, `children`, `spouse_name`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `join_date`, `leave_date`, `primary_address`, `secondary_address`, `current_address`, `primary_number`, `secondary_number`, `bank_name`, `bank_account_no`, `iban`, `employee_code`, `manager_name`, `managerID`, `warnings`, `leaves`, `allowed_leaves`, `absents`, `presents`, `days_working`, `loan`, `loan_amount`, `cnic`, `passport_no`, `working_hours`, `start_time`, `end_time`, `shift`, `join_month`, `leave_month`, `created_at`) VALUES
            ('$last_id','$fname','$mname','$lname','$dob','$yob','$gender','$desig','$designation','$grade','$dept','$department','$mStatus','$status','$children','$spousename','$basicsalary','$allowence','$deduction','$grosssalary','$joindate','$leavedate','$paddress','$saddress','$caddress','$pnumber','$snumber','$bank','$bankacc','$iban','','$manager','','$warnings','$leaves','$allowleaves','$absents','$presents','$daysworking','$loan','$loanamount','$cnic','$passport','$workinghours','$starttime','$endtime','$shift','$moj','$mor', current_timestamp())";
                
            $sql2 = "UPDATE department SET current_Strength = current_Strength + 1 WHERE departmentID = $department";

            mysqli_query($conn, $sql1);
            mysqli_query($conn, $sql2);
            header('Location: employees.php');
        }else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>