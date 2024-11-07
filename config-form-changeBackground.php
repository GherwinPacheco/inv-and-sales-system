<?php
    try{
        session_start();
        include("./includes/login-check.php");

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

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
        
        if(isset($_FILES['background'])){
            $uploaddir = './assets/';
            $uploadfile = $uploaddir . 'login-background.png';

            echo '<pre>';
            if (move_uploaded_file($_FILES['background']['tmp_name'], $uploadfile)) {
                echo "File is valid, and was successfully uploaded.\n";
            } else {
                echo "Possible file upload attack!\n";
            }

            echo 'Here is some more debugging info:';
            print_r($_FILES);

            print "</pre>";
        }

        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','config','changed the background of the login page','$date','$time'
            )
        ");

        $_SESSION["message"] = "logo-changed";
        header("Location: ./config-page-systemSettings.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./config-page-systemSettings.php");
    }
?>