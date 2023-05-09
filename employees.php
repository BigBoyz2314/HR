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
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Add Employees</h1>
        <form action="add-employee.php" method="post">
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
                <div class="col-md-3 pt-4">
                    <h4>Gender</h4>
                    <select name="gender" id="gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Date of Birth</h4>
                    <input type="date" name="dob" id="dob" class="form-control" required>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Martital Status</h4>
                    <select name="mStatus" id="mStatus" class="form-control" required>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
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
                    <input type="text" name="sAddress" id="sAddress" class="form-control">
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
                    <input type="number" min="0" onchange="salary()" name="basicSalary" id="basicSalary" class="form-control" value="0" required>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowence</h4>
                    <input type="number" min="0" onchange="salary()" name="allowence" id="allowence" class="form-control" value="0>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Deduction</h4>
                    <input type="number" min="0" onchange="salary()" name="deduction" id="deduction" class="form-control" value="0">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Gross Salary</h4>
                    <input type="number" name="grossSalary" id="grossSalary" class="form-control" value="0" required>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 pt-4">
                    <h4>Designation</h4>
                    <select name="designation" id="designation" class="form-control" required>
                    <?php
                        require_once('config.php');
                        $stmt = "SELECT * FROM designation";
                        $result = $conn->query($stmt);
                        
                        if ($result->num_rows > 0) {
                            // output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['designationID'] .'">' . $row['name'] .'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 pt-4">
                    <h4>Department</h4>
                    <select name="department" id="department" class="form-control" required>
                    <?php
                        require_once('config.php');
                        $stmt = "SELECT * FROM department";
                        $result = $conn->query($stmt);
                        
                        if ($result->num_rows > 0) {
                            // output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['departmentID'] .'">' . $row['name'] .'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>Join Date</h4>
                    <input type="date" name="joinDate" id="joinDate" class="form-control" required>
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
                    <select name="loan" id="loan" class="form-control" required>
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
                <div class="col-md-2 pt-4">
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
                <div class="col-md-2 pt-4">
                    <h4>Shift</h4>
                    <select name="shift" id="shift" class="form-control">
                        <option value="Morning">Morning</option>
                        <option value="Evening">Evening</option>
                        <option value="Night">Night</option>
                    </select>
                </div>
                <div class="col-md-2 pt-4">
                    <h4>Status</h4>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Working">Working</option>
                        <option value="Retired">Retired</option>
                        <option value="Terminated">Terminated</option>
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