<?php
// Redirect legacy filter page to unified view-employees.php page
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: view-employees.php" . $queryString);
exit;