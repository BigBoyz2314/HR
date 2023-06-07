<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $eid = ucwords($_REQUEST["eid"]);
        $type = ucwords($_REQUEST["type"]);
        $month = $_REQUEST["month"];
        $year = $_REQUEST["year"];
        $amount = $_REQUEST["amount"];
        $desc = $_REQUEST["desc"];
    }

        $sql ="INSERT INTO deductions VALUES ('', '$eid', '$type', '$amount', '$desc', '$month', '$year', current_timestamp(), current_timestamp())";
        
        if(mysqli_query($conn, $sql)){

            header('Location: add-deduction.php?emp='. $eid .'&month='. $month .'&year='. $year .'&action=added');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>