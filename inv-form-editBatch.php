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
        $price = $_POST["price"];
        $quantitySingle = $_POST["quantitySingle"];
        $quantityBundle = $_POST["quantityBundle"] !== ""  ? $_POST["quantityBundle"] : "0";
        $expirationDate = $_POST["expirationDate"] !== "" ? $_POST["expirationDate"] : "0000-00-00";
        $supplier = $_POST["supplier"];

        //get the expiration status of the expiration date
        $expirationStatus = "";
        if($expirationDate === "0000-00-00"){
            $expirationStatus = "0";
        }
        elseif($date >= $expirationDate){
            $expirationStatus = "3";
        }
        elseif($date >= date("Y-m-d", strtotime("-3 months", strtotime($expirationDate))) ){
            $expirationStatus = "2";
        }
        else{
            $expirationStatus = "1";
        }

    
        //update batch
        $db->sql("
            UPDATE 
                `batch` 
            SET 
                `single_quantity`='$quantitySingle',`bundle_quantity`='$quantityBundle',`price`='$price',`expiration_date`='$expirationDate',`expiration_status`='$expirationStatus',`supplier_id`='$supplier',`date_modified`='$date',`time_modified`='$time',`modified_by`='$user' WHERE `batch_id` = '$batchId'
        ");
        
        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

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
                '','$user','inventory','edited the batch details of BATCH-$batchId','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "batch-edited";
        header("Location: ./inv-page-productList.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-productList.php");
    }

?>
