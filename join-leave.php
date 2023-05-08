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
    <title>Join / Leave</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Join / Leave</h1>

        <?php
        if (isset($_GET['joinleave'])) {

            $month = $_GET['joinleave'];
        
            $stmt = "SELECT * FROM employees WHERE join_date = $";
            $result = $conn->query($stmt);
            $i = 1;

            if ($result->num_rows > 0) {
                // output data of each row
                
                while($row = $result->fetch_assoc()) { 
                    $id = $row['employeeID'];
                    $fname = $row['fname'];
                    $mname = $row['mname'];
                    $lname = $row['lname'];
                    $dob = $row['dob'];
                    $desig = $row['designation'];
                    $dept = $row['department'];
                    $gender = $row['gender'];
                    $mstatus = $row['martital_status'];
                    $joindate = $row['join_date'];
                    $status = $row['status'];
                    $children = $row['children'];
                    $spouse = $row['spouse_name'];
                    $basic = $row['basic_salary'];
                    $allowance = $row['allowance'];
                    $deduction = $row['deduction'];
                    $gross = $row['gross_salary'];

                    echo "<tr>";
                    echo "<td>". $i++ ."</td>";
                    echo "<td>$fname</td>";
                    echo "<td>$mname</td>";
                    echo "<td>$lname</td>";
                    echo "<td>". date("d M y", strtotime($dob)) ."</td>";
                    echo "<td>$desig</td>";
                    echo "<td>$gender</td>";
                    echo "<td>$dept</td>";
                    echo "<td>$mstatus</td>";
                    echo "<td>$status</td>";
                    echo "<td>". date("d M y", strtotime($joindate)) ."</td>";
                    echo "<td>$spouse</td>";
                    echo "<td>$basic</td>";
                    echo "<td>$allowance</td>";
                    echo "<td>$deduction</td>";
                    echo "<td>$gross</td>";
                    echo "<td><form action='view-emp.php' method='get'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='View Details' class='btn btn-info'></form></td>";
                    // echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                    echo "</tr>";

                }
            }
        }
        ?>
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>