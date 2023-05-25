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
    <link rel="stylesheet" href="css/styles.css">
    <script>
        $(document).ready(function(){

            function printData() {
                $("#table1").removeClass();
                $("#table2").removeClass();
                var divToPrint1 = document.getElementById("table1");
                var divToPrint2 = document.getElementById("table2");
                newWin= window.open("");
                newWin.document.write('<!DOCTYPE html><html><head>  <title>Print Preview</title>  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >  <style>    table {        font-size: small;    }   form { display: none;   }  </style></head><body><div class="table table-bordered w-100 text-center">');
                newWin.document.write('<h3>Joined</h3>');
                newWin.document.write(divToPrint1.outerHTML);
                newWin.document.write('<br>');
                newWin.document.write('<h3>Left</h3>');
                newWin.document.write(divToPrint2.outerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                newWin.print();
                newWin.close();
                window.location.reload()
            }
            document.querySelector('#browserPrint').addEventListener('click', printData);

            $(".export-btn1").click(function(){  
                $("#table1").tableHTMLExport({
                type:'csv',
                filename:'employees-joined.csv',
                });
            });

            $(".export-btn2").click(function(){  
                $("#table2").tableHTMLExport({
                type:'csv',
                filename:'employees-left.csv',
                });
            });
        });
    </script>
    <title>Join / Leave</title>
</head>
<body>
    <?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Join / Leave</h1>
        <div class="row mt-5">
            <button class="btn btn-info m-3 export-btn1">Export to Excel</button>	
            <button class="btn btn-danger m-3" id="browserPrint">Print PDF</button>	
            <div class="col-md-12">
                <table class="table table-responsive text-nowrap table-bordered w-100 text-center" id="table1">
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

                            if (isset($_GET['joinleave'])) {

                                $month = $_GET['joinleave'];
                            
                                $stmt = "SELECT * FROM employees WHERE MONTH(join_date) = $month";
                                $result = $conn->query($stmt);
                                $i = 0;
        
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
                                        echo "<td>". ++$i ."</td>";
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
                                    echo "</tbody>";
                                    echo "</table>";
                                    echo "<h4 class='mb-5'>Employees Joined: ". $i ."</h4>";
                                }
                                else {
                                    echo "No Employee";
                                }
                            }
        ?>

        
                        <?php

                            if (isset($_GET['joinleave'])) {
                                

                                $month = $_GET['joinleave'];
                            
                                $stmt1 = "SELECT * FROM employees WHERE MONTH(leave_date) = $month";
                                $result1 = $conn->query($stmt1);
                                $j = 0;
        
                                if ($result1->num_rows > 0) {
                                    
                                    echo '<div class="row mt-5">
                                <button class="btn btn-info m-3 export-btn2">Export to Excel</button>	
                                    <table class="table text-nowrap table-bordered w-100 text-center" id="table2">
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
                                        <tbody>';
                                    
                                    while($row = $result1->fetch_assoc()) { 
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
                                        echo "<td>". ++$j ."</td>";
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
                                    echo "</tbody>";
                                    echo "</table>";
                                    echo "<h4 class='mb-5'>Employees Left: ". $j ."</h4>";
                                }
                            }
        ?>
        </div>
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>