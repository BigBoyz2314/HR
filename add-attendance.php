<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
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
    <title>Manual Attendance & Individual Entry - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.55rem 0.85rem !important;
            font-size: 0.875rem !important;
            border-color: #cbd5e1 !important;
        }
        .ts-wrapper.single .ts-control {
            background-color: #ffffff !important;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("select.searchable-select").forEach(function(el) {
                new TomSelect(el, { create: false });
            });
        });

        function goToIndividualPage(type) {
            var empId = document.getElementById("empSelect").value;
            if (!empId) {
                alert("Please select an employee first!");
                return;
            }
            if (type === 'attendance') {
                window.location.href = "edit-individual-attendance.php?emp_id=" + empId;
            } else if (type === 'overtime') {
                window.location.href = "edit-individual-overtime.php?emp_id=" + empId;
            }
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true', selectedEmp: '' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <div class="max-w-3xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Attendance & Overtime Management</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Manual Entry Hub</h1>
                        <p class="text-sm text-slate-500 mt-1">Select an employee to log daily attendance, manage monthly timesheets, or adjust individual overtime hours.</p>
                    </div>
                    <a href="upload-attendance.php" class="px-3.5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition inline-flex items-center space-x-2">
                        <i class="fa-solid fa-file-csv"></i>
                        <span>Import Machine CSV</span>
                    </a>
                </div>

                <!-- Alert Message -->
                <?php if (isset($_GET['action'])): ?>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-2 text-xs font-bold">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>Attendance recorded successfully for Employee ID: <strong><?php echo htmlspecialchars($_GET['emp'] ?? ''); ?></strong></span>
                        </div>
                        <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Selection Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Employee *</label>
                        <select id="empSelect" name="emp" x-model="selectedEmp" class="searchable-select w-full bg-white">
                            <option value="" disabled selected>Search & Select Employee...</option>
                            <?php
                            $stmt = "SELECT employeeID, sNo, employee_code, fname, mname, lname, department, designation FROM employees WHERE status = 'Active' ORDER BY CAST(COALESCE(NULLIF(sNo, 0), NULLIF(employee_code, ''), employeeID) AS UNSIGNED) ASC, employeeID ASC";
                            $result = $conn->query($stmt);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $full = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                    $code = !empty($row['sNo']) ? $row['sNo'] : $row['employeeID'];
                                    echo '<option value="' . $row['employeeID'] . '">#' . htmlspecialchars($code) . ' - ' . htmlspecialchars($full) . ' (' . htmlspecialchars($row['department'] ?: 'General') . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Individual Navigation Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <!-- Card 1: Individual Attendance Sheet -->
                        <div onclick="goToIndividualPage('attendance')" class="p-5 rounded-2xl border-2 border-slate-200 hover:border-emerald-500 bg-slate-50 hover:bg-emerald-50/40 transition cursor-pointer group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg group-hover:bg-emerald-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-user-pen"></i>
                                </div>
                                <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all"></i>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-sm group-hover:text-emerald-950">Individual Attendance Page</h3>
                            <p class="text-xs text-slate-500 mt-1">Open full monthly calendar to input manual daily In/Out punch times.</p>
                        </div>

                        <!-- Card 2: Individual Overtime Page -->
                        <div onclick="goToIndividualPage('overtime')" class="p-5 rounded-2xl border-2 border-slate-200 hover:border-amber-500 bg-slate-50 hover:bg-amber-50/40 transition cursor-pointer group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg group-hover:bg-amber-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-amber-600 group-hover:translate-x-1 transition-all"></i>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-sm group-hover:text-amber-950">Individual Overtime Page</h3>
                            <p class="text-xs text-slate-500 mt-1">View monthly overtime hours, add additional hours, or edit OT payout.</p>
                        </div>
                    </div>

                    <!-- Single Day Quick Punch Form -->
                    <div class="pt-4 border-t border-slate-100 space-y-4">
                        <div class="flex items-center space-x-2 text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                            <i class="fa-solid fa-calendar-day text-emerald-600"></i>
                            <span>Or Submit Single-Day Quick Attendance</span>
                        </div>

                        <form action="includes/add-attend2.php" method="post" class="space-y-4">
                            <input type="hidden" name="emp" x-model="selectedEmp">

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Attendance Date *</label>
                                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm transition">
                            </div>

                            <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-check-double"></i>
                                <span>Submit Quick Attendance Log</span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>

        </main>
    </div>

</body>
</html>