<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_REQUEST["id"];
        $name = $_REQUEST["name1"];
        $allowed = $_REQUEST["allowed"];
    }

        $sql ="UPDATE `department` SET `name`='$name',`allowed_Strength`='$allowed',`updated_at` = current_timestamp() WHERE `name` = '$name' AND `departmentID` = '$id'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: department.php?edit='. $name .'');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>