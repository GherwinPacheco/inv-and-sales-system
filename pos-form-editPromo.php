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

        $promoId = $_POST["promoId"];
        $productId = $_POST["product"];
        $oldProductId = $_POST["oldProductId"];
        $productName = $db->sql("SELECT CONCAT_WS(' ', `product_name`, `properties`) AS prd_name FROM products WHERE product_id = $productId")->fetch_assoc()["prd_name"];
        $promoType = $_POST["promoType"];
        $buy = isset($_POST["buyQuantity"]) ? $_POST["buyQuantity"] : 0;
        $get = isset($_POST["getQuantity"]) ? $_POST["getQuantity"] : 0;
        $productFreebie = isset($_POST["freeProduct"]) ? $_POST["freeProduct"] : 0;
        $minSpend = isset($_POST["minimumSpend"]) ? $_POST["minimumSpend"] : 0;
        $percentage = isset($_POST["percentage"]) ? $_POST["percentage"] : 0;


        $checkExist = $db->sql("SELECT * FROM `promo` WHERE `product_id` = $productId AND `promo_status` = 1");

        if($checkExist->num_rows and $oldProductId !== $productId){
            $_SESSION["message"] = "product-promo-active";
            header("Location: ./pos-page-settings.php");
        }
        else{
            $db->sql("
                UPDATE `promo` 
                SET `promo_type`='$promoType',`product_id`='$productId',
                    `buy_quantity`='$buy',`get_quantity`='$get',
                    `product_freebie`='$productFreebie',`min_spend`='$minSpend',`percentage`='$percentage'
                 WHERE `promo_id` = $promoId
            ");


            //add logs
            $db->sql("
                INSERT INTO `user_logs`(
                    `log_id`, `user_id`, `activity_type`, `activity_description`, 
                    `date_added`, `time_added`
                ) 
                VALUES (
                    '','$user','pos','updated the promo details of $productName','$date','$time'
                )
            ");

            $_SESSION["message"] = "promo-updated";
            header("Location: ./pos-page-settings.php");

        }


        
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./pos-page-settings.php");
    }
?>