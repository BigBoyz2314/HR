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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" ></script>
    <script src="js/tableHTMLExport.js"></script>
    <script>
        $(document).ready(function(){
            
            function printData() {
                $("#table").removeClass();
                var divToPrint = document.getElementById("table");
                newWin= window.open("");
                newWin.document.write('<!DOCTYPE html><html><head>  <title>Print Preview</title>  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" >  <style>    table {        font-size: small;    }   form { display: none;   }  </style></head><body><div class="table table-bordered w-100 text-center">');
                newWin.document.write(divToPrint.outerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                newWin.print();
                newWin.close();
                window.location.reload()
            }
            document.querySelector('#browserPrint').addEventListener('click', printData);

            $(".export-btn").click(function(){  
                $("#table").tableHTMLExport({
                type:'csv',
                filename:'employee-log.csv',
                });
            });

            $("#search").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
   
    <link rel="stylesheet" href="css/styles.css">
    <title>View Attendance</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid py-5">
        <h1>View Attendance</h1>
        <h4>Month: <?php echo date("F", mktime(0, 0, 0, $_GET['month'], 10)); ?></h4>
        <h4>Year: <?php echo $_GET['year']; ?></h4>

        <div class="row mt-5">
            <button class="btn btn-info m-3 export-btn">Export to Excel</button>
            <button class="btn btn-danger m-3" id="browserPrint">Print PDF</button>
                <input type="text" name="search" id="search" class="form-control w-25 ml-auto" placeholder="Search...">	
            <div class="col-md-12">
                <table class="table table-responsive text-nowrap table-bordered w-100 text-center" id="table">

                </table>
            </div>
        </div>
    </div>
  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>