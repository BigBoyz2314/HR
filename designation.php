<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
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
    <title>Designations - Footprint HR</title>
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
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <div class="flex items-center space-x-2 text-amber-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fas fa-sitemap"></i>
                        <span>Job Positions</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Manage Designations</h1>
                    <p class="text-sm text-slate-500 mt-1">Configure company designations and job grades.</p>
                </div>
            </div>

            <!-- Status Alert Notifications -->
            <?php if (isset($_GET['del'])): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2 text-xs font-bold">
                        <i class="fa-solid fa-trash text-rose-500"></i>
                        <span>Designation "<?php echo htmlspecialchars($_GET['del']); ?>" has been deleted.</span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="text-rose-500 hover:text-rose-700 text-sm">&times;</button>
                </div>
            <?php elseif (isset($_GET['add'])): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2 text-xs font-bold">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Designation "<?php echo htmlspecialchars($_GET['add']); ?>" created successfully.</span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm">&times;</button>
                </div>
            <?php elseif (isset($_GET['edit'])): ?>
                <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2 text-xs font-bold">
                        <i class="fa-solid fa-pen text-amber-500"></i>
                        <span>Designation updated to "<?php echo htmlspecialchars($_GET['edit']); ?>".</span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="text-amber-500 hover:text-amber-700 text-sm">&times;</button>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Form Card: Add Designation -->
                <?php if ($_SESSION['role'] == '1'): ?>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4 h-fit">
                        <div class="border-b border-slate-100 pb-3 flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <h2 class="text-base font-bold text-slate-900">Add Designation</h2>
                        </div>

                        <form action="includes/add-designation.php" method="post" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Designation Name *</label>
                                <input type="text" name="name" required placeholder="e.g., Software Engineer" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Grade Level *</label>
                                <input type="number" min="1" max="20" name="grade" required value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition">
                            </div>

                            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold text-xs shadow-md transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Create Designation</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Table Card: Designation List -->
                <div class="<?php echo $_SESSION['role'] == '1' ? 'lg:col-span-2' : 'lg:col-span-3'; ?> bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <h2 class="text-base font-bold text-slate-900">Configured Designations</h2>
                        <span class="text-xs font-semibold text-slate-400">Positions List</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 rounded-l-xl">ID</th>
                                    <th class="py-3 px-4">Designation Name</th>
                                    <th class="py-3 px-4 text-center">Grade</th>
                                    <?php if ($_SESSION['role'] == '1'): ?>
                                        <th class="py-3 px-4 text-center rounded-r-xl">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                                <?php
                                $stmt = "SELECT * FROM designation ORDER BY name ASC";
                                $result = $conn->query($stmt);
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">#<?php echo $row['designationID']; ?></td>
                                            <td class="py-3.5 px-4 font-bold text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 font-extrabold text-xs">
                                                    Grade <?php echo htmlspecialchars($row['grade'] ?: '1'); ?>
                                                </span>
                                            </td>
                                            <?php if ($_SESSION['role'] == '1'): ?>
                                                <td class="py-3.5 px-4 text-center space-x-2">
                                                    <a href="includes/edit-desig.php?id=<?php echo $row['designationID']; ?>" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold text-xs border border-indigo-200 transition">Edit</a>
                                                    <a href="includes/del-designation.php?id=<?php echo $row['designationID']; ?>" onclick="return confirm('Are you sure you want to delete this designation?');" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition">Delete</a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="py-6 text-center text-slate-400">No designations added yet.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </main>
    </div>

</body>
</html>