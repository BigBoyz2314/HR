<?php
require_once('config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: edit-department.php?id=$id");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_REQUEST["id"]);
    $name = $conn->real_escape_string($_REQUEST["name1"]);
    $allowed = intval($_REQUEST["allowed"]);

    $sql = "UPDATE `department` SET `name`='$name', `allowed_Strength`='$allowed', `updated_at` = current_timestamp() WHERE `departmentID` = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        header('Location: ../department.php?edit=' . urlencode($name));
        exit;
    } else {
        echo "ERROR: " . mysqli_error($conn);
    }
}