<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="js/tableHTMLExport.js"></script>
    <script>
        $(document).ready(function(){
            $(".export-btn").click(function(){  
                $("#table").tableHTMLExport({
                type:'csv',
                filename:'employees.csv',
                });
            });

            $("#search").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
   
    <link rel="stylesheet" href="css/styles.css">
    <title>View Employees</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid py-5">
        <h1>View Employees</h1>

        <div class="row mt-5">
            <button class="btn btn-info m-3 export-btn">Export to Excel</button>
            <input type="text" name="search" id="search" class="form-control w-25 ml-auto" placeholder="Search...">	
                <table class="table table-bordered w-100 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>D.O.B</th> 
                        <th>Designation</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Martial Status</th>
                        <th>Status</th>
                        <th>Joining Date</th>
                        <th>Spouse Name</th>
                        <th>Basic Salary</th>
                        <th>Allowance</th>
                        <th>Deduction</th>
                        <th>Gross Salary</th>
                        <th></th>
                    </thead>
                    <tbody class="">
                        <?php

                            if (isset($_GET['id'])) {

                                $eid = $_GET['id'];
                            
                                $stmt = "SELECT * FROM employees WHERE employeeID = $eid";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['status'])) {
                                
                                $sta = $_GET['status'];
                            
                                $stmt = "SELECT * FROM employees WHERE status = '$sta'";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $status = $row['status'];
                                        $joindate = $row['join_date'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['gender'])) {
                                
                                $gen = $_GET['gender'];
                            
                                $stmt = "SELECT * FROM employees WHERE gender = '$gen'";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $status = $row['status'];
                                        $joindate = $row['join_date'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['dept'])) {
                                
                                $deptid = $_GET['dept'];
                            
                                $stmt = "SELECT * FROM employees WHERE departmentID = '$deptid'";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['from']) || isset($_GET['to'])) {
                                
                                $from = $_GET['from'];
                                $to = $_GET['to'];
                            
                                $stmt = "SELECT * FROM employees WHERE yob BETWEEN $from AND $to";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['desig'])) {
                                
                                $deptid = $_GET['desig'];
                            
                                $stmt = "SELECT * FROM employees WHERE designationID = '$deptid'";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['moj'])) {
                                
                                $moj = $_GET['moj'];
                            
                                $stmt = "SELECT * FROM employees WHERE join_month = '$moj'";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            elseif (isset($_GET['salfrom']) || isset($_GET['salto'])) {
                                
                                $salfrom = $_GET['salfrom'];
                                $salto = $_GET['salto'];
                            
                                $stmt = "SELECT * FROM employees WHERE gross_salary BETWEEN $salfrom AND $salto";
                                $result = $conn->query($stmt);
                                $i = 1;
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $dob = $row['dob'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $mstatus = $row['martital_status'];
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $spouse = $row['spouse_name'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$mstatus</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$spouse</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                        // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
                            else{
                            
                            $stmt = "SELECT * FROM employees";
                            $result = $conn->query($stmt);
                            $i = 1;
    
                            if ($result->num_rows > 0) {
                                // output data of each row
                                
                                while($row = $result->fetch_assoc()) {
                                    $id = $row['employeeID'];
                                    $fname = $row['fname'];
                                    $mname = $row['mname'];
                                    $lname = $row['lname'];
                                    $dob = $row['dob'];
                                    $desig = $row['designation'];
                                    $dept = $row['department'];
                                    $gender = $row['gender'];
                                    $mstatus = $row['martital_status'];
                                    $joindate = $row['join_date'];
                                    $status = $row['status'];
                                    $children = $row['children'];
                                    $spouse = $row['spouse_name'];
                                    $basic = $row['basic_salary'];
                                    $allowance = $row['allowance'];
                                    $deduction = $row['deduction'];
                                    $gross = $row['gross_salary'];

                                    echo "<tr>";
                                    echo "<td>". $i++ ."</td>";
                                    echo "<td>$fname</td>";
                                    echo "<td>$mname</td>";
                                    echo "<td>$lname</td>";
                                    echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                    echo "<td>$desig</td>";
                                    echo "<td>$gender</td>";
                                    echo "<td>$dept</td>";
                                    echo "<td>$mstatus</td>";
                                    echo "<td>$status</td>";
                                    echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                    echo "<td>$spouse</td>";
                                    echo "<td>$basic</td>";
                                    echo "<td>$allowance</td>";
                                    echo "<td>$deduction</td>";
                                    echo "<td>$gross</td>";
                                    echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                                    // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                    echo "</tr>";

    
                                }
                              }
                            }
                            //   if ($_REQUEST) {
                            //     $delID = $_GET['id'];
                            //     $delName = $_GET['desigName'];
                                  
                            //         echo '<script type="text/javascript"> ';  
                            //         echo '  if (confirm("Are you sure you want to DELETE '. "'$delName'" .'?")) {';  
                            //         echo '    window.location.href = "del-designation.php?delName='. $delName .'&delID='. $delID .'";';  
                            //         echo '  }';
                            //         echo '</script>'; 
                            //   } 
                        ?>
                    </tbody>
            </table>
            </div>
        </div>
    </div>
  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>