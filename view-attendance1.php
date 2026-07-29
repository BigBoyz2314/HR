<?php
require_once('includes/config.php');
init_hr_session();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
if ($_SESSION['role'] != '1') {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="js/tableHTMLExport.js"></script>
    <script>
        $(document).ready(function(){
            
            function printData() {
                var divToPrint = document.getElementById("table");
                var newWin = window.open("", "Print-Window");
                newWin.document.write('<!DOCTYPE html><html><head><title>Print Preview - Attendance Report</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"><style>body { padding: 20px; } table { font-size: 8px; width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 4px; text-align: center; } th { background-color: #4a5568; color: white; } @media print { @page { size: landscape; margin: 0.5cm; } }</style></head><body><h2 style="text-align: center; margin-bottom: 20px;">Attendance Report</h2>');
                newWin.document.write(divToPrint.outerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                setTimeout(function() {
                    newWin.print();
                    newWin.close();
                }, 250);
            }
            
            document.querySelector('#browserPrint').addEventListener('click', printData);

            $(".export-btn").click(function(){  
                $("#table").tableHTMLExport({
                    type:'csv',
                    filename:'employee-attendance.csv',
                });
            });

            // Search functionality
            $("#searc").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                
                $("#table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
                
                // Show/hide clear button
                if (value.length > 0) {
                    $("#clearSearch").removeClass("hidden");
                } else {
                    $("#clearSearch").addClass("hidden");
                }
            });

            // Clear search
            $("#clearSearch").on("click", function() {
                $("#searc").val("");
                $("#searc").trigger("keyup");
            });
        });
    </script>
   
    <title>View Attendance</title>
</head>
<body class="h-screen overflow-hidden">
<?php include 'includes/nav1.php' ?>
<div class="flex h-[calc(100vh-4rem)]">
<?php include 'includes/side-nav.php' ?>
<div class="flex-1 bg-gray-100 overflow-y-auto">
    <div class="max-w-7xl mx-auto p-4 md:p-6 bg-white rounded-lg shadow-lg mt-4 md:mt-8 mb-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">View Attendance</h1>
            <p class="text-gray-600">View attendance records for employees</p>
        </div>
        <!-- Search and Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex flex-wrap items-center gap-4 text-sm md:text-base">
                <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                    <span class="font-semibold text-gray-700"><?php echo date("F", mktime(0, 0, 0, $_GET["month"], 1)) . ' ' . $_GET["year"]; ?></span>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="searc" id="searc" class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Search by employee ID or name...">
                    <button id="clearSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                        <i class="fas fa-times text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                    </button>
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg transition shadow-md hover:shadow-lg flex items-center gap-2 export-btn">
                    <i class="fas fa-file-excel"></i>
                    <span>Export to Excel</span>
                </button>
                <button class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-5 rounded-lg transition shadow-md hover:shadow-lg flex items-center gap-2" id="browserPrint">
                    <i class="fas fa-print"></i>
                    <span>Print PDF</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto shadow-md rounded-lg border border-gray-200">
            <table class="min-w-full bg-white text-center text-sm" id="table">
                <thead class="bg-gradient-to-r from-gray-700 to-gray-800 text-white sticky top-0 z-10">
                    <tr>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Employee ID</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Name</th>
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
                            }
                            $header_class = ($dt2 == 'Sun' || $dt2 == 'Sat') ? 'bg-gray-600 ' : '';
                            echo '<th class="py-3 px-2 font-semibold border-r border-gray-600 whitespace-nowrap '.$header_class.'">'.$dt2.'<br><span class="text-xs">'.$dt3.'</span></th>';
                            $d++;
                        }
                        ?>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Total Present</th>
                        <th class="py-3 px-4 font-semibold whitespace-nowrap">Total Absent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Optimized: Fetch holidays once (same for all employees)
                        $holidays = [];
                        $holidays_sql = "SELECT `day` FROM `holidays` WHERE `month` = ? AND `year` = ? ORDER BY `day` ASC";
                        $holidays_stmt = $conn->prepare($holidays_sql);
                        $holidays_stmt->bind_param("ii", $month, $year);
                        $holidays_stmt->execute();
                        $holidays_result = $holidays_stmt->get_result();
                        while($holiday_row = $holidays_result->fetch_assoc()) {
                            $holidays[] = (int)$holiday_row['day'];
                        }
                        $holidays_stmt->close();

                        // Optimized: Fetch all attendance records in one query
                        $attendance_data = [];
                        $attendance_sql = "SELECT `employeeID`, `day` FROM `attendance` WHERE `month` = ? AND `year` = ? ORDER BY `employeeID`, `day` ASC";
                        $attendance_stmt = $conn->prepare($attendance_sql);
                        $attendance_stmt->bind_param("ii", $month, $year);
                        $attendance_stmt->execute();
                        $attendance_result = $attendance_stmt->get_result();
                        while($attendance_row = $attendance_result->fetch_assoc()) {
                            $emp_id = $attendance_row['employeeID'];
                            if (!isset($attendance_data[$emp_id])) {
                                $attendance_data[$emp_id] = [];
                            }
                            $attendance_data[$emp_id][] = (int)$attendance_row['day'];
                        }
                        $attendance_stmt->close();

                        // Fetch all employees
                        $sql = "SELECT `employeeID`, `fname`, `mname`, `lname`, `join_date` FROM `employees`";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                                $id = $row['employeeID'];
                                $fname = $row['fname'];
                                $mname = $row['mname'];
                                $lname = $row['lname'];
                                $doj = $row['join_date'];

                                $doj1 = strtotime($doj);
                                $doj2 = date("d-m-y", $doj1);
                                $dojd = date("d", $doj1);
                                $k = 0;

                                echo "<tr class='text-nowrap'>";
                                echo "<td class='id font-weight-bold'>$id</td>";
                                echo "<td class='text-left'>". $fname ." ". $mname ." ". $lname ."</td>";

                                // Get attendance days from pre-fetched data
                                $employee_days = isset($attendance_data[$id]) ? $attendance_data[$id] : [];
                                
                                $present = 0;
                                $absent = 0;
                                
                                for ($d = 1; $d <= $t; $d++) {
                                    $dt = date($year."-".$month."-".$d);
                                    $dt1 = strtotime($dt);
                                    $dt2 = date("D", $dt1);
                                    $is_present = in_array($d, $employee_days);
                                    $is_holiday = in_array($d, $holidays);
                                    $is_sunday = ($dt2 == 'Sun');
                                    
                                    if ($is_present) {
                                        echo "<td class='py-2 px-2 border-r border-gray-200 bg-green-100 text-green-800 font-semibold'>P</td>";
                                        $present++;
                                    } elseif ($is_holiday) {
                                        echo "<td class='py-2 px-2 border-r border-gray-200 bg-blue-200 text-blue-900 font-semibold'>H</td>";
                                    } elseif ($is_sunday) {
                                        echo "<td class='py-2 px-2 border-r border-gray-200 bg-yellow-200 text-yellow-900 font-semibold'>W</td>";
                                    } else {
                                        echo "<td class='py-2 px-2 border-r border-gray-200 bg-red-200 text-red-900 font-semibold'>A</td>";
                                        $absent++;
                                    }
                                }
                                
                                echo "<td class='py-3 px-4 border-r border-gray-200 font-bold text-green-700'>$present</td>";
                                echo "<td class='py-3 px-4 font-bold text-red-700'>$absent</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='" . ($t + 4) . "' class='py-8 text-center text-gray-500'><i class='fas fa-inbox text-4xl mb-2 block'></i>No employees found</td></tr>";
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