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
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({theme: "bootstrap4"});

            setTimeout(function() {
            $(".alert").alert('close');
            }, 3000);
        });
    </script>
    <title>View Salary</title>
</head>
<body>  
<?php 
    if (isset($_GET['action'])) {
    if (($_GET['action']) == 'generated') {
        $month = $_GET['month'];
        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        echo '<div class="alert alert-success alert-dismissible fade show position-absolute paid" role="alert">
            <strong>Salary Generated</strong> for '. $monthName .''. ' '. $_GET['year'] .'
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>';
        }
    }
?>
    <?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>View Salary</h1>
        <div class="row">
            <div class="col-md-6 pt-4">
                <form action="view-salary.php" method="get">
                    <h4>Month</h4>
                    <select class="js-example-basic-single w-50" name="month" required>
                        <option></option>
                        <?php
                        $i = 1;
                        while ($i <= 12) {
                            echo '<option value="'. $i .'">';echo date("F", mktime(0, 0, 0, $i, $i));'</option>';
                            $i++;
                        }
                        ?>
                    </select>
                    <h4 class="mt-3">Year</h4>
                    <select class="js-example-basic-single w-50" name="year" required>
                        <option></option>
                        <option>2023</option>
                    </select>
                    <button type="submit" class="btn btn-success mt-3 d-block">Submit</button>
                </form>  
            </div>
        </div>
    </div> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>