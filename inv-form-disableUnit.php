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

        $id = $_POST["unitId"];

        $result = $db->sql("
            SELECT COUNT(*) 
            FROM `products` 
            WHERE `unit_single` = $id OR `unit_bundle` = $id AND `archive_status` = '0'
        ");

        if($result->num_rows > 0){
            //$_SESSION["message"] = "unit-in-use";
            //header("Location: ./inv-page-settings.php");
            //exit();
        }
        
    
        $db->sql("
            UPDATE `unit_measurement` SET `unit_status`='0' WHERE `unit_id` = '$id'
        ");


        $_SESSION["message"] = "unit-disabled";
        header("Location: ./inv-page-settings.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php");
    }
?>