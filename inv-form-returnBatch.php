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

        $batchId = $_POST["batchId"];
        $productId = $_POST["productId"];
        $returnRemarks = $_POST["returnReason"].' '.$_POST["returnRemarks"];
        $returnRemarks = trim($returnRemarks);

        $validation = $_POST["returnValidation"];
        $encValidation = md5($validation);

        $result = $db->sql("
            SELECT inv_validation FROM validations WHERE inv_validation = '$encValidation'
        ");

        if($result->num_rows == 0){
            $_SESSION["message"] = "validation-wrong";
            header("Location: ./inv-page-productList.php");
            exit();
        }
    
        $db->sql("
            UPDATE `batch` 
            SET `return`='1',`date_archived`='$date',`time_archived`='$time',`archived_by`='$user'
            WHERE `batch_id` = '$batchId'
        ");
        
        $db->sql("
            INSERT INTO `return_list`(
                `return_id`, `batch_id`, `product_id`, `return_status`, 
                `added_by`, `date_added`, `time_added`, 
                `date_returned`, `time_returned`, `returned_by`, `return_remarks`) 
            VALUES (
                '','$batchId','$productId','1','$user','$date','$time','','', '$user', '$returnRemarks')
        ");
        
        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

        $inv->updateQuantity($productId);
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
                '','$user','inventory','added $productName (BATCH-$batchId) to return list','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "batch-returned";
        header("Location: ./inv-page-productList.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-productList.php");
    }
?>
