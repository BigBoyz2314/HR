<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_REQUEST["name"];
        $date = $_REQUEST["date"];
    }

        $day = date('d', strtotime($date));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        
        $sql ="INSERT INTO holidays VALUES ('', '$name', '$day', '$month', '$year', '$date', current_timestamp(), current_timestamp())";
        
        if(mysqli_query($conn, $sql)){

            header('Location: gazette.php?action=added');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>