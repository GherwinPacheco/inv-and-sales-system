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

        $categoryName = $_POST["categoryName"];

    
        $result = $db->sql("SELECT `category_name` FROM `category` WHERE LOWER(`category_name`) = LOWER('$categoryName') AND category_status = 1");
        if($result->num_rows > 0){
            $_SESSION["message"] = "category-exist";
            header("Location: ./inv-page-settings.php");
        }
        else{
            $db->sql("
                INSERT INTO `category`(`category_id`, `category_name`, `category_status`) 
                VALUES ('','$categoryName', 1)
            ");

            
            //add logs
            $db->sql("
                INSERT INTO `user_logs`(
                    `log_id`, `user_id`, `activity_type`, `activity_description`, 
                    `date_added`, `time_added`
                ) 
                VALUES (
                    '','$user','inventory','added a new category named $categoryName','$date','$time'
                )
            ");

            $_SESSION["message"] = "category-added";
            header("Location: ./inv-page-settings.php");
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-settings.php");
    }
?>