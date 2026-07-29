<?php
require_once('config.php');
init_hr_session();
session_unset();
session_destroy();

header('Location: ../login.php');
exit;
?>