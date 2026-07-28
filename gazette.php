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
    <title>Gazette Holidays - Footprint HR</title>
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
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-umbrella-beach"></i>
                        <span>Calendar Setup</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Gazette Holidays</h1>
                    <p class="text-sm text-slate-500 mt-1">Configure official gazette holidays affecting attendance and payroll.</p>
                </div>
            </div>

            <!-- Status Alert -->
            <?php if (isset($_GET['add'])): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2 text-xs font-bold">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span>Holiday added successfully.</span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm">&times;</button>
                </div>
            <?php endif; ?>

            <div class="max-w-xl mx-auto bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                    <i class="fa-solid fa-calendar-plus text-indigo-600 mr-2"></i>
                    Add Gazette Holiday
                </h2>

                <form action="includes/add-gazette.php" method="post" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Holiday Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Independence Day" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Holiday Date *</label>
                        <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs shadow-md transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add Gazette Holiday
                    </button>
                </form>
            </div>

        </main>
    </div>

</body>
</html>