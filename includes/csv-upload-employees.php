<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Karachi');

// Helper to convert Excel date (serial number or formatted string) to YYYY-MM-DD
function excelDateToYmd($val, $default = '1990-01-01') {
    $val = trim((string)$val);
    if ($val === '') return $default;

    // Excel numeric serial date (e.g. 29587 or 46272)
    if (is_numeric($val) && (float)$val > 1000) {
        $days = (float)$val;
        // Excel 1900 date system epoch adjustment
        $timestamp = round(($days - 25569) * 86400);
        return gmdate('Y-m-d', $timestamp);
    }

    // String date e.g. 20-1-1980 or 20/01/1980 or 1980-01-20
    $val = str_replace('/', '-', $val);
    $parts = explode('-', $val);
    if (count($parts) === 3) {
        if (strlen($parts[2]) === 4) {
            $day = intval($parts[0]);
            $month = intval($parts[1]);
            $year = intval($parts[2]);
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        } elseif (strlen($parts[0]) === 4) {
            return date('Y-m-d', strtotime($val));
        }
    }

    $time = strtotime($val);
    if ($time !== false && $time > 0) {
        return date('Y-m-d', $time);
    }

    return $default;
}

// Helper to parse XLSX files natively using ZipArchive and SimpleXML
function parseXLSX($filePath) {
    if (!class_exists('ZipArchive')) {
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return false;
    }

    // 1. Shared Strings Table
    $sharedStrings = [];
    $sstXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sstXml !== false) {
        $xml = simplexml_load_string($sstXml);
        if ($xml && isset($xml->si)) {
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $t = '';
                    foreach ($si->r as $r) {
                        $t .= (string)$r->t;
                    }
                    $sharedStrings[] = $t;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }
    }

    // 2. Worksheet sheet1.xml
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml === false) {
        $zip->close();
        return false;
    }

    $xml = simplexml_load_string($sheetXml);
    if (!$xml || !isset($xml->sheetData)) {
        $zip->close();
        return false;
    }

    $rows = [];
    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            preg_match('/^([A-Z]+)/', $ref, $matches);
            $colLetter = $matches[1] ?? 'A';
            $colIdx = 0;
            for ($i = 0; $i < strlen($colLetter); $i++) {
                $colIdx = $colIdx * 26 + (ord($colLetter[$i]) - ord('A') + 1);
            }
            $colIdx -= 1;

            $type = (string)$c['t'];
            $val = isset($c->v) ? (string)$c->v : '';

            if ($type === 's' && isset($sharedStrings[(int)$val])) {
                $val = $sharedStrings[(int)$val];
            } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                $val = (string)$c->is->t;
            }

            $rowData[$colIdx] = trim((string)$val);
        }

        if (!empty($rowData)) {
            $maxCol = max(array_keys($rowData));
            $denseRow = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $denseRow[$i] = $rowData[$i] ?? '';
            }
            if (count(array_filter($denseRow, 'strlen')) > 0) {
                $rows[] = $denseRow;
            }
        }
    }

    $zip->close();
    return $rows;
}

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

    if (!in_array($fileType, ['csv', 'xlsx'])) {
        header("Location: ../upload-employees.php?status=error&msg=" . urlencode("Only Excel (.xlsx) or CSV files are allowed."));
        exit;
    }

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $rows = [];

        if ($fileType === 'xlsx') {
            $rows = parseXLSX($target_file);
            if ($rows === false || empty($rows)) {
                header("Location: ../upload-employees.php?status=error&msg=" . urlencode("Failed to parse XLSX file or file is empty."));
                exit;
            }
        } else {
            // CSV parsing
            $file = fopen($target_file, 'r');
            if (!$file) {
                header("Location: ../upload-employees.php?status=error&msg=" . urlencode("Failed to open CSV file."));
                exit;
            }
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) > 0) {
                    $rows[] = $row;
                }
            }
            fclose($file);

            if (empty($rows)) {
                header("Location: ../upload-employees.php?status=error&msg=" . urlencode("CSV file is empty."));
                exit;
            }
        }

        // Extract header row
        $header = array_shift($rows);
        if (isset($header[0])) {
            // Strip potential UTF-8 BOM
            $header[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $header[0]);
        }

        $headerLower = array_map(function($col) {
            return strtolower(trim((string)$col));
        }, $header);

        // Identify column indices
        $sNoIdx      = -1;
        $nameIdx     = -1;
        $lNameIdx    = -1;
        $fatherIdx   = -1;
        $desigIdx    = -1;
        $deptIdx     = -1;
        $phoneIdx    = -1;
        $addrIdx     = -1;
        $cnicIdx     = -1;
        $dobIdx      = -1;
        $joinIdx     = -1;
        $salaryIdx   = -1;
        $additionIdx = -1;
        $statusIdx   = -1;

        foreach ($headerLower as $idx => $colName) {
            // Worker ID / Employee Code
            if ($sNoIdx === -1 && (
                strpos($colName, 'worker id') !== false ||
                strpos($colName, 'worker no') !== false ||
                strpos($colName, 'employee no') !== false ||
                strpos($colName, 'employee_code') !== false ||
                strpos($colName, 'emp no') !== false ||
                strpos($colName, 'emp. no') !== false ||
                strpos($colName, 'sno') !== false ||
                $colName === 'id' ||
                $colName === 'code' ||
                $colName === 'no' ||
                $colName === 'no.'
            )) {
                if (!in_array($colName, ['sr. no.', 'sr. no', 'sr no', 'sr.no.', 'sr#'])) {
                    $sNoIdx = $idx;
                }
            }

            // Name
            if ($nameIdx === -1 && (
                strpos($colName, 'worker name') !== false ||
                strpos($colName, 'employee name') !== false ||
                strpos($colName, 'full name') !== false ||
                strpos($colName, 'first name') !== false ||
                $colName === 'name'
            )) {
                $nameIdx = $idx;
            }

            // Last Name
            if ($lNameIdx === -1 && (strpos($colName, 'last name') !== false || $colName === 'surname' || $colName === 'lname')) {
                $lNameIdx = $idx;
            }

            // Father Name
            if ($fatherIdx === -1 && (strpos($colName, 'father') !== false || strpos($colName, 's/o') !== false || strpos($colName, 'd/o') !== false)) {
                $fatherIdx = $idx;
            }

            // Department
            if ($deptIdx === -1 && (strpos($colName, 'dept') !== false || strpos($colName, 'unit') !== false || strpos($colName, 'section') !== false)) {
                $deptIdx = $idx;
            }

            // Designation
            if ($desigIdx === -1 && (strpos($colName, 'desig') !== false || strpos($colName, 'role') !== false || strpos($colName, 'title') !== false || strpos($colName, 'position') !== false)) {
                $desigIdx = $idx;
            }

            // Contact / Phone
            if ($phoneIdx === -1 && (strpos($colName, 'contact') !== false || strpos($colName, 'phone') !== false || strpos($colName, 'mobile') !== false || strpos($colName, 'cell') !== false)) {
                $phoneIdx = $idx;
            }

            // Address
            if ($addrIdx === -1 && (strpos($colName, 'address') !== false || strpos($colName, 'city') !== false || strpos($colName, 'location') !== false)) {
                $addrIdx = $idx;
            }

            // CNIC
            if ($cnicIdx === -1 && (strpos($colName, 'cnic') !== false || strpos($colName, 'nic') !== false || strpos($colName, 'id card') !== false || strpos($colName, 'national id') !== false)) {
                $cnicIdx = $idx;
            }

            // DOB
            if ($dobIdx === -1 && (strpos($colName, 'dob') !== false || strpos($colName, 'birth') !== false)) {
                $dobIdx = $idx;
            }

            // Joining Date
            if ($joinIdx === -1 && (strpos($colName, 'join') !== false || strpos($colName, 'doj') !== false)) {
                $joinIdx = $idx;
            }

            // Salary
            if ($salaryIdx === -1 && (strpos($colName, 'salary') !== false || strpos($colName, 'monthly') !== false || strpos($colName, 'pay') !== false)) {
                $salaryIdx = $idx;
            }

            // Additions / Allowance
            if ($additionIdx === -1 && (strpos($colName, 'addition') !== false || strpos($colName, 'allowance') !== false)) {
                $additionIdx = $idx;
            }

            // Status
            if ($statusIdx === -1 && strpos($colName, 'status') !== false) {
                $statusIdx = $idx;
            }
        }

        // Fallback for Name if not mapped
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

        foreach ($rows as $row) {
            if (empty($row) || !is_array($row)) continue;

            $rawName = isset($row[$nameIdx]) ? trim((string)$row[$nameIdx]) : '';
            if ($rawName === '') continue;

            $rawEmpNo = ($sNoIdx !== -1 && isset($row[$sNoIdx])) ? trim((string)$row[$sNoIdx]) : '';
            $empNoVal = (is_numeric($rawEmpNo) && intval($rawEmpNo) > 0) ? intval($rawEmpNo) : 0;

            $rawFather = ($fatherIdx !== -1 && isset($row[$fatherIdx])) ? trim((string)$row[$fatherIdx]) : '';
            $fatherName = ucwords($rawFather);

            // Use Father Name as Last Name (lname)
            if (!empty($rawFather)) {
                $fname = ucwords(trim($rawName));
                $lname = $fatherName;
            } else {
                $rawLName = ($lNameIdx !== -1 && isset($row[$lNameIdx])) ? trim((string)$row[$lNameIdx]) : '';
                if (empty($rawLName) && strpos($rawName, ' ') !== false) {
                    $parts = preg_split('/\s+/', $rawName, 2);
                    $rawFName = $parts[0] ?? $rawName;
                    $rawLName = $parts[1] ?? '';
                } else {
                    $rawFName = $rawName;
                }
                $fname = ucwords(trim($rawFName));
                $lname = !empty($rawLName) ? ucwords(trim($rawLName)) : '';
            }

            $rawDept = ($deptIdx !== -1 && isset($row[$deptIdx])) ? trim((string)$row[$deptIdx]) : '';
            $rawDesig = ($desigIdx !== -1 && isset($row[$desigIdx])) ? trim((string)$row[$desigIdx]) : '';

            // Handle cases where role like "Office Boy" is placed under Department column
            if (strcasecmp($rawDept, 'Office Boy') === 0) {
                $rawDesig = 'Office Boy';
                $rawDept = 'Admin';
            }

            $deptName = !empty($rawDept) ? ucwords($rawDept) : 'Production';
            $desigName = !empty($rawDesig) ? ucwords($rawDesig) : 'Staff';

            $rawPhone = ($phoneIdx !== -1 && isset($row[$phoneIdx])) ? trim((string)$row[$phoneIdx]) : '';
            $rawAddress = ($addrIdx !== -1 && isset($row[$addrIdx])) ? trim((string)$row[$addrIdx]) : '';
            $rawCnic = ($cnicIdx !== -1 && isset($row[$cnicIdx])) ? trim((string)$row[$cnicIdx]) : '';

            $dobVal = ($dobIdx !== -1 && isset($row[$dobIdx])) ? excelDateToYmd($row[$dobIdx], '1990-01-01') : '1990-01-01';
            $yobVal = intval(date('Y', strtotime($dobVal)));

            $joinVal = ($joinIdx !== -1 && isset($row[$joinIdx])) ? excelDateToYmd($row[$joinIdx], date('Y-m-d')) : date('Y-m-d');
            $joinMonthVal = intval(date('n', strtotime($joinVal)));

            $rawSalary = ($salaryIdx !== -1 && isset($row[$salaryIdx])) ? $row[$salaryIdx] : 0;
            $salary = floatval(preg_replace('/[^0-9.]/', '', (string)$rawSalary));

            $rawAdditions = ($additionIdx !== -1 && isset($row[$additionIdx])) ? $row[$additionIdx] : 0;
            $additions = floatval(preg_replace('/[^0-9.]/', '', (string)$rawAdditions));

            $rawStatus = ($statusIdx !== -1 && isset($row[$statusIdx])) ? trim((string)$row[$statusIdx]) : '';
            $status = !empty($rawStatus) ? ucwords(strtolower($rawStatus)) : 'Active';

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

            // Resolve Designation ID or Create
            $designationID = 0;
            $grade = '1';
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

            $fnameEsc = $conn->real_escape_string($fname);
            $lnameEsc = $conn->real_escape_string($lname);
            $phoneEsc = $conn->real_escape_string($rawPhone);
            $addrEsc = $conn->real_escape_string($rawAddress);
            $cnicEsc = $conn->real_escape_string($rawCnic);
            $statusEsc = $conn->real_escape_string($status);

            if ($existingID > 0) {
                // Update existing record
                $updFields = [
                    "`fname` = '$fnameEsc'",
                    "`lname` = '$lnameEsc'",
                    "`department` = '$deptEsc'",
                    "`departmentID` = '$departmentID'",
                    "`designation` = '$desigEsc'",
                    "`designationID` = '$designationID'",
                    "`basic_salary` = '$salary'",
                    "`allowance` = '$additions'",
                    "`status` = '$statusEsc'",
                    "`primary_number` = '$phoneEsc'",
                    "`primary_address` = '$addrEsc'",
                    "`current_address` = '$addrEsc'",
                    "`cnic` = '$cnicEsc'",
                    "`dob` = '$dobVal'",
                    "`yob` = '$yobVal'",
                    "`join_date` = '$joinVal'",
                    "`join_month` = '$joinMonthVal'",
                    "`employee_code` = '$codeStr'"
                ];

                if ($empNoVal > 0) {
                    $updFields[] = "`sNo` = $empNoVal";
                }

                $updSql = "UPDATE `employees` SET " . implode(', ', $updFields) . " WHERE `employeeID` = '$existingID'";
                if ($conn->query($updSql)) {
                    $updatedCount++;
                }
            } else {
                // Insert new record
                $empData = [
                    'sNo'             => $empNoVal > 0 ? $empNoVal : null,
                    'fname'           => $fname,
                    'mname'           => '',
                    'lname'           => $lname,
                    'dob'             => $dobVal,
                    'yob'             => $yobVal,
                    'gender'          => 'Male',
                    'designation'     => $desigName,
                    'designationID'   => $designationID,
                    'grade'           => $grade,
                    'department'      => $deptName,
                    'departmentID'    => $departmentID,
                    'martital_status' => 'Single',
                    'status'          => $status,
                    'children'        => 0,
                    'spouse_name'     => '',
                    'basic_salary'    => $salary,
                    'allowance'       => $additions,
                    'join_date'       => $joinVal,
                    'leave_date'      => '0000-00-00',
                    'primary_address' => $rawAddress,
                    'secondary_address' => '',
                    'current_address' => $rawAddress,
                    'primary_number'  => $rawPhone,
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
                    'cnic'            => $rawCnic,
                    'passport_no'     => '',
                    'working_hours'   => 8,
                    'start_time'      => '09:00:00',
                    'end_time'        => '17:00:00',
                    'shift'           => 'Day',
                    'shift_id'        => 1,
                    'join_month'      => $joinMonthVal,
                    'leave_month'     => 0,
                    'disability'      => 'No',
                    'photo'           => '',
                    'e_fname'         => $fatherName,
                    'e_mname'         => '',
                    'e_lname'         => '',
                    'e_dob'           => '1990-01-01',
                    'e_cnic'          => 0,
                    'e_contact'       => $rawPhone,
                    'e_gender'        => 'Male',
                    'e_address'       => $rawAddress,
                    'e_emp'           => $codeStr
                ];

                if ($empNoVal > 0) {
                    $empData['employeeID'] = $empNoVal;
                }

                if (insertEmployeeArray($conn, $empData, 'employees')) {
                    $last_id = intval($conn->insert_id ?: $empNoVal);
                    
                    // Sync sNo if empty
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

        $msg = "Import completed! Successfully created $insertedCount new employee(s) and updated $updatedCount existing employee(s).";
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
