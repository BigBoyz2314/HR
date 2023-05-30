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
                newWin.document.write('<!DOCTYPE html><html><head>  <title>Print Preview</title>  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >  <style>    table {        font-size: small;    }   form { display: none;   }  </style></head><body><div class="table table-bordered w-100 text-center">');
                newWin.document.write(divToPrint.outerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                newWin.print();
                newWin.close();
                window.location.reload()
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
        });

        $gross = $("#gross").text();
        $ndays = 365/12;
        const d = new Date();
        $day = d.getDate();
        $("#day").text = $day;
        console.log($gross);

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
                        <th>Allowance</th>
                        <th>Deduction</th>
                        <th>Absent Deduc.</th>
                        <th>Gross Salary</th>
                        <th>Payable</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                        <!-- <th></th> -->
                    </thead>
                    <tbody>
                        <?php

                            if (isset($_GET['year'])) {
                            
                                $stmt = "SELECT * FROM employees";
                                $result = $conn->query($stmt);
                                $i = 1;
                                $totaldays = cal_days_in_month(CAL_GREGORIAN,$_GET['month'],$_GET['year']);
                                $today = date("d M y");
                                $thismonth = date("n");
                                $thisyear = date("Y");
                                $date = date("d");
                                $firstDate = date("Ymd", strtotime($today));
                                $secondDates = $_GET['year'] .'-0'. $_GET['month'];
                                $secondDate = date("Ym", strtotime($secondDates));
        
                                if ($result->num_rows > 0) {
                                    // output data of each row
                                    
                                    while($row = $result->fetch_assoc()) { 
                                        $id = $row['employeeID'];
                                        $fname = $row['fname'];
                                        $mname = $row['mname'];
                                        $lname = $row['lname'];
                                        $desig = $row['designation'];
                                        $dept = $row['department'];
                                        $gender = $row['gender'];
                                        $basic = $row['basic_salary'];
                                        $allowance = $row['allowance'];
                                        $deduction = $row['deduction'];
                                        $gross = $row['gross_salary'];                                      
                                        
                                        $day = 30;
                                        if ($_GET['month'] == 2) {
                                            $day = 28;
                                        }
                                        $dpay = $gross / $day;
                                        if ($thismonth == $_GET['month']) {
                                            $abs = $day - $date;
                                            if ($abs < 1) {
                                                $abs = 0;
                                            }
                                            $absent = $abs * $dpay;
                                        }
                                        else {
                                            $absent = 0;
                                        }

                                        if ($secondDate < $firstDate && $thismonth == $_GET['month']) {
                                            $pay = $dpay * $date;
                                            if ($date == 31) {
                                                $pay = $dpay * $day;
                                                $date = 30;
                                            }
                                        }
                                        elseif ($thismonth >= $_GET['month']) {
                                            $pay = $dpay * $day;
                                            $date = $totaldays;
                                        }
                                        else {
                                            $pay = 0;
                                        }
                                        echo "<tr>";
                                        echo "<td>". $i++ ."</td>";
                                        echo "<td>$fname</td>";
                                        echo "<td>$mname</td>";
                                        echo "<td>$lname</td>";
                                        echo "<td>$desig</td>";
                                        echo "<td>$dept</td>"; 
                                        echo "<td id='day'>". $date ."</td>";
                                        echo "<td>$basic</td>";
                                        echo "<td>$allowance</td>";
                                        echo "<td>$deduction</td>";
                                        echo "<td>". number_format($absent) ."</td>";
                                        echo "<td id='gross'>$gross</td>";
                                        echo "<td id='pay'>". number_format($pay) ."</td>";
                                        echo "<td id=''>0</td>";
                                        echo "<td id=''>0</td>";
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>