<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Metrics Queries
$totalEmployees = 0;
$maleCount = 0;
$femaleCount = 0;
$totalDepartments = 0;
$totalDesignations = 0;
$totalSalaryThisMonth = 0;

$qEmp = $conn->query("SELECT 
    COUNT(employeeID) AS total,
    SUM(CASE WHEN LOWER(TRIM(COALESCE(gender, 'Male'))) = 'female' THEN 1 ELSE 0 END) AS female_count,
    SUM(CASE WHEN LOWER(TRIM(COALESCE(gender, 'Male'))) != 'female' THEN 1 ELSE 0 END) AS male_count
FROM employees");
if ($qEmp && $rEmp = $qEmp->fetch_assoc()) {
    $totalEmployees = intval($rEmp['total'] ?? 0);
    $femaleCount = intval($rEmp['female_count'] ?? 0);
    $maleCount = intval($rEmp['male_count'] ?? 0);
}

$qDept = $conn->query("SELECT COUNT(departmentID) AS total FROM department");
if ($qDept && $rDept = $qDept->fetch_assoc()) $totalDepartments = $rDept['total'];

$qDesig = $conn->query("SELECT COUNT(designationID) AS total FROM designation");
if ($qDesig && $rDesig = $qDesig->fetch_assoc()) $totalDesignations = $rDesig['total'];

$currentMonthNum = date('n');
$qSal = $conn->query("SELECT SUM(payable) AS total FROM salary1 WHERE month = '$currentMonthNum'");
if ($qSal && $rSal = $qSal->fetch_assoc()) $totalSalaryThisMonth = $rSal['total'] ?? 0;

// Salary by Gender Metrics
$maleSalaryTotal = 0;
$femaleSalaryTotal = 0;
$maleSalaryAvg = 0;
$femaleSalaryAvg = 0;

$salGenderQuery = "SELECT 
    CASE WHEN LOWER(TRIM(COALESCE(gender, 'Male'))) = 'female' THEN 'Female' ELSE 'Male' END AS g_group,
    SUM(basic_salary) AS tot_sal,
    AVG(basic_salary) AS avg_sal
FROM employees 
WHERE status = 'Active' OR status IS NULL OR status = ''
GROUP BY g_group";
$salGenRes = $conn->query($salGenderQuery);
if ($salGenRes) {
    while ($sgRow = $salGenRes->fetch_assoc()) {
        if ($sgRow['g_group'] === 'Female') {
            $femaleSalaryTotal = (float)$sgRow['tot_sal'];
            $femaleSalaryAvg = round((float)$sgRow['avg_sal']);
        } else {
            $maleSalaryTotal = (float)$sgRow['tot_sal'];
            $maleSalaryAvg = round((float)$sgRow['avg_sal']);
        }
    }
}

// Attendance by Gender Metrics (Current Month Only)
$maleAttTotal = 0;
$femaleAttTotal = 0;
$currAttMonth = date('n');
$currAttYear = date('Y');
$attGenQuery = "SELECT 
    CASE WHEN LOWER(TRIM(COALESCE(e.gender, 'Male'))) = 'female' THEN 'Female' ELSE 'Male' END AS g_group,
    COUNT(a.id) AS total_presents
FROM attendance1 a
JOIN employees e ON (a.employeeID = e.employeeID OR a.employeeID = e.sNo)
WHERE COALESCE(a.updated_by, '') != 'manual_absent'
  AND (
      (a.month = $currAttMonth AND a.year = $currAttYear) 
      OR (MONTH(a.date) = $currAttMonth AND YEAR(a.date) = $currAttYear)
  )
GROUP BY g_group";
$attGenRes = $conn->query($attGenQuery);
if ($attGenRes) {
    while ($agRow = $attGenRes->fetch_assoc()) {
        if ($agRow['g_group'] === 'Female') {
            $femaleAttTotal = (int)$agRow['total_presents'];
        } else {
            $maleAttTotal = (int)$agRow['total_presents'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Dashboard - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Header Navigation -->
    <?php include("includes/nav1.php") ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include("includes/side-nav.php") ?>

        <!-- Main Dashboard View -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-8">
            
            <!-- Compact Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Overview</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time headcount, payroll expenses, overtime trends, and leave analytics.</p>
                </div>
                <div class="flex items-center space-x-2.5">
                    <a href="employees.php" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-sm transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-user-plus text-[11px]"></i>
                        <span>+ Add Employee</span>
                    </a>
                    <a href="upload-attendance.php" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition border border-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-file-csv text-slate-500 text-[11px]"></i>
                        <span>Import CSV</span>
                    </a>
                </div>
            </div>

            <!-- Stats Metric Grid (Compact Height) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Card 1: Total Employees -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold shrink-0">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="text-[11px] text-slate-500 font-bold leading-tight">Total Registered Headcount</div>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 flex items-center space-x-1 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Active Staff</span>
                            </span>
                        </div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight mt-1"><?php echo number_format($totalEmployees); ?></div>
                    </div>

                    <!-- Male & Female Breakup with Person Icons -->
                    <div class="mt-2.5 pt-2 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <div class="flex items-center space-x-1.5 bg-blue-50 rounded-lg px-2 py-1 border border-blue-100">
                            <div class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px] shrink-0 font-bold">
                                <i class="fa-solid fa-person"></i>
                            </div>
                            <div class="flex items-baseline space-x-1 min-w-0">
                                <span class="text-[10px] uppercase font-bold text-blue-700">Male:</span>
                                <span class="text-xs font-black text-black font-mono"><?php echo number_format($maleCount); ?></span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 bg-pink-50 rounded-lg px-2 py-1 border border-pink-100">
                            <div class="w-5 h-5 rounded-md bg-pink-500 text-white flex items-center justify-center text-[10px] shrink-0 font-bold">
                                <i class="fa-solid fa-person-dress"></i>
                            </div>
                            <div class="flex items-baseline space-x-1 min-w-0">
                                <span class="text-[10px] uppercase font-bold text-pink-700">Female:</span>
                                <span class="text-xs font-black text-black font-mono"><?php echo number_format($femaleCount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Departments -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm font-bold shrink-0">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div class="text-[11px] text-slate-500 font-bold leading-tight">Active Departments</div>
                            </div>
                            <a href="department.php" class="text-[11px] font-bold text-cyan-600 hover:underline shrink-0">Manage &rarr;</a>
                        </div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight mt-1"><?php echo number_format($totalDepartments); ?></div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center text-[11px] text-slate-500">
                        <i class="fa-solid fa-layer-group text-cyan-500 mr-1.5 text-xs"></i>
                        <span>Organizational Units</span>
                    </div>
                </div>

                <!-- Card 3: Designations -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold shrink-0">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <div class="text-[11px] text-slate-500 font-bold leading-tight">Job Roles & Titles</div>
                            </div>
                            <a href="designation.php" class="text-[11px] font-bold text-amber-600 hover:underline shrink-0">Manage &rarr;</a>
                        </div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight mt-1"><?php echo number_format($totalDesignations); ?></div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center text-[11px] text-slate-500">
                        <i class="fa-solid fa-id-badge text-amber-500 mr-1.5 text-xs"></i>
                        <span>Defined Staff Roles</span>
                    </div>
                </div>

                <!-- Card 4: Monthly Salary -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold shrink-0">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </div>
                                <div class="text-[11px] text-slate-500 font-bold leading-tight">Monthly Payroll Expense</div>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 shrink-0"><?php echo date('F'); ?></span>
                        </div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight mt-1">PKR <?php echo number_format(round($totalSalaryThisMonth)); ?></div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center text-[11px] text-slate-500">
                        <i class="fa-solid fa-calendar-check text-emerald-500 mr-1.5 text-xs"></i>
                        <span><?php echo date('F Y'); ?> Payroll</span>
                    </div>
                </div>

            </div>

            <!-- Analytics Charts Grid - Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Chart 1: Salary Trend -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-chart-line text-indigo-600"></i>
                            <h3 class="text-base font-bold text-slate-900">Monthly Salary Expense Trend</h3>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Payroll History</span>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chart1"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Department Strength -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-chart-bar text-cyan-600"></i>
                            <h3 class="text-base font-bold text-slate-900">Headcount by Department</h3>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Staff Distribution</span>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chart2"></canvas>
                    </div>
                </div>

            </div>

            <!-- Analytics Charts Grid - Row 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Chart 3: Salary by Gender -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-money-bill-transfer text-indigo-600"></i>
                            <h3 class="text-base font-bold text-slate-900">Salary by Gender</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 flex items-center space-x-1">
                                <i class="fa-solid fa-person text-xs"></i>
                                <span>Male: PKR <?php echo number_format($maleSalaryTotal); ?></span>
                            </span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-100 flex items-center space-x-1">
                                <i class="fa-solid fa-person-dress text-xs"></i>
                                <span>Female: PKR <?php echo number_format($femaleSalaryTotal); ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chart3"></canvas>
                    </div>
                </div>

                <!-- Chart 4: Attendance by Gender -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-clipboard-user text-emerald-600"></i>
                            <h3 class="text-base font-bold text-slate-900">Attendance by Gender</h3>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><?php echo date('F Y'); ?></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 flex items-center space-x-1">
                                <i class="fa-solid fa-person text-xs"></i>
                                <span>Male: <?php echo number_format($maleAttTotal); ?> Days</span>
                            </span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-100 flex items-center space-x-1">
                                <i class="fa-solid fa-person-dress text-xs"></i>
                                <span>Female: <?php echo number_format($femaleAttTotal); ?> Days</span>
                            </span>
                        </div>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chart4"></canvas>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- Chart.js Libraries & Script Initialization -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script>
        window.onload = function () {
            Chart.defaults.global.defaultFontFamily = 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
            Chart.defaults.global.defaultFontColor = '#64748b';
            
            <?php
            // Chart 1: Salary Expense
            $query = "SELECT `month`, SUM(payable) AS total_payable FROM salary1 GROUP BY `month` ORDER BY `month` ASC";
            $result = $conn->query($query);
            $salary = array();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $monthName = date("F", mktime(0, 0, 0, $row['month'], 1));
                    $salary[] = array("y" => (float)$row['total_payable'], "label" => $monthName);
                }
            }
            ?>
            var salaryData = <?php echo json_encode($salary); ?>;

            var ctx1 = document.getElementById('chart1').getContext('2d');
            var areaChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: salaryData.map(item => item.label),
                    datasets: [{
                        label: 'Total Payable (PKR)',
                        data: salaryData.map(item => item.y),
                        lineTension: 0.4,
                        backgroundColor: "rgba(99, 102, 241, 0.15)",
                        borderColor: "#4f46e5",
                        pointRadius: 4,
                        pointBackgroundColor: "#4f46e5",
                        pointBorderColor: "#ffffff",
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ gridLines: { display: false } }],
                        yAxes: [{ gridLines: { color: "#f1f5f9" } }]
                    }
                }
            });

            <?php
            // Chart 2: Department Strength
            $query1 = "SELECT `name` FROM department ORDER BY name ASC";
            $result1 = $conn->query($query1);
            $dept = array();
            if ($result1) {
                while ($row = $result1->fetch_assoc()) {
                    $deptName = $conn->real_escape_string($row['name']);
                    $query2 = "SELECT COUNT(employeeId) AS current_Strength FROM employees WHERE department = '$deptName'";
                    $result2 = $conn->query($query2);
                    if ($result2 && $row2 = $result2->fetch_assoc()) {
                        $dept[] = array("y" => (int)$row2['current_Strength'], "label" => $row['name']);
                    }
                }
            }
            ?>
            var deptData = <?php echo json_encode($dept); ?>;

            var ctx2 = document.getElementById('chart2').getContext('2d');
            var barChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: deptData.map(item => item.label),
                    datasets: [{
                        label: 'Current Strength',
                        data: deptData.map(item => item.y),
                        backgroundColor: "#06b6d4",
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ gridLines: { display: false } }],
                        yAxes: [{ gridLines: { color: "#f1f5f9" }, ticks: { beginAtZero: true } }]
                    }
                }
            });

            // Chart 3: Salary by Gender (Bar Chart)
            var ctx3 = document.getElementById('chart3').getContext('2d');
            var salaryGenderChart = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: ['Male Staff', 'Female Staff'],
                    datasets: [
                        {
                            label: 'Total Salary (PKR)',
                            data: [<?php echo $maleSalaryTotal; ?>, <?php echo $femaleSalaryTotal; ?>],
                            backgroundColor: ['rgba(59, 130, 246, 0.85)', 'rgba(236, 72, 153, 0.85)'],
                            borderColor: ['#2563eb', '#db2777'],
                            borderWidth: 1.5
                        },
                        {
                            label: 'Average Salary (PKR)',
                            data: [<?php echo $maleSalaryAvg; ?>, <?php echo $femaleSalaryAvg; ?>],
                            backgroundColor: ['rgba(99, 102, 241, 0.45)', 'rgba(244, 114, 182, 0.45)'],
                            borderColor: ['#4f46e5', '#f472b6'],
                            borderWidth: 1.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: true, position: 'top' },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': PKR ' + Number(tooltipItem.yLabel).toLocaleString();
                            }
                        }
                    },
                    scales: {
                        xAxes: [{ gridLines: { display: false } }],
                        yAxes: [{
                            gridLines: { color: "#f1f5f9" },
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return 'PKR ' + Number(value).toLocaleString();
                                }
                            }
                        }]
                    }
                }
            });

            // Chart 4: Attendance by Gender (Doughnut Chart)
            var ctx4 = document.getElementById('chart4').getContext('2d');
            var attGenderChart = new Chart(ctx4, {
                type: 'doughnut',
                data: {
                    labels: ['Male Attendance', 'Female Attendance'],
                    datasets: [{
                        data: [<?php echo $maleAttTotal; ?>, <?php echo $femaleAttTotal; ?>],
                        backgroundColor: ['#3b82f6', '#ec4899'],
                        hoverBackgroundColor: ['#2563eb', '#db2777'],
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: true, position: 'right' },
                    cutoutPercentage: 65,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce((acc, curr) => acc + curr, 0);
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = total > 0 ? ((currentValue / total) * 100).toFixed(1) : 0;
                                return data.labels[tooltipItem.index] + ': ' + Number(currentValue).toLocaleString() + ' Days (' + percentage + '%)';
                            }
                        }
                    }
                }
            });
        };
    </script>
</body>
</html>
