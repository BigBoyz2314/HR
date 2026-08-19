<?php
require_once('config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Required Core Fields
    $fname       = isset($_REQUEST["fName"]) ? ucwords(trim($_REQUEST["fName"])) : '';
    $basicsalary = isset($_REQUEST["basicSalary"]) ? floatval($_REQUEST["basicSalary"]) : 0.0;
    $allowance   = isset($_REQUEST["allowence"]) ? floatval($_REQUEST["allowence"]) : 0.0;
    $joindate    = isset($_REQUEST["joinDate"]) && !empty($_REQUEST["joinDate"]) ? trim($_REQUEST["joinDate"]) : date('Y-m-d');

    // Manual Employee ID / Code
    $emp         = isset($_REQUEST["e_emp"]) ? trim($_REQUEST["e_emp"]) : '';
    $empIdVal    = (is_numeric($emp) && intval($emp) > 0) ? intval($emp) : 0;

    // Secondary / Optional Fields with safe fallbacks
    $mname       = isset($_REQUEST["mName"]) ? ucwords(trim($_REQUEST["mName"])) : '';
    $lname       = isset($_REQUEST["lName"]) ? ucwords(trim($_REQUEST["lName"])) : '';
    $gender      = isset($_REQUEST["gender"]) ? trim($_REQUEST["gender"]) : 'Male';
    $pnumber     = isset($_REQUEST["pNumber"]) ? trim($_REQUEST["pNumber"]) : '';
    $cnic        = isset($_REQUEST["cnic"]) ? trim($_REQUEST["cnic"]) : '';
    $department  = isset($_REQUEST["department"]) ? intval($_REQUEST["department"]) : 0;
    $designation = isset($_REQUEST["designation"]) ? intval($_REQUEST["designation"]) : 0;

    $dob         = isset($_REQUEST["dob"]) && !empty($_REQUEST["dob"]) ? trim($_REQUEST["dob"]) : '1990-01-01';
    $edob        = isset($_REQUEST["edob"]) && !empty($_REQUEST["edob"]) ? trim($_REQUEST["edob"]) : '1990-01-01';
    $mStatus     = isset($_REQUEST["mStatus"]) ? trim($_REQUEST["mStatus"]) : 'Single';
    $children    = isset($_REQUEST["children"]) && $_REQUEST["children"] !== '' ? intval($_REQUEST["children"]) : 0;
    $spousename  = isset($_REQUEST["spouseName"]) ? ucwords(trim($_REQUEST["spouseName"])) : '';
    $ecnic       = isset($_REQUEST["ecnic"]) ? trim($_REQUEST["ecnic"]) : '';
    $passport    = isset($_REQUEST["passport"]) ? trim($_REQUEST["passport"]) : '';
    $paddress    = isset($_REQUEST["pAddress"]) ? ucwords(trim($_REQUEST["pAddress"])) : '';
    $saddress    = isset($_REQUEST["sAddress"]) ? ucwords(trim($_REQUEST["sAddress"])) : '';
    $caddress    = isset($_REQUEST["cAddress"]) ? ucwords(trim($_REQUEST["cAddress"])) : '';
    $eaddress    = isset($_REQUEST["eAddress"]) ? ucwords(trim($_REQUEST["eAddress"])) : '';
    $snumber     = isset($_REQUEST["sNumber"]) ? trim($_REQUEST["sNumber"]) : '';
    $enumber     = isset($_REQUEST["eNumber"]) ? trim($_REQUEST["eNumber"]) : '';
    $cAllowance  = isset($_REQUEST["cAllowence"]) ? floatval($_REQUEST["cAllowence"]) : 0.0;
    $eobi        = isset($_REQUEST["eobi"]) ? floatval($_REQUEST["eobi"]) : 0.0;
    $deduction   = isset($_REQUEST["deduction"]) ? floatval($_REQUEST["deduction"]) : 0.0;
    $grosssalary = isset($_REQUEST["grossSalary"]) ? floatval($_REQUEST["grossSalary"]) : ($basicsalary + $allowance);
    $leavedate   = isset($_REQUEST["leaveDate"]) && !empty($_REQUEST["leaveDate"]) ? trim($_REQUEST["leaveDate"]) : '0000-00-00';
    $bank        = isset($_REQUEST["bank"]) ? ucwords(trim($_REQUEST["bank"])) : '';
    $bankacc     = isset($_REQUEST["bankAcc"]) ? trim($_REQUEST["bankAcc"]) : '';
    $iban        = isset($_REQUEST["iban"]) ? trim($_REQUEST["iban"]) : '';
    $manager     = isset($_REQUEST["manager"]) ? ucwords(trim($_REQUEST["manager"])) : '';
    $warnings    = isset($_REQUEST["warnings"]) ? intval($_REQUEST["warnings"]) : 0;
    $leaves      = isset($_REQUEST["leaves"]) ? intval($_REQUEST["leaves"]) : 0;
    $allowleaves = isset($_REQUEST["allowLeaves"]) ? intval($_REQUEST["allowLeaves"]) : 14;
    $absents     = isset($_REQUEST["absents"]) ? intval($_REQUEST["absents"]) : 0;
    $presents    = isset($_REQUEST["presents"]) ? intval($_REQUEST["presents"]) : 0;
    $daysworking = isset($_REQUEST["daysWorking"]) ? intval($_REQUEST["daysWorking"]) : 30;
    $loan        = isset($_REQUEST["loan"]) ? trim($_REQUEST["loan"]) : '0';
    $loanamount  = isset($_REQUEST["loanAmount"]) ? floatval($_REQUEST["loanAmount"]) : 0.0;
    $shift_id    = isset($_REQUEST["shift_id"]) ? intval($_REQUEST["shift_id"]) : 0;
    $ot_rate     = isset($_REQUEST["overtime_rate"]) && $_REQUEST["overtime_rate"] !== '' ? floatval($_REQUEST["overtime_rate"]) : 0.0;

    $workinghours= 8;
    $starttime   = '09:00:00';
    $endtime     = '17:00:00';
    $shift       = 'General Shift';

    if ($shift_id > 0) {
        $sQuery = $conn->query("SELECT shift_name, start_time, end_time, working_hours FROM shifts WHERE id = $shift_id");
        if ($sQuery && $sQuery->num_rows > 0) {
            $sData = $sQuery->fetch_assoc();
            $shift       = $sData['shift_name'];
            $starttime   = $sData['start_time'];
            $endtime     = $sData['end_time'];
            $workinghours= $sData['working_hours'];
        }
    }

    $status      = isset($_REQUEST["status"]) ? trim($_REQUEST["status"]) : 'Active';
    $egender     = isset($_REQUEST["egender"]) ? trim($_REQUEST["egender"]) : 'Male';
    $disability  = isset($_REQUEST["disability"]) ? trim($_REQUEST["disability"]) : 'No';
    $efname      = isset($_REQUEST["efName"]) ? ucwords(trim($_REQUEST["efName"])) : '';
    $emname      = isset($_REQUEST["emName"]) ? ucwords(trim($_REQUEST["emName"])) : '';
    $elname      = isset($_REQUEST["elName"]) ? ucwords(trim($_REQUEST["elName"])) : '';

    // Handle Photo Upload optionally
    $filename = '';
    if (isset($_FILES["photo"]) && !empty($_FILES["photo"]["name"])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $filename;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
    }

    // Resolve Department Name
    $dept = 'General';
    if ($department > 0) {
        $stmt1 = "SELECT name FROM department WHERE departmentID = $department";
        $result1 = $conn->query($stmt1);
        if ($result1 && $result1->num_rows > 0) {                   
            $row1 = $result1->fetch_assoc();
            $dept = $row1['name'];
        }
    }

    // Resolve Designation Name
    $desig = 'Staff';
    $grade = 'G1';
    if ($designation > 0) {
        $stmt2 = "SELECT name, grade FROM designation WHERE designationID = $designation";
        $result2 = $conn->query($stmt2);
        if ($result2 && $result2->num_rows > 0) {                       
            $row2 = $result2->fetch_assoc();
            $desig = $row2['name'];
            $grade = $row2['grade'];
        }
    }

    $yob = date('Y', strtotime($dob));
    $moj = date('m', strtotime($joindate));
    $mor = ($leavedate != '0000-00-00') ? date('m', strtotime($leavedate)) : '0';

    // Insert Employee using associative array mapping
    $empData = [
        'sNo'             => $empIdVal > 0 ? $empIdVal : null,
        'fname'           => $fname,
        'mname'           => $mname,
        'lname'           => $lname,
        'dob'             => $dob,
        'yob'             => $yob,
        'gender'          => $gender,
        'designation'     => $desig,
        'designationID'   => $designation,
        'grade'           => $grade,
        'department'      => $dept,
        'departmentID'    => $department,
        'martital_status' => $mStatus,
        'status'          => $status,
        'children'        => $children,
        'spouse_name'     => $spousename,
        'basic_salary'    => $basicsalary,
        'allowance'       => $allowance,
        'join_date'       => $joindate,
        'leave_date'      => $leavedate,
        'primary_address' => $paddress,
        'secondary_address' => $saddress,
        'current_address' => $caddress,
        'primary_number'  => $pnumber,
        'secondary_number' => $snumber,
        'bank_name'       => $bank,
        'bank_account_no' => $bankacc,
        'iban'            => $iban,
        'employee_code'   => $emp,
        'manager_name'    => $manager,
        'managerID'       => 0,
        'warnings'        => $warnings,
        'leaves'          => $leaves,
        'allowed_leaves'  => $allowleaves,
        'absents'         => $absents,
        'presents'        => $presents,
        'days_working'    => $daysworking,
        'loan'            => $loan,
        'loan_amount'     => $loanamount,
        'cnic'            => $cnic,
        'passport_no'     => $passport,
        'working_hours'   => $workinghours,
        'start_time'      => $starttime,
        'end_time'        => $endtime,
        'shift'           => $shift,
        'shift_id'        => $shift_id,
        'overtime_rate'   => $ot_rate,
        'join_month'      => $moj,
        'leave_month'     => $mor,
        'disability'      => $disability,
        'photo'           => $filename,
        'e_fname'         => $efname,
        'e_mname'         => $emname,
        'e_lname'         => $elname,
        'e_dob'           => $edob,
        'e_cnic'          => $ecnic,
        'e_contact'       => $enumber,
        'e_gender'        => $egender,
        'e_address'       => $eaddress,
        'e_emp'           => $emp
    ];

    function insertEmpFormArray($conn, $data, $table = 'employees') {
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

    try {
        if (insertEmpFormArray($conn, $empData, 'employees')) {
            $last_id = intval($conn->insert_id);
            $finalCode = $empIdVal > 0 ? $empIdVal : $last_id;
            
            // If sNo was NULL, update sNo to match generated employeeID
            $conn->query("UPDATE `employees` SET `sNo` = '$finalCode' WHERE `employeeID` = '$last_id' AND (`sNo` IS NULL OR `sNo` = 0)");

            $logData = $empData;
            $logData['employeeID'] = $last_id;
            $logData['sNo'] = $finalCode;
            insertEmpFormArray($conn, $logData, 'employees_log');
                
            if ($department > 0) {
                $sql2 = "UPDATE department SET current_Strength = current_Strength + 1 WHERE departmentID = $department";
                $conn->query($sql2);
            }

            header('Location: ../view-employees.php?status=success&msg=' . urlencode("Employee $fname added successfully with ID/Code: $finalCode."));
            exit;
        } else {
            header('Location: ../view-employees.php?action=add&status=error&msg=' . urlencode("Unable to save record: " . $conn->error));
            exit;
        }
    } catch (Throwable $e) {
        header('Location: ../view-employees.php?action=add&status=error&msg=' . urlencode("Server error while saving employee: " . $e->getMessage()));
        exit;
    }
} else {
    header('Location: ../view-employees.php');
    exit;
}