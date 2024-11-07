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
    
        $username = $_POST["username"];
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $password = md5($_POST["password"]);
        $confirmPassword = $_POST["confirmPassword"];
        $role = $_POST["role"];

        $usernameResult = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_name` = '$username'");

        if($_POST["password"] !== $_POST["confirmPassword"]){
            $_SESSION["message"] = "add-password-mismatch";
            header("Location: ./config-page-manageAccount.php");
            exit();
        }
        elseif($usernameResult->num_rows > 0){
            $_SESSION["message"] = "add-username-exist";
            header("Location: ./config-page-manageAccount.php");
            exit();
        }
        else{
            $db->sql("
                INSERT INTO `accounts`(
                    `user_id`, `first_name`, `last_name`, 
                    `user_name`, `password`, `user_level`, 
                    `account_status`
                ) VALUES (
                    '','$firstname','$lastname','$username','$password','$role','1')
            ");


            
            $id = $db->sql("SELECT MAX(`user_id`) AS `user_id` FROM `accounts`")->fetch_assoc()["user_id"];

            if($_FILES["userImage"]["name"] !== ""){
                $uploaddir = './assets/profiles/';
                $uploadfile = $uploaddir . 'user_'.$id.'.png';

                echo '<pre>';
                if (move_uploaded_file($_FILES['userImage']['tmp_name'], $uploadfile)) {
                    echo "File is valid, and was successfully uploaded.\n";
                } else {
                    echo "Possible file upload attack!\n";
                }

                echo 'Here is some more debugging info:';
                print_r($_FILES);

                print "</pre>";
                
            }
            else{
                $img = "";
                if($role == "1"){
                    $img = "default_staff.png";
                }
                else{
                    $img = "default_admin.png";
                }

                copy("./assets/profiles/$img", "./assets/profiles/user_$id.png");
            }

            //add logs
            $db->sql("
                INSERT INTO `user_logs`(
                    `log_id`, `user_id`, `activity_type`, `activity_description`, 
                    `date_added`, `time_added`
                ) 
                VALUES (
                    '','$user','config','added new account named $username','$date','$time'
                )
            ");

            $_SESSION["message"] = "account-added-successfully";
            header("Location: ./config-page-manageAccount.php");
            exit();
        }
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./config-page-manageAccount.php");
        exit();
    }
?>