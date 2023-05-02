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
    <title>Departments</title>
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
                <li class="nav-item">
                    <a class="nav-link" href="employees.php">Employees</a>
                </li>
                <li class="nav-item active">
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
    <div class="container-fluid">
    <div class="container-fluid p-5">
        <h1>Add Department</h1>
        <form action="add-department.php" method="post">
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Name</h4>
                    <input type="text" name="name" id="name" class="form-control w-50">
                </div>
                <div class="col-6 pt-4">
                    <h4>Allowed Strength</h4>
                    <input type="number" name="allowed" id="allowed" class="form-control w-50">
                </div>
                <div class="col-6 pt-4">
                    <h4>Current Strength</h4>
                    <input type="number" name="current" id="current" class="form-control w-50">
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