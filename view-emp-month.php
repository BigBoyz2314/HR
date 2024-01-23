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
        });
    </script>
    <title>View Employees By Month</title>
</head>
<body>  
<?php include 'nav1.php' ?>
<div id="layoutSidenav">
<?php include 'side-nav.php' ?>
<div id="layoutSidenav_content">
    <div class="container-fluid p-5">
        <h1>View Employee</h1>
        <div class="row">
            <div class="col-md-6 pt-4">
                <form action="view-employees.php" method="get">
                    <h4>Joining Month</h4>
                    <select class="js-example-basic-single w-50" name="moj">
                        <option></option>
                        <?php
                        $i = 1;
                        while ($i <= 12) {
                            echo '<option value="'. $i .'">';echo date("F", mktime(0, 0, 0, $i, $i));'</option>';
                            $i++;
                        }
                        ?>
                    </select>
                        <button type="submit" class="btn btn-success mt-3 d-block">Submit</button>
                    </form>  
                    </div>
            <div class="col-md-6 pt-4">
                <form action="join-leave.php" method="get">
                    <h4>Join / Leave</h4>
                    <select class="js-example-basic-single w-50" name="joinleave">
                        <option></option>
                        <?php
                        $i = 1;
                        while ($i <= 12) {
                            echo '<option value="'. $i .'">';echo date("F", mktime(0, 0, 0, $i, $i));'</option>';
                            $i++;
                        }
                        ?>
                    </select>
                        <button type="submit" class="btn btn-success mt-3 d-block">Submit</button>
                </form>
            </div>
        </div>
    </div> 
</div>
</div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>