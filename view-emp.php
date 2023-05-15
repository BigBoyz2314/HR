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
        <h1>View Employee</h1>
        <form action="" method="post">
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>First Name</h4>
                    <input value="<?php echo $fname?>" disabled type="text" name="fName" id="fName" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Middle Name</h4>
                    <input value="<?php echo $mname?>" disabled type="text" name="mName" id="mName" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Last Name</h4>
                    <input value="<?php echo $lname?>" disabled type="text" name="lName" id="lName" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Date of Birth</h4>
                    <input value="<?php echo $dob?>" disabled type="text" name="dob" id="dob" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Martital Status</h4>
                    <input value="<?php echo $mstatus?>" disabled name="mStatus" id="mStatus" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Children</h4>
                    <input value="<?php echo $children?>" disabled type="number" min="0" name="children" id="children" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Spouse Name</h4>
                    <input value="<?php echo $spouse?>" disabled type="text" name="spouseName" id="spouseName" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>CNIC</h4>
                    <input value="<?php echo $cnic?>" disabled type="number" name="cnic" id="cnic" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Passport no.</h4>
                    <input value="<?php echo $passport?>" disabled type="text" name="passport" id="passport" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 pt-4">
                    <h4>Primary Address</h4>
                    <input value="<?php echo $paddress?>" disabled type="text" name="pAddress" id="pAddress" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Secondary Address</h4>
                    <input value="<?php echo $saddress?>" disabled type="text" name="sAddress" id="sAddress" class="form-control">
                </div>
                <div class="col-md-4 pt-3">
                    <h4 class="d-inline-block">Current Address</h4>
                    <input value="<?php echo $caddress?>" disabled type="text" name="cAddress" id="cAddress" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>Primary Number</h4>
                    <input value="<?php echo $pnumber?>" disabled type="number" name="pNumber" id="pNumber" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Secondary Number</h4>
                    <input value="<?php echo $snumber?>" disabled type="number" name="sNumber" id="sNumber" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Basic Salary</h4>
                    <input value="<?php echo $basic?>" disabled type="number" min="0" name="basicSalary" id="basicSalary" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowence</h4>
                    <input value="<?php echo $allowance?>" disabled type="number" min="0" name="allowence" id="allowence" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Deduction</h4>
                    <input value="<?php echo $deduction?>" disabled type="number" min="0" name="deduction" id="deduction" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Gross Salary</h4>
                    <input value="<?php echo $gross?>" disabled type="number" name="grossSalary" id="grossSalary" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>Designation</h4>
                    <input value="<?php echo $desig?>" disabled type="text" name="designation" id="designation" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Department</h4>
                    <input value="<?php echo $dept?>" disabled type="text" name="department" id="department" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>Join Date</h4>
                    <input value="<?php echo $joindate?>" disabled type="text" name="joinDate" id="joinDate" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Leave Date</h4>
                    <input value="<?php echo $leavedate?>" disabled type="text" name="leaveDate" id="leaveDate" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Created</h4>
                    <input value="<?php echo $created?>" disabled type="text" name="created" id="created" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Updated</h4>
                    <input value="<?php echo $updated?>" disabled type="text" name="created" id="created" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Bank Name</h4>
                    <input value="<?php echo $bank?>" disabled type="text" name="bank" id="bank" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Bank Account no.</h4>
                    <input value="<?php echo $bankacc?>" disabled type="number" name="bankAcc" id="bankAcc" class="form-control">
                </div>
                <div class="col-md-5 pt-4">
                    <h4>IBAN</h4>
                    <input value="<?php echo $iban?>" disabled type="text" name="iban" id="iban" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>Manager Name</h4>
                    <input value="<?php echo $manager?>" disabled type="text" name="manager" id="manager" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Warnings</h4>
                    <input value="<?php echo $warnings?>" disabled type="number" min="0" name="warnings" id="warnings" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Leaves</h4>
                    <input value="<?php echo $leaves?>" disabled type="number" min="0" name="leaves" id="leaves" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowed Leaves</h4>
                    <input value="<?php echo $allowleave?>" disabled type="number" min="0" name="allowLeaves" id="allowLeaves" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-2 pt-4">
                    <h4>Absents</h4>
                    <input value="<?php echo $absents?>" disabled type="number" min="0" name="absents" id="absents" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Presents</h4>
                    <input value="<?php echo $presents?>" disabled type="number" min="0" name="presents" id="presents" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Days Workings</h4>
                    <input value="<?php echo $daysworking?>" disabled type="number" min="0" name="daysWorking" id="daysWorking" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Loan</h4>
                    <input value="<?php echo $loan?>" disabled name="loan" id="loan" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Loan Amount</h4>
                    <input value="<?php echo $loanamount?>" disabled type="number" min="0" name="loanAmount" id="loanAmount" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-2 pt-4">
                    <h4>Working Hours</h4>
                    <input value="<?php echo $daysworking?>" disabled type="number" min="0" name="workingHours" id="workingHours" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Start Time</h4>
                    <input value="<?php echo $starttime?>" disabled type="text" name="startTime" id="startTime" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>End Time</h4>
                    <input value="<?php echo $endtime?>" disabled type="text" name="endTime" id="endTime" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Shift</h4>
                    <input value="<?php echo $shift?>" disabled name="shift" id="shift" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Status</h4>
                    <input value="<?php echo $status?>" disabled name="status" id="status" class="form-control">
            </div>
        </form>
        <?php
        
        if ($_SESSION['role'] == '1') {
            echo '<form action="edit-employee.php" method="get" class="mt-4">
                    <input type="hidden" name="empID" id="empID" value="'. $id .'">
                    <input type="submit" value="Edit" class="btn btn-warning">
                </form>';
        }

        ?>
        
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script>
        
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>