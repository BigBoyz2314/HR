<link rel="stylesheet" href="css/styles.css">
<nav class="bg-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <div class="flex items-center">
        <a href="index.php" class="text-white font-bold text-xl">Home</a>
        <div class="hidden md:block ml-10 flex items-baseline space-x-4">
          <div class="relative group">
            <button class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium focus:outline-none">Employees</button>
            <div class="absolute left-0 mt-2 w-48 bg-gray-700 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
              <?php
              if ($_SESSION['role'] == '1') {
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="employees.php">Add Employees</a>';
              }
              ?>
              <a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="view-employees.php">View All Employees</a>
              <a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="view-emp-name.php">View Employees by Filter</a>
              <a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="view-emp-month.php">View Employees by Month</a>
              <?php
              if ($_SESSION['role'] == '1') {
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="view-employee-log.php">View Employees Log</a>';
              }
              ?>
            </div>
          </div>
          <a href="department.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Departments</a>
          <a href="designation.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Designation</a>
          <div class="relative group">
            <button class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium focus:outline-none">Allow/Deduc</button>
            <div class="absolute left-0 mt-2 w-48 bg-gray-700 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
              <?php
              if ($_SESSION['role'] == '1') {
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="allowance.php">Add Allowances</a>';
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="deduction.php">Add Deductions</a>';
              }
              ?>
            </div>
          </div>
          <div class="relative group">
            <button class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium focus:outline-none">Payroll</button>
            <div class="absolute left-0 mt-2 w-48 bg-gray-700 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
              <?php
              if ($_SESSION['role'] == '1') {
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="gen-salary.php">Generate Salary</a>';
              }
              ?>
              <a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="salary.php">View Salary</a>
            </div>
          </div>
          <div class="relative group">
            <button class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium focus:outline-none">Attendences</button>
            <div class="absolute left-0 mt-2 w-48 bg-gray-700 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10">
              <?php
              if ($_SESSION['role'] == '1') {
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="add-attendance.php">Add Attendance</a>';
                echo '<a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="gazette.php">Add Gazette Holiday</a>';
              }
              ?>
              <a class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600" href="attendance.php">View Attendance</a>
            </div>
          </div>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="text-gray-300 px-3 py-2 rounded-md text-sm font-medium"><?php echo $_SESSION['name']; ?></span>
        <a href="includes/logout.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Log out</a>
      </div>
    </div>
  </div>
</nav>