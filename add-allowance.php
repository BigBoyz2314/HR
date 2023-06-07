<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
    require_once('config.php');

    $id = $_GET['emp'];
    $month = $_GET['month'];
    $year = $_GET['year'];

    $sql = "SELECT * FROM employees WHERE employeeID =  $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];

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
    <title>Add Allowances</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Add Allowance</h1>
        <form action="add-department.php" method="post">
            <div class="row pt-4 align-items-end">
                <div class="col-md-3">
                    <h5>Employee Name</h5>
                    <input type="text" name="name" id="name" value="<?php echo $name ?>" class="form-control w-100" disabled>
                    <input type="text" name="name" id="name" value="<?php echo $name ?>" class="form-control w-100" hidden>
                </div>
                <div class="col-md-2">
                    <h5>Month</h5>
                    <input type="text" value="<?php echo date("F", mktime(0, 0, 0, $month, 10)) ?>" name="month" id="month" class="form-control w-100" disabled>
                    <input type="text" value="<?php echo $month ?>" name="month" id="month" class="form-control w-100" hidden>
                </div>
                <div class="col-md-2">
                    <h5>Month</h5>
                    <input type="text" value="<?php echo $year ?>" name="year" id="year" class="form-control w-100" disabled>
                    <input type="text" value="<?php echo $year ?>" name="year" id="year" class="form-control w-100" hidden>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form> 
        <!-- <div class="row mt-5">
            <div class="col-md-12">
                <table class="table text-nowrap table-bordered w-100 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>Department Name</th>
                        <th>Current Strength</th>
                        <th>Allowed Strength</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </thead>
                    <tbody class="">
                        <?php
                            // $stmt = "SELECT * FROM department";
                            // $result = $conn->query($stmt);
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
                                    if ($_SESSION['role'] == '1') {
                                        echo "<td><form action='edit-department.php' method='get'><input type='hidden' name='deptName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Edit' class='btn btn-warning'></form></td>";
                                        echo "<td><form action='' method='get'><input type='hidden' name='deptName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                    }
                                    echo "</tr>";
    
                                }
                              }

                        ?>
                    </tbody>
            </table>
            </div>
        </div>  -->
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>