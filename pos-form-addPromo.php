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

    
        $productId = $_POST["product"];
        $productName = $db->sql("SELECT CONCAT_WS(' ', `product_name`, `properties`) AS prd_name FROM products WHERE product_id = $productId")->fetch_assoc()["prd_name"];
        $promoType = $_POST["promoType"];
        $buy = isset($_POST["buyQuantity"]) ? $_POST["buyQuantity"] : 0;
        $get = isset($_POST["getQuantity"]) ? $_POST["getQuantity"] : 0;
        $productFreebie = isset($_POST["freeProduct"]) ? $_POST["freeProduct"] : 0;
        $minSpend = isset($_POST["minimumSpend"]) ? $_POST["minimumSpend"] : 0;
        $percentage = isset($_POST["percentage"]) ? $_POST["percentage"] : 0;

        $promoTypeName = "";
        if($promoType == 1){
            $promoTypeName = "BOGO";
        }
        elseif($promoType == 1){
            $promoTypeName = "Product Discount";
        }
        elseif($promoType == 1){
            $promoTypeName = "Buy More Save More";
        }

        $checkExist = $db->sql("SELECT * FROM `promo` WHERE `product_id` = $productId AND `promo_status` = 1");

        if($checkExist->num_rows){
            $_SESSION["message"] = "product-promo-active";
            header("Location: ./pos-page-settings.php");
        }
        else{
            $db->sql("
                INSERT INTO `promo`(
                    `promo_id`, `promo_type`, `product_id`, 
                    `buy_quantity`, `get_quantity`, `product_freebie`, 
                    `min_spend`, `percentage`, `promo_status`
                ) VALUES (
                    '','$promoType','$productId',
                    '$buy','$get','$productFreebie',
                    '$minSpend','$percentage','1'
                )
            ");

            
            //add logs
            $db->sql("
                INSERT INTO `user_logs`(
                    `log_id`, `user_id`, `activity_type`, `activity_description`, 
                    `date_added`, `time_added`
                ) 
                VALUES (
                    '','$user','pos','added new $promoTypeName promo for $productName','$date','$time'
                )
            ");

            $_SESSION["message"] = "promo-added";
            header("Location: ./pos-page-settings.php");
        }


        
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./pos-page-settings.php");
    }
?>