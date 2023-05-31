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
        $fname = $_REQUEST["fname"];
    }

    $pai = $paid + $pay;
    
    $remain = $remaining - $pai;

    echo $remaining;

        $sql ="UPDATE `salary` SET `remaining`='$remain', `paid`='$pai', `updated_at` = current_timestamp() WHERE `fname` = '$fname' AND `id` = '$id'";
        
        if(mysqli_query($conn, $sql)){

            header('Location: salary.php?action=paid');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>