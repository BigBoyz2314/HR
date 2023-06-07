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
        $year = $_REQUEST["year"];
        $month = $_REQUEST["month"];
    }

    $pai = $paid + $pay;
    
    $remain = $remaining - $pay;

    echo $remaining;

        $sql ="UPDATE `salary` SET `remaining`='$remain', `paid`='$pai', `updated_at` = current_timestamp() WHERE `fname` = '$fname' AND `id` = '$id' AND `year` = $year AND `month` = $month";
        
        if(mysqli_query($conn, $sql)){

            header('Location: view-salary.php?month='. $month .'&year='. $year .'&action=paid');
        
        } else{
            echo "ERROR: Hush! Sorry $sql. "
            . mysqli_error($conn);
        }

?>