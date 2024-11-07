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
        $oldBarcode = $_POST["oldBarcode"];
        $barcodeId = $_POST["barcodeId"];
        $productName = $_POST["productName"];
        $properties = $_POST["productProperties"];
        $category = $_POST["category"];
        $price = $_POST["price"];
        $applyPrice = isset($_POST["applyPrice"]) ? "1" : "0";
        $minimumStock = $_POST["minimumStock"];
        $bundlePieces = $_POST["bundlePieces"] !== "" ? $_POST["bundlePieces"] : "0";
        $unitSingle = $_POST["unitSingle"];
        $unitBundle = isset($_POST["unitBundle"])  ? $_POST["unitBundle"] : "0";
        $expirable = $_POST["expirable"];
        $description = $_POST["description"];
    

    
        if( $barcodeId !== "" and $db->sql(" SELECT `product_id` FROM `products` WHERE `barcode_id` = '$barcodeId' ")->num_rows > 0 and $oldBarcode !== $barcodeId ){
            $_SESSION["message"] = "edit-barcode-exist";
            header("Location: ./inv-page-productList.php");
        }
        else{
            //edit product details
            $db->sql("
            UPDATE `products` 
            SET 
                `barcode_id`='$barcodeId',`product_name`='$productName',`properties`='$properties',`category_id`='$category',
                `bundle`='$bundlePieces',`unit_single`='$unitSingle',`unit_bundle`='$unitBundle',
                `price`='$price', `minimum_stock`='$minimumStock',`expirable`='$expirable',`product_description`='$description',
                `date_modified`='$date',`time_modified`='$time',`modified_by`='$user' WHERE `product_id` = '$productId'
            ");
            
            if($applyPrice === "1"){
                //edit product details
                $db->sql("
                    UPDATE 
                        `batch` 
                    SET `price`='$price' 
                    WHERE `product_id` = '$productId'
                ");
            }

            include("./includes/inventory-functions.php");
            $inv = new Inventory($db);
            $inv->updateBundleQuantity($productId);
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
                    '','$user','inventory','edited the product details of $productName','$productId','$date','$time'
                )
            ");

            $_SESSION["message"] = "product-edited";
            header("Location: ./inv-page-productList.php");
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        $_SESSION["error"] = $e->getMessage();
        header("Location: ./inv-page-productList.php");
    }


?>
