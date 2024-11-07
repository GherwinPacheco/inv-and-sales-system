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

        $supplierId = $_POST["supplierId"];
        $supplierName = $_POST["supplierName"];
        $contact = $_POST["contact"];

    
        $result = $db->sql("SELECT `supplier_name` FROM `supplier` WHERE LOWER(`supplier_name`) = LOWER('$supplierName') AND `contact` = '$contact' AND `supplier_status` = 1");
        if($result->num_rows > 0){
            $_SESSION["message"] = "supplier-exist";
            header("Location: ./inv-page-settings.php");
        }
        else{
            $db->sql("
                UPDATE `supplier` 
                SET `supplier_name`='$supplierName', `contact`='$contact' WHERE `supplier_id` = '$supplierId';
            ");


            //add logs
            $db->sql("
                INSERT INTO `user_logs`(
                    `log_id`, `user_id`, `activity_type`, `activity_description`, 
                    `date_added`, `time_added`
                ) 
                VALUES (
                    '','$user','inventory','edited the details of S-$supplierId','$date','$time'
                )
            ");

            $_SESSION["message"] = "supplier-edited";
            header("Location: ./inv-page-settings.php");
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php");
    }
?>