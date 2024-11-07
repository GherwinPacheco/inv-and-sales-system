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
        
    
        $discountName = $_POST["discountName"];
        $discountPercent = $_POST["percentage"];

        $checkExist = $db->sql("SELECT * FROM `discount` WHERE `discount_name` = '$discountName'");

        if($checkExist->num_rows){
            $_SESSION["message"] = "discount-existing";
            header("Location: ./pos-page-settings.php");
        }
        else{
            $db->sql("
                INSERT INTO `discount`(`discount_id`, `discount_name`, `percent`, `discount_status`) 
                VALUES ('','$discountName','$discountPercent','1')
            ");

            //add logs
            $db->sql("
                INSERT INTO `user_logs`(
                    `log_id`, `user_id`, `activity_type`, `activity_description`, 
                    `date_added`, `time_added`
                ) 
                VALUES (
                    '','$user','pos','added new discount named $discountName','$date','$time'
                )
            ");

            $_SESSION["message"] = "discount-added";
            header("Location: ./pos-page-settings.php");
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./pos-page-settings.php");
    }
        
    
?>