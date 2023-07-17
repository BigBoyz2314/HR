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

            // Get the employee IDs from the table.
            var employeeIDs = [];
            $(".id").each(function() {
                employeeIDs.push($(this).text());
            });

            var geturl = window.location.href;

            var url = new URL(geturl);

            var month = url.searchParams.get("month");
            var year = url.searchParams.get("year");

            // Iterate for each employee ID.
            for (var i = 0; i < employeeIDs.length; i++) {

                // Create the AJAX request.
                var request = $.ajax({
                    url: "get-attend.php",
                    type: "POST",
                    data: {
                        employeeID: employeeIDs[i],
                        month: month,
                        year: year
                    }, 
                    success: function(data) {
                        // Add the name to the table.
                        var name = JSON.parse(data)[0];

                        console.log(name);
                        
                    }
                });

                // Asynchronously execute the request.
                request.fail(function() {
                    // Do something if the request fails.
                });
            }

        });
    </script>
   
    <link rel="stylesheet" href="css/styles.css">
    <title>View Attendance</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid py-5">
        <h1>View Attendance</h1>
        <h4 id="month">Month: <?php echo $_GET["month"]; ?></h4>
        <h4 id="year">Year: <?php echo $_GET["year"]; ?></h4>

        <div class="row mt-5">
            <button class="btn btn-info m-3 export-btn">Export to Excel</button>
            <button class="btn btn-danger m-3" id="browserPrint">Print PDF</button>
                <input type="text" name="search" id="search" class="form-control w-25 ml-auto" placeholder="Search...">	
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
                        $dt = date($year."-".$month."-".$d);
                        $ld = date($year.'-'.$month.'-'.$t);
                        $dt1 = strtotime($dt);
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
                                $fname = $row['fname'];
                                $mname = $row['mname'];
                                $lname = $row['lname'];
                                $doj = $row['join_date'];

                                $k = 0;

                                echo "<tr class='text-nowrap'>";
                                echo "<td class='id'>$id</td>";
                                echo "<td>". $fname ." ". $mname ." ". $lname ."</td>";

                                $sql1 = "SELECT * FROM `attendance` WHERE `employeeID`  = '$id' AND `month` = '$month' AND `year` = '$year'";
                                $result1 = $conn->query($sql1);
                                $absent = $t;
                                $present = 0; 
                                $prevDay = "";  

                                if ($result1->num_rows > 0) {
                                    while($row1 = $result1->fetch_assoc()) {
                                        $unit = $row1['unit'];
                                        $day = $row1['day'];
                                        $amonth = $row1['month'];
                                        $ayear = $row1['year'];
                                        $date = $row1['date'];
                                        $timeIn = $row1['time_in'];
                                        $timeOut = $row1['time_out'];
                                        $created_at = $row1['created_at'];
                                        $updated_at = $row1['updated_at'];
                                        $updated_by = $row1['updated_by'];

                                        $timeIn = date('h:i A', strtotime($timeIn));
                                        $timeOut = date('h:i A', strtotime($timeOut));

                                        if ($prevDay == "") {
                                            for ($i=1; $i < $day ; $i++) { 
                                                if (in_array($i, $fs)) {
                                                    echo "<td class='bg-success'></td>";
                                                    $absent--;
                                                    $present++;
                                                }
                                                else {
                                                echo "<td></td>";
                                                }
                                            }
                                        }
                                        elseif ($prevDay != "") {
                                            for ($i=$prevDay+1; $i < $day; $i++) { 
                                                if (in_array($i, $fs)) {
                                                    echo "<td class='bg-success'></td>";
                                                    $absent--;
                                                    $present++;
                                                }
                                                else {
                                                echo "<td></td>";
                                                }
                                            }
                                        }
                                        $prevDay = $day;
                                            
                                        echo "<td>$timeIn<br>$timeOut</td>";
                                        
                                        $absent--;
                                        $present++;

                                        }
                                        for ($l=$day; $l < $t ; $l++) {
                                            if (in_array($l+1, $fs)) {
                                                echo "<td class='bg-success'></td>";
                                            }
                                            else {
                                            echo "<td></td>";
                                            }
                                        }
                                        echo "<td id='$id-present'>$present</td><td id='$id-absent'>$absent</td>";
                                    }
                                    elseif ($result1->num_rows == 0) {
                                        for ($k=0; $k < $t ; $k++) { 
                                            if (in_array($k+1, $fs)) {
                                                echo "<td class='bg-success'></td>";
                                            }
                                            else {
                                            echo "<td></td>";
                                            }
                                        }
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>