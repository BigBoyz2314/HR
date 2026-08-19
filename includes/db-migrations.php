<?php
// Automatic Production Database Migration Runner
function runAutoMigrations($conn) {
    if (!$conn) return;

    // 1. Shifts Table
    $conn->query("CREATE TABLE IF NOT EXISTS `shifts` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `shift_name` VARCHAR(100) NOT NULL,
      `start_time` TIME NOT NULL DEFAULT '09:00:00',
      `end_time` TIME NOT NULL DEFAULT '17:00:00',
      `working_hours` DECIMAL(5,2) NOT NULL DEFAULT 8.00,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed General Shift if empty
    $sRes = $conn->query("SELECT id FROM shifts WHERE shift_name = 'General Shift' LIMIT 1");
    if (!$sRes || $sRes->num_rows == 0) {
        $conn->query("INSERT INTO shifts (id, shift_name, start_time, end_time, working_hours) VALUES (1, 'General Shift', '09:00:00', '17:00:00', 8.00)");
    }

    // 2. Overtime Table
    $conn->query("CREATE TABLE IF NOT EXISTS `overtime` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `employeeID` INT NOT NULL,
      `month` INT NOT NULL,
      `year` INT NOT NULL,
      `hours` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `remarks` VARCHAR(255) DEFAULT '',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY `emp_month_year` (`employeeID`, `month`, `year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Leaves Table
    $conn->query("CREATE TABLE IF NOT EXISTS `leaves` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `employeeID` INT NOT NULL,
      `leave_type` VARCHAR(50) NOT NULL DEFAULT 'Casual Leave',
      `start_date` DATE NOT NULL,
      `end_date` DATE NOT NULL,
      `total_days` INT NOT NULL DEFAULT 1,
      `reason` TEXT DEFAULT NULL,
      `status` VARCHAR(20) NOT NULL DEFAULT 'Approved',
      `applied_by` VARCHAR(100) DEFAULT 'Admin',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `emp_dates` (`employeeID`, `start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Employees Table Schema Columns Check
    $empCols = [];
    $colRes = $conn->query("SHOW COLUMNS FROM employees");
    if ($colRes) {
        while ($c = $colRes->fetch_assoc()) {
            $empCols[] = strtolower($c['Field']);
        }
    }

    if (!in_array('shift_id', $empCols)) {
        $conn->query("ALTER TABLE employees ADD COLUMN shift_id INT DEFAULT 1");
    }
    if (!in_array('overtime_rate', $empCols)) {
        $conn->query("ALTER TABLE employees ADD COLUMN overtime_rate DECIMAL(10,2) DEFAULT 0.00");
    }

    // 5. Employees Log Table Schema Columns Check
    $logCols = [];
    $logRes = $conn->query("SHOW COLUMNS FROM employees_log");
    if ($logRes) {
        while ($c = $logRes->fetch_assoc()) {
            $logCols[] = strtolower($c['Field']);
        }
    }

    if (!in_array('shift_id', $logCols)) {
        $conn->query("ALTER TABLE employees_log ADD COLUMN shift_id INT DEFAULT 1");
    }
    if (!in_array('overtime_rate', $logCols)) {
        $conn->query("ALTER TABLE employees_log ADD COLUMN overtime_rate DECIMAL(10,2) DEFAULT 0.00");
    }
}
