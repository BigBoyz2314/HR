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

// Generate Next Employee ID / sNo suggestion
$sql = "SELECT MAX(sNo) as max_sno, MAX(employeeID) as max_id FROM employees";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$max_sno = intval($row['max_sno']);
$max_id = intval($row['max_id']);
$next_eid = max($max_sno, $max_id) + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Workspace -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Main Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto" x-data="{ showAdvanced: false }">
            
            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Page Title Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Staff Registration</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Add New Employee</h1>
                        <p class="text-sm text-slate-500 mt-1">Fill in the required information (First Name, Basic Salary, Joining Date) to register staff.</p>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="upload-employees.php" class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition">
                            <i class="fa-solid fa-file-csv"></i>
                            <span>Bulk CSV Import</span>
                        </a>
                    </div>
                </div>

                <!-- Registration Form -->
                <form action="includes/add-employee.php" method="post" enctype="multipart/form-data" class="space-y-6">
                    
                    <!-- Section 1: Core Profile & Manual Code & Salary Additions -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <h2 class="text-base font-bold text-slate-900 flex items-center">
                                <i class="fa-solid fa-id-card text-indigo-600 mr-2.5"></i>
                                Core Employee & Salary Details
                            </h2>
                            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">Required: First Name, Basic Salary, Join Date</span>
                        </div>

                        <!-- Employee Code / ID Input (sNo) -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Employee Code / sNo
                                    <span class="text-[10px] text-indigo-600 font-semibold ml-1">(Editable)</span>
                                </label>
                                <input type="number" name="e_emp" id="e_emp" value="<?php echo $next_eid; ?>" placeholder="e.g. 7001 or 1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-sm bg-white transition shadow-sm">
                            </div>
                            <div class="md:col-span-3 flex items-center text-xs text-slate-500 leading-relaxed pt-2 md:pt-0">
                                <i class="fa-solid fa-circle-info text-indigo-500 mr-2 text-sm shrink-0"></i>
                                <span>You can manually enter any Employee ID / sNo from your attendance machine, or keep the auto-suggested number.</span>
                            </div>
                        </div>

                        <!-- 3 Required Fields + Addition/Allowance -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">First Name *</label>
                                <input type="text" name="fName" id="fName" required placeholder="John" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Basic Salary (PKR) *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs font-bold">PKR</span>
                                    <input type="number" step="0.01" min="0" name="basicSalary" id="basicSalary" required placeholder="50000" class="w-full pl-11 pr-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm font-semibold">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Addition / Allowance <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs font-bold">+</span>
                                    <input type="number" step="0.01" min="0" name="allowence" id="allowence" placeholder="0.00" class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm font-semibold text-emerald-700">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Joining Date *</label>
                                <input type="date" name="joinDate" id="joinDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm">
                            </div>
                        </div>

                        <!-- Optional Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Middle Name <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <input type="text" name="mName" id="mName" placeholder="Middle Name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Last Name <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <input type="text" name="lName" id="lName" placeholder="Doe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gender <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <select name="gender" id="gender" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm bg-white">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Department <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <select name="department" id="department" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm bg-white">
                                    <option value="0">General / Default Department</option>
                                    <?php
                                    $stmt = "SELECT departmentID, name FROM department ORDER BY name ASC";
                                    $result = $conn->query($stmt);
                                    if ($result && $result->num_rows > 0) {
                                        while($drow = $result->fetch_assoc()) {
                                            echo '<option value="' . $drow['departmentID'] . '">' . htmlspecialchars($drow['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Designation <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <select name="designation" id="designation" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm transition shadow-sm bg-white">
                                    <option value="0">Staff / Default Designation</option>
                                    <?php
                                    $stmt2 = "SELECT designationID, name FROM designation ORDER BY name ASC";
                                    $result2 = $conn->query($stmt2);
                                    if ($result2 && $result2->num_rows > 0) {
                                        while($desrow = $result2->fetch_assoc()) {
                                            echo '<option value="' . $desrow['designationID'] . '">' . htmlspecialchars($desrow['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Toggle for Additional Details -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
                        <button type="button" @click="showAdvanced = !showAdvanced" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-sliders"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">Secondary Contact & Additional Details</h3>
                                    <p class="text-xs text-slate-400">Optional: Phone, CNIC, address, bank details, emergency contact</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-indigo-600 flex items-center space-x-1">
                                <span x-text="showAdvanced ? 'Hide Optional Fields' : 'Show Optional Fields'"></span>
                                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': showAdvanced }"></i>
                            </span>
                        </button>

                        <div x-show="showAdvanced" x-collapse class="px-6 pb-6 pt-2 border-t border-slate-100 space-y-5">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone Number</label>
                                    <input type="text" name="pNumber" id="pNumber" placeholder="03001234567" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">CNIC / Govt ID</label>
                                    <input type="text" name="cnic" id="cnic" placeholder="42101-1234567-1" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Date of Birth</label>
                                    <input type="date" name="dob" id="dob" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Marital Status</label>
                                    <select name="mStatus" id="mStatus" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Primary Address</label>
                                    <input type="text" name="pAddress" id="pAddress" placeholder="Street Address" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bank Name</label>
                                    <input type="text" name="bank" id="bank" placeholder="HBL, Meezan, etc." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Number</label>
                                    <input type="text" name="bankAcc" id="bankAcc" placeholder="1234567890" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Emergency Contact Name</label>
                                    <input type="text" name="efName" id="efName" placeholder="Contact Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Photo Upload</label>
                                <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-4 pt-2">
                        <a href="view-employees.php" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-sm border border-slate-300 transition">Cancel</a>
                        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition transform active:scale-[0.99] flex items-center space-x-2">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Save & Register Employee</span>
                        </button>
                    </div>

                </form>

            </div>

        </main>
    </div>

</body>
</html>