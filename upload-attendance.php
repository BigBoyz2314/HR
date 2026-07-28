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
    <title>Import Attendance CSV - HR System</title>
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
                        <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-file-csv"></i>
                            <span>Attendance Ingestion</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Import Attendance Machine CSV</h1>
                        <p class="text-sm text-slate-500 mt-1">Upload machine-generated attendance export files (`.csv`) directly into the system database.</p>
                    </div>
                    <a href="attendance.php" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition border border-slate-200">
                        <i class="fa-solid fa-table-list text-slate-500"></i>
                        <span>View Attendance Logs</span>
                    </a>
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
                                    <h4 class="font-bold text-sm">Upload Successful</h4>
                                    <p class="text-xs text-emerald-700 mt-0.5"><?php echo htmlspecialchars($_GET['msg'] ?? 'Attendance records uploaded successfully.'); ?></p>
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
                                    <h4 class="font-bold text-sm">Upload Failed</h4>
                                    <p class="text-xs text-rose-700 mt-0.5"><?php echo htmlspecialchars($_GET['msg'] ?? 'An error occurred during file upload.'); ?></p>
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
                            <i class="fa-solid fa-cloud-arrow-up text-indigo-600 mr-2.5"></i>
                            Select CSV File
                        </h2>

                        <form action="includes/csv-upload.php" method="post" enctype="multipart/form-data" class="space-y-6">
                            
                            <!-- File Drop Zone -->
                            <div class="border-2 border-dashed border-slate-300 hover:border-indigo-500 transition rounded-2xl p-8 text-center bg-slate-50/50 group cursor-pointer relative" id="dropZone">
                                <input type="file" name="fileToUpload" id="fileToUpload" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)">
                                
                                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl mx-auto flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-file-excel"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700" id="fileNameText">Click to choose or drag & drop machine CSV</h3>
                                <p class="text-xs text-slate-400 mt-1">Supports standard CSV exports from attendance machines</p>
                                <span class="inline-block mt-3 px-3 py-1 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg shadow-2xl">Browse File</span>
                            </div>

                            <button type="submit" name="submit" class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-file-import"></i>
                                <span>Upload & Process Machine CSV</span>
                            </button>
                        </form>
                    </div>

                    <!-- Supported Format Helper -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-circle-info text-indigo-500 mr-2"></i>
                            Supported Format
                        </h3>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            The importer automatically detects and parses machine exports containing punch-in and punch-out timestamps.
                        </p>

                        <div class="bg-slate-900 text-slate-100 p-3.5 rounded-xl text-[11px] font-mono overflow-x-auto space-y-1">
                            <div class="text-slate-400 font-bold border-b border-slate-800 pb-1">Sample Headers:</div>
                            <div class="text-indigo-400">Employee, First Name, Departme, Date, Weekday, First Punch, Last Punch, Total Time</div>
                            <div class="text-emerald-400 border-t border-slate-800 pt-1 mt-1">Sample Row:</div>
                            <div class="text-slate-300">7001, FAZAL KH, Department, 06/07/2026, Monday, 08:15, 18:10, 09:54</div>
                        </div>

                        <ul class="text-xs text-slate-500 space-y-2 pt-2">
                            <li class="flex items-start">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 mr-2 shrink-0"></i>
                                <span>Dates formatted as <strong>DD/MM/YYYY</strong> are automatically parsed.</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 mr-2 shrink-0"></i>
                                <span>First Punch is saved as <strong>Time In</strong>, Last Punch as <strong>Time Out</strong>.</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fa-solid fa-check text-emerald-500 mt-0.5 mr-2 shrink-0"></i>
                                <span>Existing attendance records for the same employee and date will be updated.</span>
                            </li>
                        </ul>
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
                fileNameText.classList.add('text-indigo-600');
            } else {
                fileNameText.textContent = "Click to choose or drag & drop machine CSV";
                fileNameText.classList.remove('text-indigo-600');
            }
        }
    </script>
</body>
</html>