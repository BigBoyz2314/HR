<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_REQUEST["id"];
        $name = $_REQUEST["name1"];
        $allowed = $_REQUEST["allowed"];
        $current = $_REQUEST["current"];
    }

        $sql ="UPDATE `department` SET `name`='$name',`current_Strength`='$current',`allowed_Strength`='$allowed',`updated_at` = current_timestamp() WHERE `name` = '$name' AND `departmentID` = '$id'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: department.php');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>