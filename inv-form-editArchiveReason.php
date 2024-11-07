<?php
    try{
        session_start();
        include("./includes/login-check.php");
        
        //check request method
        if($_SERVER["REQUEST_METHOD"] !== "POST"){
            //$_SESSION["message"] = "what";
            if(isset($_SERVER['HTTP_REFERER'])){
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            else{
                header("Location: javascript://history.go(-1)");
                exit();
            }
        }

        include ("./includes/database.php");
        $db = new Database();

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

        $reasonId = $_POST["reasonId"];
        $oldReason = $_POST["oldReason"];
        $reason = $_POST["reason"];
        
        $db->sql("
            UPDATE `archive_reasons` 
            SET `reason`='$reason' WHERE `reason_id` = '$reasonId';
        ");


        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','inventory','edited the reason name of $oldReason into $reason','$date','$time'
            )
        ");

        $_SESSION["message"] = "reason-edited";
        header("Location: ./inv-page-settings.php");
        
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php");
    }
?>