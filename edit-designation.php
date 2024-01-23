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

    $desigName = $_GET['desigName'];
    $id = $_GET['id'];
    $stmt = "SELECT * FROM designation WHERE name='$desigName' AND designationID = '$id'";
    $result = $conn->query($stmt);
    $row = $result->fetch_assoc();
    $grade = $row["grade"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/styles1.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <title>Edit Designation</title>
</head>
<body>
<?php include 'nav1.php' ?>
<div id="layoutSidenav">
<?php include 'side-nav.php' ?>
<div id="layoutSidenav_content">
    <div class="container-fluid p-5">
        <h1>Edit Designation</h1>
        <form action="edit-desig.php" method="post">
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Name</h4>
                    <input type="text" disabled name="name" id="name" value="<?php echo $desigName ?>" class="form-control w-50">
                    <input type="hidden" name="name1" id="name1" value="<?php echo $desigName ?>" class="form-control w-50">
                    <input type="hidden" name="id" id="id" value="<?php echo $id ?>" class="form-control w-50">
                </div>
            </div>
            <div class="row">
                <div class="col-6 pt-4">
                    <h4>Grade</h4>
                    <input type="number" min="1" name="grade" id="grade" value="<?php echo $grade ?>" class="form-control w-50" required>
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
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>