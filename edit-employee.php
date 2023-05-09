<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: login.php");
        exit;
    }
    require_once('config.php');

    $empID = $_GET['empID'];
    $stmt = "SELECT * FROM employees WHERE employeeID = '$empID'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $desig = $row["designation"];
    $fname = $row["fname"];
    $mname = $row["mname"];
    $lname = $row["lname"];
    $dept = $row["department"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <title>Edit Employee</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Edit Designation</h1>
        <form action="edit-emp.php" method="post">
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Name</h4>
                    <input type="text" disabled name="name" id="name" value="<?php echo $fname . "" . $mname . " " . $lname ?>" class="form-control w-50">
                    <input type="hidden" name="name1" id="name1" value="<?php echo $fname ?>" class="form-control w-50">
                    <input type="hidden" name="empID" id="empID" value="<?php echo $empID ?>" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Designation</h4>
                    <input type="text" disabled value="<?php echo $desig ?>" class="form-control w-50">
                    <h4 class="pt-4">Transfer to</h4>
                    <input type="text"value="" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Department</h4>
                    <input type="text" disabled value="<?php echo $dept ?>" class="form-control w-50">
                    <h4 class="pt-4">Transfer to</h4>
                    <input type="text" value="" class="form-control w-50">
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