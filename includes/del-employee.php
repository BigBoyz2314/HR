<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;

if ($id > 0) {
    // Retrieve employee info before deletion
    $empQuery = $conn->query("SELECT fname, lname, sNo, departmentID, photo FROM employees WHERE employeeID = $id LIMIT 1");
    if ($empQuery && $empQuery->num_rows > 0) {
        $empData = $empQuery->fetch_assoc();
        $name = trim($empData['fname'] . ' ' . $empData['lname']);
        $sNo = $empData['sNo'] ?: $id;
        $deptId = intval($empData['departmentID']);
        $photo = $empData['photo'];

        // Optionally delete employee photo file if present
        if (!empty($photo) && file_exists(__DIR__ . '/uploads/' . $photo)) {
            @unlink(__DIR__ . '/uploads/' . $photo);
        }

        // Delete employee record
        $delQuery = $conn->query("DELETE FROM employees WHERE employeeID = $id");

        if ($delQuery) {
            // Decrement department strength if applicable
            if ($deptId > 0) {
                $conn->query("UPDATE department SET current_Strength = GREATEST(0, current_Strength - 1) WHERE departmentID = $deptId");
            }

            header('Location: ../view-employees.php?status=success&msg=' . urlencode("Employee #$sNo ($name) was successfully deleted."));
            exit;
        } else {
            header('Location: ../view-employees.php?status=error&msg=' . urlencode("Unable to delete employee: " . $conn->error));
            exit;
        }
    } else {
        header('Location: ../view-employees.php?status=error&msg=' . urlencode("Employee record not found."));
        exit;
    }
} else {
    header('Location: ../view-employees.php');
    exit;
}
