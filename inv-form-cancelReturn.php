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

        $returnId = $_POST["returnId"];
        $batchId = $_POST["batchId"];
        $cancelRemarks = $_POST["cancelRemarks"];

    
        $db->sql("
            UPDATE `return_list` 
            SET `return_status`='0', `cancel_remarks` = '$cancelRemarks'
            WHERE `return_id` = '$returnId'
        ");

        $db->sql("
            UPDATE `batch` 
            SET `return`='0'
            WHERE `batch_id` = '$batchId'
        ");

        $productId = $db->sql("SELECT `product_id` FROM `batch` WHERE `batch_id` = '$batchId'")->fetch_assoc()["product_id"];

        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);
    
        $inv->updateQuantity($productId);
        $inv->updateStockStatus();
        $inv->updateExpirationStatus();

        //add logs
        $productName = $db->sql("
            SELECT `products`.`product_name`
            FROM `batch`
                INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
            WHERE `batch`.`batch_id` = '$batchId'
        ")->fetch_assoc()["product_name"];
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, `product_id`,
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','inventory','cancelled the return of $productName (BATCH-$batchId)','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "cancel-return-success";
        header("Location: ./inv-page-returnList.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-returnList.php");
    }
?>