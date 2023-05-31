<?php
    require_once('config.php');
?>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_REQUEST["id"];
        $payable = $_REQUEST["payable"];
        $paid = $_REQUEST["paid"];
        $remaining = $_REQUEST["remaining"];
        $pay = $_REQUEST["pay"];
    }

        $sql ="UPDATE `designation` SET `name`='$name', `grade`='$grade',`updated_at` = current_timestamp() WHERE `name` = '$name' AND `designationID` = '$id'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: designation.php');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>