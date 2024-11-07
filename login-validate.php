<?php
    session_start();
    if(isset($_SESSION["user"]) and isset($_SESSION["level"])){
        header("Location: ./dashboard.php");
        exit();
    }
    
    
    include ("./includes/database.php");
    $db = new Database();

    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);

    $username = $_POST["username"];
    $password = md5($_POST["password"]);

    $result = $db->sql("
        SELECT * FROM `accounts` WHERE `user_name` = '$username' AND `password` = '$password' AND `account_status` = 1
    ");

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $_SESSION["user"] = $row["user_id"];
            $_SESSION["level"] = $row["user_level"];
        }

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

        

        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','login','logged in their account','$date','$time'
            )
        ");

        $inv->updateBundleQuantity("all");
        $inv->updateQuantity("all");
        $inv->updateStockStatus();
        $inv->updateExpirationStatus();
        header("Location: ./dashboard.php");
    }
    else{
        $_SESSION["message"] = "wrong-credentials";
        header("Location: ./login-page.php");
    }
?>