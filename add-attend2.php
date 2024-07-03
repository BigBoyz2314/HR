<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $eid = $_REQUEST["emp"];
        $date = $_REQUEST["date"];
        // $timeIn = $_REQUEST["timeIn"];
        // $timeOut = $_REQUEST["timeOut"];
    }

    $day = date('d', strtotime($date));
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    $date = date('Y-m-d', strtotime($date));

    $timeIn = date('Y-m-d h:i A', strtotime($date));
    $timeOut = date('Y-m-d h:i A', strtotime($date));
    
    $d = 1;
    $t = $day;
    $dt = date($year."-".$month."-".$d);
    $dt1 = strtotime($dt);
    $fs = array();

    while ($d <= $t) {
        $dt = date($year."-".$month."-".$d);
        $dt1 = strtotime($dt);
        $dt2 = date("D", $dt1);
        $dt3 = date("j", $dt1);
        if ($dt2 == 'Sun') {
            $fs[] = $dt3; 
        }
        
        $d++;
        
    }
    echo $timeIn;
    if (in_array($day - 1, $fs)) {
        $dayfs = $day - 1;

        $stmt ="SELECT * FROM `attendance1` WHERE `employeeID`  = '$eid' AND `month` = '$month' AND `year` = '$year' AND `day` = $day - 1";

        $result = $conn->query($stmt);
        if ($result->num_rows == 0) {
            $sql0 ="INSERT INTO `attendance1` VALUES ('', '$eid', '', '$dayfs', '$month', '$year', '$date', '$timeIn', '$timeOut', current_timestamp(), current_timestamp(), '')";
            mysqli_query($conn, $sql0);
        }
    }
    elseif (in_array($day + 1, $fs)) {
        $dayfs = $day + 1;

        $stmt1 ="SELECT * FROM `attendance1` WHERE `employeeID`  = '$eid' AND `month` = '$month' AND `year` = '$year' AND `day` = $day + 1";

        $result1 = $conn->query($stmt1);
        if ($result1->num_rows == 0) {
            $sql ="INSERT INTO `attendance`1 VALUES ('', '$eid', '', '$dayfs', '$month', '$year', '$date', '$timeIn', '$timeOut', current_timestamp(), current_timestamp(), '')";
            mysqli_query($conn, $sql);
        }
    }
    
    $sql1 ="INSERT INTO `attendance1` VALUES ('', '$eid', '', '$day', '$month', '$year', '$date', '$timeIn', '$timeOut', current_timestamp(), current_timestamp(), '')";
    
    if(mysqli_query($conn, $sql1)){

        header('Location: add-attendance.php?emp='. $eid .'&action=added');
    
    } else{
        echo "ERROR: Hush! Sorry $sql. "
        . mysqli_error($conn);
    }

?>