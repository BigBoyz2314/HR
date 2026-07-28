<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allowances - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.75rem !important;
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
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <div class="max-w-2xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div class="flex items-center space-x-2 text-purple-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fas fa-sliders-h"></i>
                        <span>Payroll Adjustment</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Manage Employee Allowances</h1>
                    <p class="text-sm text-slate-500 mt-1">Select an employee, month, and year to assign monthly allowances.</p>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-6">
                    <form action="add-allowance.php" method="get" class="space-y-5">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Employee *</label>
                            <select name="emp" required class="searchable-select w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm transition bg-white">
                                <option value="" disabled selected>Choose Employee...</option>
                                <?php
                                $stmt = "SELECT employeeID, fname, mname, lname FROM employees ORDER BY fname ASC";
                                $result = $conn->query($stmt);
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $full = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                        echo '<option value="' . $row['employeeID'] .'">' . htmlspecialchars($full) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Month *</label>
                                <select name="month" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm transition bg-white">
                                    <?php
                                    $currM = date('n');
                                    for ($i = 1; $i <= 12; $i++) {
                                        $sel = ($i == $currM) ? 'selected' : '';
                                        echo '<option value="' . $i . '" ' . $sel . '>' . date("F", mktime(0, 0, 0, $i, 1)) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Year *</label>
                                <select name="year" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm transition bg-white">
                                    <?php
                                    $currY = date('Y');
                                    for ($y = $currY - 1; $y <= $currY + 2; $y++) {
                                        $sel = ($y == $currY) ? 'selected' : '';
                                        echo '<option value="' . $y . '" ' . $sel . '>' . $y . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-purple-500/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-circle-plus"></i>
                            <span>Configure Allowances</span>
                        </button>

                    </form>
                </div>

            </div>

        </main>
    </div>

</body>
</html>