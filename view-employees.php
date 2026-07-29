<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');

// Handle combined filter parameters
$filterEmpId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$filterDept  = isset($_GET['dept']) ? trim($_GET['dept']) : '';
$filterMonth = isset($_GET['month']) ? intval($_GET['month']) : 0;
$filterStatus= isset($_GET['status']) ? trim($_GET['status']) : '';
$filterShift = isset($_GET['shift']) ? trim($_GET['shift']) : '';

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
if (!empty($filterStatus)) {
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
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.75rem !important;
            border-color: #cbd5e1 !important;
            box-shadow: none !important;
        }
        .ts-wrapper.single .ts-control {
            background-color: #ffffff !important;
        }
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
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true' }">
    
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
                        <a href="employees.php" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Add Employee</span>
                        </a>
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
            <?php if (isset($_GET['status'])): ?>
                <div class="no-print bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                        <span class="text-xs font-bold"><?php echo htmlspecialchars($_GET['msg'] ?? 'Operation completed successfully.'); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700 text-sm">&times;</button>
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
                        <select name="status" class="searchable-select w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-xs transition bg-white">
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
                                <th class="py-3.5 px-4 rounded-l-xl sticky left-0 z-20 bg-slate-900 shadow-[2px_0_5px_rgba(0,0,0,0.15)]">ID / Code</th>
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
                                while($row = $result->fetch_assoc()) {
                                    $fullName = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                    $initials = strtoupper(substr($row['fname'], 0, 1) . substr($row['lname'] ?: $row['fname'], 0, 1));
                                    $displayCode = !empty($row['sNo']) ? $row['sNo'] : (!empty($row['employee_code']) ? $row['employee_code'] : sprintf('%04d', $row['employeeID']));
                                    $allowanceVal = floatval($row['allowance'] ?? 0);
                                    ?>
                                    <tr class="group hover:bg-slate-50/80 transition">
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
                                        <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-bold text-[11px] border border-indigo-100"><i class="fa-solid fa-clock text-[9px] mr-1 text-indigo-500"></i><?php echo htmlspecialchars($row['shift'] ?: 'General Shift'); ?></span></td>
                                        <td class="py-3 px-4 text-right font-bold font-mono <?php echo $allowanceVal > 0 ? 'text-emerald-600' : 'text-slate-400'; ?>"><?php echo $allowanceVal > 0 ? '+' . number_format($allowanceVal) : '0'; ?></td>
                                        <td class="py-3 px-4 font-mono text-slate-600"><?php echo htmlspecialchars($row['cnic'] ?: '-'); ?></td>
                                        <td class="py-3 px-4 font-mono text-slate-600"><?php echo htmlspecialchars($row['primary_number'] ?: '-'); ?></td>
                                        <td class="py-3 px-4 text-slate-500"><?php echo htmlspecialchars($row['join_date'] ?: '-'); ?></td>
                                        <td class="py-3 px-4">
                                            <?php if (($row['status'] ?? 'Active') == 'Active'): ?>
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Active</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-extrabold uppercase">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($_SESSION['role'] == '1'): ?>
                                            <td class="py-3 px-4 text-center action-col">
                                                <a href="includes/edit-emp.php?id=<?php echo $row['employeeID']; ?>" class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold text-xs transition border border-indigo-200">
                                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                                    <span>Edit</span>
                                                </a>
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

</body>
</html>