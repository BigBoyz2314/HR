<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $id = $_REQUEST["id"];
        $eid = $_REQUEST["eid"];
        $month = $_REQUEST["month"];
        $year = $_REQUEST["year"];
    }

        $sql ="DELETE FROM `deductions` WHERE `id` = '$id' AND `employeeID` = '$eid' AND `month` = '$month' AND `year` = '$year'";
        
        if(mysqli_query($conn, $sql)){
            header('Location: add-deduction.php?emp='. $eid .'&month='. $month .'&year='. $year .'');
        } else{
            echo "ERROR: Hush! Sorry $sql. " . mysqli_error($conn);
        }

?>