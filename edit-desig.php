<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_REQUEST["name1"];
        $grade = $_REQUEST["grade"];
        $id = $_REQUEST["id"];
    }

        $sql ="UPDATE `designation` SET `name`='$name', `grade`='$grade',`updated_at` = current_timestamp() WHERE `name` = '$name' AND `designationID` = '$id'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: designation.php?edit='. $name .'');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>