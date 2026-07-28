<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
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
    <title>Manual Attendance Entry - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
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
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Attendance Management</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Manual Attendance Entry</h1>
                        <p class="text-sm text-slate-500 mt-1">Record or update daily attendance logs for specific staff members.</p>
                    </div>
                    <a href="upload-attendance.php" class="px-3.5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition inline-flex items-center space-x-2">
                        <i class="fa-solid fa-file-csv"></i>
                        <span>Import CSV</span>
                    </a>
                </div>

                <!-- Alert Message -->
                <?php if (isset($_GET['action'])): ?>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-2 text-xs font-bold">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            <span>Attendance recorded for Employee ID: <strong><?php echo htmlspecialchars($_GET['emp'] ?? ''); ?></strong></span>
                        </div>
                        <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-6">
                    <form action="includes/add-attend2.php" method="post" class="space-y-5">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Employee *</label>
                            <select name="emp" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition bg-white">
                                <option value="" disabled selected>Select Employee...</option>
                                <?php
                                $stmt = "SELECT employeeID, sNo, fname, mname, lname FROM employees ORDER BY fname ASC";
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

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Attendance Date *</label>
                            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition">
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-check-double"></i>
                            <span>Submit Attendance Entry</span>
                        </button>

                    </form>
                </div>

            </div>

        </main>
    </div>

</body>
</html>