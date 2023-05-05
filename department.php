<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <title>Department</title>
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
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                    Employees
                    </a>
                    <div class="dropdown-menu bg-dark">
                        <a class="dropdown-item text-white-50" href="employees.php">Add Employees</a>
                        <a class="dropdown-item text-white-50" href="view-employees.php">View All Employees</a>
                        <a class="dropdown-item text-white-50" href="view-employees.php">View Employees by Name</a>
                    </div>
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
    <div class="container-fluid p-5">
        <h1>Add Department</h1>
        <form action="add-department.php" method="post">
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Name</h4>
                    <input type="text" name="name" id="name" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Allowed Strength</h4>
                    <input type="number" min="0" name="allowed" id="allowed" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Current Strength</h4>
                    <input type="number" min="0" name="current" id="current" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-3 pt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form> 
        <div class="row mt-5">
            <div class="col-md-12">
                <table class="table table-bordered w-100 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>Department Name</th>
                        <th>Current Strength</th>
                        <th>Allowed Strength</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th></th>
                    </thead>
                    <tbody class="">
                        <?php
                            $stmt = "SELECT * FROM department";
                            $result = $conn->query($stmt);
                            $i = 1;
    
                            if ($result->num_rows > 0) {
                                // output data of each row
                                
                                while($row = $result->fetch_assoc()) {
                                    $id = $row['departmentID'];
                                    $name = $row['name'];
                                    $current = $row['current_Strength'];
                                    $allowed = $row['allowed_Strength']; 
                                    $created = $row['created_at']; 
                                    $updated = $row['updated_at']; 

                                    echo "<tr>";
                                    echo "<td>". $i++ ."</td>";
                                    echo "<td>$name</td>";
                                    echo "<td>$current</td>";
                                    echo "<td>$allowed</td>";
                                    echo "<td>". date("d M y g:i:s A", strtotime($created)) ."</td>";
                                    echo "<td>". date("d M y g:i:s A", strtotime($updated)) ."</td>";
                                    echo "<td><form action='edit-department.php' method='get'><input type='hidden' name='deptName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Edit' class='btn btn-warning'></form></td>";
                                    echo "<td><form action='' method='get'><input type='hidden' name='deptName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                    echo "</tr>";
    
                                }
                              }

                              if ($_REQUEST) {
                                $delID = $_GET['id'];
                                $delName = $_GET['deptName'];
                                  
                                    echo '<script type="text/javascript"> ';  
                                    echo '  if (confirm("Are you sure you want to DELETE '. "'$delName'" .'?")) {';  
                                    echo '    window.location.href = "del-department.php?delName='. $delName .'&delID='. $delID .'";';  
                                    echo '  }';
                                    echo '</script>';  
                              }
                        ?>
                    </tbody>
            </table>
            </div>
        </div> 
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>