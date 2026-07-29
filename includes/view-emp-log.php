<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
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
    <title>View Employees</title>
</head>
<body class="h-screen overflow-hidden">
<?php include 'nav1.php' ?>
<div class="flex h-[calc(100vh-4rem)]">
<?php include 'side-nav.php' ?>
<div class="flex-1 bg-gray-100 overflow-y-auto">
    <div class="max-w-7xl mx-auto p-4 md:p-6 bg-white rounded-lg shadow-lg mt-4 md:mt-8 mb-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">View Employees</h1>
            <p class="text-gray-600">Browse employee records</p>
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

        <div class="overflow-x-auto shadow-md rounded-lg border border-gray-200">
            <table class="min-w-full bg-white text-center text-sm" id="table">
                <thead class="bg-gradient-to-r from-gray-700 to-gray-800 text-white sticky top-0 z-10">
                    <tr>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Sr.</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">First Name</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Middle Name</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Last Name</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">D.O.B</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Designation</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Gender</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Department</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Status</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Joining Date</th>
                        <th class="py-3 px-4 font-semibold border-r border-gray-600 whitespace-nowrap">Basic Salary</th>
                        <th class="py-3 px-4 font-semibold whitespace-nowrap">Updated</th>
                    </tr>
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
                                        $updated = $row['updated_at'];

                                        $row_class = ($i % 2 == 0) ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 hover:bg-gray-100';
                                        echo "<tr class='$row_class transition-colors'>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200 font-medium'>". $i++ ."</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>$fname</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>$mname</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>$lname</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>". date("d M y", strtotime($dob)) ."</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>$desig</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>$gender</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>$dept</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'><span class='px-2 py-1 rounded text-xs font-semibold " . ($status == 'Working' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') . "'>$status</span></td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200'>". date("d M y", strtotime($joindate)) ."</td>";
                                        echo "<td class='py-3 px-4 border-r border-gray-200 font-semibold'>". number_format($basic) ."</td>";
                                        echo "<td class='py-3 px-4 text-xs'>". date("d M y h:i:s a", strtotime($updated)) ."</td>";
                                        echo "</tr>";
        
                                    }
                                }
                                else {
                                    echo "<tr><td colspan='12' class='py-8 text-center text-gray-500'><i class='fas fa-inbox text-4xl mb-2 block'></i>No employee log found</td></tr>";
                                }
                            }

                            ?>
                </tbody>
            </table>
        </div>
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
                newWin.document.write('<!DOCTYPE html><html><head><title>Print Preview - Employee Log</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"><style>body { padding: 20px; } table { font-size: 8px; width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 4px; text-align: center; } th { background-color: #4a5568; color: white; } @media print { @page { size: landscape; margin: 0.5cm; } }</style></head><body><h2 style="text-align: center; margin-bottom: 20px;">Employee Log</h2>');
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
                    filename:'employee-log.csv',
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
</body>
</html>