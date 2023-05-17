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
            
            function downloadPDFWithBrowserPrint() {
            window.print();
            }
            document.querySelector('#browserPrint').addEventListener('click', downloadPDFWithBrowserPrint);

            $(".export-btn").click(function(){  
                $("#table").tableHTMLExport({
                type:'csv',
                filename:'employee-log.csv',
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
            <button class="btn btn-danger m-3" id="browserPrint">Print PDF</button>
            <input type="text" name="search" id="search" class="form-control w-25 ml-auto" placeholder="Search...">	
                <table class="table text-nowrap table-bordered w-100 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>D.O.B</th> 
                        <th>Designation</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Joining Date</th>
                        <th>Basic Salary</th>
                        <th>Allowance</th>
                        <th>Deduction</th>
                        <th>Gross Salary</th>
                        <th>Updated</th>
                    </thead>
                    <tbody>
                        <?php

                            if (isset($_GET['id'])) {

                                $eid = $_GET['id'];
                            
                                $stmt = "SELECT * FROM employees_log WHERE employeeID = $eid";
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
                                        $joindate = $row['join_date'];
                                        $status = $row['status'];
                                        $children = $row['children'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];
                                        $updated = $row['updated_at'];

                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$gender</td>";
                                        echo "<td>$dept</td>";
                                        echo "<td>$status</td>";
                                        echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>$gross</td>";
                                        echo "<td>". date("d M y h:i:s a", strtotime($updated)) ."</td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "No Employee";
                                }
                            }

                            ?>
                    </tbody>
            </table>
            </div>
        </div>
    </div>
  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>