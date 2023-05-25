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

    $deptName = $_GET['deptName'];
    $id = $_GET['id'];
    $stmt = "SELECT * FROM department WHERE name='$deptName' AND departmentID = '$id'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $allow = $row["allowed_Strength"];
    $current = $row["current_Strength"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <title>Edit Department</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Edit Department</h1>
        <form action="edit-dept.php" method="post">
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Name</h4>
                    <input type="text" disabled name="name" id="name" value="<?php echo $deptName ?>" class="form-control w-50">
                    <input type="hidden" name="name1" id="name1" value="<?php echo $deptName ?>" class="form-control w-50">
                    <input type="hidden" name="id" id="id" value="<?php echo $id ?>" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Allowed Strength</h4>
                    <input type="number" min="1" name="allowed" id="allowed" value="<?php echo $allow ?>" class="form-control w-50" required>
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