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
    <link rel="stylesheet" href="css/styles1.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function(){
            
            function printData() {
                $("#table").removeClass();
                var divToPrint = document.getElementById("table");
                newWin= window.open("");
                newWin.document.write('<!DOCTYPE html><html><head>  <title>Print Preview</title>  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >  <style>    table {        font-size: 7px;     }   form { display: none;   }  </style></head><body><div class="table table-bordered text-center">');
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
                filename:'employee-attendance.csv',
                });
            });

            $("#searc").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
   
    <link rel="stylesheet" href="css/styles.css">
    <title>View Attendance</title>
</head>
<body>
<?php include 'nav1.php' ?>
<div id="layoutSidenav">
<?php include 'side-nav.php' ?>
<div id="layoutSidenav_content">
    <div class="container-fluid p-4">
        <h1>View Attendance</h1>
        <h4 id="month">Month: <?php echo $_GET["month"]; ?></h4>
        <h4 id="year">Year: <?php echo $_GET["year"]; ?></h4>

        <div class="row mt-5">
            <button class="btn btn-info m-3 w-25 export-btn">Export to Excel</button>
            <button class="btn btn-danger m-3 w-25" id="browserPrint">Print PDF</button>
                <input type="text" name="searc" id="searc" class="form-control w-25 ms-auto" placeholder="Search...">	
            <div class="col-md-12">
                <table class="table table-responsive table-bordered w-100 text-center" id="table">
                    <thead>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <?php
                        $year = $_GET["year"];   
                        $month = $_GET["month"];
                        $d = 1;
                        $t = cal_days_in_month(CAL_GREGORIAN,$month,$year);
                        $df = date($year."-".$month."-01");
                        $dt = date($year."-".$month."-".$d);
                        $ld = date($year.'-'.$month.'-'.$t);
                        $dt1 = strtotime($dt);
                        $df1 = strtotime($df);
                        $df2 = date("d-m-y", $df1);
                        $dt2 = date("D j", $dt1);
                        $fs = array();

                        while ($d <= $t) {
                            $dt = date($year."-".$month."-".$d);
                            $dt1 = strtotime($dt);
                            $dt2 = date("D", $dt1);
                            $dt3 = date("j", $dt1);
                            if ($dt2 == 'Sun') {
                                $fs[] = $dt3; 
                                $day = "bg-success";
                            }
                            else {
                                $day = "";
                            }
                            echo '<th class="'.$day.'">'.$dt2.'<br>'.$dt3.'</th>';
                            $d++;
                        }
                        ?>
                        <th>Present</th>
                        <th>Absents</th>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM `employees`";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                                $id = $row['employeeID'];
                                $sNo = $row['sNo'];
                                $fname = $row['fname'];
                                $mname = $row['mname'];
                                $lname = $row['lname'];
                                $doj = $row['join_date'];

                                $doj1 = strtotime($doj);
                                $doj2 = date("d-m-y", $doj1);
                                $dojd = date("d", $doj1);
                                $k = 0;

                                echo "<tr class='text-nowrap'>";
                                echo "<td class='id font-weight-bold'>$sNo</td>";
                                echo "<td class='text-left'>". $fname ." ". $mname ." ". $lname ."</td>";
                                // echo $doj2."<br>".++$dojd."<br>";

                                $sql0 = "SELECT * FROM `attendance` WHERE `employeeID`  = '$id' AND `month` = '$month' AND `year` = '$year' ORDER BY `day` ASC";
                                $result0 = $conn->query($sql0);
                                $sql1 = "SELECT * FROM `holidays` WHERE `month` = '$month' AND `year` = '$year' ORDER BY `day` ASC";
                                $result1 = $conn->query($sql1);
                                if ($result1->num_rows > 0) {
                                    $row1 = $result1->fetch_all(MYSQLI_ASSOC);
                                    $holidays[] = $row1[0]['day'];
                                }
                                $absent = $t;
                                $present = 0; 
                                $prevDay = "";

                                if ($result0->num_rows > 0) {
                                    while($row0 = $result0->fetch_assoc()) {
                                        $unit = $row0['unit'];
                                        $day = $row0['day'];
                                        $amonth = $row0['month'];
                                        $ayear = $row0['year'];
                                        $date = $row0['date'];
                                        $timeIn = $row0['time_in'];
                                        $timeOut = $row0['time_out'];
                                        $created_at = $row0['created_at'];
                                        $updated_at = $row0['updated_at'];
                                        $updated_by = $row0['updated_by'];

                                        $timeIn = date('h:i A', strtotime($timeIn));
                                        $timeOut = date('h:i A', strtotime($timeOut));

                                        // check if its first present of month
                                        if ($prevDay == "") {
                                            if ($doj1 > $df1) {
                                                for ($i=0; $i < $dojd && $doj1 > $df1; $i++) { 
                                                    echo "<td class='bg-info font-weight-bold'>DNJ</td>";
                                                }
                                            }else {
                                            for ($i=1; $i < $day ; $i++) { 
                                                // check if day is sunday
                                                if (in_array($i, $fs)) {
                                                    $stmt2 = "SELECT * FROM `attendance` WHERE `employeeID`  = '$id' AND `month` = '$month' AND `year` = '$year' and `day` = $day+1 and `day` = $day-1 ORDER BY `day` ASC";
                                                    $result2 = $conn->query($stmt2);
                                                    if (isset($holidays)) {
                                                        if (in_array($i, $holidays) || $i == current($holidays)) {
                                                            echo "<td class='bg-warning'></td>";
                                                            $absent--;
                                                            $present++;
                                                        }
                                                    }
                                                    elseif ($result2->num_rows > 0) {
                                                        echo "<td></td>";
                                                    }
                                                    else {
                                                        echo "<td class='bg-success'></td>";
                                                        $absent--;
                                                        $present++;
                                                    }
                                                }
                                                else {
                                                    echo "<td></td>";
                                                }
                                            }
                                        }
                                    }
                                        //check if it is not first present of month
                                        elseif ($prevDay != "") {
                                            for ($i=$prevDay+1; $i < $day; $i++) { 
                                                if (in_array($i, $holidays) || $i == current($holidays)) {
                                                    echo "<td class='bg-warning'></td>";
                                                    $absent--;
                                                    $present++;
                                                }
                                                //check if day is sunday
                                                elseif (in_array($i, $fs)) {
                                                    $stmt1 = "SELECT * FROM `attendance` WHERE `employeeID`  = '$id' AND `month` = '$month' AND `year` = '$year' AND `day` = $day+1 OR `day` = $day-2";
                                                    $result2 = $conn->query($stmt1);
                                                    if ($result2->num_rows > 0) {
                                                        echo "<td class='bg-success'></td>";
                                                        $absent--;
                                                        $present++;
                                                    }
                                                    else {
                                                        echo "<td></td>";
                                                    }
                                                }
                                                else {
                                                echo "<td></td>";
                                                }
                                            }
                                        }

                                        $prevDay = $day;
                                        if (isset($holidays)) {                                       
                                            if (in_array($prevDay, $holidays) || $prevDay == current($holidays)) {
                                                echo "<td class='bg-warning'></td>";
                                                $absent--;
                                                $present++;
                                            }
                                        }
                                        elseif (in_array($day, $fs)) { 
                                            echo "<td class='bg-success'></td>";
                                            $absent--;
                                            $present++;
                                        }
                                        else {
                                            echo "<td>$timeIn<br>$timeOut</td>";
                                            $absent--;
                                            $present++;
                                        }
                                        
                                        }

                                        for ($l=$day; $l < $t ; $l++) {
                                            if (isset($holidays)) {
                                                if (in_array($l+1, $holidays) || $l+1 == current($holidays)) {
                                                    echo "<td class='bg-warning'></td>";
                                                    $absent--;
                                                    $present++;
                                                }
                                            }
                                            elseif (in_array($l+1, $fs)) {
                                                $stmt1 = "SELECT * FROM `attendance` WHERE `employeeID`  = '$id' AND `month` = '$month' AND `year` = '$year' AND `day` = $day+1 OR `day` = $day-2";
                                                $result2 = $conn->query($stmt1);
                                                if ($result2->num_rows > 0) {
                                                    echo "<td class='bg-success'></td>";
                                                    // $absent--;
                                                    // $present++;
                                                }
                                                else {
                                                    echo "<td class='bg-success'></td>";
                                                }
                                            }
                                            // check if day is sunday
                                            else {
                                                echo "<td></td>";
                                            }
                                        }
                                        echo "<td id='$id-present'>$present</td><td id='$id-absent'>$absent</td>";
                                    }
                                    // check if not present any day of month
                                    elseif ($result0->num_rows == 0) {
                                        if ($doj1 > $df1) {
                                            for ($i=0; $i < $dojd && $doj1 > $df1; $i++) { 
                                                echo "<td class='bg-info font-weight-bold'>DNJ</td>";
                                            }
                                            for ($k=$i; $k < $t ; $k++) {
                                                if (in_array($k+1, $fs)) {
                                                    echo "<td class='bg-success'></td>";
                                                }
                                                else {
                                                echo "<td></td>";
                                                }
                                        }
                                        }
                                        else  {
                                            for ($k=0; $k < $t ; $k++) { 
                                                if (isset($holidays)){
                                                    if (in_array($k+1, $holidays) || $k+1 == current($holidays)) {
                                                    echo "<td class='bg-warning'></td>";
                                                } }
                                                elseif (in_array($k+1, $fs)) {
                                                    echo "<td class='bg-success'></td>";
                                                }
                                                else {
                                                echo "<td></td>";
                                                }
                                            }
                                        }
                                        //check if day is sunday
                                        
                                        $absent = $t;
                                        $present = 0;  
                                        echo "<td id='$id-present'>$present</td><td id='$id-absent'>$absent</td>";

                                    }
                                }
                                
                            }

                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>