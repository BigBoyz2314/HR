<?php
require_once('config.php');
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['role'] != '1') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['empID']) ? intval($_GET['empID']) : 0);
    header("Location: ../edit-employee.php?id=$id");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_REQUEST["empID"]);

    $fname       = isset($_REQUEST["fName"]) ? ucwords(trim($_REQUEST["fName"])) : '';
    $mname       = isset($_REQUEST["mName"]) ? ucwords(trim($_REQUEST["mName"])) : '';
    $lname       = isset($_REQUEST["lName"]) ? ucwords(trim($_REQUEST["lName"])) : '';
    
    $rawEmpNo    = isset($_REQUEST["e_emp"]) ? trim($_REQUEST["e_emp"]) : '';
    $empNoVal    = (is_numeric($rawEmpNo) && intval($rawEmpNo) > 0) ? intval($rawEmpNo) : $id;

    $oldDesig    = isset($_REQUEST["oldDesig"]) ? $conn->real_escape_string($_REQUEST["oldDesig"]) : '';
    $oldDept     = isset($_REQUEST["oldDept"]) ? $conn->real_escape_string($_REQUEST["oldDept"]) : '';
    $oldDesigID  = isset($_REQUEST["oldDesigID"]) ? intval($_REQUEST["oldDesigID"]) : 0;
    $oldDeptID   = isset($_REQUEST["oldDeptID"]) ? intval($_REQUEST["oldDeptID"]) : 0;
    
    $newDept     = isset($_REQUEST["dept"]) ? intval($_REQUEST["dept"]) : 0;
    $newDesig    = isset($_REQUEST["desig"]) ? intval($_REQUEST["desig"]) : 0;
    $newbasic    = isset($_REQUEST["basicSalary"]) ? floatval($_REQUEST["basicSalary"]) : 0.0;
    $newallow    = isset($_REQUEST["allowance"]) ? floatval($_REQUEST["allowance"]) : 0.0;

    $status      = isset($_REQUEST["status"]) ? trim($_REQUEST["status"]) : 'Active';
    $joinDate    = isset($_REQUEST["joinDate"]) && !empty($_REQUEST["joinDate"]) ? trim($_REQUEST["joinDate"]) : date('Y-m-d');
    $phone       = isset($_REQUEST["pNumber"]) ? trim($_REQUEST["pNumber"]) : '';
    $cnic        = isset($_REQUEST["cnic"]) ? trim($_REQUEST["cnic"]) : '';

    $DesigName = $oldDesig;
    $DesigID = $oldDesigID;
    $Grade = 'G1';

    if ($newDesig > 0) {
        $DesigID = $newDesig;
        $result3 = $conn->query("SELECT name, grade FROM designation WHERE designationID = $DesigID");
        if ($result3 && $result3->num_rows > 0) {
            $r3 = $result3->fetch_assoc();
            $DesigName = $r3['name'];
            $Grade = $r3['grade'];
        }
    }

    $DeptName = $oldDept;
    $DeptID = $oldDeptID;
    if ($newDept > 0) {
        $DeptID = $newDept;
        $result1 = $conn->query("SELECT name FROM department WHERE departmentID = $DeptID");
        if ($result1 && $result1->num_rows > 0) {
            $r1 = $result1->fetch_assoc();
            $DeptName = $r1['name'];
        }
    }

    $fnameEsc = $conn->real_escape_string($fname);
    $mnameEsc = $conn->real_escape_string($mname);
    $lnameEsc = $conn->real_escape_string($lname);
    $phoneEsc = $conn->real_escape_string($phone);
    $cnicEsc  = $conn->real_escape_string($cnic);
    $deptEsc  = $conn->real_escape_string($DeptName);
    $desigEsc = $conn->real_escape_string($DesigName);
    $moj      = date('m', strtotime($joinDate));

    $sql = "UPDATE employees SET 
                sNo = '$empNoVal',
                employee_code = '$rawEmpNo',
                e_emp = '$rawEmpNo',
                fname = '$fnameEsc',
                mname = '$mnameEsc',
                lname = '$lnameEsc',
                basic_salary = '$newbasic', 
                allowance = '$newallow',
                join_date = '$joinDate',
                join_month = '$moj',
                status = '$status',
                primary_number = '$phoneEsc',
                cnic = '$cnicEsc',
                department = '$deptEsc', 
                departmentID = '$DeptID', 
                designation = '$desigEsc', 
                designationID = '$DesigID', 
                grade = '$Grade',
                updated_at = current_timestamp()
            WHERE employeeID = '$id'";

    if (mysqli_query($conn, $sql)) {
        // Log updated employee snapshot
        $conn->query("INSERT INTO employees_log (sNo, employeeID, fname, mname, lname, basic_salary, allowance, join_date, status, department, departmentID, designation, designationID, grade, cnic, primary_number, employee_code, e_emp, updated_at) 
                      VALUES ('$empNoVal', '$id', '$fnameEsc', '$mnameEsc', '$lnameEsc', '$newbasic', '$newallow', '$joinDate', '$status', '$deptEsc', '$DeptID', '$desigEsc', '$DesigID', '$Grade', '$cnicEsc', '$phoneEsc', '$rawEmpNo', '$rawEmpNo', current_timestamp())");

        header("Location: ../view-employees.php?status=success&msg=" . urlencode("Employee #$empNoVal ($fname) updated successfully."));
        exit;
    } else {
        echo "ERROR updating employee: " . mysqli_error($conn);
    }
}
?>