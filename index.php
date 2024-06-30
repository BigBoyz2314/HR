<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once('config.php');

$query = "SELECT `month`, SUM(payable) AS total_payable FROM salary1 GROUP BY `month`";

$result = $conn->query($query);

$salary = array();

while ($row = $result->fetch_assoc()) {
    $monthName = date("F", mktime(0, 0, 0, $row['month'], 1));
    $salary[] = array("y" => $row['total_payable'], "label" => $monthName);
}

$query1 = "SELECT `name`, `current_Strength` FROM department";

$result1 = $conn->query($query1);

$dept = array();

while ($row = $result1->fetch_assoc()) {
    $dept[] = array("y" => $row['current_Strength'], "label" => $row['name']);
}
 

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard</title>
        <link href="css/styles1.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include("nav1.php") ?>
        <div id="layoutSidenav">
            <?php include("side-nav.php") ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card text-center mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5>Total Headcount</h5>
                                    </div>
                                    <div class="card-body">
                                        <h5>
                                        <?php 

                                        $query1 = "SELECT COUNT(employeeId) AS total_headcount FROM employees";
                                        $result1 = $conn->query($query1);
                                        $row1 = $result1->fetch_assoc();
                                        echo $row1['total_headcount'];


                                        ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">Warning Card</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">Success Card</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-body">Danger Card</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-area me-1"></i>
                                        Monthly Salary
                                    </div>
                                    <div class="card-body"><canvas id="chart1" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Department Strength
                                    </div>
                                    <div class="card-body"><canvas id="chart2" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script>
            window.onload = function () {

            Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.global.defaultFontColor = '#292b2c';
            
            // Your PHP data
        <?php
        require_once('config.php');

        $query = "SELECT `month`, SUM(payable) AS total_payable FROM salary GROUP BY `month`";
        $result = $conn->query($query);

        $salary = array();

        while ($row = $result->fetch_assoc()) {
            $monthName = date("F", mktime(0, 0, 0, $row['month'], 1));
            $salary[] = array("y" => $row['total_payable'], "label" => $monthName);
        }
        ?>

        // Convert PHP data to JavaScript
        var salaryData = <?php echo json_encode($salary); ?>;

        // Chart.js configuration
        var ctx = document.getElementById('chart1').getContext('2d');
        var areaChart = new Chart(ctx, {
            type: 'line', // Use 'line' type for area chart
            data: {
                labels: salaryData.map(item => item.label),
                datasets: [{
                    label: 'Total Payable',
                    data: salaryData.map(item => item.y),
                    lineTension: 0.3,
                    backgroundColor: "rgba(2,117,216,0.2)",
                    borderColor: "rgba(2,117,216,1)",
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(2,117,216,1)",
                    pointBorderColor: "rgba(255,255,255,0.8)",
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgba(2,117,216,1)",
                    pointHitRadius: 50,
                    pointBorderWidth: 2,
                }]
            },
            options: {
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false
        },
        ticks: {
          maxTicksLimit: 7
        }
      }],
      yAxes: [{
        ticks: {
          min: 0,
          max: 40000,
          maxTicksLimit: 5
        },
        gridLines: {
          color: "rgba(0, 0, 0, .125)",
        }
      }],
    },
    legend: {
      display: false
    }
  }
});
        
            // Your PHP data
        <?php
        require_once('config.php');

        $query1 = "SELECT `name` FROM department";
        $result1 = $conn->query($query1);


        $dept = array();

        while ($row = $result1->fetch_assoc()) {
            $deptName = $row['name'];
            $query2 =  "SELECT COUNT(department) AS current_Strength FROM employees WHERE department = '$deptName'";
        
            $result2 = $conn->query($query2);

            while ($row2 = $result2->fetch_assoc()) {
                $dept[] = array("y" => $row2['current_Strength'], "label" => $row['name']);
            }
        }
        ?>

        // Convert PHP data to JavaScript
        var deptData = <?php echo json_encode($dept); ?>;

        // Chart.js configuration
        var ctx = document.getElementById('chart2').getContext('2d');
        var barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: deptData.map(item => item.label),
                datasets: [{
                    label: 'Current Strength',
                    data: deptData.map(item => item.y),
                    backgroundColor: "rgba(2,117,216,1)",
                    borderColor: "rgba(2,117,216,1)",
                }]
            },
            options: {
    legend: {
      display: false
    }
  }
})};

        </script>
    </body>
</html>
