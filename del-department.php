<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "GET" && $_SESSION['role'] == '1') {
        $id = $_REQUEST["delID"];
        $name = $_REQUEST["delName"];

    }

        $sql ="DELETE FROM `department` WHERE `departmentID` = '$id' AND `name` = '$name'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: department.php');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>