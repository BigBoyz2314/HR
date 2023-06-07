<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
    require_once('config.php');

    $eid = $_GET['emp'];
    $month = $_GET['month'];
    $year = $_GET['year'];

    $sql = "SELECT * FROM employees WHERE employeeID =  $eid";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];

    if ($mname == '') {
        $name = $fname . ' ' . $lname;
    }
    else {
        $name = $fname . ' ' . $mname . ' ' . $lname;

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
    <title>Add Allowances</title>
</head>
<body>
<?php include 'nav.php' ?>
    <div class="container-fluid p-5">
        <h1>Add Allowance</h1>
        <form action="add-allow.php" method="post">
            <div class="row pt-4">
                <div class="col-md-3">
                    <h5>Employee Name</h5>
                    <input type="text" name="name" id="name" value="<?php echo $name ?>" class="form-control w-100" disabled>
                    <input type="text" name="name" id="name" value="<?php echo $name ?>" class="form-control w-100" hidden>
                    <input type="text" name="eid" id="eid" value="<?php echo $eid ?>" class="form-control w-100" hidden>
                </div>
                <div class="col-md-2">
                    <h5>Month</h5>
                    <input type="text" value="<?php echo date("F", mktime(0, 0, 0, $month, 10)) ?>" name="month" id="month" class="form-control w-100" disabled>
                    <input type="text" value="<?php echo $month ?>" name="month" id="month" class="form-control w-100" hidden>
                </div>
                <div class="col-md-2">
                    <h5>Month</h5>
                    <input type="text" value="<?php echo $year ?>" name="year" id="year" class="form-control w-100" disabled>
                    <input type="text" value="<?php echo $year ?>" name="year" id="year" class="form-control w-100" hidden>
                </div>
            </div>
            <div class="row pt-4 align-items-end">
                <div class="col-md-2">
                    <h5>Allowance Type</h5>
                    <select class="js-example-basic-single w-100" name="type" required>
                        <option></option>
                        <option value="Travel">Travel</option>
                        <option value="Food">Food</option>
                        <option value="Mobile">Mobile</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5>Amount</h5>
                    <input type="number" name="amount" id="amount" class="form-control" min="0" required>
                </div>
                <div class="col-md-3">
                    <h5>Description</h5>
                    <input type="text" name="desc" id="desc" class="form-control" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form> 
        <div class="row pt-4">
        <div class="col-md-12">
                <table class="table text-nowrap table-bordered w-100 text-center" id="table">
                    <thead class="font-weight-bolder">
                        <th>Sr.</th>
                        <th>Allowance Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </thead>
                    <tbody class="">
                        <?php
                            $stmt = "SELECT * FROM `allowances` WHERE `employeeID` = '$eid' AND `month` = '$month' AND `year` = '$year'";
                            $result = $conn->query($stmt);
                            $i = 1;
    
                            if ($result->num_rows > 0) {
                                // output data of each row
                                
                                while($row = $result->fetch_assoc()) {
                                    $id = $row['id'];
                                    $eid = $row['employeeID'];
                                    $type = $row['allowance_type'];
                                    $amount = $row['amount'];
                                    $desc = $row['description'];
                                    $created = $row['created_at']; 
                                    $updated = $row['updated_at']; 

                                    echo "<tr>";
                                    echo "<td>". $i++ ."</td>";
                                    echo "<td>$type</td>";
                                    echo "<td>$desc</td>";
                                    echo "<td>$amount</td>";
                                    echo "<td>". date("d M y g:i:s A", strtotime($created)) ."</td>";
                                    echo "<td>". date("d M y g:i:s A", strtotime($updated)) ."</td>";
                                    if ($_SESSION['role'] == '1') {
                                        // echo "<td><form action='edit-allowance.php' method='get'><input type='hidden' name='year' value='". $year ."'><input type='hidden' name='month' value='". $month ."'><input type='hidden' name='eid' value='". $eid ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Edit' class='btn btn-warning'></form></td>";
                                        echo "<td><form id='del-form' action='del-allowance.php' method='get'><input type='hidden' name='year' value='". $year ."'><input type='hidden' name='month' value='". $month ."'><input type='hidden' name='eid' value='". $eid ."'><input type='hidden' name='id' value='". $id ."'><input type='submit' value='Delete' class='btn btn-danger'></form></td>";
                                    }
                                    echo "</tr>";
    
                                }
                              }
                        ?>
                    </tbody>
            </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" ></script>    
</body>
</html>