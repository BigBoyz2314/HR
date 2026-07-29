<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$m = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$y = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

header("location: view-salary.php?month=$m&year=$y");
exit;
?>