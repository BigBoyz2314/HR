<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $id = $_REQUEST["delID"];
        $name = $_REQUEST["delName"];
    }

        $sql ="DELETE FROM `department` WHERE `departmentID` = '$id' AND `name` = '$name'";
        
        if(mysqli_query($conn, $sql)){
            header('Location: department.php?del='. $name .'');
        } else{
            echo "ERROR: Hush! Sorry $sql. " . mysqli_error($conn);
        }

?>