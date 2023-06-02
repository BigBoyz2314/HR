<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fname = ucwords($_REQUEST["fName"]);
        $mname = ucwords($_REQUEST["mName"]);
        $lname = ucwords($_REQUEST["lName"]);
        $dob = $_REQUEST["dob"];
        $edob = $_REQUEST["edob"];
        $mStatus = $_REQUEST["mStatus"];
        $children = $_REQUEST["children"];
        $spousename = ucwords($_REQUEST["spouseName"]);
        $cnic = $_REQUEST["cnic"];
        $ecnic = $_REQUEST["ecnic"];
        $passport = $_REQUEST["passport"];
        $paddress = ucwords($_REQUEST["pAddress"]);
        $saddress = ucwords($_REQUEST["sAddress"]);
        $caddress = ucwords($_REQUEST["cAddress"]);
        $eaddress = ucwords($_REQUEST["eAddress"]);
        $pnumber = $_REQUEST["pNumber"];
        $snumber = $_REQUEST["sNumber"];
        $enumber = $_REQUEST["eNumber"];
        $basicsalary = $_REQUEST["basicSalary"];
        $allowance = $_REQUEST["allowence"];
        $cAllowance = $_REQUEST["cAllowence"];
        $eobi = $_REQUEST["eobi"];
        $deduction = $_REQUEST["deduction"];
        $grosssalary = $_REQUEST["grossSalary"];
        $designation = $_REQUEST["designation"];
        $department = $_REQUEST["department"];
        $joindate = $_REQUEST["joinDate"];
        $leavedate = $_REQUEST["leaveDate"];
        $bank = ucwords($_REQUEST["bank"]);
        $bankacc = $_REQUEST["bankAcc"];
        $iban = $_REQUEST["iban"];
        $manager = ucwords($_REQUEST["manager"]);
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
        $egender = $_REQUEST["egender"];
        $disability = $_REQUEST["disability"];
        $emp = $_REQUEST["e_emp"];
        $efname = ucwords($_REQUEST["efName"]);
        $emname = ucwords($_REQUEST["emName"]);
        $elname = ucwords($_REQUEST["elName"]);


        $target_dir = "uploads/";
        $filename = basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["photo"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            echo "The file ". htmlspecialchars( basename( $_FILES["photo"]["name"])). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
        }
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

        $sql = "INSERT INTO `employees`(`employeeID`, `fname`, `mname`, `lname`, `dob`,`yob`,`gender`, `designation`, `designationID`, `grade`, `department`, `departmentID`, `martital_status`, `status`, `children`, `spouse_name`, `basic_salary`, `allowance`, `c_allowance`, `eobi`, `deduction`, `gross_salary`, `join_date`, `leave_date`, `primary_address`, `secondary_address`, `current_address`, `primary_number`, `secondary_number`, `bank_name`, `bank_account_no`, `iban`, `employee_code`, `manager_name`, `managerID`, `warnings`, `leaves`, `allowed_leaves`, `absents`, `presents`, `days_working`, `loan`, `loan_amount`, `cnic`, `passport_no`, `working_hours`, `start_time`, `end_time`, `shift`, `join_month`, `leave_month`, `disability`, `photo`, `created_at`, `e_fname`, `e_mname`, `e_lname`, `e_dob`, `e_cnic`, `e_contact`, `e_gender`, `e_address`, `e_emp`) VALUES
         ('','$fname','$mname','$lname','$dob','$yob','$gender','$desig','$designation','$grade','$dept','$department','$mStatus','$status','$children','$spousename','$basicsalary','$allowance','$cAllowance','$eobi','$deduction','$grosssalary','$joindate','$leavedate','$paddress','$saddress','$caddress','$pnumber','$snumber','$bank','$bankacc','$iban','','$manager','','$warnings','$leaves','$allowleaves','$absents','$presents','$daysworking','$loan','$loanamount','$cnic','$passport','$workinghours','$starttime','$endtime','$shift','$moj','$mor','$disability','$filename', current_timestamp(), '$efname','$emname','$elname','$edob','$ecnic','$enumber','$egender','$eaddress','$emp')";


        if(mysqli_query($conn, $sql)){
            $last_id = $conn->insert_id;

            $sql1 = "INSERT INTO `employees_log`(`employeeID`, `fname`, `mname`, `lname`, `dob`,`yob`,`gender`, `designation`, `designationID`, `grade`, `department`, `departmentID`, `martital_status`, `status`, `children`, `spouse_name`, `basic_salary`, `allowance`, `c_allowance`, `eobi`, `deduction`, `gross_salary`, `join_date`, `leave_date`, `primary_address`, `secondary_address`, `current_address`, `primary_number`, `secondary_number`, `bank_name`, `bank_account_no`, `iban`, `employee_code`, `manager_name`, `managerID`, `warnings`, `leaves`, `allowed_leaves`, `absents`, `presents`, `days_working`, `loan`, `loan_amount`, `cnic`, `passport_no`, `working_hours`, `start_time`, `end_time`, `shift`, `join_month`, `leave_month`, `disability`, `photo`, `created_at`, `e_fname`, `e_mname`, `e_lname`, `e_dob`, `e_cnic`, `e_contact`, `e_gender`, `e_address`, `e_emp`) VALUES
            ('$last_id','$fname','$mname','$lname','$dob','$yob','$gender','$desig','$designation','$grade','$dept','$department','$mStatus','$status','$children','$spousename','$basicsalary','$allowance','$cAllowance','$eobi','$deduction','$grosssalary','$joindate','$leavedate','$paddress','$saddress','$caddress','$pnumber','$snumber','$bank','$bankacc','$iban','','$manager','','$warnings','$leaves','$allowleaves','$absents','$presents','$daysworking','$loan','$loanamount','$cnic','$passport','$workinghours','$starttime','$endtime','$shift','$moj','$mor','$disability','$filename', current_timestamp(), '$efname','$emname','$elname','$edob','$ecnic','$enumber','$egender','$eaddress','$emp')";
   
                
            $sql2 = "UPDATE department SET current_Strength = current_Strength + 1 WHERE departmentID = $department";

            mysqli_query($conn, $sql1);
            mysqli_query($conn, $sql2);
            header('Location: employees.php');
        }else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>