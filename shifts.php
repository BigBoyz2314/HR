<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');

$msg = '';
$msg_type = '';

// Handle Create / Edit / Delete POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['role']) && $_SESSION['role'] == '1') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $shift_name = trim($_POST['shift_name'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        
        if (!empty($shift_name) && !empty($start_time) && !empty($end_time)) {
            // Calculate working hours
            $t_start = strtotime("1970-01-01 $start_time");
            $t_end = strtotime("1970-01-01 $end_time");
            if ($t_end < $t_start) {
                $t_end += 86400; // Overnight shift
            }
            $working_hours = round(($t_end - $t_start) / 3600, 2);

            $stmt = $conn->prepare("INSERT INTO shifts (shift_name, start_time, end_time, working_hours) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $shift_name, $start_time, $end_time, $working_hours);
            if ($stmt->execute()) {
                $msg = "Shift '$shift_name' added successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error adding shift: " . $conn->error;
                $msg_type = "error";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $shift_id = intval($_POST['shift_id'] ?? 0);
        $shift_name = trim($_POST['shift_name'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        
        if ($shift_id > 0 && !empty($shift_name) && !empty($start_time) && !empty($end_time)) {
            $t_start = strtotime("1970-01-01 $start_time");
            $t_end = strtotime("1970-01-01 $end_time");
            if ($t_end < $t_start) {
                $t_end += 86400;
            }
            $working_hours = round(($t_end - $t_start) / 3600, 2);

            $stmt = $conn->prepare("UPDATE shifts SET shift_name = ?, start_time = ?, end_time = ?, working_hours = ? WHERE id = ?");
            $stmt->bind_param("ssddi", $shift_name, $start_time, $end_time, $working_hours, $shift_id);
            if ($stmt->execute()) {
                // Update employees using this shift_id
                $conn->query("UPDATE employees SET shift = '$shift_name', start_time = '$start_time', end_time = '$end_time', working_hours = '$working_hours' WHERE shift_id = $shift_id");
                $msg = "Shift updated successfully!";
                $msg_type = "success";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $shift_id = intval($_POST['shift_id'] ?? 0);
        if ($shift_id > 0) {
            $conn->query("DELETE FROM shifts WHERE id = $shift_id");
            $conn->query("UPDATE employees SET shift_id = NULL WHERE shift_id = $shift_id");
            $msg = "Shift deleted successfully!";
            $msg_type = "success";
        }
    }
}

// Fetch all shifts
$shifts_res = $conn->query("SELECT * FROM shifts ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shifts - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('hr_sidebar_collapsed') === 'true', modalOpen: false, editMode: false, editId: 0, shiftName: '', startTime: '09:00', endTime: '17:00' }">
    
    <!-- Top Navigation -->
    <?php include 'includes/nav1.php' ?>

    <!-- Main Container -->
    <div class="flex flex-1 h-[calc(100vh-2.75rem)] overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include 'includes/side-nav.php' ?>

        <!-- Content Area -->
        <main class="flex-1 bg-slate-50 p-6 md:p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto space-y-6">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
                    <div>
                        <div class="flex items-center space-x-2 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-business-time"></i>
                            <span>Attendance & Schedule Management</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Work Shifts</h1>
                        <p class="text-sm text-slate-500 mt-1">Configure standard shifts with In & Out punch times for employee scheduling and overtime calculations.</p>
                    </div>

                    <?php if ($_SESSION['role'] == '1'): ?>
                    <button @click="modalOpen = true; editMode = false; shiftName = ''; startTime = '09:00'; endTime = '17:00';" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add New Shift</span>
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (!empty($msg)): ?>
                <div class="p-4 rounded-xl text-sm font-bold flex items-center justify-between <?php echo $msg_type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                    <span><?php echo htmlspecialchars($msg); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <?php endif; ?>

                <!-- Shifts Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-slate-900 text-slate-100">
                                <tr>
                                    <th class="py-3.5 px-4 font-extrabold"># ID</th>
                                    <th class="py-3.5 px-4 font-extrabold">Shift Name</th>
                                    <th class="py-3.5 px-4 font-extrabold text-center">Time In (Start)</th>
                                    <th class="py-3.5 px-4 font-extrabold text-center">Time Out (End)</th>
                                    <th class="py-3.5 px-4 font-extrabold text-center">Shift Hours</th>
                                    <th class="py-3.5 px-4 font-extrabold text-center">Assigned Staff</th>
                                    <?php if ($_SESSION['role'] == '1'): ?>
                                    <th class="py-3.5 px-4 font-extrabold text-right">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if ($shifts_res && $shifts_res->num_rows > 0): ?>
                                    <?php while ($s = $shifts_res->fetch_assoc()): ?>
                                        <?php 
                                        $s_id = $s['id'];
                                        $emp_cnt_res = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE shift_id = $s_id OR shift = '" . $conn->real_escape_string($s['shift_name']) . "'");
                                        $emp_cnt = ($emp_cnt_res && $r = $emp_cnt_res->fetch_assoc()) ? $r['c'] : 0;
                                        ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">#<?php echo $s['id']; ?></td>
                                            <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                                                    <span><?php echo htmlspecialchars($s['shift_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-emerald-700">
                                                <?php echo date('h:i A', strtotime($s['start_time'])); ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-rose-700">
                                                <?php echo date('h:i A', strtotime($s['end_time'])); ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-mono font-extrabold text-slate-700">
                                                <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs border border-indigo-200">
                                                    <?php echo number_format($s['working_hours'], 1); ?> hrs / day
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-extrabold text-slate-600">
                                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs">
                                                    <i class="fa-solid fa-users text-slate-400 mr-1"></i> <?php echo $emp_cnt; ?> Employees
                                                </span>
                                            </td>
                                            <?php if ($_SESSION['role'] == '1'): ?>
                                            <td class="py-3.5 px-4 text-right space-x-2">
                                                <button @click="modalOpen = true; editMode = true; editId = <?php echo $s['id']; ?>; shiftName = '<?php echo addslashes($s['shift_name']); ?>'; startTime = '<?php echo $s['start_time']; ?>'; endTime = '<?php echo $s['end_time']; ?>';" class="p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 transition" title="Edit Shift">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form action="shifts.php" method="post" class="inline" onsubmit="return confirm('Are you sure you want to delete this shift?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="shift_id" value="<?php echo $s['id']; ?>">
                                                    <button type="submit" class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 transition" title="Delete Shift">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-400 font-bold">No shifts configured yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Shift Modal (Create / Edit) -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-slate-200">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-extrabold text-slate-900" x-text="editMode ? 'Edit Work Shift' : 'Add New Work Shift'"></h3>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="shifts.php" method="post" class="space-y-4">
                <input type="hidden" name="action" :value="editMode ? 'edit' : 'create'">
                <input type="hidden" name="shift_id" :value="editId">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Shift Name *</label>
                    <input type="text" name="shift_name" x-model="shiftName" required placeholder="e.g. Morning Shift, General Shift" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Time In (Start) *</label>
                        <input type="time" name="start_time" x-model="startTime" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Time Out (End) *</label>
                        <input type="time" name="end_time" x-model="endTime" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold font-mono">
                    </div>
                </div>

                <div class="pt-2 flex justify-end space-x-3">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition" x-text="editMode ? 'Save Changes' : 'Create Shift'"></button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
