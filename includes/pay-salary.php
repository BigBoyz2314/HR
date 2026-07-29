<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}


$userName = $_SESSION['name'] ?? 'Admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;
    $pay = isset($_REQUEST["pay"]) ? floatval($_REQUEST["pay"]) : 0.0;
    $year = isset($_REQUEST["year"]) ? intval($_REQUEST["year"]) : date('Y');
    $month = isset($_REQUEST["month"]) ? intval($_REQUEST["month"]) : date('n');

    if ($id <= 0 || $pay <= 0) {
        header("Location: ../view-salary.php?month=$month&year=$year&status=error&msg=" . urlencode("Invalid payment amount specified."));
        exit;
    }

    // Fetch existing salary record
    $stmt = "SELECT * FROM salary1 WHERE employeeID = '$id' AND month = '$month' AND year = '$year'";
    $result = $conn->query($stmt);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $payable = floatval($row['payable']);
        $currentPaid = floatval($row['paid']);

        // Allow paying full amount, including overpayments (paying more than salary in sheet)
        $newPaid = $currentPaid + $pay;
        $newRemaining = $payable - $newPaid;

        // Update salary1 table
        $sql = "UPDATE `salary1` SET `paid` = '$newPaid', `remaining` = '$newRemaining', `updated_at` = current_timestamp() WHERE `employeeID` = '$id' AND `month` = '$month' AND `year` = '$year'";
        
        if (mysqli_query($conn, $sql)) {
            // Write to audit log
            $fname = $row['fname'];
            $mname = $row['mname'];
            $lname = $row['lname'];
            $desig = $row['designation'];
            $dept = $row['department'];
            $gender = $row['gender'];
            $paydays = $row['pay_days'];
            $totaldays = $row['total_days'];
            $basic = $row['basic_salary'];
            $allowance = $row['allowance'];
            $deduction = $row['deduction'];
            $gross = $row['gross_salary'];
            $absent = $row['absent'];

            $sql1 = "INSERT IGNORE INTO `salary_log1`(`employeeID`,`fname`, `mname`, `lname`, `designation`, `department`, `gender`, `basic_salary`, `allowance`, `deduction`, `gross_salary`, `month`, `year`, `total_days`, `pay_days`, `absent`, `payable`, `paid`, `remaining`, `created_at`, `updated_at`, `updated_by`)
            VALUES ('$id','$fname','$mname','$lname','$desig','$dept','$gender','$basic','$allowance','$deduction','$gross','$month','$year','$totaldays','$paydays','$absent','$payable','$newPaid','$newRemaining',current_timestamp(),current_timestamp(),'$userName')";
           
            mysqli_query($conn, $sql1);

            // Record full Payment Debit in employee_ledger
            require_once('ledger-helper.php');
            $conn->query("INSERT INTO employee_ledger (employeeID, trans_date, type, month, year, description, debit, credit, balance, created_by) VALUES ('$id', NOW(), 'Salary Payment', '$month', '$year', 'Salary Payment', '$pay', 0.00, 0.00, '$userName')");
            $vId = $conn->insert_id;

            recalculateEmployeeLedgerBalance($conn, $id);

            header("Location: ../print-voucher.php?id=" . $vId);
            exit;
        } else {
            echo "ERROR updating salary: " . mysqli_error($conn);
        }
    } else {
        header("Location: ../salary.php?status=error&msg=" . urlencode("Salary record not found for Employee #$id."));
        exit;
    }
} else {
    header("Location: ../salary.php");
    exit;
}
?>