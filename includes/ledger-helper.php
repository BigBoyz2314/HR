<?php
// Central Employee Ledger Synchronization Helper

/**
 * Recalculate running balance for a given employee in chronological order.
 */
function recalculateEmployeeLedgerBalance($conn, $employeeID) {
    $employeeID = intval($employeeID);
    $res = $conn->query("SELECT id, debit, credit FROM employee_ledger WHERE employeeID = $employeeID ORDER BY trans_date ASC, id ASC");
    if ($res && $res->num_rows > 0) {
        $runningBal = 0.0;
        while ($r = $res->fetch_assoc()) {
            $lid = $r['id'];
            $runningBal = $runningBal + floatval($r['credit']) - floatval($r['debit']);
            $conn->query("UPDATE employee_ledger SET balance = '$runningBal' WHERE id = '$lid'");
        }
    }
}

/**
 * Sync Salary Accrual into Ledger (Handles initial creation & regenerations seamlessly)
 */
function syncSalaryAccrualToLedger($conn, $employeeID, $month, $year, $payableAmount, $createdBy = 'System') {
    $employeeID = intval($employeeID);
    $month = intval($month);
    $year = intval($year);
    $payableAmount = floatval($payableAmount);
    $createdByEsc = $conn->real_escape_string($createdBy);
    $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
    $desc = "Salary Accrued for " . $monthName . " " . $year;

    $lCheck = $conn->query("SELECT id FROM employee_ledger WHERE employeeID = $employeeID AND month = $month AND year = $year AND type = 'Salary Accrued' LIMIT 1");
    if ($lCheck && $lCheck->num_rows > 0) {
        $lRow = $lCheck->fetch_assoc();
        $lid = $lRow['id'];
        $conn->query("UPDATE employee_ledger SET credit = '$payableAmount', description = '$desc' WHERE id = '$lid'");
    } else {
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) VALUES ($employeeID, NOW(), 'Salary Accrued', $month, $year, '$desc', 0.00, '$payableAmount', 0.00, '$createdByEsc')");
    }

    recalculateEmployeeLedgerBalance($conn, $employeeID);
}
?>
