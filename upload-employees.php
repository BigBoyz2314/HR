<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import Employees (.xlsx / .csv) - Footprint HR</title>
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
            
            <div class="max-w-5xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-file-arrow-up"></i>
                            <span>Bulk Staff Onboarding</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Import Employees via Excel / CSV</h1>
                        <p class="text-sm text-slate-500 mt-1">Upload `.xlsx` (Excel) or `.csv` files directly to import and update employee records in bulk.</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="view-employees.php" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition border border-slate-200">
                            <i class="fa-solid fa-users text-slate-500"></i>
                            <span>View All Staff</span>
                        </a>
                        <a href="employees.php" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-semibold transition border border-indigo-200">
                            <i class="fa-solid fa-user-plus text-indigo-600"></i>
                            <span>Single Employee</span>
                        </a>
                    </div>
                </div>

                <!-- Status Alert Notification -->
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm shadow-emerald-500/30">
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
                                <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm shadow-rose-500/30">
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

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Upload Card Form -->
                    <div class="lg:col-span-7 bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-200/80 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-slate-800 flex items-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-emerald-600 mr-2.5"></i>
                                    Select Excel (.xlsx) or CSV File
                                </h2>
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold tracking-wide">
                                    .xlsx & .csv
                                </span>
                            </div>

                            <form action="includes/csv-upload-employees.php" method="post" enctype="multipart/form-data" class="space-y-6" id="uploadForm">
                                
                                <!-- File Drop Zone -->
                                <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 transition-all duration-200 rounded-2xl p-8 text-center bg-slate-50/60 hover:bg-emerald-50/20 group cursor-pointer relative" id="dropZone">
                                    <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)">
                                    
                                    <div class="w-16 h-16 bg-emerald-100/70 text-emerald-600 rounded-2xl mx-auto flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform shadow-sm">
                                        <i class="fa-solid fa-file-excel"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800" id="fileNameText">Click to choose or drag & drop file</h3>
                                    <p class="text-xs text-slate-500 mt-1.5" id="fileSubText">Supports standard Excel (.xlsx) files like <span class="font-semibold text-slate-700">Employees Name_Panda.xlsx</span> or CSV</p>
                                    
                                    <div class="mt-4 flex items-center justify-center space-x-2">
                                        <span class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg shadow-sm">
                                            <i class="fa-solid fa-file-circle-plus text-emerald-600 mr-1.5"></i> Browse File
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 text-xs text-slate-600 space-y-1.5">
                                    <div class="font-bold text-slate-700 flex items-center">
                                        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1.5"></i> Safe Synchronization
                                    </div>
                                    <p>Existing employees matching Worker ID / Employee Code will be cleanly updated with the latest details, while new workers will be registered automatically.</p>
                                </div>

                                <button type="submit" name="submit" class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2 cursor-pointer">
                                    <i class="fa-solid fa-circle-arrow-up text-base"></i>
                                    <span>Import & Process Employees</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Column Structure & Instructions -->
                    <div class="lg:col-span-5 bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center">
                            <i class="fa-solid fa-circle-info text-emerald-500 mr-2"></i>
                            Recognized Columns
                        </h3>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            Headers are recognized and mapped automatically from your spreadsheet:
                        </p>

                        <!-- Code Sample Box -->
                        <div class="bg-slate-900 text-slate-100 p-4 rounded-xl text-[11px] font-mono overflow-x-auto space-y-2">
                            <div class="text-slate-400 font-bold border-b border-slate-800 pb-1.5 flex items-center justify-between">
                                <span>Panda Format Headers:</span>
                                <span class="text-[10px] text-emerald-400 uppercase font-sans font-bold">Auto-Mapped</span>
                            </div>
                            <div class="text-emerald-400 font-semibold leading-relaxed">
                                Worker ID, Worker Name, Father Name, Designation, Department, Contact, Address, CNIC, DOB, Joining Date, Monthly Salary (Rs.), Status
                            </div>
                        </div>

                        <div class="pt-2">
                            <h4 class="text-xs font-bold text-slate-800 mb-2 uppercase tracking-wide">Supported Field Mappings:</h4>
                            <div class="max-h-64 overflow-y-auto pr-1 space-y-1.5 text-xs text-slate-600">
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Worker ID / Code</span>
                                    <span class="text-emerald-600 font-bold">sNo & Employee ID</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Worker Name</span>
                                    <span class="text-emerald-600 font-bold">First Name (fname)</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Father Name</span>
                                    <span class="text-indigo-600 font-bold">Last Name (lname)</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Department</span>
                                    <span class="text-slate-500">Auto-Linked Department</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Designation</span>
                                    <span class="text-slate-500">Auto-Linked Designation</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Contact</span>
                                    <span class="text-slate-500">Primary Phone</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Address</span>
                                    <span class="text-slate-500">Primary Address</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">CNIC</span>
                                    <span class="text-slate-500">National ID</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">DOB & Joining Date</span>
                                    <span class="text-amber-600 font-bold">Auto Excel Serial / YMD</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="font-medium text-slate-700">Monthly Salary</span>
                                    <span class="text-emerald-600 font-bold">Basic Salary</span>
                                </div>
                                <div class="flex items-center justify-between py-1">
                                    <span class="font-medium text-slate-700">Status</span>
                                    <span class="text-slate-500">Active / Inactive</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

    <script>
        function updateFileName(input) {
            const fileNameText = document.getElementById('fileNameText');
            const fileSubText = document.getElementById('fileSubText');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                fileNameText.textContent = "Selected: " + file.name;
                fileNameText.classList.add('text-emerald-600');
                const fileSizeKb = Math.round(file.size / 1024);
                fileSubText.textContent = `File ready for upload (${fileSizeKb} KB). Click Import & Process Employees below.`;
            } else {
                fileNameText.textContent = "Click to choose or drag & drop file";
                fileNameText.classList.remove('text-emerald-600');
                fileSubText.textContent = "Supports standard Excel (.xlsx) files like Employees Name_Panda.xlsx or CSV";
            }
        }

        // Drag & drop highlight
        const dropZone = document.getElementById('dropZone');
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-emerald-500', 'bg-emerald-50/40');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-emerald-500', 'bg-emerald-50/40');
            }, false);
        });
    </script>
</body>
</html>
