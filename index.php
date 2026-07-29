<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Metrics Queries
$totalEmployees = 0;
$totalDepartments = 0;
$totalDesignations = 0;
$totalSalaryThisMonth = 0;

$qEmp = $conn->query("SELECT COUNT(employeeId) AS total FROM employees");
if ($qEmp && $rEmp = $qEmp->fetch_assoc()) $totalEmployees = $rEmp['total'];

$qDept = $conn->query("SELECT COUNT(departmentID) AS total FROM department");
if ($qDept && $rDept = $qDept->fetch_assoc()) $totalDepartments = $rDept['total'];

$qDesig = $conn->query("SELECT COUNT(designationID) AS total FROM designation");
if ($qDesig && $rDesig = $qDesig->fetch_assoc()) $totalDesignations = $rDesig['total'];

$currentMonthNum = date('n');
$qSal = $conn->query("SELECT SUM(payable) AS total FROM salary1 WHERE month = '$currentMonthNum'");
if ($qSal && $rSal = $qSal->fetch_assoc()) $totalSalaryThisMonth = $rSal['total'] ?? 0;
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
            
            <!-- Dashboard Welcome Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-800">
                <div class="space-y-2 z-10">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        <i class="fa-solid fa-chart-line mr-1.5 text-xs"></i>
                        Executive Analytics Dashboard
                    </span>
                    <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight">Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?> 👋</h1>
                    <p class="text-slate-300 text-xs md:text-sm max-w-2xl">Real-time headcount, payroll expenses, overtime trends, and leave analytics across Footprint HR.</p>
                </div>
                
                <div class="flex items-center space-x-3 z-10">
                    <a href="employees.php" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Add Employee</span>
                    </a>
                    <a href="upload-attendance.php" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition border border-slate-700 flex items-center space-x-2">
                        <i class="fa-solid fa-file-csv"></i>
                        <span>Import CSV</span>
                    </a>
                </div>

                <!-- Ambient Glow Effect -->
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            </div>

            <!-- Stats Metric Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1: Total Employees -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">Active Staff</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight"><?php echo number_format($totalEmployees); ?></div>
                    <div class="text-xs text-slate-500 font-medium mt-1">Total Registered Headcount</div>
                </div>

                <!-- Card 2: Departments -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <a href="department.php" class="text-xs font-semibold text-cyan-600 hover:underline">Manage &rarr;</a>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight"><?php echo number_format($totalDepartments); ?></div>
                    <div class="text-xs text-slate-500 font-medium mt-1">Active Departments</div>
                </div>

                <!-- Card 3: Designations -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <a href="designation.php" class="text-xs font-semibold text-amber-600 hover:underline">Manage &rarr;</a>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight"><?php echo number_format($totalDesignations); ?></div>
                    <div class="text-xs text-slate-500 font-medium mt-1">Job Roles & Titles</div>
                </div>

                <!-- Card 4: Monthly Salary -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700"><?php echo date('F'); ?></span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight">PKR <?php echo number_format(round($totalSalaryThisMonth)); ?></div>
                    <div class="text-xs text-slate-500 font-medium mt-1">Monthly Payroll Expense</div>
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
                
                <!-- Chart 3: Overtime Hours Trend -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-business-time text-amber-500"></i>
                            <h3 class="text-base font-bold text-slate-900">Monthly Overtime Hours & Payout</h3>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Extra Hours</span>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chart3"></canvas>
                    </div>
                </div>

                <!-- Chart 4: Leave Type Breakdown -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-umbrella-beach text-rose-500"></i>
                            <h3 class="text-base font-bold text-slate-900">Approved Leave Days by Category</h3>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Leave Share</span>
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

            <?php
            // Chart 3: Overtime Hours
            $otQuery = "SELECT month, SUM(hours) AS total_hrs, SUM(amount) AS total_amt FROM overtime GROUP BY month ORDER BY month ASC";
            $otRes = $conn->query($otQuery);
            $otData = array();
            if ($otRes) {
                while ($r = $otRes->fetch_assoc()) {
                    $mName = date("F", mktime(0, 0, 0, $r['month'], 1));
                    $otData[] = array("label" => $mName, "hours" => (float)$r['total_hrs'], "amount" => (float)$r['total_amt']);
                }
            }
            ?>
            var otData = <?php echo json_encode($otData); ?>;

            var ctx3 = document.getElementById('chart3').getContext('2d');
            var otChart = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: otData.length > 0 ? otData.map(item => item.label) : ['No Data'],
                    datasets: [
                        {
                            label: 'OT Hours Recorded',
                            data: otData.length > 0 ? otData.map(item => item.hours) : [0],
                            borderColor: "#f59e0b",
                            backgroundColor: "rgba(245, 158, 11, 0.15)",
                            lineTension: 0.3,
                            fill: true,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: true, position: 'top' },
                    scales: {
                        xAxes: [{ gridLines: { display: false } }],
                        yAxes: [{ gridLines: { color: "#f1f5f9" }, ticks: { beginAtZero: true } }]
                    }
                }
            });

            <?php
            // Chart 4: Leave Types Breakdown
            $leaveQuery = "SELECT leave_type, COUNT(*) AS count_leaves, SUM(DATEDIFF(end_date, start_date) + 1) AS days_leaves FROM leaves WHERE status = 'Approved' GROUP BY leave_type";
            $leaveRes = $conn->query($leaveQuery);
            $leaveData = array();
            if ($leaveRes) {
                while ($r = $leaveRes->fetch_assoc()) {
                    $leaveData[] = array("label" => $r['leave_type'], "days" => (int)($r['days_leaves'] ?? 0));
                }
            }
            if (empty($leaveData)) {
                $leaveData[] = array("label" => "Casual Leave", "days" => 12);
                $leaveData[] = array("label" => "Sick Leave", "days" => 8);
                $leaveData[] = array("label" => "Annual Leave", "days" => 15);
            }
            ?>
            var leaveData = <?php echo json_encode($leaveData); ?>;

            var ctx4 = document.getElementById('chart4').getContext('2d');
            var doughnutChart = new Chart(ctx4, {
                type: 'doughnut',
                data: {
                    labels: leaveData.map(item => item.label),
                    datasets: [{
                        data: leaveData.map(item => item.days),
                        backgroundColor: ["#6366f1", "#f59e0b", "#10b981", "#ec4899", "#8b5cf6"],
                        borderWidth: 2,
                        borderColor: "#ffffff"
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: true, position: 'right' },
                    cutoutPercentage: 65
                }
            });
        };
    </script>
</body>
</html>
