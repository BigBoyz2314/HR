<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$empActive = in_array($currentPage, ['employees.php', 'view-employees.php', 'view-emp-name.php', 'view-emp-month.php', 'view-employee-log.php', 'upload-employees.php']);
$allowDeducActive = in_array($currentPage, ['allowance.php', 'deduction.php']);
$payrollActive = in_array($currentPage, ['gen-salary.php', 'salary.php', 'view-salary.php', 'edit-salary.php']);
$attendanceActive = in_array($currentPage, ['attendance.php', 'add-attendance.php', 'upload-attendance.php', 'gazette.php', 'view-attendance.php']);
?>

<aside :class="sidebarCollapsed ? 'w-16' : 'w-64'" 
       class="h-full bg-slate-900 text-slate-100 flex flex-col shrink-0 shadow-2xl border-r border-slate-800 select-none z-30 transition-all duration-300 relative">
  
  <!-- Brand Header -->
  <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between bg-slate-950/40 min-h-[3.25rem]">
    <a href="index.php" class="flex items-center space-x-3 group mx-auto md:mx-0">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center shadow-md shadow-indigo-500/30 group-hover:scale-105 transition-transform shrink-0">
        <i class="fa-solid fa-users text-white text-xs"></i>
      </div>
      <div x-show="!sidebarCollapsed" class="transition-opacity duration-200">
        <span class="text-sm font-bold tracking-tight text-white block leading-tight">Footprint HR</span>
        <span class="text-[9px] font-semibold tracking-wider text-indigo-400 uppercase">Portal v2.0</span>
      </div>
    </a>
  </div>

  <!-- Navigation Links -->
  <nav class="flex-1 px-2.5 py-3 space-y-1.5 overflow-y-auto custom-scrollbar">
    
    <!-- Dashboard -->
    <a href="index.php" 
       :title="sidebarCollapsed ? 'Dashboard' : ''"
       class="flex items-center px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $currentPage == 'index.php' ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
      <i class="fas fa-tachometer-alt w-5 text-center shrink-0 <?php echo $currentPage == 'index.php' ? 'text-white' : 'text-indigo-400'; ?>"></i>
      <span x-show="!sidebarCollapsed" class="ml-3 truncate">Dashboard</span>
    </a>

    <!-- Employees Section -->
    <div x-data="{ open: <?php echo $empActive ? 'true' : 'false'; ?> }" class="space-y-1">
      <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; } else { open = !open; }" 
              :title="sidebarCollapsed ? 'Employees' : ''"
              class="flex items-center justify-between w-full px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $empActive ? 'bg-slate-800/90 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
        <div class="flex items-center">
          <i class="fa-solid fa-user-group w-5 text-center text-blue-400 shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="ml-3 truncate">Employees</span>
        </div>
        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
      </button>
      
      <!-- Submenu -->
      <div x-show="open && !sidebarCollapsed" x-collapse class="pl-8 pr-1 py-1 space-y-1">
        <?php if ($_SESSION['role'] == '1'): ?>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'employees.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="employees.php">Add Employee</a>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'upload-employees.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="upload-employees.php">
            <span class="flex items-center justify-between">
              <span>Import CSV</span>
              <span class="px-1 py-0.5 text-[8px] bg-emerald-500/20 text-emerald-400 rounded font-semibold">Bulk</span>
            </span>
          </a>
        <?php endif; ?>
        <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'view-employees.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="view-employees.php">View & Filter Staff</a>
        <?php if ($_SESSION['role'] == '1'): ?>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'view-employee-log.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="view-employee-log.php">Audit Logs</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Departments -->
    <a href="department.php" 
       :title="sidebarCollapsed ? 'Departments' : ''"
       class="flex items-center px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $currentPage == 'department.php' ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
      <i class="fa-solid fa-building w-5 text-center shrink-0 <?php echo $currentPage == 'department.php' ? 'text-white' : 'text-cyan-400'; ?>"></i>
      <span x-show="!sidebarCollapsed" class="ml-3 truncate">Departments</span>
    </a>

    <!-- Designations -->
    <a href="designation.php" 
       :title="sidebarCollapsed ? 'Designations' : ''"
       class="flex items-center px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $currentPage == 'designation.php' ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-600/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
      <i class="fas fa-sitemap w-5 text-center shrink-0 <?php echo $currentPage == 'designation.php' ? 'text-white' : 'text-amber-400'; ?>"></i>
      <span x-show="!sidebarCollapsed" class="ml-3 truncate">Designations</span>
    </a>

    <!-- Allowances / Deductions -->
    <div x-data="{ open: <?php echo $allowDeducActive ? 'true' : 'false'; ?> }" class="space-y-1">
      <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; } else { open = !open; }" 
              :title="sidebarCollapsed ? 'Allow & Deduc' : ''"
              class="flex items-center justify-between w-full px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $allowDeducActive ? 'bg-slate-800/90 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
        <div class="flex items-center">
          <i class="fas fa-sliders-h w-5 text-center text-purple-400 shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="ml-3 truncate">Allow & Deduc</span>
        </div>
        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
      </button>
      <div x-show="open && !sidebarCollapsed" x-collapse class="pl-8 pr-1 py-1 space-y-1">
        <?php if ($_SESSION['role'] == '1'): ?>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'allowance.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="allowance.php">Allowances</a>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'deduction.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="deduction.php">Deductions</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Attendance Section -->
    <div x-data="{ open: <?php echo $attendanceActive ? 'true' : 'false'; ?> }" class="space-y-1">
      <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; } else { open = !open; }" 
              :title="sidebarCollapsed ? 'Attendance' : ''"
              class="flex items-center justify-between w-full px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $attendanceActive ? 'bg-slate-800/90 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
        <div class="flex items-center">
          <i class="fa-solid fa-calendar-check w-5 text-center text-emerald-400 shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="ml-3 truncate">Attendance</span>
        </div>
        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
      </button>
      <div x-show="open && !sidebarCollapsed" x-collapse class="pl-8 pr-1 py-1 space-y-1">
        <?php if ($_SESSION['role'] == '1'): ?>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'add-attendance.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="add-attendance.php">Manual Entry</a>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'upload-attendance.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="upload-attendance.php">
            <span class="flex items-center justify-between">
              <span>Import Machine CSV</span>
              <span class="px-1 py-0.5 text-[8px] bg-indigo-500/20 text-indigo-400 rounded font-semibold">CSV</span>
            </span>
          </a>
          <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'gazette.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="gazette.php">Gazette Holidays</a>
        <?php endif; ?>
        <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'attendance.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="attendance.php">View Records</a>
      </div>
    </div>

    <!-- Payroll Section -->
    <div x-data="{ open: <?php echo $payrollActive ? 'true' : 'false'; ?> }" class="space-y-1">
      <button @click="if (sidebarCollapsed) { sidebarCollapsed = false; open = true; } else { open = !open; }" 
              :title="sidebarCollapsed ? 'Payroll' : ''"
              class="flex items-center justify-between w-full px-3 py-2 rounded-xl text-xs font-medium transition-all duration-200 <?php echo $payrollActive ? 'bg-slate-800/90 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'; ?>">
        <div class="flex items-center">
          <i class="fa-solid fa-money-bill-wave w-5 text-center text-teal-400 shrink-0"></i>
          <span x-show="!sidebarCollapsed" class="ml-3 truncate">Payroll</span>
        </div>
        <i x-show="!sidebarCollapsed" class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
      </button>
      <div x-show="open && !sidebarCollapsed" x-collapse class="pl-8 pr-1 py-1 space-y-1">
        <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'salary.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="salary.php">Salary Sheets</a>
        <a class="block px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition <?php echo $currentPage == 'employee-ledger.php' ? 'bg-indigo-600/30 text-indigo-300 border-l-2 border-indigo-500 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'; ?>" href="employee-ledger.php">
          <span class="flex items-center justify-between">
            <span>Employee Ledgers</span>
            <span class="px-1 py-0.5 text-[8px] bg-indigo-500/20 text-indigo-400 rounded font-semibold">Ledger</span>
          </span>
        </a>
      </div>
    </div>

  </nav>

  <!-- User Profile Footer -->
  <div class="p-2 border-t border-slate-800 bg-slate-950/60">
    <div class="flex items-center justify-between bg-slate-800/50 p-2 rounded-xl border border-slate-700/50">
      <div class="flex items-center space-x-2.5 truncate">
        <div class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-500/30">
          <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
        </div>
        <div x-show="!sidebarCollapsed" class="truncate">
          <div class="text-[11px] font-semibold text-white truncate"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></div>
          <div class="text-[9px] text-slate-400"><?php echo $_SESSION['role'] == '1' ? 'Admin' : 'User'; ?></div>
        </div>
      </div>
      <a href="includes/logout.php" title="Logout" x-show="!sidebarCollapsed" class="w-6 h-6 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 flex items-center justify-center transition shrink-0">
        <i class="fa-solid fa-power-off text-[10px]"></i>
      </a>
    </div>
  </div>
</aside>

<!-- Alpine.js collapse plugin & core for smooth transitions -->
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>