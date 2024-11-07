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
            
    
        $id = $_POST["discountId"];
        $oldDiscountName = $_POST["oldDiscountName"];
        $discountName = $_POST["discountName"];
        $discountPercent = $_POST["percentage"];

        $checkExist = $db->sql("SELECT * FROM `discount` WHERE `discount_name` = '$discountName'");

        if($checkExist->num_rows and $oldDiscountName !== $discountName){
            $_SESSION["message"] = "discount-existing";
            header("Location: ./pos-page-settings.php");
        }
        else{
            $db->sql("
                UPDATE `discount` 
                SET `discount_name`='$discountName',`percent`='$discountPercent' WHERE `discount_id` = '$id'
            ");

            $_SESSION["message"] = "discount-updated";
            header("Location: ./pos-page-settings.php");
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./pos-page-settings.php");
    }
        
    
?>