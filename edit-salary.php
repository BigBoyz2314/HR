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
    require_once('config.php');

    $id = $_GET['id'];
    $stmt = "SELECT * FROM salary WHERE `id` = '$id'";
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

    $stmt1 = "SELECT SUM(remaining) AS total_remaining FROM salary WHERE employeeID = $eid";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <title>Edit Salary</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Edit Salary</h1>
        <form action="pay-salary.php" method="post">
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Name</h4>
                    <input type="text" disabled name="name" id="name" value="<?php echo $name?>" class="form-control">
                    <input type="hidden" name="id" id="id" value="<?php echo $id ?>" class="form-control">
                    <input type="hidden" name="fname" id="fname" value="<?php echo $fname ?>" class="form-control">
                    <input type="hidden" name="payable" id="payable" value="<?php echo $payable ?>" class="form-control">
                    <input type="hidden" name="paid" id="paid" value="<?php echo $paid ?>" class="form-control">
                    <input type="hidden" name="remaining" id="remaining" value="<?php echo $remaining ?>" class="form-control">
                    <input type="hidden" name="year" id="year" value="<?php echo $year ?>" class="form-control">
                    <input type="hidden" name="month" id="month" value="<?php echo $month ?>" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Payable</h4>
                    <input type="text" disabled name="payable" id="payable" value="<?php echo $payable?>" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Paid</h4>
                    <input type="text" disabled name="paid" id="paid" value="<?php echo $paid?>" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Total Remaining</h4>
                    <input type="text" disabled name="total-remaining" id="total-remanining" value="<?php echo $total_remaining?>" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Remaining</h4>
                        <input type="text" disabled name="remaining" id="remaining" value="<?php echo $remaining?>" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4 class="d-inline-block">Pay</h4>
                    <input type="checkbox" name="same" id="same" class="float-right mt-1 ml-1" onclick="check()">
                    <p class="d-inline-block float-right">Pay Full</p>
                    <input type="number" name="pay" id="pay" value="" max="<?php echo $remaining ?>" min="0" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form> 
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>