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
        <form action="" method="post">
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Name</h4>
                    <input type="text" disabled name="name" id="name" value="<?php echo $name?>" class="form-control w-50">
                    <input type="hidden" name="id" id="id" value="<?php echo $id ?>" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-3 pt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form> 
        </div> 
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>