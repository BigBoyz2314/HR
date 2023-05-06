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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
    <title>View Employees By Filter</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>View Employee</h1>
        <div class="row">
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Name</h4>
                    <select class="js-example-basic-single w-50" name="id">
                        <option></option>
                        <?php
                        require_once('config.php');
                        $stmt = "SELECT employeeID, fname, mname, lname FROM employees";
                        $result = $conn->query($stmt);
                        
                        if ($result->num_rows > 0) {
                            // output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['employeeID'] .'">' . $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-success mt-3 d-block">Submit</button>
                </form>  
            </div>
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Status</h4>
                    <select class="js-example-basic-single w-50" name="status">
                        <option></option>
                        <option value="Working">Working</option>
                        <option value="Retired">Retired</option>
                        <option value="Terminated">Terminated</option>
                    </select>
                    <button type="submit" class="btn btn-success d-block mt-3">Submit</button>
                </form>  
            </div>
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Gender</h4>
                    <select class="js-example-basic-single w-50" name="gender">
                        <option></option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <button type="submit" class="btn btn-success d-block mt-3">Submit</button>
                </form>  
            </div>
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Department</h4>
                    <select class="js-example-basic-single w-50" name="dept">
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
                    <button type="submit" class="btn btn-success d-block mt-3">Submit</button>
                </form>  
            </div>
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Age</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <h6>From</h6>
                            <select class="js-example-basic-single w-100" name="to">
                                <option></option>
                                <?php
                                $i = 2023;
                                $j = 0;
                                while ($i >= 1970) {
                                    echo '<option value="'. $i .'">'. $j .'</option>';
                                    $i--;
                                    $j++;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <h6>To</h6>
                            <option></option>
                            <select class="js-example-basic-single w-100" name="from">
                                <?php
                                $i = 1970;
                                $j = 53;
                                while ($i <= 2023) {
                                    echo '<option value="'. $i .'">'. $j .'</option>';
                                    $i++;
                                    $j--;
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success d-block mt-3">Submit</button>
                </form>  
            </div>
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Designation</h4>
                    <select class="js-example-basic-single w-50" name="desig">
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
                    <button type="submit" class="btn btn-success d-block mt-3">Submit</button>
                </form>  
            </div>
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>By Designation</h4>
                    <select class="js-example-basic-single w-50" name="desig">
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
                    <button type="submit" class="btn btn-success d-block mt-3">Submit</button>
                </form>  
            </div>
        </div> 
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>