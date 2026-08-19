<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Karachi');

$msg = '';
$msg_type = '';

// Handle Delete Leave
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && $_SESSION['role'] == '1') {
    $delId = intval($_GET['id']);
    $conn->query("DELETE FROM leaves WHERE id = $delId");
    $msg = "Leave record deleted successfully.";
    $msg_type = "success";
}

// Handle Add / Edit Leave POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_leave'])) {
    $leave_id   = isset($_POST['leave_id']) ? intval($_POST['leave_id']) : 0;
    $emp_id     = isset($_POST['employeeID']) ? intval($_POST['employeeID']) : 0;
    $leave_type = isset($_POST['leave_type']) ? trim($_POST['leave_type']) : 'Casual Leave';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date   = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $reason     = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $status     = isset($_POST['status']) ? trim($_POST['status']) : 'Approved';

    if ($emp_id > 0 && !empty($start_date) && !empty($end_date)) {
        // Calculate total days
        $d1 = new DateTime($start_date);
        $d2 = new DateTime($end_date);
        $interval = $d1->diff($d2);
        $total_days = $interval->days + 1;

        if ($leave_id > 0) {
            $stmt = $conn->prepare("UPDATE leaves SET employeeID = ?, leave_type = ?, start_date = ?, end_date = ?, total_days = ?, reason = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("isssissi", $emp_id, $leave_type, $start_date, $end_date, $total_days, $reason, $status, $leave_id);
            $stmt->execute();
            $stmt->close();
            $msg = "Leave record updated successfully.";
        } else {
            $appliedBy = $_SESSION['name'] ?? 'Admin';
            $stmt = $conn->prepare("INSERT INTO leaves (employeeID, leave_type, start_date, end_date, total_days, reason, status, applied_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("isssisss", $emp_id, $leave_type, $start_date, $end_date, $total_days, $reason, $status, $appliedBy);
            $stmt->execute();
            $stmt->close();
            $msg = "New leave application recorded successfully.";
        }
        $msg_type = "success";
    } else {
        $msg = "Please select an employee and specify valid start and end dates.";
        $msg_type = "error";
    }
}

// Filters
$filterEmp = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;
$filterType = isset($_GET['leave_type']) ? trim($_GET['leave_type']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

$where = [];
if ($filterEmp > 0) $where[] = "l.employeeID = $filterEmp";
if (!empty($filterType)) $where[] = "l.leave_type = '" . $conn->real_escape_string($filterType) . "'";
if (!empty($filterStatus)) $where[] = "l.status = '" . $conn->real_escape_string($filterStatus) . "'";
$whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Fetch Leave Records
$lSql = "SELECT l.*, e.sNo, e.employee_code, e.fname, e.mname, e.lname, e.department, e.designation 
         FROM leaves l 
         LEFT JOIN employees e ON l.employeeID = e.employeeID 
         $whereSql 
         ORDER BY l.start_date DESC, l.id DESC";
$lRes = $conn->query($lSql);

// Fetch Stats
$statApproved = $conn->query("SELECT SUM(total_days) as cnt FROM leaves WHERE status = 'Approved'")->fetch_assoc()['cnt'] ?? 0;
$statPending  = $conn->query("SELECT COUNT(*) as cnt FROM leaves WHERE status = 'Pending'")->fetch_assoc()['cnt'] ?? 0;
$statUnpaid   = $conn->query("SELECT SUM(total_days) as cnt FROM leaves WHERE leave_type = 'Unpaid Leave' AND status = 'Approved'")->fetch_assoc()['cnt'] ?? 0;

// Fetch Employees List for Modal Select sorted by employee code ascending
$empsList = $conn->query("SELECT employeeID, sNo, employee_code, fname, mname, lname FROM employees WHERE status = 'Active' ORDER BY CAST(COALESCE(NULLIF(sNo, 0), NULLIF(employee_code, ''), employeeID) AS UNSIGNED) ASC, employeeID ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("select.searchable-select").forEach(function(el) {
                new TomSelect(el, { create: false });
            });
        });
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" 
      x-data="{ 
        sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true',
        leaveModal: false,
        modalTitle: 'Apply New Leave',
        leaveId: 0,
        empId: '',
        leaveType: 'Casual Leave',
        startDate: '<?php echo date('Y-m-d'); ?>',
        endDate: '<?php echo date('Y-m-d'); ?>',
        reason: '',
        status: 'Approved'
      }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto space-y-6">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-amber-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-umbrella-beach"></i>
                            <span>Time-off & Leave Management</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Leave Management</h1>
                        <p class="text-sm text-slate-500 mt-1">Record, track, and manage employee leave applications and balances.</p>
                    </div>

                    <?php if ($_SESSION['role'] == '1'): ?>
                        <button @click="
                                modalTitle = 'Apply New Leave';
                                leaveId = 0;
                                empId = '';
                                leaveType = 'Casual Leave';
                                startDate = '<?php echo date('Y-m-d'); ?>';
                                endDate = '<?php echo date('Y-m-d'); ?>';
                                reason = '';
                                status = 'Approved';
                                leaveModal = true;
                            " 
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-extrabold text-xs shadow-lg shadow-amber-500/25 transition flex items-center space-x-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Apply / Record Leave</span>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (!empty($msg)): ?>
                    <div class="p-4 rounded-xl text-sm font-bold flex items-center justify-between <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                        <span><?php echo htmlspecialchars($msg); ?></span>
                        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Overview Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-extrabold text-xl">
                            <i class="fa-solid fa-plane-departure"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-slate-900"><?php echo number_format($statApproved); ?> <span class="text-xs font-normal text-slate-500">Days</span></div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Approved Leave Days</div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-extrabold text-xl">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-slate-900"><?php echo number_format($statPending); ?> <span class="text-xs font-normal text-slate-500">Requests</span></div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Approvals</div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-extrabold text-xl">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-slate-900"><?php echo number_format($statUnpaid); ?> <span class="text-xs font-normal text-slate-500">Days</span></div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unpaid Leave Days</div>
                        </div>
                    </div>
                </div>

                <!-- Table Card Container -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                    
                    <!-- Filter Toolbar -->
                    <div class="p-4 border-b border-slate-200 bg-slate-50/50">
                        <form action="leaves.php" method="get" class="flex flex-wrap items-center gap-3">
                            <select name="leave_type" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white">
                                <option value="">All Leave Types</option>
                                <option value="Casual Leave" <?php echo $filterType === 'Casual Leave' ? 'selected' : ''; ?>>Casual Leave</option>
                                <option value="Sick Leave" <?php echo $filterType === 'Sick Leave' ? 'selected' : ''; ?>>Sick Leave</option>
                                <option value="Annual Leave" <?php echo $filterType === 'Annual Leave' ? 'selected' : ''; ?>>Annual Leave</option>
                                <option value="Unpaid Leave" <?php echo $filterType === 'Unpaid Leave' ? 'selected' : ''; ?>>Unpaid Leave</option>
                            </select>

                            <select name="status" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white">
                                <option value="">All Statuses</option>
                                <option value="Approved" <?php echo $filterStatus === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Pending" <?php echo $filterStatus === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Rejected" <?php echo $filterStatus === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>

                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow transition">
                                Filter
                            </button>
                            <?php if (!empty($filterType) || !empty($filterStatus) || $filterEmp > 0): ?>
                                <a href="leaves.php" class="p-1.5 rounded-xl bg-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-300 transition">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Leaves Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-slate-900 text-slate-100">
                                <tr class="text-xs font-extrabold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Staff ID</th>
                                    <th class="py-3.5 px-4">Employee Name</th>
                                    <th class="py-3.5 px-4">Leave Type</th>
                                    <th class="py-3.5 px-4">Dates</th>
                                    <th class="py-3.5 px-4 text-center">Days</th>
                                    <th class="py-3.5 px-4">Reason</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <?php if ($_SESSION['role'] == '1'): ?>
                                        <th class="py-3.5 px-4 text-center">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-xs font-medium text-slate-700">
                                <?php if ($lRes && $lRes->num_rows > 0): ?>
                                    <?php while ($row = $lRes->fetch_assoc()): ?>
                                        <?php
                                        $sNo = !empty($row['sNo']) ? $row['sNo'] : (!empty($row['employee_code']) ? $row['employee_code'] : $row['employeeID']);
                                        $fullName = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
                                        $st = $row['status'];
                                        $stColor = ($st === 'Approved') ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : (($st === 'Pending') ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300');
                                        ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3 px-4 font-mono font-bold text-indigo-600">#<?php echo htmlspecialchars($sNo); ?></td>
                                            <td class="py-3 px-4 font-extrabold text-slate-900"><?php echo htmlspecialchars($fullName); ?></td>
                                            <td class="py-3 px-4">
                                                <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[11px]">
                                                    <?php echo htmlspecialchars($row['leave_type']); ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                                <?php echo date('d M Y', strtotime($row['start_date'])); ?> &rarr; <?php echo date('d M Y', strtotime($row['end_date'])); ?>
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-slate-900 font-mono">
                                                <?php echo $row['total_days']; ?> Day<?php echo $row['total_days'] > 1 ? 's' : ''; ?>
                                            </td>
                                            <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate">
                                                <?php echo htmlspecialchars($row['reason'] ?: 'No notes'); ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border uppercase <?php echo $stColor; ?>">
                                                    <?php echo htmlspecialchars($st); ?>
                                                </span>
                                            </td>
                                            <?php if ($_SESSION['role'] == '1'): ?>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="inline-flex items-center space-x-1.5">
                                                        <button type="button" 
                                                                @click="
                                                                    modalTitle = 'Edit Leave Application';
                                                                    leaveId = <?php echo $row['id']; ?>;
                                                                    empId = '<?php echo $row['employeeID']; ?>';
                                                                    leaveType = '<?php echo addslashes($row['leave_type']); ?>';
                                                                    startDate = '<?php echo $row['start_date']; ?>';
                                                                    endDate = '<?php echo $row['end_date']; ?>';
                                                                    reason = '<?php echo addslashes($row['reason'] ?? ''); ?>';
                                                                    status = '<?php echo $row['status']; ?>';
                                                                    leaveModal = true;
                                                                " 
                                                                class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 transition" 
                                                                title="Edit Leave">
                                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                        </button>
                                                        <a href="leaves.php?action=delete&id=<?php echo $row['id']; ?>" 
                                                           onclick="return confirm('Are you sure you want to delete this leave record?');" 
                                                           class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition" 
                                                           title="Delete Leave">
                                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-slate-400 font-medium">
                                            No leave applications found. Click "Apply / Record Leave" to add one.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Add / Edit Leave Modal -->
    <div x-cloak x-show="leaveModal" style="display: none !important;" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden transform transition-all" @click.away="leaveModal = false">
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-plane-arrival text-amber-400"></i>
                    <h3 class="text-base font-extrabold" x-text="modalTitle"></h3>
                </div>
                <button type="button" @click="leaveModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="leaves.php" method="post" class="p-6 space-y-4">
                <input type="hidden" name="save_leave" value="1">
                <input type="hidden" name="leave_id" x-model="leaveId">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Employee *</label>
                    <select name="employeeID" x-model="empId" required class="searchable-select w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-amber-500 bg-white">
                        <option value="">-- Choose Employee --</option>
                        <?php if ($empsList && $empsList->num_rows > 0): ?>
                            <?php 
                            $empsList->data_seek(0);
                            while($empItem = $empsList->fetch_assoc()): 
                                $code = !empty($empItem['sNo']) ? $empItem['sNo'] : $empItem['employeeID'];
                                $eName = trim($empItem['fname'] . ' ' . $empItem['mname'] . ' ' . $empItem['lname']);
                            ?>
                                <option value="<?php echo $empItem['employeeID']; ?>">#<?php echo $code; ?> - <?php echo htmlspecialchars($eName); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Leave Type *</label>
                        <select name="leave_type" x-model="leaveType" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-amber-500 bg-white">
                            <option value="Casual Leave">Casual Leave (Paid)</option>
                            <option value="Sick Leave">Sick Leave (Paid)</option>
                            <option value="Annual Leave">Annual Leave (Paid)</option>
                            <option value="Unpaid Leave">Unpaid Leave (Deducted)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status *</label>
                        <select name="status" x-model="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-amber-500 bg-white">
                            <option value="Approved">Approved</option>
                            <option value="Pending">Pending Approval</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Start Date *</label>
                        <input type="date" name="start_date" x-model="startDate" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">End Date *</label>
                        <input type="date" name="end_date" x-model="endDate" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason / Remarks <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea name="reason" x-model="reason" rows="2" placeholder="e.g., Medical reasons / Family vacation..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-200">
                    <button type="button" @click="leaveModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-extrabold text-xs shadow-lg shadow-amber-500/25 transition">
                        Save Leave Application
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
