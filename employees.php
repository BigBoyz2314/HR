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
require_once('config.php');

$sql = "SELECT employeeID FROM employees ORDER BY employeeID DESC LIMIT 1";
$result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $eid = $row['employeeID'] + 1;
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
        <div class="row justify-content-between">
            <div class="col-md-3">
                <h1>Add Employees</h1>
            </div>
            <div class="col-md-2">
                <h5>Employee ID</h5>
                <input type="text" value="<?php echo $eid ?>" class="form-control text-center" disabled>
            </div>
        </div>
        <form action="add-employee.php" method="post">
            <div class="row">
                <div class="col-md-2 pt-4">
                    <h5>First Name</h5>
                    <input type="text" name="fName" id="fName" class="form-control" required>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Middle Name</h5>
                    <input type="text" name="mName" id="mName" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Last Name</h5>
                    <input type="text" name="lName" id="lName" class="form-control" required>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Gender</h5>
                    <select name="gender" id="gender" class="form-control w-100" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Date of Birth</h5>
                    <input type="date" name="dob" id="dob" class="form-control" required>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Martital Status</h5>
                    <select name="mStatus" id="mStatus" class="form-control" required>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 pt-4">
                    <h5>Children</h5>
                    <input type="number" min="0" name="children" id="children" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Spouse Name</h5>
                    <input type="text" name="spouseName" id="spouseName" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>CNIC</h5>
                    <input type="number" name="cnic" id="cnic" class="form-control" required>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Passport</h5>
                    <input type="text" name="passport" id="passport" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Disability</h5>
                    <select type="text" name="disability" id="disability" class="form-control">
                        <option value="none">None</option>
                        <option value="deaf">Deaf</option>
                        <option value="dumb">Dumb</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <div class="col-md-2 pt-4">
                    <h5 class="d-inline-block p-0">Photo</h5>
                    <p class="d-inline-block ml-4 p-0 mb-0">Max 2Mb</p>
                    <input type="file" name="photo" id="photo" class="btn p-0">
                </div>
            </div>
            <div class="row">
            </div>
            <div class="row">
                <div class="col-md-4 pt-4">
                    <h5>Primary Address</h5>
                    <input type="text" name="pAddress" id="pAddress" class="form-control" required>
                </div>
                <div class="col-md-4 pt-4">
                    <h5>Secondary Address</h5>
                    <input type="text" name="sAddress" id="sAddress" class="form-control">
                </div>
                <div class="col-md-4 pt-3">
                    <h5 class="d-inline-block">Current Address</h5>
                    <p class="d-inline-block font-italic">Same as Primary Address</p>
                    <input type="checkbox" name="same" id="same" onclick="check()">
                    <input type="text" name="cAddress" id="cAddress" class="form-control" value="" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 pt-4">
                    <h5>Contact # 1</h5>
                    <input type="number" name="pNumber" id="pNumber" class="form-control" required>
                </div>
                <div class="col-md-4 pt-4">
                    <h5>Contact # 2</h5>
                    <input type="number" name="sNumber" id="sNumber" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h5>Basic Salary</h5>
                    <input type="number" min="0" onchange="salary()" name="basicSalary" id="basicSalary" class="form-control" value="0" required>
                </div>
                <div class="col-md-3 pt-4">
                    <h5>Allowence</h5>
                    <input type="number" min="0" onchange="salary()" name="allowence" id="allowence" class="form-control" value="0">
                </div>
                <div class="col-md-3 pt-4">
                    <h5>Deduction</h5>
                    <input type="number" min="0" onchange="salary()" name="deduction" id="deduction" class="form-control" value="0">
                </div>
                <div class="col-md-3 pt-4">
                    <h5>Gross Salary</h5>
                    <input type="number" name="grossSalary" id="grossSalary" class="form-control" value="0" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h5>Bank Name</h5>
                    <input type="text" name="bank" id="bank" class="form-control">
                </div>
                <div class="col-md-4 pt-4">
                    <h5>Bank Account no.</h5>
                    <input type="number" name="bankAcc" id="bankAcc" class="form-control">
                </div>
                <div class="col-md-5 pt-4">
                    <h5>IBAN</h5>
                    <input type="text" name="iban" id="iban" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 pt-4">
                    <h5>Designation</h5>
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
                <div class="col-md-2 pt-4">
                    <h5>Department</h5>
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
                <div class="col-md-2 pt-4">
                    <h5>Join Date</h5>
                    <input type="date" name="joinDate" id="joinDate" class="form-control" required>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Leave Date</h5>
                    <input type="date" name="leaveDate" id="leaveDate" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Shift</h5>
                    <select name="shift" id="shift" class="form-control">
                        <option value="Morning">Morning</option>
                        <option value="Evening">Evening</option>
                        <option value="Night">Night</option>
                    </select>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Status</h5>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Working">Working</option>
                        <option value="Retired">Retired</option>
                        <option value="Terminated">Terminated</option>
                    </select>
                </div>
            </div>
            <div class="row">
            </div>
            <div class="row">
                <div class="col-md-2 pt-4">
                    <h5>Line Manager</h5>
                    <input type="text" name="manager" id="manager" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Warnings</h5>
                    <input type="number" min="0" name="warnings" id="warnings" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Leaves</h5>
                    <input type="number" min="0" name="leaves" id="leaves" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Allowed Leaves</h5>
                    <input type="number" min="0" name="allowLeaves" id="allowLeaves" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Absents</h5>
                    <input type="number" min="0" name="absents" id="absents" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Presents</h5>
                    <input type="number" min="0" name="presents" id="presents" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h5>Days Workings</h5>
                    <input type="number" min="0" name="daysWorking" id="daysWorking" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Loan</h5>
                    <select name="loan" id="loan" class="form-control" required>
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Loan Amount</h5>
                    <input type="number" min="0" name="loanAmount" id="loanAmount" class="form-control">
                </div>
                <div class="col-md-1 pt-4">
                    <h5>Hours</h5>
                    <input type="number" min="0" name="workingHours" id="workingHours" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Start Time</h5>
                    <input type="time" name="startTime" id="startTime" class="form-control">
                </div>
                <div class="col-md-2 pt-4">
                    <h5>End Time</h5>
                    <input type="time" name="endTime" id="endTime" class="form-control">
                </div>
            </div>
            <div class="row">
            </div>
            <div class="row pt-5">
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