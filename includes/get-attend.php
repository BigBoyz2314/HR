<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $eid = $_REQUEST["emp"];
        $month = $_REQUEST["timeIn"];
        $year = $_REQUEST["timeOut"];
    }

    $sql = "SELECT * FROM `attendance` WHERE `employeeID` = '$eid' AND `month` = '$month' = `year` = '$year'";


    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return $row;

?>