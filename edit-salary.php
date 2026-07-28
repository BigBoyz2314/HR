<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }
    if ($_SESSION['role'] != '1') {
        header("location: index.php");
        exit;
    }
    require_once('includes/config.php');

    $id = $_GET['id'];
    $stmt = "SELECT * FROM salary1 WHERE `employeeID` = '$id'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $eid = $row['employeeID'];
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];
    $month = $row['month'];
    $year = $row['year'];
    $desig = $row['designation'];
    $dept = $row['department'];
    $gender = $row['gender'];
    $paydays = $row['pay_days'];
    $basic = $row['basic_salary'];
    $allowance = $row['allowance'];
    $deduction = $row['deduction'];
    $gross = $row['gross_salary'];                                    
    $payable = $row['payable'];                             
    $absent = $row['absent'];                                 
    $paid = $row['paid'];
    $remaining = $row['remaining']; 

    $stmt1 = "SELECT SUM(remaining) AS total_remaining FROM salary1 WHERE employeeID = $eid";
    $result1 = $conn->query($stmt1);
    $row1 = $result1->fetch_assoc();
    $total_remaining = $row1['total_remaining'];

    if ($mname == '') {
        $name = $fname . ' ' . $lname;
    }
    else {
        $name = $fname . ' ' . $mname . ' ' . $lname;

    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <title>Edit Salary</title>
</head>
<body class="h-screen overflow-hidden">
<?php include 'includes/nav1.php' ?>
<div class="flex h-[calc(100vh-4rem)]">
<?php include 'includes/side-nav.php' ?>
<div class="flex-1 bg-gray-100 overflow-y-auto">
    <div class="max-w-4xl mx-auto p-4 md:p-6 bg-white rounded-lg shadow-lg mt-4 md:mt-8 mb-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Edit Salary</h1>
            <p class="text-gray-600">Update salary information and payment status</p>
        </div>
        <form action="includes/pay-salary.php" method="post" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Name</label>
                    <input type="text" disabled name="name" id="name" value="<?php echo $name?>" class="w-full rounded border-gray-300 bg-gray-100">
                    <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
                    <input type="hidden" name="fname" id="fname" value="<?php echo $fname ?>">
                    <input type="hidden" name="payable" id="payable" value="<?php echo $payable ?>">
                    <input type="hidden" name="paid" id="paid" value="<?php echo $paid ?>">
                    <input type="hidden" name="remaining" id="remaining" value="<?php echo $remaining ?>">
                    <input type="hidden" name="year" id="year" value="<?php echo $year ?>">
                    <input type="hidden" name="month" id="month" value="<?php echo $month ?>">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Payable</label>
                    <input type="text" disabled name="payable" id="payable" value="<?php echo $payable?>" class="w-full rounded border-gray-300 bg-gray-100">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Paid</label>
                    <input type="text" disabled name="paid" id="paid" value="<?php echo $paid?>" class="w-full rounded border-gray-300 bg-gray-100">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Total Remaining</label>
                    <input type="text" disabled name="total-remaining" id="total-remanining" value="<?php echo $total_remaining?>" class="w-full rounded border-gray-300 bg-gray-100">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Remaining</label>
                    <input type="text" disabled name="remaining" id="remaining" value="<?php echo $remaining?>" class="w-full rounded border-gray-300 bg-gray-100">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Pay</label>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="same" id="same" class="h-5 w-5 text-green-600" onclick="check()">
                        <span class="text-gray-700">Pay Full</span>
                    </div>
                    <input type="number" name="pay" id="pay" value="" max="<?php echo $remaining ?>" min="0" class="w-full rounded border-gray-300 mt-2">
                </div>
            </div>
            <div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">Submit</button>
            </div>
        </form>
    </div>
</div>
</div>
    <script>
        function check() {
            
            if (document.getElementById('same').checked) {

                console.log('checked');

                $remaining = document.getElementById('remaining').value;

                document.getElementById('pay').value = $remaining;
            }
            else {
                document.getElementById('pay').value = '';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>    
</body>
</html>