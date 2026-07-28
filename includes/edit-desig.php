<?php
require_once('config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: edit-designation.php?id=$id");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_REQUEST["id"]);
    $name = $conn->real_escape_string($_REQUEST["name1"]);
    $grade = intval($_REQUEST["grade"]);

    $sql = "UPDATE `designation` SET `name`='$name', `grade`='$grade', `updated_at` = current_timestamp() WHERE `designationID` = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        header('Location: ../designation.php?edit=' . urlencode($name));
        exit;
    } else {
        echo "ERROR: " . mysqli_error($conn);
    }
}