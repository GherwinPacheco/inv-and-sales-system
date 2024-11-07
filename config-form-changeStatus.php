<?php
    try{
        session_start();
        include("./includes/login-check.php");

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

        //check request method
        if($_SERVER["REQUEST_METHOD"] !== "GET" and !isset($_GET["id"])){
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

        $userId = $_GET["id"];

        $status = $db->sql("SELECT `account_status` FROM `accounts` WHERE `user_id` = '$userId'")->fetch_assoc()["account_status"];

        $val = $status == 1 ? 0 : 1;

        $db->sql("
            UPDATE `accounts` 
            SET `account_status`='$val'
            WHERE `user_id` = '$userId'
        ");


        $statusWord = $status == 1 ? "disabled" :"enabled";
        $username = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_id` = '$userId'")->fetch_assoc()["user_name"];
        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','config','$statusWord the account of $username','$date','$time'
            )
        ");

        header("Location: ./config-page-manageAccount.php");
        exit();
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./account-page.php");
        exit();
    }
?>