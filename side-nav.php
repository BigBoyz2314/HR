<div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <em class="nav-link fw-bold fs-4 pb-0 bg-white border-end"><img src="images/Footprint+Logo.png" class="img-fluid"></em>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                                Employees
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <?php
                                    if ($_SESSION['role'] == '1') {
                                        echo '<a class="nav-link" href="employees.php">Add Employees</a>';
                                    }
                                    ?>
                                    <a class="nav-link" href="view-employees.php">View All Employees</a>
                                    <a class="nav-link" href="view-emp-name.php">View Employees by Filter</a>
                                    <a class="nav-link" href="view-emp-month.php">View Employees by Month</a>
                                    <?php
                                    if ($_SESSION['role'] == '1') {
                                        echo '<a class="nav-link" href="view-employee-log.php">View Employees Log</a>';
                                    }
                                    ?>
                                </nav>
                            </div>
                            <a class="nav-link" href="department.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-building"></i></div>
                                Departments
                            </a>
                            <a class="nav-link" href="designation.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                Designation
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts1" aria-expanded="false" aria-controls="collapseLayouts1">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Allow/Deduc
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts1" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <?php
                                    if ($_SESSION['role'] == '1') {
                                        echo '<a class="nav-link" href="allowance.php">Add Allowances</a>';
                                        echo '<a class="nav-link" href="deduction.php">Add Deductions</a>';
                                    }
                                    ?>
                                </nav>
                            </div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts2" aria-expanded="false" aria-controls="collapseLayouts2">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                Payroll
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts2" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <?php
                                    if ($_SESSION['role'] == '1') {
                                        echo '<a class="nav-link" href="gen-salary.php">Generate Salary</a>';
                                    }
                                    ?>
                                    <a class="nav-link" href="salary.php">View Salary</a>
                                </nav>
                            </div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts3" aria-expanded="false" aria-controls="collapseLayouts3">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-clipboard-user"></i></div>
                                Attendences
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts3" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <?php
                                    if ($_SESSION['role'] == '1') {
                                        echo '<a class="nav-link" href="add-attendance.php">Add Attendance</a>';
                                        echo '<a class="nav-link" href="upload-attendance.php">Upload Attendance</a>';
                                        echo '<a class="nav-link" href="gazette.php">Add Gazette Holiday</a>';
                                    }
                                    ?>        
                                    <a class="nav-link" href="attendance.php">View Attendance</a>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?php echo $_SESSION['name']; ?>
                    </div>
                </nav>
            </div>