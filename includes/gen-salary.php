<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
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
    <title>Generate Salary - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Navigation -->
    <?php include 'nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <div class="max-w-2xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div class="flex items-center space-x-2 text-teal-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-calculator"></i>
                        <span>Payroll Processing</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Generate Monthly Salary</h1>
                    <p class="text-sm text-slate-500 mt-1">Automatically calculate and generate salary sheets based on attendance, basic salary, allowances, and deductions.</p>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-6">
                    <form action="generate-salary-new.php" method="get" class="space-y-5">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Month *</label>
                                <select name="month" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition bg-white">
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
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Year *</label>
                                <select name="year" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm transition bg-white">
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

                        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-sm shadow-lg shadow-teal-500/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-gears"></i>
                            <span>Generate Salary Sheets</span>
                        </button>

                    </form>
                </div>

            </div>

        </main>
    </div>

</body>
</html>