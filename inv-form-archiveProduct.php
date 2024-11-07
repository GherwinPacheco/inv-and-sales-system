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

        echo $user;

        $productId = $_POST["productId"];
        $archiveRemarks = $_POST["archiveReason"].' '.$_POST["archiveRemarks"];
        $archiveRemarks = trim($archiveRemarks);

        $validation = $_POST["archiveValidation"];
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
            UPDATE `products` 
            SET `archive_status` = '1', `date_archived` = '$date', `time_archived` = '$time', `archived_by` = '$user', `archive_remarks` = '$archiveRemarks'
            WHERE `product_id` = '$productId'
        ");
        
        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

        $inv->updateStockStatus();
        $inv->updateExpirationStatus();
            

        //add logs
        $productName = $db->sql("
            SELECT `product_name` FROM `products` WHERE `product_id` = '$productId'
        ")->fetch_assoc()["product_name"];
        $quantitySingle = $db->sql("
            SELECT `single_quantity` FROM `products` WHERE `product_id` = '$productId'
        ")->fetch_assoc()["single_quantity"];
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
                '','$user','inventory','archived $productName with $quantitySingle $unitSingle','$productId','$date','$time'
            )
        ");

        $_SESSION["message"] = "product-archived";
        header("Location: ./inv-page-productList.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-productList.php");
    }
?>
