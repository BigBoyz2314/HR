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
    <title>Designation</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Add Designation</h1>
        <form action="add-designation.php" method="post">
            <div class="row align-items-end">
                <div class="col-md-3 pt-4">
                    <h5>Designation</h5>
                    <input type="text" name="name" id="name" class="form-control w-100" required>
                </div>
                <div class="col-md-2 pt-4">
                    <h5>Grade</h5>
                    <input type="number" min="1" name="grade" id="grade" class="form-control w-100" required>
                </div>
                <div class="col-md-3 pt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>  
        <div class="row mt-5">
            <div class="col-md-12">
                <table class="table text-nowrap table-bordered w-75 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>Designation Name</th>
                        <th>Grade</th>
                    </thead>
                    <tbody class="">
                        <?php
                            $stmt = "SELECT * FROM designation ORDER BY grade DESC";
                            $result = $conn->query($stmt);
                            $i = 1;
    
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {

                                    $id = $row['designationID'];
                                    $name = $row['name'];
                                    $grade = $row['grade'];

                                    echo "<tr>";
                                    echo "<td>". $i++ ."</td>";
                                    echo "<td>$name</td>";
                                    echo "<td>$grade</td>";

                                    if ($_SESSION['role'] == '1') {
                                        echo "<td><form action='edit-designation.php' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Edit' class='btn btn-warning'></form></td>";
                                        echo "<td><form action='' method='get'><input type='hidden' name='desigName' value='". $name ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                    }

                                    echo "</tr>";
                                }
                            }

                            if ($_REQUEST) {
                                $delID = $_GET['id'];
                                $delName = $_GET['desigName'];
                                  
                                echo '<script type="text/javascript"> ';  
                                echo '  if (confirm("Are you sure you want to DELETE '. "'$delName'" .'?")) {';  
                                echo '    window.location.href = "del-designation.php?delName='. $delName .'&delID='. $delID .'";';  
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