<?php
session_start();
require_once('config.php');
require_once('ledger-helper.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $salID        = isset($_POST['salID']) ? intval($_POST['salID']) : 0;
    $empID        = isset($_POST['empID']) ? intval($_POST['empID']) : 0;
    $month        = isset($_POST['month']) ? intval($_POST['month']) : date('m');
    $year         = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
    
    $arrears      = isset($_POST['arrears']) ? floatval($_POST['arrears']) : 0.0;
    $ot_1_15      = isset($_POST['ot_1_15']) ? floatval($_POST['ot_1_15']) : 0.0;
    $ot_16_30     = isset($_POST['ot_16_30']) ? floatval($_POST['ot_16_30']) : 0.0;
    $allowance    = isset($_POST['allowance']) ? floatval($_POST['allowance']) : 0.0;
    $less_loans   = isset($_POST['less_loans']) ? floatval($_POST['less_loans']) : 0.0;
    $less_advance = isset($_POST['less_advance']) ? floatval($_POST['less_advance']) : 0.0;

    $whereClause = ($salID > 0) ? "id = $salID" : "employeeID = $empID AND month = $month AND year = $year";
    $res = $conn->query("SELECT * FROM salary1 WHERE $whereClause LIMIT 1");

    if ($res && $row = $res->fetch_assoc()) {
        $actualSalID = $row['id'];
        $basic = floatval($row['basic_salary']);
        $payDays = intval($row['pay_days']);
        $paid = floatval($row['paid']);
        
        $earned_basic = ($payDays / 30.0) * $basic;
        $totalDeductions = $less_loans + $less_advance;
        $newPayable = max(0, $earned_basic + $arrears + $ot_1_15 + $ot_16_30 + $allowance - $totalDeductions);
        $newRemaining = max(0, $newPayable - $paid);

        $upd = "UPDATE salary1 SET 
                arrears = '$arrears',
                ot_1_15 = '$ot_1_15',
                ot_16_30 = '$ot_16_30',
                allowance = '$allowance',
                deduction = '$totalDeductions',
                less_loans = '$less_loans',
                less_advance = '$less_advance',
                payable = '$newPayable',
                remaining = '$newRemaining',
                updated_at = NOW()
                WHERE id = $actualSalID";
        
        if ($conn->query($upd)) {
            $empNameEsc = $conn->real_escape_string($_SESSION['name'] ?? 'Admin');
            $conn->query("INSERT INTO salary_log1 (employeeID, fname, mname, lname, designation, department, gender, basic_salary, allowance, deduction, gross_salary, month, year, total_days, pay_days, absent, arrears, ot_1_15, ot_16_30, less_loans, less_advance, payable, paid, remaining, created_at, updated_at, updated_by)
                          VALUES ('{$row['employeeID']}', '{$row['fname']}', '{$row['mname']}', '{$row['lname']}', '{$row['designation']}', '{$row['department']}', '{$row['gender']}', '$basic', '$allowance', '$totalDeductions', '{$row['gross_salary']}', '$month', '$year', '{$row['total_days']}', '$payDays', '{$row['absent']}', '$arrears', '$ot_1_15', '$ot_16_30', '$less_loans', '$less_advance', '$newPayable', '$paid', '$newRemaining', NOW(), NOW(), '$empNameEsc')");

            // Sync with central Employee Ledger
            syncSalaryAccrualToLedger($conn, $row['employeeID'], $month, $year, $newPayable, $_SESSION['name'] ?? 'Admin');

            header("Location: ../view-salary.php?month=$month&year=$year&action=updated");
            exit;
        }
    }
}
header("Location: ../view-salary.php");
exit;
?>
