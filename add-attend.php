<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $eid = $_REQUEST["emp"];
        $timeIn = $_REQUEST["timeIn"];
        $timeOut = $_REQUEST["timeOut"];
    }

    $day = date('d', strtotime($timeIn));
    $month = date('m', strtotime($timeIn));
    $year = date('Y', strtotime($timeIn));
    $date = date('Y-m-d', strtotime($timeIn));

    $timeIn = date('Y-m-d h:i A', strtotime($timeIn));
    $timeOut = date('Y-m-d h:i A', strtotime($timeOut));

    $sql ="INSERT INTO `attendance` VALUES ('', '$eid', '', '$day', '$month', '$year', '$date', '$timeIn', '$timeOut', current_timestamp(), current_timestamp(), '')";
    
    if(mysqli_query($conn, $sql)){

        header('Location: add-attendance.php?emp='. $eid .'&action=added');
    
    } else{
        echo "ERROR: Hush! Sorry $sql. "
        . mysqli_error($conn);
    }

?>