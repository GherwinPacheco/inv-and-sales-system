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
    

        $oldVal = md5($_POST["oldDiscountValidation"]);
        $newVal = md5($_POST["newDiscountValidation"]);

        $result = $db->sql("SELECT * FROM validations WHERE discount_validation = '$oldVal'");

        if($result->num_rows == 0){
            $_SESSION["message"] = "validation-wrong";
            header("Location: ./config-page-systemSettings.php");
            exit();
        }

        $db->sql("UPDATE `validations` SET `discount_validation` = '$newVal' WHERE id = 1");

        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','config','changed the code for discount validation','$date','$time'
            )
        ");

        $_SESSION["message"] = "validation-changed";
        header("Location: ./config-page-systemSettings.php");
        exit();
        
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./config-page-systemSettings.php");
        exit();
    }
?>