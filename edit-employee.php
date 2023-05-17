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

    $empID = $_GET['empID'];
    $stmt = "SELECT * FROM employees WHERE employeeID = '$empID'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $desig = $row["designation"];
    $fname = $row["fname"];
    $mname = $row["mname"];
    $lname = $row["lname"];
    $dept = $row["department"];
    $desigID = $row["designationID"];
    $deptID = $row["departmentID"];
    $basic = $row["basic_salary"];
    $allow = $row["allowance"];
    $deduc = $row["deduction"];
    $gross = $row["gross_salary"];

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
    <title>Edit Employee</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Edit Designation</h1>
        <form action="edit-emp.php" method="post">
            <div class="row">
                <div class="col-3 pt-4">
                    <h4>Name</h4>
                    <input type="text" disabled name="name" id="name" value="<?php echo $fname . "" . $mname . " " . $lname ?>" class="form-control w-100">
                    <input type="hidden" name="name1" id="name1" value="<?php echo $fname ?>" class="form-control w-50">
                    <input type="hidden" name="empID" id="empID" value="<?php echo $empID ?>" class="form-control w-50">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Basic Salary</h4>
                    <input type="number" min="0" onchange="salary()" name="basicSalary" id="basicSalary" class="form-control" value="<?php echo $basic ?>" required>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Deduction</h4>
                    <input type="number" min="0" onchange="salary()" name="deduction" id="deduction" class="form-control" value="<?php echo $deduc ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-3 pt-4">
                    <h4>Designation</h4>
                    <input type="text" disabled value="<?php echo $desig ?>" class="form-control w-100">
                    <input type="hidden" name="oldDesig" value="<?php echo $desig ?>" class="form-control w-50">
                    <input type="hidden" name="oldDesigID" value="<?php echo $desigID ?>" class="form-control w-50">
                    <h4 class="pt-4">Transfer to</h4>
                    <select class="js-example-basic-single w-100" name="desig">
                        <option></option>
                    <?php
                        require_once('config.php');
                        $stmt1 = "SELECT * FROM designation";
                        $result1 = $conn->query($stmt1);
                        
                        if ($result1->num_rows > 0) {
                            // output data of each row
                            while($row = $result1->fetch_assoc()) {
                                echo '<option value="' . $row['designationID'] .'">' . $row['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowence</h4>
                    <input type="number" min="0" onchange="salary()" name="allowance" id="allowance" class="form-control" value="<?php echo $allow ?>">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Gross Salary</h4>
                    <input type="number" name="grossSalary" id="grossSalary" class="form-control" value="<?php echo $gross ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-3 pt-4">
                    <h4>Department</h4>
                    <input type="text" disabled value="<?php echo $dept ?>" class="form-control w-100">
                    <input type="hidden" name="oldDept" value="<?php echo $dept ?>" class="form-control w-50">
                    <input type="hidden" name="oldDeptID" value="<?php echo $deptID ?>" class="form-control w-50">
                    <h4 class="pt-4">Transfer to</h4>
                    <select class="js-example-basic-single w-100" name="dept">
                        <option></option>
                    <?php
                        require_once('config.php');
                        $stmt1 = "SELECT * FROM department";
                        $result1 = $conn->query($stmt1);
                        
                        if ($result1->num_rows > 0) {
                            // output data of each row
                            while($row = $result1->fetch_assoc()) {
                                echo '<option value="' . $row['departmentID'] .'">' . $row['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>                
            </div>
            <div class="row">
                <div class="col-3 pt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form> 
        </div> 
    </div>
    <script>
        function salary() {
            $basic = document.getElementById('basicSalary').value;
            $allow = document.getElementById('allowance').value;
            $deduc = document.getElementById('deduction').value;
            
            $sum = parseInt($basic) + parseInt($allow) - parseInt($deduc);

            console.log($sum);

            document.getElementById('grossSalary').value = $sum;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>