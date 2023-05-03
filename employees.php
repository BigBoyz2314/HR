<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <title>Add Employees</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item dropdown active">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                    Employees
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <a class="dropdown-item text-white-50" href="employees.php">Add Employees</a>
                        <a class="dropdown-item text-white-50" href="view-employees.php">View Employees</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="department.php">Departments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="designation.php">Designations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Log out</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid p-5">
        <h1>Add Employees</h1>
        <form action="add-employess.php" method="post">
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>First Name</h4>
                    <input type="text" name="fName" id="fName" class="form-control" required>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Middle Name</h4>
                    <input type="text" name="mName" id="mName" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Last Name</h4>
                    <input type="text" name="lName" id="lName" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Date of Birth</h4>
                    <input type="date" name="dob" id="dob" class="form-control" required>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Martital Status</h4>
                    <select name="mstatus" id="mstatus" class="form-control">
                        <option value="0">Single</option>
                        <option value="1">Married</option>
                    </select>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Children</h4>
                    <input type="number" min="0" name="children" id="children" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Spouse Name</h4>
                    <input type="text" name="spouseName" id="spouseName" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>CNIC</h4>
                    <input type="number" name="cnic" id="cnic" class="form-control" required>
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Passport no.</h4>
                    <input type="text" name="passport" id="passport" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 pt-4">
                    <h4>Primary Address</h4>
                    <input type="text" name="pAddress" id="pAddress" class="form-control" required>
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Secondary Address</h4>
                    <input type="text" name="sAddress" id="sAddress" class="form-control" required>
                </div>
                <div class="col-md-4 pt-3">
                    <h4 class="d-inline-block">Current Address</h4>
                    <p class="d-inline-block font-italic">Same as Primary Address</p>
                    <input type="checkbox" name="same" id="same" onclick="check()">
                    <input type="text" name="cAddress" id="cAddress" class="form-control" value="" required>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>Primary Number</h4>
                    <input type="number" name="pNumber" id="pNumber" class="form-control" required>
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Secondary Number</h4>
                    <input type="number" name="sNumber" id="sNumber" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Basic Salary</h4>
                    <input type="number" min="0" onchange="salary()" name="basicSalary" id="basicSalary" class="form-control" value="0">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowence</h4>
                    <input type="number" min="0" onchange="salary()" name="allowence" id="allowence" class="form-control" value="0">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Deduction</h4>
                    <input type="number" min="0" onchange="salary()" name="deduction" id="deduction" class="form-control" value="0">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Gross Salary</h4>
                    <input type="number" disabled name="grossSalary" id="grossSalary" class="form-control" value="0">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>Designation</h4>
                    <input type="text" name="designation" id="designation" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Department</h4>
                    <input type="text" name="department" id="department" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>Join Date</h4>
                    <input type="date" name="joinDate" id="joinDate" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Leave Date</h4>
                    <input type="date" name="leaveDate" id="leaveDate" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Bank Name</h4>
                    <input type="text" name="bank" id="bank" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Bank Account no.</h4>
                    <input type="number" name="bankAcc" id="bankAcc" class="form-control">
                </div>
                <div class="col-md-5 pt-4">
                    <h4>IBAN</h4>
                    <input type="text" name="iban" id="iban" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>Manager Name</h4>
                    <input type="text" name="manager" id="manager" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Warnings</h4>
                    <input type="number" min="0" name="warnings" id="warnings" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Leaves</h4>
                    <input type="number" min="0" name="leaves" id="leaves" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowed Leaves</h4>
                    <input type="number" min="0" name="allowLeaves" id="allowLeaves" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-2 pt-4">
                    <h4>Absents</h4>
                    <input type="number" min="0" name="absents" id="absents" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Presents</h4>
                    <input type="number" min="0" name="presents" id="presents" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Days Workings</h4>
                    <input type="number" min="0" name="daysWorking" id="daysWorking" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Loan</h4>
                    <select name="loan" id="loan" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Loan Amount</h4>
                    <input type="number" min="0" name="loanAmount" id="loanAmount" class="form-control">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>Working Hours</h4>
                    <input type="number" min="0" name="workingHours" id="workingHours" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Start Time</h4>
                    <input type="time" name="startTime" id="startTime" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>End Time</h4>
                    <input type="time" name="endTime" id="endTime" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Shift</h4>
                    <select name="shift" id="shift" class="form-control">
                        <option value="0">Morning</option>
                        <option value="1">Evening</option>
                        <option value="2">Night</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center pt-5">
                <div class="col-md-1">
                    <input type="submit" value="Submit" class="btn btn-success">
                </div>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script>
        
        function salary() {
            $basic = document.getElementById('basicSalary').value;
            $allow = document.getElementById('allowence').value;
            $deduc = document.getElementById('deduction').value;
            
            $sum = parseInt($basic) + parseInt($allow) - parseInt($deduc);

            console.log($sum);

            document.getElementById('grossSalary').value = $sum;
        }


        function check() {
            
            if (document.getElementById('same').checked) {

                console.log('checked');

                $pAddress = document.getElementById('pAddress').value;

                document.getElementById('cAddress').value = $pAddress;
            }
            else {
                document.getElementById('cAddress').value = '';
            }
        }


    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>