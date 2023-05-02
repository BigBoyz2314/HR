<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_REQUEST["name"];
        $allowed = $_REQUEST["allowed"];
        $current = $_REQUEST["current"];
    }

        $sql ="INSERT INTO department VALUES ('', '$name', '$current', '$allowed', current_timestamp())";
        
        if(mysqli_query($conn, $sql)){
            echo $category;
            header('Location: department.php');
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>