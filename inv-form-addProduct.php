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


        $barcodeId = $_POST["barcodeId"];
        $productName = $_POST["productName"];
        $properties = $_POST["productProperties"];
        $category = $_POST["category"];
        $price = $_POST["price"];
        $minimumStock = $_POST["minimumStock"];
        $bundlePieces = $_POST["bundlePieces"] !== "" ? $_POST["bundlePieces"] : "0";
        $quantitySingle = $_POST["quantitySingle"];
        $quantityBundle = $_POST["quantityBundle"] !== ""  ? $_POST["quantityBundle"] : "0";
        $unitSingle = $_POST["unitSingle"];
        $unitBundle = isset($_POST["unitBundle"])  ? $_POST["unitBundle"] : "0";
        $expirationDate = $_POST["expirationDate"] !== "" ? $_POST["expirationDate"] : "0000-00-00";
        $expirable = $_POST["expirationDate"] !== "" ? "1" : "0";
        $supplier = $_POST["supplier"];
        $description = $_POST["description"];

    
        if( $barcodeId !== "" and $db->sql(" SELECT `product_id` FROM `products` WHERE `barcode_id` = '$barcodeId' ")->num_rows > 0 ){
            $_SESSION["message"] = "barcode-exist";
            header("Location: ./inv-page-productList.php");
        }
        else{
            //add new product
            $db->sql("
            INSERT INTO `products`(
                `product_id`, `barcode_id`, `product_name`, `properties`,
                `category_id`, `bundle`, `single_quantity`, 
                `bundle_quantity`, `unit_single`, `unit_bundle`, 
                `price`, `minimum_stock`, `expirable`, `stock_status`, `archive_status`, `product_description`,
                `date_added`, `time_added`, `date_modified`, `time_modified`, `date_archived`, `time_archived`, 
                `added_by`, `modified_by`, `archived_by`)
            VALUES (
                '','$barcodeId','$productName','$properties','$category','$bundlePieces',
                '$quantitySingle','$quantityBundle','$unitSingle','$unitBundle',
                '$price', '$minimumStock', '$expirable', '1','0','$description','$date','$time','','','','','$user','','')
            ");


            //get product id of the added product
            $productId = $db->sql("
                SELECT MAX(`product_id`) as `product_id` FROM `products`
            ")->fetch_assoc()["product_id"];

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
                    '','$user','inventory','added a new product named $productName with $quantitySingle $unitSingle','$productId','$date','$time'
                )
            ");
            echo $quantitySingle;
            //$_SESSION["message"] = "product-added";
            header("Location: ./inv-page-productList.php");
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-productList.php");
    }

?>
