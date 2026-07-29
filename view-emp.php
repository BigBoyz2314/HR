<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


    $id = $_GET['id'];
    $stmt = "SELECT * FROM employees WHERE employeeID = '$id'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $eid = $row['employeeID'];
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];
    $dob = $row['dob'];
    $desig = $row['designation'];
    $grade = $row['grade'];
    $dept = $row['department'];
    $status = $row['status'];
    $mstatus = $row['martital_status'];
    $children = $row['children'];
    $spouse = $row['spouse_name'];
    $basic = $row['basic_salary'];
    $joindate = $row['join_date'];
    $leavedate = $row['leave_date'];
    $paddress = $row['primary_address'];
    $saddress = $row['secondary_address'];
    $caddress = $row['current_address'];
    $pnumber = $row['primary_number'];
    $snumber = $row['secondary_number'];
    $bank = $row['bank_name'];
    $bankacc = $row['bank_account_no'];
    $iban = $row['iban'];
    $cnic = $row['cnic'];
    $passport = $row['passport_no'];
    $manager = $row['manager_name'];
    $warnings = $row['warnings'];
    $leaves = $row['leaves'];
    $allowleave = $row['allowed_leaves'];
    $absents = $row['absents'];
    $presents = $row['presents'];
    $daysworking = $row['days_working'];
    $loan = $row['loan'];
    $loanamount = $row['loan_amount'];
    $starttime = $row['start_time'];
    $endtime = $row['end_time'];
    $shift = $row['shift'];
    $created = $row['created_at'];
    $updated = $row['updated_at'];
    $gender = $row['gender'];
    $disability = $row['disability'];
    $photo = $row['photo'];
    $efname = $row['e_fname'];
    $emname = $row['e_mname'];
    $elname = $row['e_lname'];
    $egender = $row['e_gender'];
    $econtact = $row['e_contact'];
    $ecnic = $row['e_cnic'];
    $eaddress = $row['e_address'];
    $edob = $row['e_dob'];
    $emp = $row['e_emp'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <title>View Employee</title>
</head>
<body class="h-screen overflow-hidden">
<?php include 'includes/nav1.php' ?>
<div class="flex h-[calc(100vh-4rem)]">
<?php include 'includes/side-nav.php' ?>
<div class="flex-1 bg-gray-100 overflow-y-auto">
    <div class="max-w-5xl mx-auto p-4 md:p-6 bg-white rounded-lg shadow-lg mt-4 md:mt-8 mb-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">View Employee Details</h1>
            <p class="text-gray-600">View complete employee information</p>
        </div>
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
            <div class="mt-4 md:mt-0">
                <label class="block text-gray-700 font-semibold mb-1">Created</label>
                <input value="<?php echo $created ?>" disabled type="text" class="w-40 rounded bg-blue-500 text-white text-center font-bold">
            </div>
            <div class="mt-4 md:mt-0">
                <label class="block text-gray-700 font-semibold mb-1">Updated</label>
                <input value="<?php echo $updated ?>" disabled type="text" class="w-40 rounded bg-blue-500 text-white text-center font-bold">
            </div>
            <div class="mt-4 md:mt-0">
                <label class="block text-gray-700 font-semibold mb-1">Employee ID</label>
                <input value="<?php echo $eid ?>" disabled type="text" class="w-32 rounded bg-blue-500 text-white text-center font-bold">
            </div>
        </div>
        <form action="" method="post" class="space-y-8">
            <div class="border rounded p-4">
                <h3 class="text-lg font-semibold mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">First Name</label>
                        <input value="<?php echo $fname?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Middle Name</label>
                        <input value="<?php echo $mname?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Last Name</label>
                        <input value="<?php echo $lname?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Gender</label>
                        <input value="<?php echo $gender?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Date of Birth</label>
                        <input value="<?php echo $dob?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Martital Status</label>
                        <input value="<?php echo $mstatus?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Children</label>
                        <input value="<?php echo $children?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Spouse Name</label>
                        <input value="<?php echo $spouse?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">CNIC</label>
                        <input value="<?php echo $cnic?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Passport</label>
                        <input value="<?php echo $passport?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Disabled</label>
                        <input value="<?php echo $disability?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <label class="block text-gray-700 font-semibold mb-2">Photo</label>
                        <img src="<?php echo "uploads/".$photo ?>" alt="" width="50px" height="50px" class="rounded-full object-cover">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Primary Address</label>
                        <input value="<?php echo $paddress?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Secondary Address</label>
                        <input value="<?php echo $saddress?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Current Address</label>
                        <input value="<?php echo $caddress?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Contact 1 #</label>
                        <input value="<?php echo $pnumber?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Contact 2 #</label>
                        <input value="<?php echo $snumber?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                </div>
            </div>
            <div class="border rounded p-4">
                <h3 class="text-lg font-semibold mb-4">Emergency Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">First Name</label>
                        <input type="text" value="<?php echo $efname?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Middle Name</label>
                        <input type="text" value="<?php echo $emname?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Last Name</label>
                        <input type="text" value="<?php echo $elname?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Gender</label>
                        <input type="text" value="<?php echo $egender?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Date of Birth</label>
                        <input type="date" value="<?php echo $edob?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Employee</label>
                        <input type="text" value="<?php echo $emp?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">CNIC</label>
                        <input type="number" value="<?php echo $ecnic?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Emergency Address</label>
                        <input type="text" value="<?php echo $eaddress?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Emergency Contact</label>
                        <input type="number" value="<?php echo $econtact?>" class="w-full rounded border border-gray-300 bg-gray-100" disabled>
                    </div>
                </div>
            </div>
            <div class="border rounded p-4">
                <h3 class="text-lg font-semibold mb-4">Salary Information</h3>
                <?php
                $otRateVal = isset($row['overtime_rate']) ? floatval($row['overtime_rate']) : 0.00;
                $wHours = !empty($row['working_hours']) ? floatval($row['working_hours']) : 8.00;
                $dWork = !empty($row['days_working']) ? intval($row['days_working']) : 30;
                $calcOtRate = ($dWork * $wHours > 0) ? round($basic / ($dWork * $wHours), 2) : 0.00;
                $finalOtRateDisplay = ($otRateVal > 0) ? "PKR " . number_format(round($otRateVal)) . " / hr (Custom)" : "PKR " . number_format(round($calcOtRate)) . " / hr (Calculated Default)";
                ?>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Bank Name</label>
                        <input value="<?php echo $bank?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Bank Account no.</label>
                        <input value="<?php echo $bankacc?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">IBAN</label>
                        <input value="<?php echo $iban?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Basic Salary</label>
                        <input value="PKR <?php echo number_format(round($basic))?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100 font-bold">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Overtime Hourly Rate</label>
                        <input value="<?php echo $finalOtRateDisplay; ?>" disabled type="text" class="w-full rounded border border-gray-300 bg-indigo-50 text-indigo-900 font-bold">
                    </div>
                </div>
            </div>
            <div class="border rounded p-4">
                <h3 class="text-lg font-semibold mb-4">Official Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Designation</label>
                        <input value="<?php echo $desig?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Department</label>
                        <input value="<?php echo $dept?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Join Date</label>
                        <input value="<?php echo $joindate?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Leave Date</label>
                        <input value="<?php echo $leavedate?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Shift</label>
                        <input value="<?php echo $shift?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Status</label>
                        <input value="<?php echo $status?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Line Manager</label>
                        <input value="<?php echo $manager?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Warnings</label>
                        <input value="<?php echo $warnings?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Leaves</label>
                        <input value="<?php echo $leaves?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Allowed Leaves</label>
                        <input value="<?php echo $allowleave?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Absents</label>
                        <input value="<?php echo $absents?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Presents</label>
                        <input value="<?php echo $presents?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Days Workings</label>
                        <input value="<?php echo $daysworking?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Loan</label>
                        <input value="<?php echo $loan?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Loan Amount</label>
                        <input value="<?php echo $loanamount?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Hours</label>
                        <input value="<?php echo $daysworking?>" disabled type="number" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Start Time</label>
                        <input value="<?php echo $starttime?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">End Time</label>
                        <input value="<?php echo $endtime?>" disabled type="text" class="w-full rounded border border-gray-300 bg-gray-100">
                    </div>
                </div>
            </div>
        </form>
        <div class="flex justify-center mt-8">
        <?php
        if ($_SESSION['role'] == '1') {
            echo '<form action="edit-employee.php" method="get" class="mt-4">'
                .'<input type="hidden" name="empID" id="empID" value="'. $id .'">'
                .'<input type="submit" value="Edit" class="bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-2 px-8 rounded">'
                .'</form>';
        }
        ?>
        </div>
    </div>
</div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>