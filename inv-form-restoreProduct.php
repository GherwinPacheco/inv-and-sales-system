<?php
    try{
        session_start();
        include("./includes/login-check.php");
        
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

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

        $productId = $_GET["id"];

    
        $db->sql("
            UPDATE `products` 
            SET `archive_status` = '0', `date_archived` = '0000-00-00', `time_archived` = '00:00:00', `archived_by` = '0', `archive_remarks` = ''
            WHERE `product_id` = '$productId'
        ");
        
        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

        $inv->updateStockStatus();
        $inv->updateExpirationStatus();
        
        
        //add logs
        $productName = $db->sql("SELECT `product_name` FROM `products` WHERE `product_id` = '$productId'")->fetch_assoc()["product_name"];
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, `product_id`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','inventory','restores $productName and returned it to the inventory','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "product-restored";
        header("Location: ./inv-page-settings.php??tableType=1#archiveTable");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php?tableType=1#archiveTable");
    }
?>