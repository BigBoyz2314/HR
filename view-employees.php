<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Generate Next Employee ID / sNo suggestion
$max_res = $conn->query("SELECT MAX(sNo) as max_sno, MAX(employeeID) as max_id FROM employees");
$max_row = $max_res ? $max_res->fetch_assoc() : [];
$next_eid = max(intval($max_row['max_sno'] ?? 0), intval($max_row['max_id'] ?? 0)) + 1;


// Handle combined filter parameters
$filterEmpId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$filterDept  = isset($_GET['dept']) ? trim($_GET['dept']) : '';
$filterMonth = isset($_GET['month']) ? intval($_GET['month']) : 0;
$filterShift = isset($_GET['shift']) ? trim($_GET['shift']) : '';

// Status filter specifically checks for valid employee statuses: Active / Inactive
$filterStatus = '';
if (isset($_GET['emp_status']) && in_array(trim($_GET['emp_status']), ['Active', 'Inactive'])) {
    $filterStatus = trim($_GET['emp_status']);
} elseif (isset($_GET['status']) && in_array(trim($_GET['status']), ['Active', 'Inactive'])) {
    $filterStatus = trim($_GET['status']);
}

// Build dynamic WHERE clause
$whereClauses = [];
if ($filterEmpId > 0) {
    $whereClauses[] = "employeeID = $filterEmpId";
}
if (!empty($filterDept)) {
    $deptEsc = $conn->real_escape_string($filterDept);
    $whereClauses[] = "department = '$deptEsc'";
}
if (!empty($filterShift)) {
    $shiftEsc = $conn->real_escape_string($filterShift);
    $whereClauses[] = "(shift = '$shiftEsc' OR shift_id = '$shiftEsc')";
}
if ($filterMonth > 0 && $filterMonth <= 12) {
    $whereClauses[] = "(join_month = '$filterMonth' OR MONTH(join_date) = $filterMonth)";
}
if (!empty($filterStatus) && in_array($filterStatus, ['Active', 'Inactive'])) {
    $statusEsc = $conn->real_escape_string($filterStatus);
    $whereClauses[] = "status = '$statusEsc'";
}

$whereSql = "";
if (count($whereClauses) > 0) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees Directory & Filter - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        /* Print Stylesheet */
        @media print {
            header, aside, .no-print, form, button, nav, .filter-panel {
                display: none !important;
            }
            body, main, div {
                background: white !important;
                color: black !important;
                overflow: visible !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-only {
                display: block !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 6px 8px !important;
                background: white !important;
                color: black !important;
                box-shadow: none !important;
                position: static !important;
            }
            th {
                background-color: #0f172a !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .action-col, th:last-child, td:last-child {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
        }
        .print-only {
            display: none;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("select.searchable-select").forEach(function(el) {
                new TomSelect(el, {
                    create: false,
                    dropdownParent: 'body'
                });
            });
        });

        // Clean Native CSV Exporter
        function exportTableToCSV(tableId, filename) {
            var csv = [];
            var rows = document.querySelectorAll("#" + tableId + " tr");
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].style.display === "none") continue;
                
                var row = [], cols = rows[i].querySelectorAll("td, th");
                var colCount = cols.length;
                
                for (var j = 0; j < colCount; j++) {
                    var headerText = (rows[0].querySelectorAll("th")[j] ? rows[0].querySelectorAll("th")[j].textContent : "").trim().toLowerCase();
                    if (headerText.includes("action")) continue;

                    var text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, " ").trim();
                    
                    // Clean PKR, #, +, - prefixes for pure data CSV exports
                    if (rows[i].parentElement.tagName.toLowerCase() === 'tbody') {
                        text = text.replace(/^#/, '');
                        text = text.replace(/^PKR\s*/i, '');
                        text = text.replace(/^[+\-]\s*PKR\s*/i, '');
                    }
                    
                    text = '"' + text.replace(/"/g, '""') + '"';
                    row.push(text);
                }
                if (row.length > 0) csv.push(row.join(","));
            }

            var csvFile = new Blob(["\uFEFF" + csv.join("\n")], { type: "text/csv;charset=utf-8;" });
            var downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        $(document).ready(function(){
            // Instant table text search
            $("#searchFilter").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true', showAddModal: <?php echo (isset($_GET['action']) && $_GET['action'] == 'add') ? 'true' : 'false'; ?>, showEditModal: false, editEmp: {} }">
    
    <!-- Top Header Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            
            <!-- Printable Only Header -->
            <div class="print-only mb-4 text-center border-b pb-2">
                <h1 class="text-xl font-bold">FOOTPRINT HR - EMPLOYEE DIRECTORY</h1>
                <p class="text-xs">Generated on: <?php echo date('d-M-Y h:i A'); ?></p>
            </div>

            <!-- Page Header -->
            <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                <div>
                    <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-users"></i>
                        <span>Unified Staff Directory</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Employees Directory</h1>
                    <p class="text-sm text-slate-500 mt-1">View, search, and filter all employee records in one place.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <?php if ($_SESSION['role'] == '1'): ?>
                        <button type="button" @click="showAddModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2 cursor-pointer">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>+ Add Employee</span>
                        </button>
                        <a href="upload-employees.php" class="px-3.5 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 transition flex items-center space-x-2">
                            <i class="fa-solid fa-file-csv"></i>
                            <span>Import CSV</span>
                        </a>
                    <?php endif; ?>
                    <button onclick="window.print()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-print text-slate-500"></i>
                        <span>Print</span>
                    </button>
                    <button onclick="exportTableToCSV('table', 'employees.csv')" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-download text-slate-500"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Status Alerts -->
            <?php if (isset($_GET['msg']) || (isset($_GET['status']) && in_array($_GET['status'], ['success', 'error']))): 
                $alertType = (isset($_GET['status']) && $_GET['status'] === 'error') ? 'error' : 'success';
                $msgText = htmlspecialchars($_GET['msg'] ?? 'Operation completed successfully.');
            ?>
                <div class="no-print <?php echo $alertType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800/60 dark:text-rose-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-300'; ?> border p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid <?php echo $alertType === 'error' ? 'fa-triangle-exclamation text-rose-500' : 'fa-circle-check text-emerald-500'; ?> text-lg"></i>
                        <span class="text-xs font-bold"><?php echo $msgText; ?></span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="<?php echo $alertType === 'error' ? 'text-rose-500 hover:text-rose-700' : 'text-emerald-500 hover:text-emerald-700'; ?> text-sm">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Filter Controls Panel -->
            <div class="no-print filter-panel bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                <form action="view-employees.php" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    
                    <!-- Search Input -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search Keyword</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-400 text-xs"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" id="searchFilter" placeholder="Search name, code, CNIC, phone..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs transition">
                        </div>
                    </div>

                    <!-- Department Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Department</label>
                        <select name="dept" class="searchable-select w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs transition bg-white">
                            <option value="">All Departments</option>
                            <?php
                            $dRes = $conn->query("SELECT name FROM department ORDER BY name ASC");
                            if ($dRes && $dRes->num_rows > 0) {
                                while($dRow = $dRes->fetch_assoc()) {
                                    $sel = ($filterDept === $dRow['name']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($dRow['name']) . '" ' . $sel . '>' . htmlspecialchars($dRow['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Shift Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Work Shift</label>
                        <select name="shift" class="searchable-select w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs transition bg-white">
                            <option value="">All Shifts</option>
                            <?php
                            $sFilterRes = $conn->query("SELECT id, shift_name FROM shifts ORDER BY shift_name ASC");
                            if ($sFilterRes && $sFilterRes->num_rows > 0) {
                                while($sF = $sFilterRes->fetch_assoc()) {
                                    $sel = ($filterShift === $sF['shift_name'] || $filterShift == $sF['id']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($sF['shift_name']) . '" ' . $sel . '>' . htmlspecialchars($sF['shift_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Joining Month Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Joining Month</label>
                        <select name="month" class="searchable-select w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs transition bg-white">
                            <option value="0">All Months</option>
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $sel = ($filterMonth === $m) ? 'selected' : '';
                                echo '<option value="' . $m . '" ' . $sel . '>' . date("F", mktime(0,0,0,$m,1)) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Status Filter & Submit Buttons -->
                    <div class="flex items-center space-x-2">
                        <select name="emp_status" class="searchable-select w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs transition bg-white">
                            <option value="">All Statuses</option>
                            <option value="Active" <?php echo $filterStatus === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $filterStatus === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow transition shrink-0">Filter</button>
                        <?php if (!empty($filterDept) || !empty($filterShift) || $filterMonth > 0 || !empty($filterStatus) || $filterEmpId > 0): ?>
                            <a href="view-employees.php" title="Reset Filters" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition border border-slate-200 shrink-0">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </form>
            </div>

            <!-- Table Card Container -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 space-y-4">
                
                <!-- Table Wrapper -->
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="table" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-3 rounded-l-xl text-center w-[50px]">Sr.</th>
                                <th class="py-3.5 px-4 sticky left-0 z-20 bg-slate-900 shadow-[2px_0_5px_rgba(0,0,0,0.15)]">ID / Code</th>
                                <th class="py-3.5 px-4">Employee Name</th>
                                <th class="py-3.5 px-4">Department</th>
                                <th class="py-3.5 px-4">Designation</th>
                                <th class="py-3.5 px-4">Shift</th>
                                <th class="py-3.5 px-4 text-right">Basic Salary</th>
                                <th class="py-3.5 px-4 text-right">Addition</th>
                                <th class="py-3.5 px-4">CNIC</th>
                                <th class="py-3.5 px-4">Phone</th>
                                <th class="py-3.5 px-4">Joined</th>
                                <th class="py-3.5 px-4">Status</th>
                                <?php if ($_SESSION['role'] == '1'): ?>
                                    <th class="py-3.5 px-4 text-center rounded-r-xl action-col">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                            <?php
                            $stmt = "SELECT * FROM employees $whereSql ORDER BY CAST(COALESCE(NULLIF(sNo, 0), NULLIF(employee_code, ''), employeeID) AS UNSIGNED) ASC, employeeID ASC";
                            $result = $conn->query($stmt);
                            if ($result && $result->num_rows > 0) {
                                $sr_no = 0;
                                while($row = $result->fetch_assoc()) {
                                    $sr_no++;
                                    $fullName = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                    $initials = strtoupper(substr($row['fname'], 0, 1) . substr($row['lname'] ?: $row['fname'], 0, 1));
                                    $displayCode = !empty($row['sNo']) ? $row['sNo'] : (!empty($row['employee_code']) ? $row['employee_code'] : sprintf('%04d', $row['employeeID']));
                                    $allowanceVal = floatval($row['allowance'] ?? 0);
                                    ?>
                                    <tr class="group hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-3 font-mono font-bold text-slate-500 text-center whitespace-nowrap"><?php echo $sr_no; ?></td>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-900 sticky left-0 z-10 bg-white group-hover:bg-slate-50 shadow-[2px_0_5px_rgba(0,0,0,0.05)] whitespace-nowrap"><?php echo htmlspecialchars($displayCode); ?></td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="no-print w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs border border-indigo-200 shrink-0">
                                                    <?php echo $initials; ?>
                                                </div>
                                                <a href="employee-ledger.php?id=<?php echo $row['employeeID']; ?>" class="font-bold text-slate-900 hover:text-indigo-600 hover:underline transition-colors" title="Click to view Employee Ledger"><?php echo htmlspecialchars($fullName); ?></a>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full bg-cyan-50 text-cyan-700 font-semibold text-[11px]"><?php echo htmlspecialchars($row['department'] ?: 'General'); ?></span></td>
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-semibold text-[11px]"><?php echo htmlspecialchars($row['designation'] ?: 'Staff'); ?></span></td>
                                        <td class="py-3 px-4 whitespace-nowrap"><span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-bold text-[11px] border border-indigo-100 whitespace-nowrap"><i class="fa-solid fa-clock text-[9px] mr-1 text-indigo-500"></i><?php echo htmlspecialchars($row['shift'] ?: 'General Shift'); ?></span></td>
                                        <td class="py-3 px-4 text-right font-bold font-mono text-slate-900"><?php echo number_format($row['basic_salary']); ?></td>
                                        <td class="py-3 px-4 text-right font-bold font-mono <?php echo $allowanceVal > 0 ? 'text-emerald-600' : 'text-slate-400'; ?>"><?php echo $allowanceVal > 0 ? '+' . number_format($allowanceVal) : '-'; ?></td>
                                        <td class="py-3 px-4 font-mono text-slate-600"><?php echo htmlspecialchars($row['cnic'] ?: '-'); ?></td>
                                        <td class="py-3 px-4 font-mono text-slate-600"><?php echo htmlspecialchars($row['primary_number'] ?: '-'); ?></td>
                                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap"><?php echo htmlspecialchars($row['join_date'] ?: '-'); ?></td>
                                        <td class="py-3 px-4">
                                            <?php if (($row['status'] ?? 'Active') == 'Active'): ?>
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Active</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-extrabold uppercase">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($_SESSION['role'] == '1'): ?>
                                            <td class="py-3 px-4 text-center action-col">
                                                <button type="button" 
                                                        @click="editEmp = { 
                                                            id: '<?php echo $row['employeeID']; ?>', 
                                                            sNo: '<?php echo htmlspecialchars($displayCode, ENT_QUOTES); ?>', 
                                                            fname: '<?php echo htmlspecialchars($row['fname'], ENT_QUOTES); ?>', 
                                                            mname: '<?php echo htmlspecialchars($row['mname'] ?? '', ENT_QUOTES); ?>', 
                                                            lname: '<?php echo htmlspecialchars($row['lname'] ?? '', ENT_QUOTES); ?>', 
                                                            deptID: '<?php echo $row['departmentID']; ?>', 
                                                            deptName: '<?php echo htmlspecialchars($row['department'] ?? '', ENT_QUOTES); ?>', 
                                                            desigID: '<?php echo $row['designationID']; ?>', 
                                                            desigName: '<?php echo htmlspecialchars($row['designation'] ?? '', ENT_QUOTES); ?>', 
                                                            basic: '<?php echo $row['basic_salary']; ?>', 
                                                            allowance: '<?php echo $row['allowance']; ?>', 
                                                            shift_id: '<?php echo $row['shift_id']; ?>', 
                                                            ot_rate: '<?php echo $row['overtime_rate']; ?>', 
                                                            status: '<?php echo $row['status'] ?? 'Active'; ?>', 
                                                            joinDate: '<?php echo $row['join_date']; ?>', 
                                                            phone: '<?php echo htmlspecialchars($row['primary_number'] ?? '', ENT_QUOTES); ?>', 
                                                            cnic: '<?php echo htmlspecialchars($row['cnic'] ?? '', ENT_QUOTES); ?>' 
                                                        }; showEditModal = true" 
                                                        class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold text-xs transition border border-indigo-200 cursor-pointer">
                                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                                    <span>Edit</span>
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="11" class="py-8 text-center text-slate-400 font-medium">No matching employee records found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>

    <!-- Add Employee Modal Dialog -->
    <div x-cloak
         x-show="showAddModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none !important;">
        
        <div @click.away="showAddModal = false" 
             class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-slate-200 overflow-hidden my-8 max-h-[90vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Register New Employee</h2>
                        <p class="text-xs text-slate-400">Fill in required details to add a new staff member</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar" x-data="{ showAdvancedModal: false }">
                <form action="includes/add-employee.php" method="post" enctype="multipart/form-data" class="space-y-6">
                    
                    <!-- Code & Required Basic Info -->
                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 space-y-4">
                        <div class="flex items-center justify-between border-b border-indigo-100 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-900 flex items-center">
                                <i class="fa-solid fa-id-card text-indigo-600 mr-2"></i>
                                Core Employee Details
                            </span>
                            <span class="text-[10px] bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full font-bold">Required: Name, Basic Salary, Join Date</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Employee Code / sNo</label>
                                <input type="number" name="e_emp" value="<?php echo $next_eid; ?>" placeholder="Code" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-sm bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">First Name *</label>
                                <input type="text" name="fName" required placeholder="John" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Basic Salary (PKR) *</label>
                                <input type="number" step="0.01" min="0" name="basicSalary" required placeholder="50000" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Joining Date *</label>
                                <input type="date" name="joinDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Middle Name</label>
                                <input type="text" name="mName" placeholder="Middle" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Last Name</label>
                                <input type="text" name="lName" placeholder="Doe" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Allowance (PKR)</label>
                                <input type="number" step="0.01" min="0" name="allowence" placeholder="0.00" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-emerald-700 font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gender</label>
                                <select name="gender" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Department, Designation & Shift -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department</label>
                            <select name="department" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="0">General Department</option>
                                <?php
                                $dRes = $conn->query("SELECT departmentID, name FROM department ORDER BY name ASC");
                                if ($dRes && $dRes->num_rows > 0) {
                                    while($dRow = $dRes->fetch_assoc()) {
                                        echo '<option value="' . $dRow['departmentID'] . '">' . htmlspecialchars($dRow['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Designation</label>
                            <select name="designation" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="0">Staff Designation</option>
                                <?php
                                $dsRes = $conn->query("SELECT designationID, name FROM designation ORDER BY name ASC");
                                if ($dsRes && $dsRes->num_rows > 0) {
                                    while($dsRow = $dsRes->fetch_assoc()) {
                                        echo '<option value="' . $dsRow['designationID'] . '">' . htmlspecialchars($dsRow['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Assigned Shift</label>
                            <select name="shift_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white font-semibold">
                                <option value="0">Default Shift (09:00 AM - 05:00 PM | 8.0 hrs)</option>
                                <?php
                                $sRes = $conn->query("SELECT id, shift_name, start_time, end_time, working_hours FROM shifts ORDER BY shift_name ASC");
                                if ($sRes && $sRes->num_rows > 0) {
                                    while($sRow = $sRes->fetch_assoc()) {
                                        $tIn = date('h:i A', strtotime($sRow['start_time']));
                                        $tOut = date('h:i A', strtotime($sRow['end_time']));
                                        $hrs = number_format($sRow['working_hours'], 1);
                                        echo '<option value="' . $sRow['id'] . '">' . htmlspecialchars($sRow['shift_name']) . ' (' . $tIn . ' - ' . $tOut . ' | ' . $hrs . ' hrs)</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Overtime Rate -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Overtime Hourly Rate (PKR) <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="number" step="0.01" min="0" name="overtime_rate" placeholder="Default: Basic Salary ÷ (30 Days × Shift Hrs)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>

                    <!-- Collapsible Contact & Advanced Options -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <button type="button" @click="showAdvancedModal = !showAdvancedModal" class="w-full px-4 py-3 bg-slate-50 flex items-center justify-between text-left hover:bg-slate-100 transition">
                            <span class="text-xs font-bold text-slate-700 flex items-center space-x-2">
                                <i class="fa-solid fa-sliders text-indigo-600"></i>
                                <span>Optional Secondary Details (Contact, Bank, Emergency, Photo)</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showAdvancedModal }"></i>
                        </button>
                        
                        <div x-show="showAdvancedModal" x-collapse class="p-4 space-y-4 border-t border-slate-200 bg-white">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Phone Number</label>
                                    <input type="text" name="pNumber" placeholder="03001234567" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">CNIC / Govt ID</label>
                                    <input type="text" name="cnic" placeholder="42101-1234567-1" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Date of Birth</label>
                                    <input type="date" name="dob" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bank Name</label>
                                    <input type="text" name="bank" placeholder="Bank Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Number</label>
                                    <input type="text" name="bankAcc" placeholder="Account Number" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Emergency Contact</label>
                                    <input type="text" name="efName" placeholder="Contact Name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Photo Upload</label>
                                <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500">
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition flex items-center space-x-2">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Save & Register Employee</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <!-- Edit Employee Modal Dialog -->
    <div x-cloak
         x-show="showEditModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none !important;">
        
        <div @click.away="showEditModal = false" 
             class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-slate-200 overflow-hidden my-8 max-h-[90vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-600/30 text-amber-300 border border-amber-500/30 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Edit Employee Details</h2>
                        <p class="text-xs text-slate-400">Update staff information for <strong class="text-amber-400" x-text="editEmp.fname + ' ' + editEmp.lname"></strong></p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
                <form action="includes/edit-emp.php" method="post" class="space-y-6">
                    <input type="hidden" name="empID" :value="editEmp.id">
                    <input type="hidden" name="oldDept" :value="editEmp.deptName">
                    <input type="hidden" name="oldDeptID" :value="editEmp.deptID">
                    <input type="hidden" name="oldDesig" :value="editEmp.desigName">
                    <input type="hidden" name="oldDesigID" :value="editEmp.desigID">

                    <!-- Core Employee Info -->
                    <div class="p-4 bg-amber-50/40 rounded-2xl border border-amber-100 space-y-4">
                        <div class="flex items-center justify-between border-b border-amber-100 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center">
                                <i class="fa-solid fa-id-card text-amber-600 mr-2"></i>
                                Basic Details & Profile
                            </span>
                            <span class="text-[10px] bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-bold">Employee ID: #<span x-text="editEmp.sNo"></span></span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Employee Code / Serial #</label>
                                <input type="text" name="e_emp" :value="editEmp.sNo" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">First Name *</label>
                                <input type="text" name="fName" :value="editEmp.fname" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Middle Name</label>
                                <input type="text" name="mName" :value="editEmp.mname" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Last Name</label>
                                <input type="text" name="lName" :value="editEmp.lname" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department</label>
                                <select name="dept" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                                    <option value="0">Keep Current (<span x-text="editEmp.deptName"></span>)</option>
                                    <?php
                                    $dResModal = $conn->query("SELECT departmentID, name FROM department ORDER BY name ASC");
                                    if ($dResModal && $dResModal->num_rows > 0) {
                                        while($dRow = $dResModal->fetch_assoc()) {
                                            echo '<option value="' . $dRow['departmentID'] . '">' . htmlspecialchars($dRow['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Designation</label>
                                <select name="desig" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                                    <option value="0">Keep Current (<span x-text="editEmp.desigName"></span>)</option>
                                    <?php
                                    $dsResModal = $conn->query("SELECT designationID, name FROM designation ORDER BY name ASC");
                                    if ($dsResModal && $dsResModal->num_rows > 0) {
                                        while($dsRow = $dsResModal->fetch_assoc()) {
                                            echo '<option value="' . $dsRow['designationID'] . '">' . htmlspecialchars($dsRow['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Work Shift</label>
                                <select name="shift_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white">
                                    <option value="0" :selected="editEmp.shift_id == 0">Default Shift (09:00 AM - 05:00 PM | 8.0 hrs)</option>
                                    <?php
                                    $shResModal = $conn->query("SELECT id, shift_name, start_time, end_time, working_hours FROM shifts ORDER BY shift_name ASC");
                                    if ($shResModal && $shResModal->num_rows > 0) {
                                        while($shRow = $shResModal->fetch_assoc()) {
                                            $tIn = date('h:i A', strtotime($shRow['start_time']));
                                            $tOut = date('h:i A', strtotime($shRow['end_time']));
                                            $hrs = number_format($shRow['working_hours'], 1);
                                            $label = htmlspecialchars($shRow['shift_name']) . " ($tIn - $tOut | {$hrs} hrs)";
                                            echo '<option value="' . $shRow['id'] . '" :selected="editEmp.shift_id == ' . $shRow['id'] . '">' . $label . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Financial & Employment Details -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center border-b border-slate-200 pb-2">
                            <i class="fa-solid fa-money-bill-wave text-emerald-600 mr-2"></i>
                            Salary & Employment Configuration
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Basic Salary (PKR) *</label>
                                <input type="number" step="0.01" name="basicSalary" :value="editEmp.basic" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monthly Allowance (PKR)</label>
                                <input type="number" step="0.01" name="allowance" :value="editEmp.allowance" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Overtime Rate (PKR/hr)</label>
                                <input type="number" step="0.01" name="overtime_rate" :value="editEmp.ot_rate" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Joining Date *</label>
                                <input type="date" name="joinDate" :value="editEmp.joinDate" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Account Status *</label>
                                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold bg-white">
                                    <option value="Active" :selected="editEmp.status == 'Active'">Active</option>
                                    <option value="Inactive" :selected="editEmp.status == 'Inactive'">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                                <input type="text" name="pNumber" :value="editEmp.phone" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CNIC / Govt ID</label>
                                <input type="text" name="cnic" :value="editEmp.cnic" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" 
                                @click="if (confirm('Are you sure you want to permanently delete employee ' + editEmp.fname + ' ' + editEmp.lname + ' (#' + (editEmp.sNo || editEmp.id) + ')? This action cannot be undone.')) { window.location.href = 'includes/del-employee.php?id=' + editEmp.id; }"
                                class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 font-bold text-xs border border-rose-200 dark:border-rose-800/60 transition flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Delete Employee</span>
                        </button>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">Cancel</button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-2 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Update Employee</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>

</body>
</html>