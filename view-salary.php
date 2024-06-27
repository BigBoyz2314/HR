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
            
            function printData() {
                $("#table").removeClass();
                var divToPrint = document.getElementById("table");
                newWin= window.open("");
                newWin.document.write('<!DOCTYPE html><html><head>  <title>Print Preview</title>  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >  <style>    table {        font-size: small;    }   form { display: none;   }  @page { size: landscape; }  </style></head><body><div class="table table-bordered w-100 text-center">');
                newWin.document.write(divToPrint.outerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                newWin.print();
                newWin.close();
                window.location.reload();
            }
            document.querySelector('#browserPrint').addEventListener('click', printData);

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

            setTimeout(function() {
            $(".alert-dismissible").alert('close');
            }, 3000);
        });

    </script>
   
    <link rel="stylesheet" href="css/styles.css">
    <title>View Salary</title>
</head>
<?php include 'nav1.php' ?>
<div id="layoutSidenav">
<?php include 'side-nav.php' ?>
<div id="layoutSidenav_content">
    <div class="container-fluid p-4">
<?php 
    if (isset($_GET['action'])) {
    if (($_GET['action']) == 'paid') {
        echo '<div class="alert alert-success alert-dismissible fade show position-fixed paid" role="alert">
                <strong>Salary Paid!</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>';
        } 
    }
?>
        <h1>View Salary</h1>
        <h4>Month: <?php echo date("F", mktime(0, 0, 0, $_GET['month'], 10)); ?></h4>
        <h4>Year: <?php echo $_GET['year']; ?></h4>

        <div class="row mt-5">
            <button class="btn btn-info m-3 export-btn">Export to Excel</button>
            <button class="btn btn-danger m-3" id="browserPrint">Print PDF</button>
            <input type="text" name="search" id="search" class="form-control m-3 w-25 ml-auto" placeholder="Search...">	
            <div class="col-md-12">
                <table class="table table-responsive text-nowrap table-bordered w-100 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Days Payable</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Deduction</th>
                        <th>Absent Deduc.</th>
                        <th>Gross Salary</th>
                        <th>Payable</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                        <th>Edit</th>
                    </thead>
                    <tbody>
                        <?php

                            if (isset($_GET['year'])) {
                                $year = $_GET['year'];
                                $month = $_GET['month'];
                                
                            
                                $stmt = "SELECT * FROM salary WHERE `year` = $year AND `month` = $month";
                                $result = $conn->query($stmt);
                                $i = 1;

                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['id'];
                                        $eid = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $paydays = $row['pay_days'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];                                    
                                        $payable = $row['payable'];                             
                                        $absent = $row['absent'];                                 
                                        $paid = $row['paid'];
                                        $remaining = $row['remaining'];                                    


                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$dept</td>"; 
                                        echo "<td id='day'>$paydays</td>";
                                        echo "<td class='text-right'>". number_format($basic) ."</td>";
                                        echo "<td class='text-right'>". number_format($allowance) ."</td>";
                                        echo "<td class='text-right'>". number_format($deduction) ."</td>";
                                        echo "<td class='text-right'>". number_format($absent) ."</td>";
                                        echo "<td id='gross' class='text-right'>". number_format($gross) ."</td>";
                                        echo "<td id='pay' class='text-right'>". number_format($payable) ."</td>";
                                        echo "<td class='text-right'>". number_format($paid) ."</td>";
                                        echo "<td class='text-right'>". number_format($remaining) ."</td>";
                                        echo '<td><form action="edit-salary.php" method="get"><input type="hidden" name="id" value="'. $id .'"> <input class="btn btn-success" type="submit" value="Edit"></form></td>';
                                        echo "</tr>";
        
                                    }
                                $stmt1 = "SELECT SUM(basic_salary) AS `basic` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt2 = "SELECT SUM(allowance) AS `allowance` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt4 = "SELECT SUM(deduction) AS `deduction` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt6 = "SELECT SUM(absent) AS `absent` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt7 = "SELECT SUM(gross_salary) AS `gross` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt8 = "SELECT SUM(payable) AS `payable` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt9 = "SELECT SUM(paid) AS `paid` FROM salary WHERE `year` = $year AND `month` = $month";
                                $stmt0 = "SELECT SUM(remaining) AS `remaining` FROM salary WHERE `year` = $year AND `month` = $month";
                                $result1 = $conn->query($stmt1);
                                $row1 = $result1->fetch_assoc();

                                $result2 = $conn->query($stmt2);
                                $row2 = $result2->fetch_assoc();

                                $result4 = $conn->query($stmt4);
                                $row4 = $result4->fetch_assoc();

                                $result6 = $conn->query($stmt6);
                                $row6 = $result6->fetch_assoc();

                                $result7 = $conn->query($stmt7);
                                $row7 = $result7->fetch_assoc();

                                $result8 = $conn->query($stmt8);
                                $row8 = $result8->fetch_assoc();

                                $result9 = $conn->query($stmt9);
                                $row9 = $result9->fetch_assoc();

                                $result0 = $conn->query($stmt0);
                                $row0 = $result0->fetch_assoc();

                                echo "<tr id='total'>";
                                    echo "<td colspan='7' class='font-weight-bold'>Total</td>";
                                    echo "<td>". number_format($row1['basic']) ."</td>";
                                    echo "<td>". number_format($row2['allowance']) ."</td>";
                                    echo "<td>". number_format($row4['deduction']) ."</td>";
                                    echo "<td>". number_format($row6['absent']) ."</td>";
                                    echo "<td>". number_format($row7['gross']) ."</td>";
                                    echo "<td>". number_format($row8['payable']) ."</td>";
                                    echo "<td>". number_format($row9['paid']) ."</td>";
                                    echo "<td>". number_format($row0['remaining']) ."</td>";
                                echo "</tr>";
                                }
                                else {
                                    echo '<div class="alert alert-danger" role="alert">
                                    <strong>Salary Not Generated!</strong>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>';
                                }
                            }

                            ?>
                    </tbody>
            </table>
            </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>