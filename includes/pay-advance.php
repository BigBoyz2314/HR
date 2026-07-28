<?php
require_once('config.php');
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

$userName = $_SESSION['name'] ?? 'Admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empID    = isset($_POST['employeeID']) ? intval($_POST['employeeID']) : 0;
    $amount   = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
    $type     = isset($_POST['type']) ? trim($_POST['type']) : 'Advance Payment';
    $month    = isset($_POST['month']) ? intval($_POST['month']) : date('n');
    $year     = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
    $method   = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Cash';
    $notes    = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if ($empID <= 0 || $amount <= 0) {
        header("Location: ../employee-ledger.php?status=error&msg=" . urlencode("Invalid payment details."));
        exit;
    }

    // Get previous running balance
    $prevBalance = 0.0;
    $balRes = $conn->query("SELECT balance FROM employee_ledger WHERE employeeID = '$empID' ORDER BY id DESC LIMIT 1");
    if ($balRes && $balRes->num_rows > 0) {
        $bRow = $balRes->fetch_assoc();
        $prevBalance = floatval($bRow['balance']);
    }

    // Debit reduces payable balance (or increases advance overpaid)
    $newBalance = $prevBalance - $amount;

    $stmt = $conn->prepare("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, payment_method, created_by) VALUES (?, NOW(), ?, ?, ?, ?, ?, 0.00, ?, ?, ?)");
    $stmt->bind_param("isissddss", $empID, $type, $month, $year, $notes, $amount, $newBalance, $method, $userName);

    if ($stmt->execute()) {
        $ledgerId = $stmt->insert_id;
        $stmt->close();

        require_once('ledger-helper.php');
        recalculateEmployeeLedgerBalance($conn, $empID);

        // Also update salary1 table if a salary record exists for this month/year
        $salRes = $conn->query("SELECT * FROM salary1 WHERE employeeID = '$empID' AND month = '$month' AND year = '$year'");
        if ($salRes && $salRes->num_rows > 0) {
            $sRow = $salRes->fetch_assoc();
            $payable = floatval($sRow['payable']);
            $currentPaid = floatval($sRow['paid']);
            $newPaid = $currentPaid + $amount;
            $newRem = max(0, $payable - $newPaid);

            $conn->query("UPDATE salary1 SET paid = '$newPaid', remaining = '$newRem', updated_at = NOW() WHERE employeeID = '$empID' AND month = '$month' AND year = '$year'");
        }

        // Redirect directly to thermal voucher print
        header("Location: ../print-voucher.php?id=" . $ledgerId);
        exit;
    } else {
        echo "Error saving advance payment: " . $conn->error;
    }
} else {
    header("Location: ../employee-ledger.php");
    exit;
}
