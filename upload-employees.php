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
require_once('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import Employees - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Header Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-file-arrow-up"></i>
                            <span>Bulk Staff Onboarding</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Import Employees via CSV</h1>
                        <p class="text-sm text-slate-500 mt-1">Upload `.csv` or `.xlsx` exported files to import/update employee records in bulk.</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="employees.php" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition border border-slate-200">
                            <i class="fa-solid fa-user-plus text-slate-500"></i>
                            <span>Single Employee Form</span>
                        </a>
                    </div>
                </div>

                <!-- Status Alert Notification -->
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm">Import Successful</h4>
                                    <p class="text-xs text-emerald-700 mt-0.5"><?php echo htmlspecialchars($_GET['msg'] ?? 'Employees uploaded successfully.'); ?></p>
                                </div>
                            </div>
                            <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    <?php else: ?>
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-rose-500 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm">Import Failed</h4>
                                    <p class="text-xs text-rose-700 mt-0.5"><?php echo htmlspecialchars($_GET['msg'] ?? 'An error occurred during employee import.'); ?></p>
                                </div>
                            </div>
                            <button onclick="this.parentElement.remove();" class="text-rose-500 hover:text-rose-700 text-sm"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Upload Card Form -->
                    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
                        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                            <i class="fa-solid fa-cloud-arrow-up text-emerald-600 mr-2.5"></i>
                            Select Employee CSV File
                        </h2>

                        <form action="includes/csv-upload-employees.php" method="post" enctype="multipart/form-data" class="space-y-6">
                            
                            <!-- File Drop Zone -->
                            <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 transition rounded-2xl p-8 text-center bg-slate-50/50 group cursor-pointer relative" id="dropZone">
                                <input type="file" name="fileToUpload" id="fileToUpload" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)">
                                
                                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl mx-auto flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-users-gear"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700" id="fileNameText">Click to choose or drag & drop Employee CSV</h3>
                                <p class="text-xs text-slate-400 mt-1">Supports Employee No., Name, Dept, Monthly Salary, Additions</p>
                                <span class="inline-block mt-3 px-3 py-1 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg shadow-sm">Browse CSV</span>
                            </div>

                            <button type="submit" name="submit" class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Import & Create Employees</span>
                            </button>
                        </form>
                    </div>

                    <!-- Column Structure & Instructions -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-circle-info text-emerald-500 mr-2"></i>
                            Supported CSV Format
                        </h3>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            Flexibly maps your spreadsheet headers automatically.
                        </p>

                        <div class="bg-slate-900 text-slate-100 p-3.5 rounded-xl text-[11px] font-mono overflow-x-auto space-y-1">
                            <div class="text-slate-400 font-bold border-b border-slate-800 pb-1">CSV Header Format:</div>
                            <div class="text-emerald-400">Sr. No., Employee No., Name, Dept, Monthly Salary, Additions</div>
                            <div class="text-indigo-400 border-t border-slate-800 pt-1 mt-1">Sample Rows:</div>
                            <div class="text-slate-300">1, 7001, Fazal Khalik, Production, 51000, 5000</div>
                            <div class="text-slate-300">2, 7002, Javed Iqbal, , 51000, 5000</div>
                        </div>

                        <div class="pt-2">
                            <h4 class="text-xs font-bold text-slate-800 mb-1.5 uppercase">Field Mappings:</h4>
                            <ul class="text-xs text-slate-500 space-y-1.5">
                                <li class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700">Employee No.</span>
                                    <span class="text-emerald-600 font-bold">sNo / Code</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700">Name</span>
                                    <span class="text-emerald-600 font-bold">Required</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700">Dept</span>
                                    <span class="text-slate-400">Default: General</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700">Monthly Salary</span>
                                    <span class="text-slate-400">Basic Salary</span>
                                </li>
                                <li class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-700">Additions</span>
                                    <span class="text-emerald-600 font-semibold">Allowance</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

    <script>
        function updateFileName(input) {
            const fileNameText = document.getElementById('fileNameText');
            if (input.files && input.files[0]) {
                fileNameText.textContent = "Selected: " + input.files[0].name;
                fileNameText.classList.add('text-emerald-600');
            } else {
                fileNameText.textContent = "Click to choose or drag & drop Employee CSV";
                fileNameText.classList.remove('text-emerald-600');
            }
        }
    </script>
</body>
</html>
