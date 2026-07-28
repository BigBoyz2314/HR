<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = "SELECT * FROM designation WHERE designationID = '$id'";
$result = $conn->query($stmt);

if (!$result || $result->num_rows == 0) {
    header("location: designation.php");
    exit;
}

$row = $result->fetch_assoc();
$desigName = $row["name"];
$grade = $row["grade"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Designation - Footprint HR</title>
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
            
            <div class="max-w-xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex items-center justify-between bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-amber-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Position Configuration</span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Designation</h1>
                    </div>
                    <a href="designation.php" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition border border-slate-200">Cancel</a>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-5">
                    <form action="includes/edit-desig.php" method="post" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Designation Name *</label>
                            <input type="text" name="name1" value="<?php echo htmlspecialchars($desigName); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 text-sm font-semibold transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Grade Level</label>
                            <input type="number" min="1" max="20" name="grade" value="<?php echo htmlspecialchars($grade ?: 1); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 text-sm font-semibold transition">
                        </div>

                        <div class="pt-2 flex items-center justify-end space-x-3">
                            <a href="designation.php" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">Cancel</a>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold text-xs shadow-md transition">Update Designation</button>
                        </div>
                    </form>
                </div>

            </div>

        </main>
    </div>

</body>
</html>