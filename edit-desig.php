<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_REQUEST["name1"];
        $grade = $_REQUEST["grade"];
    }

        $sql ="UPDATE `designation` SET `name`='$name', `grade`='$grade',`updated_at` = current_timestamp() WHERE `name` = '$name'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: designation.php?edited='. $name .'');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>