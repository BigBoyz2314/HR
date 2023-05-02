<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
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
    <title>Add Employees</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item dropdown active">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                    Employees
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <a class="dropdown-item text-white-50" href="employees.php">Add Employees</a>
                        <a class="dropdown-item text-white-50" href="view-employees.php">View Employees</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="department.php">Departments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="designation.php">Designations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Log out</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid p-5">
        <h1>Add Employees</h1>
        <form action="add-employess.php" method="post">
            <div class="row justify-content-center">
                <div class="col-md-3 pt-4">
                    <h4>First Name</h4>
                    <input type="text" name="fName" id="fName" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Middle Name</h4>
                    <input type="text" name="mName" id="mName" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Last Name</h4>
                    <input type="text" name="lName" id="lName" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Date of Birth</h4>
                    <input type="date" name="dob" id="dob" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Designation</h4>
                    <input type="text" name="designation" id="designation" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Department</h4>
                    <input type="text" name="department" id="department" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Martital Status</h4>
                    <input type="text" name="mStatus" id="mStatus" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Children</h4>
                    <input type="text" name="children" id="children" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Supouse Name</h4>
                    <input type="text" name="spouseName" id="spouseName" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Basic Salary</h4>
                    <input type="text" name="basicSalary" id="basicSalary" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Allowence</h4>
                    <input type="text" name="allowence" id="allowence" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 pt-4">
                    <h4>Deduction</h4>
                    <input type="text" name="deduction" id="deduction" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Gross Salary</h4>
                    <input type="text" name="grossSalary" id="grossSalary" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Join Date</h4>
                    <input type="date" name="joinDate" id="JoinDate" class="form-control">
                </div>
                <div class="col-md-3 pt-4">
                    <h4>Leave Date</h4>
                    <input type="date" name="leaveDate" id="leaveDate" class="form-control">
                </div>
            </div>
        </form>
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>