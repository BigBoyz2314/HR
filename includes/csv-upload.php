<?php
// Initialize session and auth check
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

require_once('config.php');
date_default_timezone_set('Asia/Karachi');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $fileName = $_FILES["fileToUpload"]["name"];
    $target_file = $target_dir . time() . '_' . basename($fileName);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileType != "csv") {
        header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("Only CSV files are allowed."));
        exit;
    }

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $file = fopen($target_file, 'r');
        if (!$file) {
            header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("Failed to open uploaded CSV file."));
            exit;
        }

        // Read header row
        $header = fgetcsv($file);
        if (!$header) {
            fclose($file);
            header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("CSV file is empty or invalid."));
            exit;
        }

        // Remove BOM if present in header
        if (isset($header[0])) {
            $header[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $header[0]);
        }

        // Normalize header columns to lowercase for flexible matching
        $headerLower = array_map(function($col) {
            return strtolower(trim($col));
        }, $header);

        // Identify column indices
        $empIdx = -1;
        $deptIdx = -1;
        $dateIdx = -1;
        $timeInIdx = -1;
        $timeOutIdx = -1;

        foreach ($headerLower as $idx => $colName) {
            if ($empIdx === -1 && (strpos($colName, 'employee') !== false || strpos($colName, 'emp') !== false || $colName === 'number' || $colName === 'id' || $colName === 'sno')) {
                $empIdx = $idx;
            }
            if ($deptIdx === -1 && (strpos($colName, 'depart') !== false || strpos($colName, 'unit') !== false || strpos($colName, 'dept') !== false)) {
                $deptIdx = $idx;
            }
            if ($dateIdx === -1 && (strpos($colName, 'date') !== false || $colName === 'dt')) {
                $dateIdx = $idx;
            }
            if ($timeInIdx === -1 && (strpos($colName, 'first punch') !== false || strpos($colName, 'first punc') !== false || strpos($colName, 'time in') !== false || strpos($colName, 'check in') !== false || $colName === 'in')) {
                $timeInIdx = $idx;
            }
            if ($timeOutIdx === -1 && (strpos($colName, 'last punch') !== false || strpos($colName, 'last punc') !== false || strpos($colName, 'time out') !== false || strpos($colName, 'check out') !== false || $colName === 'out')) {
                $timeOutIdx = $idx;
            }
        }

        // Default fallbacks if header detection is partial
        if ($empIdx === -1) $empIdx = 0;
        if ($deptIdx === -1) $deptIdx = 2;
        if ($dateIdx === -1) $dateIdx = 3;
        if ($timeInIdx === -1) $timeInIdx = 5;
        if ($timeOutIdx === -1) $timeOutIdx = 6;

        $insertedCount = 0;
        $updatedCount = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) < 2) continue; // Skip empty lines

            $rawEmp = isset($row[$empIdx]) ? trim($row[$empIdx]) : '';
            $rawDept = isset($row[$deptIdx]) ? trim($row[$deptIdx]) : 'General';
            $rawDate = isset($row[$dateIdx]) ? trim($row[$dateIdx]) : '';
            $rawTimeIn = isset($row[$timeInIdx]) ? trim($row[$timeInIdx]) : '';
            $rawTimeOut = isset($row[$timeOutIdx]) ? trim($row[$timeOutIdx]) : '';

            if (empty($rawEmp) || empty($rawDate)) {
                continue;
            }

            // Parse Date format (Support DD/MM/YYYY e.g. 06/07/2026 or YYYY-MM-DD or MM/DD/YYYY)
            $parsedDate = null;
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawDate)) {
                // DD/MM/YYYY
                $dtObj = DateTime::createFromFormat('d/m/Y', $rawDate);
                if ($dtObj) $parsedDate = $dtObj->format('Y-m-d');
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
                $parsedDate = $rawDate;
            } else {
                $timestamp = strtotime($rawDate);
                if ($timestamp) {
                    $parsedDate = date('Y-m-d', $timestamp);
                }
            }

            if (!$parsedDate) continue;

            $day = intval(date('d', strtotime($parsedDate)));
            $month = intval(date('m', strtotime($parsedDate)));
            $year = intval(date('Y', strtotime($parsedDate)));

            // Resolve employeeID matching sNo / employee_code / employeeID
            $empEsc = $conn->real_escape_string($rawEmp);
            $realEmpID = (is_numeric($rawEmp) && intval($rawEmp) > 0) ? intval($rawEmp) : 0;
            $chkEmp = $conn->query("SELECT employeeID FROM employees WHERE employeeID = '$empEsc' OR sNo = '$empEsc' OR employee_code = '$empEsc' LIMIT 1");
            if ($chkEmp && $chkEmp->num_rows > 0) {
                $eRow = $chkEmp->fetch_assoc();
                $realEmpID = intval($eRow['employeeID']);
            }

            if ($realEmpID <= 0) continue; // Skip if no valid employee match

            // Parse Time In & Time Out into MySQL TIME string (HH:MM:SS)
            $timeInStr = !empty($rawTimeIn) ? date('H:i:s', strtotime($parsedDate . ' ' . $rawTimeIn)) : '09:00:00';
            $timeOutStr = !empty($rawTimeOut) ? date('H:i:s', strtotime($parsedDate . ' ' . $rawTimeOut)) : '17:00:00';

            $deptEsc = $conn->real_escape_string($rawDept);
            $parsedDateEsc = $conn->real_escape_string($parsedDate);
            $updatedBy = 'csv_import';

            // Check if record exists for this employee on this date (using correct 'id' column)
            $checkQuery = "SELECT id FROM attendance1 WHERE employeeID = '$realEmpID' AND date = '$parsedDateEsc' LIMIT 1";
            $checkResult = $conn->query($checkQuery);

            if ($checkResult && $checkResult->num_rows > 0) {
                // Update record
                $cRow = $checkResult->fetch_assoc();
                $attID = $cRow['id'];
                $updateSql = "UPDATE attendance1 SET 
                                time_in = '$timeInStr', 
                                time_out = '$timeOutStr', 
                                unit = '$deptEsc', 
                                updated_at = NOW(), 
                                updated_by = '$updatedBy' 
                              WHERE id = '$attID'";
                if ($conn->query($updateSql)) {
                    $updatedCount++;
                }
            } else {
                // Insert record
                $insertSql = "INSERT INTO attendance1 (employeeID, unit, date, day, month, year, time_in, time_out, created_at, updated_at, updated_by)
                              VALUES ('$realEmpID', '$deptEsc', '$parsedDateEsc', '$day', '$month', '$year', '$timeInStr', '$timeOutStr', NOW(), NOW(), '$updatedBy')";
                if ($conn->query($insertSql)) {
                    $insertedCount++;
                }
            }
        }

        fclose($file);

        $msg = "Attendance Import Complete! Successfully processed $insertedCount new record(s) and updated $updatedCount existing record(s).";
        header("Location: ../upload-attendance.php?status=success&msg=" . urlencode($msg));
        exit;
    } else {
        header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("Failed to move uploaded file."));
        exit;
    }
} else {
    header("Location: ../upload-attendance.php");
    exit;
}
?>
