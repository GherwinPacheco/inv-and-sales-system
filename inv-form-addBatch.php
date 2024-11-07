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

        $productId = $_POST["productId"];
        $barcodeId = $_POST["barcodeId"];
        $productName = $_POST["productName"];
        $price = $_POST["price"];
        $applyPrice = isset($_POST["applyPrice"]) ? "1" : "0";
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

        
        //add new batch of the product
        $db->sql("
            INSERT INTO `batch`(
                `batch_id`, `product_id`, `single_quantity`, `bundle_quantity`, 
                `price`, `expiration_date`, `expiration_status`, `supplier_id`, 
                `date_added`, `time_added`, `added_by`, `date_modified`, `time_modified`, 
                `modified_by`, `archive_status`, `date_archived`, `time_archived`, 
                `archived_by`, `return`) 
            VALUES (
                '','$productId','$quantitySingle','$quantityBundle',
                '$price','$expirationDate','$expirationStatus',
                '$supplier','$date','$time','$user',
                '','','','','','','','')
        ");

        //update the price if Apply Price is checked
        if($applyPrice == "1"){
            $db->sql("
                UPDATE `products` 
                SET `price`='$price' 
                WHERE `product_id` = '$productId'
            ");
        }
        
        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

        $inv->updateQuantity($productId);
        $inv->updateStockStatus();
        $inv->updateExpirationStatus();
        

        //add logs
        $unitSingle = $db->sql("
            SELECT `unit_measurement`.`unit_name`
            FROM `products`
                INNER JOIN `unit_measurement` ON `products`.`unit_single` = `unit_measurement`.`unit_id`
            WHERE `products`.`product_id` = '$productId'
        ")->fetch_assoc()["unit_name"];

        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, `product_id`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','inventory','added a new batch of $productName with $quantitySingle $unitSingle','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "batch-added";
        header("Location: ./inv-page-productList.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-productList.php");
    }


?>
