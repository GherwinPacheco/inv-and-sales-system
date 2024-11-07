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

        $id = $_POST["reasonId"];

        
        
    
        $db->sql("
            UPDATE `archive_reasons` SET `reason_status`='0' WHERE `reason_id` = '$id'
        ");


        $_SESSION["message"] = "reason-disabled";
        header("Location: ./inv-page-settings.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php");
    }
?>