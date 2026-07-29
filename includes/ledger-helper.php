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
 * Helper to sync a specific ledger component (Salary Accrued or Overtime Accrued)
 */
function syncLedgerComponent($conn, $employeeID, $month, $year, $targetAmount, $typePrefix, $baseDescription, $createdByEsc) {
    $netAccrued = 0.0;
    $hasEntries = false;
    $typePattern = $conn->real_escape_string($typePrefix) . '%';
    $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
    
    $sumRes = $conn->query("SELECT SUM(credit - debit) AS total_accrued, COUNT(*) as cnt FROM employee_ledger WHERE employeeID = $employeeID AND month = $month AND year = $year AND type LIKE '$typePattern'");
    if ($sumRes && $sumRow = $sumRes->fetch_assoc()) {
        $netAccrued = floatval($sumRow['total_accrued'] ?? 0);
        $hasEntries = intval($sumRow['cnt'] ?? 0) > 0;
    }

    $diff = round($targetAmount - $netAccrued, 2);

    if (!$hasEntries && $targetAmount > 0) {
        // Initial entry
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) 
                      VALUES ($employeeID, NOW(), '$typePrefix', $month, $year, '$baseDescription', 0.00, '$targetAmount', 0.00, '$createdByEsc')");
    } elseif ($hasEntries && $diff > 0) {
        // Additional adjustment entry
        $adjType = $typePrefix . ' Adjustment';
        $adjDesc = $typePrefix . " Adjustment for " . $monthName . " " . $year;
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) 
                      VALUES ($employeeID, NOW(), '$adjType', $month, $year, '$adjDesc', 0.00, '$diff', 0.00, '$createdByEsc')");
    } elseif ($hasEntries && $diff < 0) {
        // Reduction adjustment entry
        $debitDiff = abs($diff);
        $adjType = $typePrefix . ' Adjustment';
        $adjDesc = $typePrefix . " Reduction for " . $monthName . " " . $year;
        $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) 
                      VALUES ($employeeID, NOW(), '$adjType', $month, $year, '$adjDesc', '$debitDiff', 0.00, 0.00, '$createdByEsc')");
    }
}

/**
 * Sync Salary & Overtime Accruals into Ledger
 */
function syncSalaryAccrualToLedger($conn, $employeeID, $month, $year, $payableAmount, $createdBy = 'System', $overtimeAmount = 0.0) {
    $employeeID = intval($employeeID);
    $month = intval($month);
    $year = intval($year);
    $payableAmount = floatval($payableAmount);
    $overtimeAmount = floatval($overtimeAmount);
    $createdByEsc = $conn->real_escape_string($createdBy);
    $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

    $baseSalaryPayable = max(0, $payableAmount - $overtimeAmount);

    // 1. Sync Base Salary Accrual
    syncLedgerComponent($conn, $employeeID, $month, $year, $baseSalaryPayable, 'Salary Accrued', 'Salary Accrued for ' . $monthName . ' ' . $year, $createdByEsc);

    // 2. Sync Overtime Accrual
    if ($overtimeAmount > 0 || hasExistingLedgerComponent($conn, $employeeID, $month, $year, 'Overtime Accrued')) {
        syncLedgerComponent($conn, $employeeID, $month, $year, $overtimeAmount, 'Overtime Accrued', 'Overtime Accrued for ' . $monthName . ' ' . $year, $createdByEsc);
    }

    recalculateEmployeeLedgerBalance($conn, $employeeID);
}

function hasExistingLedgerComponent($conn, $employeeID, $month, $year, $typePrefix) {
    $typePattern = $conn->real_escape_string($typePrefix) . '%';
    $res = $conn->query("SELECT id FROM employee_ledger WHERE employeeID = $employeeID AND month = $month AND year = $year AND type LIKE '$typePattern' LIMIT 1");
    return ($res && $res->num_rows > 0);
}
?>
