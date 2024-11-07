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

        $batchId = $_GET["id"];

    
        $db->sql("
            UPDATE `batch` 
            SET `archive_status`='0',`date_archived`='0000-00-00',`time_archived`='00:00:00',`archived_by`='0', `archive_remarks` = ''
            WHERE `batch_id` = '$batchId'
        ");
        
        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

        $productId = $db->sql("SELECT `product_id` FROM `batch` WHERE `batch_id` = '$batchId'")->fetch_assoc()["product_id"];

        $inv->updateQuantity($productId);
        $inv->updateStockStatus();
        $inv->updateExpirationStatus();
            

        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, `product_id`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','inventory','restores BATCH-$batchId and returned it to the inventory','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "batch-restored";
        header("Location: ./inv-page-settings.php?tableType=2#archiveTable");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php?tableType=2#archiveTable");
    }
?>