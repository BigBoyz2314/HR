<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the user has admin role (role 1), if not redirect to index.php
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
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
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
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <?php
                                $year = $_GET["year"];
                                $month = $_GET["month"];
                                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                                $first_day = new DateTime("$year-$month-01");
                                $last_day = new DateTime("$year-$month-$days_in_month");

                                $current_day = clone $first_day;
                                $fs = []; // Array to store Sundays

                                while ($current_day <= $last_day) {
                                    $day_of_week = $current_day->format('D');
                                    $day_number = $current_day->format('j');

                                    if ($day_of_week == 'Sun') {
                                        $fs[] = $day_number;
                                    }

                                    echo '<th>' . $day_of_week . '<br>' . $day_number . '</th>';

                                    $current_day->modify('+1 day');
                                }
                                ?>
                                <th>Total Present</th>
                                <th>Total Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM `employees`";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $employee_id = $row['sNo'];
                                    $fname = $row['fname'];
                                    $mname = $row['mname'];
                                    $lname = $row['lname'];
                                    $join_date = $row['join_date'];

                                    echo "<tr>";
                                    echo "<td>$employee_id</td>";
                                    echo "<td>$fname $mname $lname</td>";

                                    $attendance_sql = "SELECT * FROM `attendance1` WHERE `employeeID` = '$employee_id' AND `month` = '$month' AND `year` = '$year' ORDER BY `day` ASC";
                                    $attendance_result = $conn->query($attendance_sql);

                                    // Fetch holidays for the current month and year
                                    $holidays_sql = "SELECT * FROM `holidays` WHERE `month` = '$month' AND `year` = '$year'";
                                    $holidays_result = $conn->query($holidays_sql);

                                    // Prepare an array to store holiday dates
                                    $holidays = [];
                                    if ($holidays_result->num_rows > 0) {
                                        while ($holiday_row = $holidays_result->fetch_assoc()) {
                                            $holidays[] = (int) $holiday_row['day'];
                                        }
                                    }

                                    $absent_count = 0;
                                    $present_count = 0;

                                    // Initialize an array to track days for this employee
                                    $employee_days = [];

                                    // Collect all days this employee is present
                                    if ($attendance_result->num_rows > 0) {
                                        while ($attendance_row = $attendance_result->fetch_assoc()) {
                                            $employee_days[] = $attendance_row['day'];
                                        }
                                    }

                                    // Calculate total present and absent days for the employee
                                    $current_day = clone $first_day;
                                    // Inside the loop where you iterate over each employee's attendance
                                    while ($current_day <= $last_day) {
                                        $day_number = $current_day->format('j');
                                        $day_of_week = $current_day->format('D');

                                        // Check if the current day is a Sunday
                                        $is_sunday = ($day_of_week == 'Sun');

                                        // Check if the day is a holiday
                                        $is_holiday = in_array($day_number, $holidays);

                                        // Check if previous Saturday and next Monday are absent
                                        $prev_saturday = $current_day->modify('-1 day')->format('j');
                                        $next_monday = $current_day->modify('+2 days')->format('j');
                                        $current_day->modify('-1 day'); // Restore current day to original

                                        $prev_saturday_absent = !in_array($prev_saturday, $employee_days);
                                        $next_monday_absent = !in_array($next_monday, $employee_days);
                                        
                                        if (in_array($day_number, $employee_days)) {
                                            echo "<td>Present</td>";
                                            $present_count++;
                                        } elseif ($is_holiday && ($prev_saturday_absent || $next_monday_absent)) {
                                            echo "<td class='bg-info'>Holiday (including Sunday)</td>";
                                            $present_count++;
                                        } elseif ($is_holiday) {
                                            echo "<td class='bg-info'>Holiday</td>";
                                            $present_count++;
                                        } elseif ($is_sunday) {
                                            echo "<td class='bg-success'>Present (Sunday)</td>";
                                            $present_count++;
                                        } elseif ($prev_saturday_absent && $next_monday_absent && $is_sunday) {
                                            echo "<td class='bg-warning'>Absent (Including Sunday)</td>";
                                            $absent_count++;
                                        } else {
                                            echo "<td class='bg-warning'>Absent</td>";
                                            $absent_count++;
                                        }

                                        $current_day->modify('+1 day');
                                    }

                                    echo "<td>$present_count</td>";
                                    echo "<td>$absent_count</td>";
                                    echo "</tr>";
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
</body>
</html>
