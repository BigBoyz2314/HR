<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"]) {
        $id = $_REQUEST["delID"];
        $name = $_REQUEST["delName"];
    }

        $sql ="DELETE FROM `designation` WHERE `designationID` = '$id' AND `name` = '$name'";
        
        if(mysqli_query($conn, $sql)){
            header('Location: designation.php?del='. $name .'');
        } else{
            echo "ERROR: Hush! Sorry $sql. " . mysqli_error($conn);
        }

?>