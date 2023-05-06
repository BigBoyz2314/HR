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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
    <title>View Employees By Month</title>
</head>
<body>  
    <?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>View Employee</h1>
        <form action="view-employees.php" method="get">
        <div class="row">
            <div class="col-md-6 pt-4">
                    <h4>Joining Month</h4>
                    <select class="js-example-basic-single w-50" name="to">
                        <option></option>
                        <?php
                        $i = 1;
                        while ($i <= 12) {
                            echo '<option value="'. $i .'">';echo date("F", mktime(0, 0, 0, $i, $i));'</option>';
                            $i++;
                        }
                        ?>
                    </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-3">Submit</button>
            </div> 
        </form>  
    </div>
    <script>
        
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>