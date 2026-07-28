<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');

$eid = isset($_GET['emp']) ? intval($_GET['emp']) : 0;
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$sql = "SELECT * FROM employees WHERE employeeID = '$eid'";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    header("location: allowance.php");
    exit;
}
$row = $result->fetch_assoc();
$name = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
$month_name = date("F", mktime(0, 0, 0, $month, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Allowances - Footprint HR</title>
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
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex items-center justify-between bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-circle-plus"></i>
                            <span>Allowance Entry &bull; <?php echo $month_name . ' ' . $year; ?></span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Allowances for <?php echo htmlspecialchars($name); ?></h1>
                        <p class="text-xs text-slate-500 mt-0.5">Add, manage, or remove monthly bonus and allowance additions.</p>
                    </div>
                    <a href="allowance.php" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition border border-slate-200">Back</a>
                </div>

                <!-- Add Form Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                        <i class="fa-solid fa-plus-circle text-emerald-600 mr-2"></i>
                        Add New Monthly Allowance
                    </h2>

                    <form action="includes/add-allow.php" method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <input type="hidden" name="eid" value="<?php echo $eid; ?>">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Allowance Type *</label>
                            <select name="type" required class="searchable-select w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                                <option value="Travel">Travel Allowance</option>
                                <option value="Food">Food / Meal</option>
                                <option value="Mobile">Mobile / Internet</option>
                                <option value="Bonus">Performance Bonus</option>
                                <option value="Medical">Medical Allowance</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount (PKR) *</label>
                            <input type="number" step="0.01" min="1" name="amount" required placeholder="5000" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description *</label>
                            <input type="text" name="desc" required placeholder="Reason / Details" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-plus"></i>
                                <span>Save Allowance</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Allowances Table Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center">
                        <i class="fa-solid fa-list text-indigo-600 mr-2"></i>
                        Configured Allowances (<?php echo $month_name . ' ' . $year; ?>)
                    </h3>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 rounded-l-xl">#</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Description</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4">Added On</th>
                                    <?php if ($_SESSION['role'] == '1'): ?>
                                        <th class="py-3 px-4 text-center rounded-r-xl">Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                                <?php
                                $stmt = "SELECT * FROM `allowances` WHERE `employeeID` = '$eid' AND `month` = '$month' AND `year` = '$year' ORDER BY id DESC";
                                $res = $conn->query($stmt);
                                $i = 1;
                                if ($res && $res->num_rows > 0) {
                                    while($rowA = $res->fetch_assoc()) {
                                        ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-3 px-4 font-mono font-bold"><?php echo $i++; ?></td>
                                            <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] uppercase"><?php echo htmlspecialchars($rowA['allowance_type']); ?></span></td>
                                            <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($rowA['description']); ?></td>
                                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">+PKR <?php echo number_format($rowA['amount']); ?></td>
                                            <td class="py-3 px-4 text-slate-500 font-mono"><?php echo date('d-M-Y h:i A', strtotime($rowA['created_at'])); ?></td>
                                            <?php if ($_SESSION['role'] == '1'): ?>
                                                <td class="py-3 px-4 text-center">
                                                    <form action="includes/del-allowance.php" method="get" onsubmit="return confirm('Delete this allowance record?');">
                                                        <input type="hidden" name="id" value="<?php echo $rowA['id']; ?>">
                                                        <input type="hidden" name="eid" value="<?php echo $eid; ?>">
                                                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                                                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition">Delete</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="py-6 text-center text-slate-400 font-medium">No custom allowances added for ' . $month_name . ' ' . $year . '.</td></tr>';
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