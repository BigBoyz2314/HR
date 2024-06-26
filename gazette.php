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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="css/styles1.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({theme: "bootstrap4"});

            setTimeout(function() {
            $(".alert").alert('close');
            }, 3000);
        });
    </script>
    <title>Add Gazette Holiday</title>
</head>
<body class="sb-nav-fixed">  
<?php include 'nav1.php' ?>
<div id="layoutSidenav">
<?php include 'side-nav.php' ?>
<div id="layoutSidenav_content">
<?php 
    if (isset($_GET['add'])) {
        echo '<div class="alert alert-success alert-dismissible fade show position-fixed paid" role="alert">
                <strong>'. $_GET['action'] .'</strong>Holiday Added!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
?>
    <div class="container-fluid p-4">
        <h1>Add Gazette Holiday</h1>
        <form action="add-gazette.php" method="post">
            <div class="row pt-4">
                <div class="col-md-3">
                    <h4>Name</h4>
                    <input type="text" name="name" id="name"  class="form-control">
                </div>
                <div class="col-md-3">
                    <h4>Date</h4>
                    <input type="date" name="date" id="date"  class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success mt-3 d-block">Submit</button>
                </div>
            </div>
        </form>
    </div> 
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>    
</body>
</html>