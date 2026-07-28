<?php
// Initialize session and auth check
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

require_once('config.php');
date_default_timezone_set('Asia/Karachi');

// Helper to insert array into MySQL safely with dynamic table schema filtering
function insertEmployeeArray($conn, $data, $table = 'employees') {
    static $schemaCache = [];
    if (!isset($schemaCache[$table])) {
        $colRes = $conn->query("SHOW COLUMNS FROM `$table`");
        $validCols = [];
        if ($colRes) {
            while ($cRow = $colRes->fetch_assoc()) {
                $validCols[] = $cRow['Field'];
            }
        }
        $schemaCache[$table] = $validCols;
    }

    $keys = [];
    $vals = [];
    foreach ($data as $col => $val) {
        if (!empty($schemaCache[$table]) && !in_array($col, $schemaCache[$table])) {
            continue; // Skip fields not present in target table schema
        }
        $keys[] = "`" . $conn->real_escape_string($col) . "`";
        if ($val === null) {
            $vals[] = "NULL";
        } elseif (is_int($val) || is_float($val)) {
            $vals[] = $val;
        } else {
            $vals[] = "'" . $conn->real_escape_string($val) . "'";
        }
    }
    $sql = "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ")";
    return $conn->query($sql);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $fileName = $_FILES["fileToUpload"]["name"];
    $target_file = $target_dir . time() . '_' . basename($fileName);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileType != "csv") {
        header("Location: ../upload-employees.php?status=error&msg=" . urlencode("Only CSV files are allowed."));
        exit;
    }

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $file = fopen($target_file, 'r');
        if (!$file) {
            header("Location: ../upload-employees.php?status=error&msg=" . urlencode("Failed to open CSV file."));
            exit;
        }

        // Read header row
        $header = fgetcsv($file);
        if (!$header) {
            fclose($file);
            header("Location: ../upload-employees.php?status=error&msg=" . urlencode("CSV file is empty."));
            exit;
        }

        // Remove BOM if present in header
        if (isset($header[0])) {
            $header[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $header[0]);
        }

        $headerLower = array_map(function($col) {
            return strtolower(trim($col));
        }, $header);

        // Identify column indices
        $sNoIdx      = -1;
        $nameIdx     = -1;
        $lNameIdx    = -1;
        $deptIdx     = -1;
        $desigIdx    = -1;
        $salaryIdx   = -1;
        $additionIdx = -1;

        foreach ($headerLower as $idx => $colName) {
            if ($sNoIdx === -1 && (strpos($colName, 'emp') !== false || strpos($colName, 'code') !== false || strpos($colName, 'sno') !== false || ($colName === 'no' || $colName === 'no.'))) {
                if (!in_array($colName, ['sr. no.', 'sr. no', 'sr no', 'sr.no.'])) {
                    $sNoIdx = $idx;
                }
            }
            if (in_array($colName, ['employee no.', 'employee no', 'emp no', 'emp. no.', 'emp no.', 'employee_code', 'sno'])) {
                $sNoIdx = $idx;
            }
            if ($nameIdx === -1 && (strpos($colName, 'first') !== false || $colName === 'name' || $colName === 'full name' || strpos($colName, 'employee name') !== false)) {
                $nameIdx = $idx;
            }
            if ($lNameIdx === -1 && strpos($colName, 'last') !== false) {
                $lNameIdx = $idx;
            }
            if ($deptIdx === -1 && (strpos($colName, 'dept') !== false || strpos($colName, 'unit') !== false)) {
                $deptIdx = $idx;
            }
            if ($desigIdx === -1 && (strpos($colName, 'desig') !== false || strpos($colName, 'role') !== false || strpos($colName, 'title') !== false)) {
                $desigIdx = $idx;
            }
            if ($salaryIdx === -1 && (strpos($colName, 'salary') !== false || strpos($colName, 'monthly') !== false || strpos($colName, 'pay') !== false)) {
                $salaryIdx = $idx;
            }
            if ($additionIdx === -1 && (strpos($colName, 'addition') !== false || strpos($colName, 'allowance') !== false || strpos($colName, 'additions') !== false)) {
                $additionIdx = $idx;
            }
        }

        // Default fallbacks if header detection was partial
        if ($nameIdx === -1) {
            for ($i = 0; $i < count($headerLower); $i++) {
                if ($i !== $sNoIdx && $i !== $deptIdx && $i !== $salaryIdx && $i !== $additionIdx) {
                    $nameIdx = $i;
                    break;
                }
            }
        }
        if ($nameIdx === -1) $nameIdx = 0;

        $insertedCount = 0;
        $updatedCount = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) < 1) continue;

            $rawName = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : '';
            if (empty($rawName)) continue;

            $rawEmpNo = ($sNoIdx !== -1 && isset($row[$sNoIdx])) ? trim($row[$sNoIdx]) : '';
            $empNoVal = (is_numeric($rawEmpNo) && intval($rawEmpNo) > 0) ? intval($rawEmpNo) : 0;

            // Handle name splitting
            $rawLName = ($lNameIdx !== -1 && isset($row[$lNameIdx])) ? trim($row[$lNameIdx]) : '';
            if (empty($rawLName) && strpos($rawName, ' ') !== false) {
                $parts = explode(' ', $rawName, 2);
                $rawFName = $parts[0];
                $rawLName = $parts[1];
            } else {
                $rawFName = $rawName;
            }

            $fname = ucwords($rawFName);
            $lname = !empty($rawLName) ? ucwords($rawLName) : 'Employee';

            $deptName = ($deptIdx !== -1 && isset($row[$deptIdx]) && !empty($row[$deptIdx])) ? trim($row[$deptIdx]) : 'General';
            $desigName = ($desigIdx !== -1 && isset($row[$desigIdx]) && !empty($row[$desigIdx])) ? trim($row[$desigIdx]) : 'Staff';
            
            $salary = ($salaryIdx !== -1 && isset($row[$salaryIdx])) ? floatval(preg_replace('/[^0-9.]/', '', $row[$salaryIdx])) : 0.0;
            $additions = ($additionIdx !== -1 && isset($row[$additionIdx])) ? floatval(preg_replace('/[^0-9.]/', '', $row[$additionIdx])) : 0.0;

            // Resolve Department ID or Create
            $departmentID = 0;
            $deptEsc = $conn->real_escape_string($deptName);
            $deptRes = $conn->query("SELECT departmentID FROM department WHERE name LIKE '$deptEsc' LIMIT 1");
            if ($deptRes && $deptRes->num_rows > 0) {
                $drow = $deptRes->fetch_assoc();
                $departmentID = intval($drow['departmentID']);
            } else {
                $conn->query("INSERT INTO department (name, current_Strength) VALUES ('$deptEsc', 0)");
                $departmentID = intval($conn->insert_id);
            }

            // Resolve Designation ID
            $designationID = 0;
            $grade = 'G1';
            $desigEsc = $conn->real_escape_string($desigName);
            $desigRes = $conn->query("SELECT designationID, grade FROM designation WHERE name LIKE '$desigEsc' LIMIT 1");
            if ($desigRes && $desigRes->num_rows > 0) {
                $desRow = $desigRes->fetch_assoc();
                $designationID = intval($desRow['designationID']);
                $grade = $desRow['grade'];
            } else {
                $conn->query("INSERT INTO designation (name, grade) VALUES ('$desigEsc', '$grade')");
                $designationID = intval($conn->insert_id);
            }

            $codeStr = $rawEmpNo !== '' ? $rawEmpNo : '';

            // Check if employee already exists by sNo or employeeID or employee_code
            $existingID = 0;
            if ($empNoVal > 0) {
                $chkRes = $conn->query("SELECT employeeID FROM employees WHERE employeeID = $empNoVal OR sNo = $empNoVal OR employee_code = '$codeStr' LIMIT 1");
                if ($chkRes && $chkRes->num_rows > 0) {
                    $cRow = $chkRes->fetch_assoc();
                    $existingID = intval($cRow['employeeID']);
                }
            }

            if ($existingID > 0) {
                // Update existing record
                $fnameEsc = $conn->real_escape_string($fname);
                $lnameEsc = $conn->real_escape_string($lname);
                $updSql = "UPDATE `employees` SET 
                            `fname` = '$fnameEsc', 
                            `lname` = '$lnameEsc', 
                            `department` = '$deptEsc', 
                            `departmentID` = '$departmentID', 
                            `basic_salary` = '$salary', 
                            `allowance` = '$additions', 
                            `sNo` = IF($empNoVal > 0, $empNoVal, `sNo`), 
                            `employee_code` = '$codeStr' 
                           WHERE `employeeID` = '$existingID'";
                if ($conn->query($updSql)) {
                    $updatedCount++;
                }
            } else {
                // Insert new record using associative array mapping
                $empData = [
                    'sNo'             => $empNoVal > 0 ? $empNoVal : null,
                    'fname'           => $fname,
                    'mname'           => '',
                    'lname'           => $lname,
                    'dob'             => '1990-01-01',
                    'yob'             => '1990',
                    'gender'          => 'Male',
                    'designation'     => $desigName,
                    'designationID'   => $designationID,
                    'grade'           => $grade,
                    'department'      => $deptName,
                    'departmentID'    => $departmentID,
                    'martital_status' => 'Single',
                    'status'          => 'Active',
                    'children'        => 0,
                    'spouse_name'     => '',
                    'basic_salary'    => $salary,
                    'allowance'       => $additions,
                    'join_date'       => date('Y-m-d'),
                    'leave_date'      => '0000-00-00',
                    'primary_address' => '',
                    'secondary_address' => '',
                    'current_address' => '',
                    'primary_number'  => '',
                    'secondary_number' => '',
                    'bank_name'       => '',
                    'bank_account_no' => '',
                    'iban'            => '',
                    'employee_code'   => $codeStr,
                    'manager_name'    => '',
                    'managerID'       => 0,
                    'warnings'        => 0,
                    'leaves'          => 0,
                    'allowed_leaves'  => 14,
                    'absents'         => 0,
                    'presents'        => 0,
                    'days_working'    => 30,
                    'loan'            => '0',
                    'loan_amount'     => 0.0,
                    'cnic'            => '',
                    'passport_no'     => '',
                    'working_hours'   => 8,
                    'start_time'      => '09:00:00',
                    'end_time'        => '17:00:00',
                    'shift'           => 'Day',
                    'join_month'      => date('m'),
                    'leave_month'     => '0',
                    'disability'      => 'No',
                    'photo'           => '',
                    'e_fname'         => '',
                    'e_mname'         => '',
                    'e_lname'         => '',
                    'e_dob'           => '1990-01-01',
                    'e_cnic'          => '',
                    'e_contact'       => '',
                    'e_gender'        => 'Male',
                    'e_address'       => '',
                    'e_emp'           => $codeStr
                ];

                if ($empNoVal > 0) {
                    $empData['employeeID'] = $empNoVal;
                }

                if (insertEmployeeArray($conn, $empData, 'employees')) {
                    $last_id = intval($conn->insert_id);
                    
                    // Sync sNo if NULL
                    $conn->query("UPDATE `employees` SET `sNo` = '$last_id' WHERE `employeeID` = '$last_id' AND (`sNo` IS NULL OR `sNo` = 0)");

                    $logData = $empData;
                    $logData['employeeID'] = $last_id;
                    $logData['sNo'] = $last_id;
                    insertEmployeeArray($conn, $logData, 'employees_log');

                    if ($departmentID > 0) {
                        $conn->query("UPDATE department SET current_Strength = current_Strength + 1 WHERE departmentID = $departmentID");
                    }
                    $insertedCount++;
                }
            }
        }

        fclose($file);

        $msg = "Import Completed! Created $insertedCount new employee(s) and updated $updatedCount existing employee(s).";
        header("Location: ../upload-employees.php?status=success&msg=" . urlencode($msg));
        exit;
    } else {
        header("Location: ../upload-employees.php?status=error&msg=" . urlencode("Failed to move uploaded file."));
        exit;
    }
} else {
    header("Location: ../upload-employees.php");
    exit;
}
?>
