<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
    require_once('config.php');

    $id = $_GET['id'];
    $stmt = "SELECT * FROM employees WHERE employeeID = '$id'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $eid = $row['employeeID'];
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];
    $dob = $row['dob'];
    $desig = $row['designation'];
    $grade = $row['grade'];
    $dept = $row['department'];
    $status = $row['status'];
    $mstatus = $row['martital_status'];
    $children = $row['children'];
    $spouse = $row['spouse_name'];
    $basic = $row['basic_salary'];
    $deduction = $row['deduction'];
    $allowance = $row['allowance'];
    $cAllowance = $row['c_allowance'];
    $eobi = $row['eobi'];
    $gross = $row['gross_salary'];
    $joindate = $row['join_date'];
    $leavedate = $row['leave_date'];
    $paddress = $row['primary_address'];
    $saddress = $row['secondary_address'];
    $caddress = $row['current_address'];
    $pnumber = $row['primary_number'];
    $snumber = $row['secondary_number'];
    $bank = $row['bank_name'];
    $bankacc = $row['bank_account_no'];
    $iban = $row['iban'];
    $cnic = $row['cnic'];
    $passport = $row['passport_no'];
    $manager = $row['manager_name'];
    $warnings = $row['warnings'];
    $leaves = $row['leaves'];
    $allowleave = $row['allowed_leaves'];
    $absents = $row['absents'];
    $presents = $row['presents'];
    $daysworking = $row['days_working'];
    $loan = $row['loan'];
    $loanamount = $row['loan_amount'];
    $starttime = $row['start_time'];
    $endtime = $row['end_time'];
    $shift = $row['shift'];
    $created = $row['created_at'];
    $updated = $row['updated_at'];
    $gender = $row['gender'];
    $disability = $row['disability'];
    $photo = $row['photo'];
    $efname = $row['e_fname'];
    $emname = $row['e_mname'];
    $elname = $row['e_lname'];
    $egender = $row['e_gender'];
    $econtact = $row['e_contact'];
    $ecnic = $row['e_cnic'];
    $eaddress = $row['e_address'];
    $edob = $row['e_dob'];
    $emp = $row['e_emp'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <title>View Employee</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <div class="row justify-content-between">
            <div class="col-md-3">
                <h1>View Employee</h1>
            </div>
            <div class="col-md-3">
                    <h5>Created</h5>
                    <input value="<?php echo $created;?>" disabled type="text" name="created" id="created" class="form-control">
                </div>
                <div class="col-md-3">
                    <h5>Updated</h5>
                    <input value="<?php echo $updated?>" disabled type="text" name="created" id="created" class="form-control">
                </div>    
                <div class="col-md-2">
                    <h5>Employee ID</h5>
                    <input value="<?php echo $eid?>" disabled type="text" name="eid" id="eid" class="form-control text-center">
                </div>    
        </div>
        <form action="" method="post">
            <div class="personal-info border rounded p-2 mt-2">
                <h3>Personal Information</h3>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>First Name</h5>
                        <input value="<?php echo $fname?>" disabled type="text" name="fName" id="fName" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Middle Name</h5>
                        <input value="<?php echo $mname?>" disabled type="text" name="mName" id="mName" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Last Name</h5>
                        <input value="<?php echo $lname?>" disabled type="text" name="lName" id="lName" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Gender</h5>
                        <input value="<?php echo $gender?>" disabled type="text" name="lName" id="lName" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Date of Birth</h5>
                        <input value="<?php echo $dob?>" disabled type="text" name="dob" id="dob" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Martital Status</h5>
                        <input value="<?php echo $mstatus?>" disabled name="mStatus" id="mStatus" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>Children</h5>
                        <input value="<?php echo $children?>" disabled type="number" min="0" name="children" id="children" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Spouse Name</h5>
                        <input value="<?php echo $spouse?>" disabled type="text" name="spouseName" id="spouseName" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>CNIC</h5>
                        <input value="<?php echo $cnic?>" disabled type="number" name="cnic" id="cnic" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Passport</h5>
                        <input value="<?php echo $passport?>" disabled type="text" name="passport" id="passport" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Disabled</h5>
                        <input value="<?php echo $disability?>" disabled type="text" name="passport" id="passport" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4 ">
                        <h5>Photo</h5>
                        <div class="text-center">
                            <img src="<?php echo "uploads/".$photo ?>" alt="" width="50px" height="50px">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 pt-4">
                        <h5>Primary Address</h5>
                        <input value="<?php echo $paddress?>" disabled type="text" name="pAddress" id="pAddress" class="form-control">
                    </div>
                    <div class="col-md-4 pt-4">
                        <h5>Secondary Address</h5>
                        <input value="<?php echo $saddress?>" disabled type="text" name="sAddress" id="sAddress" class="form-control">
                    </div>
                    <div class="col-md-4 pt-3">
                        <h5 class="d-inline-block">Current Address</h5>
                        <input value="<?php echo $caddress?>" disabled type="text" name="cAddress" id="cAddress" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 pt-4">
                        <h5>Contact 1 #</h5>
                        <input value="<?php echo $pnumber?>" disabled type="number" name="pNumber" id="pNumber" class="form-control">
                    </div>
                    <div class="col-md-4 pt-4">
                        <h5>Contact 2 #</h5>
                        <input value="<?php echo $snumber?>" disabled type="number" name="sNumber" id="sNumber" class="form-control">
                    </div>
                </div>
            </div>
            <div class="emergency-info border rounded p-2 mt-2">
                <h3>Emergency Information</h3>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>First Name</h5>
                        <input type="text" value="<?php echo $efname?>" name="efName" id="efName" class="form-control" disabled>
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Middle Name</h5>
                        <input type="text" value="<?php echo $emname?>" name="emName" id="emName" class="form-control" disabled>
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Last Name</h5>
                        <input type="text" value="<?php echo $elname?>" name="elName" id="elName" class="form-control" disabled>
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Gender</h5>
                        <input type="text" value="<?php echo $egender?>" name="egender" id="egender" class="form-control" disabled>
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Date of Birth</h5>
                        <input type="date" value="<?php echo $edob?>" name="edob" id="edob" class="form-control" disabled>
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Employee</h5>
                        <input type="text" value="<?php echo $emp?>" name="e_emp" id="e_emp" class="form-control" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>CNIC</h5>
                        <input type="number" value="<?php echo $ecnic?>" name="ecnic" id="ecnic" class="form-control" disabled>
                    </div>
                    <div class="col-md-4 pt-4">
                        <h5>Emergency Address</h5>
                        <input type="text" value="<?php echo $eaddress?>" name="eAddress" id="eAddress" class="form-control" disabled>
                    </div>
                    <div class="col-md-4 pt-4">
                        <h5>Emergency Contact</h5>
                        <input type="number" value="<?php echo $econtact?>" name="eNumber" id="eNumber" class="form-control" disabled>
                    </div>
                </div>
            </div>
            <div class="salary-information border rounded p-2 mt-2">
                <h3>Salary Information</h3>
                <div class="row">
                    <div class="col-md-3 pt-4">
                        <h5>Bank Name</h5>
                        <input value="<?php echo $bank?>" disabled type="text" name="bank" id="bank" class="form-control">
                    </div>
                    <div class="col-md-4 pt-4">
                        <h5>Bank Account no.</h5>
                        <input value="<?php echo $bankacc?>" disabled type="number" name="bankAcc" id="bankAcc" class="form-control">
                    </div>
                    <div class="col-md-5 pt-4">
                        <h5>IBAN</h5>
                        <input value="<?php echo $iban?>" disabled type="text" name="iban" id="iban" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>Basic Salary</h5>
                        <input value="<?php echo $basic?>" disabled type="number" min="0" name="basicSalary" id="basicSalary" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Allowence</h5>
                        <input value="<?php echo $allowance?>" disabled type="number" min="0" name="allowence" id="allowence" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Conveyance Allow.</h5>
                        <input value="<?php echo $cAllowance?>" disabled type="number" min="0" name="allowence" id="allowence" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Deduction</h5>
                        <input value="<?php echo $deduction?>" disabled type="number" min="0" name="deduction" id="deduction" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>EOBI</h5>
                        <input value="<?php echo $eobi?>" disabled type="number" min="0" name="deduction" id="deduction" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Gross Salary</h5>
                        <input value="<?php echo $gross?>" disabled type="number" name="grossSalary" id="grossSalary" class="form-control">
                    </div>
                </div>
            </div>
            <div class="official-information border rounded p-2 mt-2">
                <h3>Official Information</h3>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>Designation</h5>
                        <input value="<?php echo $desig?>" disabled type="text" name="designation" id="designation" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Department</h5>
                        <input value="<?php echo $dept?>" disabled type="text" name="department" id="department" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Join Date</h5>
                        <input value="<?php echo $joindate?>" disabled type="text" name="joinDate" id="joinDate" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Leave Date</h5>
                        <input value="<?php echo $leavedate?>" disabled type="text" name="leaveDate" id="leaveDate" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Shift</h5>
                        <input value="<?php echo $shift?>" disabled name="shift" id="shift" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Status</h5>
                        <input value="<?php echo $status?>" disabled name="status" id="status" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 pt-4">
                        <h5>Line Manager</h5>
                        <input value="<?php echo $manager?>" disabled type="text" name="manager" id="manager" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Warnings</h5>
                        <input value="<?php echo $warnings?>" disabled type="number" min="0" name="warnings" id="warnings" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Leaves</h5>
                        <input value="<?php echo $leaves?>" disabled type="number" min="0" name="leaves" id="leaves" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Allowed Leaves</h5>
                        <input value="<?php echo $allowleave?>" disabled type="number" min="0" name="allowLeaves" id="allowLeaves" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Absents</h5>
                        <input value="<?php echo $absents?>" disabled type="number" min="0" name="absents" id="absents" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Presents</h5>
                        <input value="<?php echo $presents?>" disabled type="number" min="0" name="presents" id="presents" class="form-control">
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-md-2 pt-4">
                        <h5>Days Workings</h5>
                        <input value="<?php echo $daysworking?>" disabled type="number" min="0" name="daysWorking" id="daysWorking" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Loan</h5>
                        <input value="<?php echo $loan?>" disabled name="loan" id="loan" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Loan Amount</h5>
                        <input value="<?php echo $loanamount?>" disabled type="number" min="0" name="loanAmount" id="loanAmount" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Hours</h5>
                        <input value="<?php echo $daysworking?>" disabled type="number" min="0" name="workingHours" id="workingHours" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>Start Time</h5>
                        <input value="<?php echo $starttime?>" disabled type="text" name="startTime" id="startTime" class="form-control">
                    </div>
                    <div class="col-md-2 pt-4">
                        <h5>End Time</h5>
                        <input value="<?php echo $endtime?>" disabled type="text" name="endTime" id="endTime" class="form-control">
                    </div>
                </div>
            </div>
            </form>
            <div class="row justify-content-center">
        <?php
        
        if ($_SESSION['role'] == '1') {
            echo '<form action="edit-employee.php" method="get" class="mt-4 ">
                    <input type="hidden" name="empID" id="empID" value="'. $id .'">
                    <input type="submit" value="Edit" class="btn btn-warning">
                </form>';
        }

        ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script>
        
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>