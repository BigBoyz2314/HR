<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = ucwords($_REQUEST["name"]);
        $allowed = $_REQUEST["allowed"];
    }

        $sql ="INSERT INTO department VALUES ('', '$name', '0', '$allowed', current_timestamp(), current_timestamp())";
        
        if(mysqli_query($conn, $sql)){

            header('Location: department.php?add='. $name .'');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>