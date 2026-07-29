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
 * Sync Salary Accrual into Ledger (Creates a new distinct ledger entry for every accrual or adjustment event)
 */
function syncSalaryAccrualToLedger($conn, $employeeID, $month, $year, $payableAmount, $createdBy = 'System') {
    $employeeID = intval($employeeID);
    $month = intval($month);
    $year = intval($year);
    $payableAmount = floatval($payableAmount);
    $createdByEsc = $conn->real_escape_string($createdBy);
    $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

    // Calculate total net accrued currently recorded in ledger for this month's salary
    $netAccrued = 0.0;
    $hasEntries = false;
    $sumRes = $conn->query("SELECT SUM(credit - debit) AS total_accrued, COUNT(*) as cnt FROM employee_ledger WHERE employeeID = $employeeID AND month = $month AND year = $year AND type LIKE 'Salary Accrued%'");
    if ($sumRes && $sumRow = $sumRes->fetch_assoc()) {
        $netAccrued = floatval($sumRow['total_accrued'] ?? 0);
        $hasEntries = intval($sumRow['cnt'] ?? 0) > 0;
    }

    $diff = round($payableAmount - $netAccrued, 2);

    if (!$hasEntries && $payableAmount > 0) {
        // Initial Accrual Entry for the month
        $desc = "Salary Accrued for " . $monthName . " " . $year;
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) 
                      VALUES ($employeeID, NOW(), 'Salary Accrued', $month, $year, '$desc', 0.00, '$payableAmount', 0.00, '$createdByEsc')");
    } elseif ($hasEntries && $diff > 0) {
        // Additional Accrual / Adjustment Entry for each new increase
        $desc = "Salary Accrual Adjustment for " . $monthName . " " . $year;
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) 
                      VALUES ($employeeID, NOW(), 'Salary Accrued Adjustment', $month, $year, '$desc', 0.00, '$diff', 0.00, '$createdByEsc')");
    } elseif ($hasEntries && $diff < 0) {
        // Reduced Accrual Adjustment Entry
        $debitDiff = abs($diff);
        $desc = "Salary Accrual Reduction for " . $monthName . " " . $year;
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) 
                      VALUES ($employeeID, NOW(), 'Salary Accrued Adjustment', $month, $year, '$desc', '$debitDiff', 0.00, 0.00, '$createdByEsc')");
    }

    recalculateEmployeeLedgerBalance($conn, $employeeID);
}
?>
