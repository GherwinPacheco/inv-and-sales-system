<?php
    session_start();
    include ("./includes/database.php");
    $db = new Database();

    date_default_timezone_set('Asia/Manila');
    $user = $_SESSION["user"];
    $date = date("Y-m-d");
    $time = date("H:i:s");

    

    //add logs
    $db->sql("
        INSERT INTO `user_logs`(
            `log_id`, `user_id`, `activity_type`, `activity_description`, 
            `date_added`, `time_added`
        ) 
        VALUES (
            '','$user','logout','logged out their account','$date','$time'
        )
    ");

    session_destroy();
    header("Location: ./login-page.php");
?>