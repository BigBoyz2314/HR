<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');

$empID = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['empID']) ? intval($_GET['empID']) : 0);
$stmt = "SELECT * FROM employees WHERE employeeID = '$empID'";
$result = $conn->query($stmt);

if (!$result || $result->num_rows == 0) {
    header("location: view-employees.php");
    exit;
}

$row = $result->fetch_assoc();
$fname = $row["fname"];
$mname = $row["mname"];
$lname = $row["lname"];
$fullName = trim("$fname $mname $lname");
$dept = $row["department"];
$desig = $row["designation"];
$desigID = $row["designationID"];
$deptID = $row["departmentID"];
$basic = $row["basic_salary"];
$allowance = isset($row["allowance"]) ? $row["allowance"] : 0.00;
$sNo = !empty($row["sNo"]) ? $row["sNo"] : (!empty($row["employee_code"]) ? $row["employee_code"] : $empID);
$joinDate = !empty($row["join_date"]) ? $row["join_date"] : date('Y-m-d');
$status = !empty($row["status"]) ? $row["status"] : 'Active';
$cnic = $row["cnic"];
$phone = $row["primary_number"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Footprint HR</title>
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
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            
            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex items-center justify-between bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-user-pen"></i>
                            <span>Staff Management</span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Employee Profile</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Updating information for <strong class="text-slate-800"><?php echo htmlspecialchars($fullName); ?></strong> (Code: <strong class="text-indigo-600"><?php echo htmlspecialchars($sNo); ?></strong>)</p>
                    </div>
                    <a href="view-employees.php" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition border border-slate-200">Back to Staff</a>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-6">
                    <form action="includes/edit-emp.php" method="post" class="space-y-6">
                        <input type="hidden" name="empID" value="<?php echo $empID; ?>">
                        <input type="hidden" name="oldDept" value="<?php echo htmlspecialchars($dept); ?>">
                        <input type="hidden" name="oldDesig" value="<?php echo htmlspecialchars($desig); ?>">
                        <input type="hidden" name="oldDeptID" value="<?php echo $deptID; ?>">
                        <input type="hidden" name="oldDesigID" value="<?php echo $desigID; ?>">

                        <!-- Employee Code / sNo -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Employee Code / sNo *</label>
                                <input type="number" name="e_emp" value="<?php echo htmlspecialchars($sNo); ?>" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 font-mono font-bold text-slate-900 text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Employment Status *</label>
                                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold bg-white">
                                    <option value="Active" <?php echo ($status === 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($status === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Joining Date *</label>
                                <input type="date" name="joinDate" value="<?php echo htmlspecialchars($joinDate); ?>" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium">
                            </div>
                        </div>

                        <!-- Name Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">First Name *</label>
                                <input type="text" name="fName" value="<?php echo htmlspecialchars($fname); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Middle Name</label>
                                <input type="text" name="mName" value="<?php echo htmlspecialchars($mname); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Last Name</label>
                                <input type="text" name="lName" value="<?php echo htmlspecialchars($lname); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-semibold">
                            </div>
                        </div>

                        <!-- Salary & Allowance -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Basic Salary (PKR) *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs font-bold">PKR</span>
                                    <input type="number" step="0.01" min="0" name="basicSalary" value="<?php echo htmlspecialchars($basic); ?>" required class="w-full pl-11 pr-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-semibold text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Addition / Allowance (PKR)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs font-bold">+</span>
                                    <input type="number" step="0.01" min="0" name="allowance" value="<?php echo htmlspecialchars($allowance); ?>" placeholder="0.00" class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-semibold text-sm text-emerald-700">
                                </div>
                            </div>
                        </div>

                        <!-- Department & Designation -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Department</label>
                                <select name="dept" class="searchable-select w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition bg-white">
                                    <option value="">Keep Current (<?php echo htmlspecialchars($dept); ?>)</option>
                                    <?php
                                    $dRes = $conn->query("SELECT departmentID, name FROM department ORDER BY name ASC");
                                    if ($dRes && $dRes->num_rows > 0) {
                                        while($dRow = $dRes->fetch_assoc()) {
                                            $sel = ($dRow['departmentID'] == $deptID) ? 'selected' : '';
                                            echo '<option value="' . $dRow['departmentID'] . '" ' . $sel . '>' . htmlspecialchars($dRow['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Designation</label>
                                <select name="desig" class="searchable-select w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition bg-white">
                                    <option value="">Keep Current (<?php echo htmlspecialchars($desig); ?>)</option>
                                    <?php
                                    $desRes = $conn->query("SELECT designationID, name FROM designation ORDER BY name ASC");
                                    if ($desRes && $desRes->num_rows > 0) {
                                        while($desRow = $desRes->fetch_assoc()) {
                                            $sel = ($desRow['designationID'] == $desigID) ? 'selected' : '';
                                            echo '<option value="' . $desRow['designationID'] . '" ' . $sel . '>' . htmlspecialchars($desRow['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                                <input type="text" name="pNumber" value="<?php echo htmlspecialchars($phone); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">CNIC / Govt ID</label>
                                <input type="text" name="cnic" value="<?php echo htmlspecialchars($cnic); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-mono">
                            </div>
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <a href="view-employees.php" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">Cancel</a>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs shadow-md transition">Save Profile Changes</button>
                        </div>
                    </form>
                </div>

            </div>

        </main>
    </div>

</body>
</html>