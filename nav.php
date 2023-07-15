<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                    Employees
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <?php
                        if ($_SESSION['role'] == '1') {
                            echo '<a class="dropdown-item bg-dark text-white-50" href="employees.php">Add Employees</a>';
                        }
                        ?>
                        <a class="dropdown-item bg-dark text-white-50" href="view-employees.php">View All Employees</a>
                        <a class="dropdown-item bg-dark text-white-50" href="view-emp-name.php">View Employees by Filter</a>
                        <a class="dropdown-item bg-dark text-white-50" href="view-emp-month.php">View Employees by Month</a>
                        <?php
                        if ($_SESSION['role'] == '1') {
                            echo '<a class="dropdown-item bg-dark text-white-50" href="view-employee-log.php">View Employees Log</a>';
                        }
                        ?>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="department.php">Departments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="designation.php">Designation</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                        Allow/Deduc
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <?php
                        if ($_SESSION['role'] == '1') {
                            echo '<a class="dropdown-item bg-dark text-white-50" href="allowance.php">Add Allowances</a>';
                            echo '<a class="dropdown-item bg-dark text-white-50" href="deduction.php">Add Deductions</a>';
                        }
                        ?>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                    Payroll
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <?php
                        if ($_SESSION['role'] == '1') {
                            echo '<a class="dropdown-item bg-dark text-white-50" href="gen-salary.php">Generate Salary</a>';
                        }
                        ?>
                        <a class="dropdown-item bg-dark text-white-50" href="salary.php">View Salary</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                    Attendences
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <?php
                        if ($_SESSION['role'] == '1') {
                            echo '<a class="dropdown-item bg-dark text-white-50" href="add-attendance.php">Add Attendance</a>';
                        }
                        ?>
                        <a class="dropdown-item bg-dark text-white-50" href="attendance.php">View Attendance</a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                <a class="nav-link"><?php echo $_SESSION['name']; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Log out</a>
                </li>
            </ul>
        </div>
    </nav>