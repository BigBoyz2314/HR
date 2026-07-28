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

require_once('includes/config.php');

// Validate and sanitize GET parameters
$year = isset($_GET["year"]) ? intval($_GET["year"]) : date('Y');
$month = isset($_GET["month"]) ? intval($_GET["month"]) : date('n');

// Validate year and month ranges
if ($year < 2000 || $year > 2100) {
    $year = date('Y');
}
if ($month < 1 || $month > 12) {
    $month = date('n');
}

// Get month name
$month_name = date('F', mktime(0, 0, 0, $month, 1, $year));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="js/tableHTMLExport.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
<?php include 'includes/nav1.php' ?>
<div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
<?php include 'includes/side-nav.php' ?>
<div class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
    <div class="max-w-7xl mx-auto p-4 md:p-6 bg-white rounded-lg shadow-lg mt-4 md:mt-8 mb-8">
        <!-- Header Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">View Attendance</h1>
            <div class="flex flex-wrap items-center gap-4 text-sm md:text-base">
                <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                    <span class="font-semibold text-gray-700"><?php echo $month_name . ' ' . $year; ?></span>
                </div>
            </div>
        </div>

        <!-- Search and Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="searc" id="searc" class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Search by employee ID or name...">
                <button id="clearSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                    <i class="fas fa-times text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                </button>
            </div>
            <div class="flex flex-wrap gap-3">
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

        <!-- Legend -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Legend:</h3>
            <div class="flex flex-wrap gap-4 text-xs md:text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-green-100 border border-green-300 rounded"></span>
                    <span class="text-gray-700">Present</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-red-200 border border-red-300 rounded"></span>
                    <span class="text-gray-700">Absent</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-red-300 border border-red-400 rounded"></span>
                    <span class="text-gray-700">Absent (Sandwich)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-blue-200 border border-blue-300 rounded"></span>
                    <span class="text-gray-700">Holiday</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-yellow-200 border border-yellow-300 rounded"></span>
                    <span class="text-gray-700">Weekend</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto shadow-md rounded-lg border border-gray-200">
            <table class="min-w-full bg-white text-center text-sm" id="table">
                    <thead class="bg-gradient-to-r from-gray-700 to-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Employee ID</th>
                            <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Name</th>
                            <?php
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

                                    $is_weekend = ($day_of_week == 'Sun' || $day_of_week == 'Sat');
                                    $header_class = $is_weekend ? 'bg-gray-600' : '';
                                    echo '<th class="py-3 px-2 font-semibold border-r border-gray-600 whitespace-nowrap ' . $header_class . '">' . $day_of_week . '<br><span class="text-xs">' . $day_number . '</span></th>';

                                    $current_day->modify('+1 day');
                                }
                                ?>
                            <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Total Present</th>
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">Total Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Fetch holidays once (same for all employees)
                            $holidays = [];
                            $holidays_sql = "SELECT `day` FROM `holidays` WHERE `month` = ? AND `year` = ?";
                            $holidays_stmt = $conn->prepare($holidays_sql);
                            $holidays_stmt->bind_param("ii", $month, $year);
                            $holidays_stmt->execute();
                            $holidays_result = $holidays_stmt->get_result();
                            while ($holiday_row = $holidays_result->fetch_assoc()) {
                                $holidays[] = (int) $holiday_row['day'];
                            }
                            $holidays_stmt->close();

                            // Fetch all attendance records for the month in one query
                            $attendance_data = [];
                            $attendance_sql = "SELECT `employeeID`, `day` FROM `attendance1` WHERE `month` = ? AND `year` = ? ORDER BY `employeeID`, `day` ASC";
                            $attendance_stmt = $conn->prepare($attendance_sql);
                            $attendance_stmt->bind_param("ii", $month, $year);
                            $attendance_stmt->execute();
                            $attendance_result = $attendance_stmt->get_result();
                            while ($attendance_row = $attendance_result->fetch_assoc()) {
                                $emp_id = $attendance_row['employeeID'];
                                if (!isset($attendance_data[$emp_id])) {
                                    $attendance_data[$emp_id] = [];
                                }
                                $attendance_data[$emp_id][] = (int) $attendance_row['day'];
                            }
                            $attendance_stmt->close();

                            // Fetch all employees
                            $sql = "SELECT `sNo`, `fname`, `mname`, `lname`, `join_date` FROM `employees` ORDER BY `sNo` ASC";
                            $result = $conn->query($sql);
                            
                            // Initialize totals
                            $total_present = 0;
                            $total_absent = 0;
                            
                            if ($result->num_rows > 0) {
                                $row_index = 0;
                                while ($row = $result->fetch_assoc()) {
                                    $employee_id = $row['sNo'];
                                    $fname = $row['fname'];
                                    $mname = $row['mname'];
                                    $lname = $row['lname'];
                                    $join_date = $row['join_date'];

                                    $row_class = ($row_index % 2 == 0) ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-gray-100';
                                    echo "<tr class='$row_class transition-colors'>";
                                    echo "<td class='py-3 px-4 border-r border-gray-200 font-medium'>$employee_id</td>";
                                    echo "<td class='py-3 px-4 border-r border-gray-200 text-left'>$fname $mname $lname</td>";

                                    // Get attendance days for this employee from pre-fetched data
                                    $employee_days = isset($attendance_data[$employee_id]) ? $attendance_data[$employee_id] : [];

                                    $absent_count = 0;
                                    $present_count = 0;

                                    // Build day status array for the month
                                    $day_status = [];
                                    $current_day = clone $first_day;
                                    while ($current_day <= $last_day) {
                                        $day_number = $current_day->format('j');
                                        $day_of_week = $current_day->format('D');
                                        $is_sunday = ($day_of_week == 'Sun');
                                        $is_holiday = in_array($day_number, $holidays);
                                        if (in_array($day_number, $employee_days)) {
                                            $day_status[$day_number] = 'present';
                                        } elseif ($is_holiday) {
                                            $day_status[$day_number] = 'holiday';
                                        } elseif ($is_sunday) {
                                            $day_status[$day_number] = 'weekend';
                                        } else {
                                            $day_status[$day_number] = 'absent';
                                        }
                                        $current_day->modify('+1 day');
                                    }
                                    // Apply sandwich policy: scan for absent blocks
                                    $days = array_keys($day_status);
                                    for ($i = 0; $i < count($days); $i++) {
                                        if ($day_status[$days[$i]] === 'absent') {
                                            $j = $i + 1;
                                            // Find next absent
                                            while ($j < count($days) && ($day_status[$days[$j]] === 'holiday' || $day_status[$days[$j]] === 'weekend')) {
                                                $j++;
                                            }
                                            if ($j < count($days) && $day_status[$days[$j]] === 'absent') {
                                                // Mark all in between as absent (sandwich)
                                                for ($k = $i + 1; $k < $j; $k++) {
                                                    $day_status[$days[$k]] = 'absent_sandwich';
                                                }
                                            }
                                        }
                                    }
                                    // Render table cells
                                    $current_day = clone $first_day;
                                    while ($current_day <= $last_day) {
                                        $day_number = $current_day->format('j');
                                        $status = $day_status[$day_number];
                                        if ($status === 'present') {
                                            echo "<td class='py-2 px-2 border-r border-gray-200 bg-green-100 text-green-800 font-semibold'>P</td>";
                                            $present_count++;
                                        } elseif ($status === 'holiday') {
                                            echo "<td class='py-2 px-2 border-r border-gray-200 bg-blue-200 text-blue-900 font-semibold'>H</td>";
                                        } elseif ($status === 'weekend') {
                                            echo "<td class='py-2 px-2 border-r border-gray-200 bg-yellow-200 text-yellow-900 font-semibold'>W</td>";
                                        } elseif ($status === 'absent_sandwich') {
                                            echo "<td class='py-2 px-2 border-r border-gray-200 bg-red-300 text-red-900 font-semibold' title='Absent (Sandwich)'>A*</td>";
                                            $absent_count++;
                                        } else { // 'absent'
                                            echo "<td class='py-2 px-2 border-r border-gray-200 bg-red-200 text-red-900 font-semibold'>A</td>";
                                            $absent_count++;
                                        }
                                        $current_day->modify('+1 day');
                                    }

                                    echo "<td class='py-3 px-4 border-r border-gray-200 font-bold text-green-700'>$present_count</td>";
                                    echo "<td class='py-3 px-4 font-bold text-red-700'>$absent_count</td>";
                                    echo "</tr>";
                                    
                                    // Accumulate totals
                                    $total_present += $present_count;
                                    $total_absent += $absent_count;
                                    
                                    $row_index++;
                                }
                                
                                // Add totals row
                                echo "<tr class='bg-gray-200 font-bold border-t-2 border-gray-400'>";
                                echo "<td colspan='2' class='py-3 px-4 border-r border-gray-300 text-left'><strong>Total</strong></td>";
                                
                                // Empty cells for day columns
                                $current_day = clone $first_day;
                                while ($current_day <= $last_day) {
                                    echo "<td class='py-2 px-2 border-r border-gray-300'></td>";
                                    $current_day->modify('+1 day');
                                }
                                
                                echo "<td class='py-3 px-4 border-r border-gray-300 font-bold text-green-700'>$total_present</td>";
                                echo "<td class='py-3 px-4 font-bold text-red-700'>$total_absent</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='" . ($days_in_month + 4) . "' class='py-8 text-center text-gray-500'><i class='fas fa-inbox text-4xl mb-2 block'></i>No employees found</td></tr>";
                            }
                            ?>
                    </tbody>
                </table>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script>
        $(document).ready(function(){
            
            function printData() {
                var divToPrint = document.getElementById("table");
                var newWin = window.open("", "Print-Window");
                newWin.document.write('<!DOCTYPE html><html><head><title>Print Preview - Attendance Report</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"><style>body { padding: 20px; } table { font-size: 8px; width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 4px; text-align: center; } th { background-color: #4a5568; color: white; } .bg-green-100 { background-color: #c6f6d5; } .bg-red-200 { background-color: #fed7d7; } .bg-red-300 { background-color: #fc8181; } .bg-blue-200 { background-color: #bee3f8; } .bg-yellow-200 { background-color: #faf089; } @media print { @page { size: landscape; margin: 0.5cm; } }</style></head><body><h2 style="text-align: center; margin-bottom: 20px;">Attendance Report - <?php echo $month_name . " " . $year; ?></h2>');
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
                    filename:'employee-attendance-<?php echo $month_name . "-" . $year; ?>.csv',
                });
            });

            // Search functionality
            $("#searc").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                var hasResults = false;
                
                $("#table tbody tr").filter(function() {
                    var matches = $(this).text().toLowerCase().indexOf(value) > -1;
                    $(this).toggle(matches);
                    if (matches) hasResults = true;
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
</body>
</html>
