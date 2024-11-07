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

        $discountId = $_POST["discountId"];
        
    
        $db->sql("
            UPDATE `discount` SET `discount_status`='0' WHERE `discount_id` = '$discountId'
        ");


        $_SESSION["message"] = "discount-disabled";
        header("Location: ./pos-page-settings.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./pos-page-settings.php");
    }
?>