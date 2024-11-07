<?php
    try{
        session_start();
        include("./includes/login-check.php");

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

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

        $storeName = $_POST["storeName"];
        $storeAddress = $_POST["storeAddress"];
        $tinNumber = $_POST["tinNumber"];
        $contact = $_POST["contact"];

    
        $db->sql("
            UPDATE `store_details` 
            SET 
                `store_name`='$storeName',`store_address`='$storeAddress',
                `tin_number`='$tinNumber',`contact`='$contact' 
            WHERE `id` = 1
        ");

        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','config','changed the store details','$date','$time'
            )
        ");

        $_SESSION["message"] = "storeDetails-edited";
        header("Location: ./config-page-systemSettings.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./config-page-systemSettings.php");
    }
?>