<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
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
    <title>Employee Audit Logs - Footprint HR</title>
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
    
    <!-- Top Header Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Audit Trail</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Employee Audit Logs</h1>
                    <p class="text-sm text-slate-500 mt-1">Select an employee to view change history and activity logs.</p>
                </div>
            </div>

            <!-- Selection Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 max-w-xl mx-auto space-y-4">
                <form action="includes/view-emp-log.php" method="get" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Employee *</label>
                        <select name="id" required class="searchable-select w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition bg-white">
                            <option value="" disabled selected>Choose Employee...</option>
                            <?php
                            $stmt = "SELECT employeeID, fname, mname, lname FROM employees ORDER BY fname ASC";
                            $result = $conn->query($stmt);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $full = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                    echo '<option value="' . $row['employeeID'] . '">#' . sprintf('%04d', $row['employeeID']) . ' - ' . htmlspecialchars($full) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs shadow-md transition">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5"></i>
                        <span>Fetch Employee Logs</span>
                    </button>
                </form>
            </div>

        </main>
    </div>

</body>
</html>