<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_REQUEST["name"];
        $grade = $_REQUEST["grade"];
    }

        $sql ="INSERT INTO designation VALUES ('', '$name', '$grade', current_timestamp())";
        
        if(mysqli_query($conn, $sql)){
            
            header('Location: designation.php');
            
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>