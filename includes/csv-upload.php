<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Karachi');

// Helper to convert Excel date (serial number or formatted string) to YYYY-MM-DD
function parseAttendanceDate($val) {
    $val = trim((string)$val);
    if ($val === '') return null;

    // Excel numeric serial date (e.g. 46307 for 2026-10-12)
    if (is_numeric($val) && (float)$val > 30000 && (float)$val < 70000) {
        $days = (float)$val;
        // Excel 1900 date system epoch adjustment
        $timestamp = round(($days - 25569) * 86400);
        return gmdate('Y-m-d', $timestamp);
    }

    // String date regex matching
    $cleanDate = trim(preg_replace('/\s+.*$/', '', $val));
    // 1. DD-MM-YYYY or DD/MM/YYYY (4-digit year) e.g. 06/07/2026 or 06-07-2026
    if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $cleanDate, $m)) {
        $day = intval($m[1]);
        $month = intval($m[2]);
        $year = intval($m[3]);
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
    // 2. YYYY-MM-DD or YYYY/MM/DD (4-digit year first) e.g. 2026-07-16
    elseif (preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})$/', $cleanDate, $m)) {
        $year = intval($m[1]);
        $month = intval($m[2]);
        $day = intval($m[3]);
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
    // 3. DD-MM-YY or DD/MM/YY (2-digit year) e.g. 16-07-26 or 06/07/26
    elseif (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2})$/', $cleanDate, $m)) {
        $day = intval($m[1]);
        $month = intval($m[2]);
        $yy = intval($m[3]);
        $year = ($yy >= 70) ? (1900 + $yy) : (2000 + $yy);
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }

    $time = strtotime($val);
    if ($time !== false && $time > 0) {
        return date('Y-m-d', $time);
    }

    return null;
}

// Helper to convert Excel time (fraction or formatted string e.g. 0.3486111 or 08:22) to HH:MM:SS
function parseAttendanceTime($val, $default = '09:00:00') {
    $val = trim((string)$val);
    if ($val === '' || $val === '0' || $val === '0.0') return $default;

    // Excel time fraction (between 0.0 and 1.0) e.g. 0.348611111111111
    if (is_numeric($val) && (float)$val >= 0 && (float)$val <= 1) {
        $sec = round((float)$val * 86400);
        $h = floor($sec / 3600) % 24;
        $m = floor(($sec % 3600) / 60);
        $s = $sec % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    // Formatted time string e.g. 08:22 or 18:06:15 or 8:22 AM or 6:06 PM
    $ts = strtotime("2000-01-01 " . $val);
    if ($ts !== false) {
        return date('H:i:s', $ts);
    }

    return $default;
}

// Helper to parse XLSX files natively using ZipArchive and SimpleXML
function parseXLSXAttendance($filePath) {
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $fileName = $_FILES["fileToUpload"]["name"];
    $target_file = $target_dir . time() . '_' . basename($fileName);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileType, ['csv', 'xlsx', 'xls'])) {
        header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("Only Excel (.xlsx) or CSV files are allowed."));
        exit;
    }

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        
        // Detect if file is an OpenXML (ZIP) file regardless of extension
        $isXlsx = ($fileType === 'xlsx');
        $fp = @fopen($target_file, 'rb');
        if ($fp) {
            $magic = fread($fp, 4);
            fclose($fp);
            if ($magic === "PK\x03\x04") {
                $isXlsx = true;
            }
        }

        $allRows = [];

        if ($isXlsx) {
            $parsed = parseXLSXAttendance($target_file);
            if ($parsed === false || empty($parsed)) {
                header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("Failed to parse Excel file or worksheet is empty."));
                exit;
            }
            $allRows = $parsed;
        } else {
            // Text CSV parsing with delimiter autodetection
            $file = fopen($target_file, 'r');
            if (!$file) {
                header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("Failed to open uploaded CSV file."));
                exit;
            }

            // Detect delimiter from first non-empty line
            $firstLine = fgets($file);
            rewind($file);
            $delimiter = ',';
            if ($firstLine !== false) {
                $commaCount = substr_count($firstLine, ',');
                $semiCount = substr_count($firstLine, ';');
                $tabCount = substr_count($firstLine, "\t");
                if ($semiCount > $commaCount && $semiCount > $tabCount) {
                    $delimiter = ';';
                } elseif ($tabCount > $commaCount && $tabCount > $semiCount) {
                    $delimiter = "\t";
                }
            }

            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                // Remove BOM from first item if present
                if (isset($row[0])) {
                    $row[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $row[0]);
                }
                if (count(array_filter($row, 'strlen')) > 0) {
                    $allRows[] = array_map('trim', $row);
                }
            }
            fclose($file);

            if (empty($allRows)) {
                header("Location: ../upload-attendance.php?status=error&msg=" . urlencode("CSV file is empty or invalid."));
                exit;
            }
        }

        // Locate header row dynamically across first 10 rows
        $headerRowIdx = -1;
        $maxScan = min(10, count($allRows));
        for ($i = 0; $i < $maxScan; $i++) {
            $rowText = strtolower(implode(' ', $allRows[$i]));
            if (
                strpos($rowText, 'punch') !== false ||
                (strpos($rowText, 'date') !== false && (strpos($rowText, 'person') !== false || strpos($rowText, 'employee') !== false || strpos($rowText, 'worker') !== false || strpos($rowText, 'id') !== false))
            ) {
                $headerRowIdx = $i;
                break;
            }
        }

        // If no recognizable header row found, default to row 0
        if ($headerRowIdx === -1) {
            $headerRowIdx = 0;
        }

        $header = $allRows[$headerRowIdx];
        $headerLower = array_map(function($col) {
            return strtolower(trim((string)$col));
        }, $header);

        // Identify column indices
        $empIdx = -1;
        $nameIdx = -1;
        $deptIdx = -1;
        $dateIdx = -1;
        $timeInIdx = -1;
        $timeOutIdx = -1;

        foreach ($headerLower as $idx => $colName) {
            if ($empIdx === -1 && (
                strpos($colName, 'person id') !== false ||
                strpos($colName, 'worker id') !== false ||
                strpos($colName, 'employee id') !== false ||
                strpos($colName, 'emp id') !== false ||
                strpos($colName, 'employee') !== false ||
                strpos($colName, 'emp') !== false ||
                $colName === 'number' ||
                $colName === 'id' ||
                $colName === 'sno' ||
                $colName === 'badge'
            )) {
                $empIdx = $idx;
            }

            if ($nameIdx === -1 && (
                strpos($colName, 'person name') !== false ||
                strpos($colName, 'worker name') !== false ||
                strpos($colName, 'employee name') !== false ||
                strpos($colName, 'first name') !== false ||
                strpos($colName, 'emp name') !== false ||
                $colName === 'name'
            )) {
                $nameIdx = $idx;
            }

            if ($deptIdx === -1 && (
                strpos($colName, 'depart') !== false ||
                strpos($colName, 'unit') !== false ||
                strpos($colName, 'dept') !== false
            )) {
                $deptIdx = $idx;
            }

            if ($dateIdx === -1 && (
                strpos($colName, 'date') !== false ||
                $colName === 'dt'
            )) {
                $dateIdx = $idx;
            }

            if ($timeInIdx === -1 && (
                strpos($colName, 'first punch') !== false ||
                strpos($colName, 'first punc') !== false ||
                strpos($colName, 'time in') !== false ||
                strpos($colName, 'check in') !== false ||
                strpos($colName, 'clock in') !== false ||
                strpos($colName, 'punch in') !== false ||
                $colName === 'in'
            )) {
                $timeInIdx = $idx;
            }

            if ($timeOutIdx === -1 && (
                strpos($colName, 'last punch') !== false ||
                strpos($colName, 'last punc') !== false ||
                strpos($colName, 'time out') !== false ||
                strpos($colName, 'check out') !== false ||
                strpos($colName, 'clock out') !== false ||
                strpos($colName, 'punch out') !== false ||
                $colName === 'out'
            )) {
                $timeOutIdx = $idx;
            }
        }

        // Sensible fallbacks if automatic detection was partial
        if ($empIdx === -1) $empIdx = 0;
        if ($nameIdx === -1) $nameIdx = 1;
        if ($deptIdx === -1) $deptIdx = 2;
        if ($dateIdx === -1) $dateIdx = 3;
        if ($timeInIdx === -1) $timeInIdx = 5;
        if ($timeOutIdx === -1) $timeOutIdx = 6;

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $skippedEmps = [];

        // Cache employee resolutions to reduce redundant database queries
        $empCache = [];

        $totalDataRows = count($allRows);
        for ($rIdx = $headerRowIdx + 1; $rIdx < $totalDataRows; $rIdx++) {
            $row = $allRows[$rIdx];
            if (count($row) < 2) continue; // Skip empty rows

            $rawEmp = isset($row[$empIdx]) ? trim((string)$row[$empIdx]) : '';
            $rawName = isset($row[$nameIdx]) ? trim((string)$row[$nameIdx]) : '';
            $rawDept = isset($row[$deptIdx]) ? trim((string)$row[$deptIdx]) : 'General';
            $rawDate = isset($row[$dateIdx]) ? trim((string)$row[$dateIdx]) : '';
            $rawTimeIn = isset($row[$timeInIdx]) ? trim((string)$row[$timeInIdx]) : '';
            $rawTimeOut = isset($row[$timeOutIdx]) ? trim((string)$row[$timeOutIdx]) : '';

            if (empty($rawEmp) && empty($rawName)) {
                continue;
            }

            if (empty($rawDate)) {
                continue;
            }

            // Parse Date
            $parsedDate = parseAttendanceDate($rawDate);
            if (!$parsedDate) {
                continue;
            }

            $day = intval(date('d', strtotime($parsedDate)));
            $month = intval(date('m', strtotime($parsedDate)));
            $year = intval(date('Y', strtotime($parsedDate)));

            // Resolve employeeID matching sNo / employee_code / employeeID / fname+lname
            $cacheKey = $rawEmp . '|' . $rawName;
            $realEmpID = 0;

            if (isset($empCache[$cacheKey])) {
                $realEmpID = $empCache[$cacheKey];
            } else {
                $empEsc = $conn->real_escape_string($rawEmp);
                $chkEmp = null;

                if (!empty($rawEmp)) {
                    $chkEmp = $conn->query("SELECT employeeID FROM employees WHERE employeeID = '$empEsc' OR sNo = '$empEsc' OR employee_code = '$empEsc' LIMIT 1");
                }

                if ($chkEmp && $chkEmp->num_rows > 0) {
                    $eRow = $chkEmp->fetch_assoc();
                    $realEmpID = intval($eRow['employeeID']);
                } elseif (!empty($rawName)) {
                    // Try matching by employee name
                    $nameEsc = $conn->real_escape_string($rawName);
                    $chkName = $conn->query("SELECT employeeID FROM employees WHERE CONCAT(TRIM(fname), ' ', TRIM(lname)) = '$nameEsc' OR TRIM(fname) = '$nameEsc' LIMIT 1");
                    if ($chkName && $chkName->num_rows > 0) {
                        $eRow = $chkName->fetch_assoc();
                        $realEmpID = intval($eRow['employeeID']);
                    }
                }

                $empCache[$cacheKey] = $realEmpID;
            }

            if ($realEmpID <= 0) {
                $skippedCount++;
                $label = !empty($rawEmp) ? $rawEmp : $rawName;
                if (!empty($rawName) && !empty($rawEmp) && $rawEmp !== $rawName) {
                    $label = "$rawEmp ($rawName)";
                }
                $skippedEmps[$label] = true;
                continue;
            }

            // Parse Time In & Time Out
            $timeInStr = parseAttendanceTime($rawTimeIn, '09:00:00');
            $timeOutStr = parseAttendanceTime($rawTimeOut, $timeInStr); // Default to timeIn if only single punch recorded

            $deptEsc = $conn->real_escape_string($rawDept ?: 'General');
            $parsedDateEsc = $conn->real_escape_string($parsedDate);
            $updatedBy = 'csv_import';

            // Check if record exists for this employee on this date
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

        $msg = "Attendance Import Complete! Processed $insertedCount new record(s) and updated $updatedCount existing record(s).";
        if ($skippedCount > 0) {
            $unmatchedList = implode(', ', array_slice(array_keys($skippedEmps), 0, 5));
            if (count($skippedEmps) > 5) {
                $unmatchedList .= ' and ' . (count($skippedEmps) - 5) . ' more';
            }
            $msg .= " Note: $skippedCount record(s) were skipped because the worker was not found in Employees ($unmatchedList).";
        }

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
