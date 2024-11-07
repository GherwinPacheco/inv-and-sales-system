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

    
        $user = $_SESSION["user"];
        $curPass = $db->sql("SELECT `password` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["password"];
        $verifyPass = $_POST["password"];

        $username = $_POST["username"];
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $newPassword = $_POST["newPassword"] !== "" ? "'".md5($_POST["newPassword"])."'" : "`password`";
        $question = $_POST["securityQuestion"] !== "" ? "'".$_POST["securityQuestion"]."'" : "`security_question`";
        $answer = $_POST["securityAnswer"] !== "" ? "'".md5(strtolower($_POST["securityAnswer"]))."'" : "`question_answer`";

        $usernameResult = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_name` = '$username' AND `user_id` != '$user'");

        if($_POST["newPassword"] !== $_POST["confirmPassword"]){
            $_SESSION["message"] = "edit-password-mismatch";
            header("Location: ./account-page.php");
            exit();
        }
        elseif($curPass !== md5($verifyPass)){
            $_SESSION["message"] = "edit-wrong-password";
            header("Location: ./account-page.php");
            exit();
        }
        elseif($usernameResult->num_rows > 0){
            $_SESSION["message"] = "edit-username-exist";
            header("Location: ./account-page.php");
            exit();
        }
        else{

            $db->sql("
                UPDATE `accounts` 
                SET 
                    `first_name`='$firstname', `last_name`='$lastname',
                    `user_name`='$username',`password`=$newPassword, `security_question`=$question,`question_answer`=$answer
                WHERE `user_id` = '$user'
            ");

            if(isset($_FILES["userImage"])){
                $uploaddir = './assets/profiles/';
                $uploadfile = $uploaddir . 'user_'.$user.'.png';

                echo '<pre>';
                if (move_uploaded_file($_FILES['userImage']['tmp_name'], $uploadfile)) {
                    echo "File is valid, and was successfully uploaded.\n";
                } else {
                    echo "Possible file upload attack!\n";
                }

                echo 'Here is some more debugging info:';
                print_r($_FILES);

                print "</pre>";

                //add logs
                $db->sql("
                    INSERT INTO `user_logs`(
                        `log_id`, `user_id`, `activity_type`, `activity_description`, 
                        `date_added`, `time_added`
                    ) 
                    VALUES (
                        '','$user','config','changed their account details','$date','$time'
                    )
                ");

                $_SESSION["message"] = "account-edited-successfully";
                header("Location: ./account-page.php");
                exit();
            }
        }

        
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./account-page.php");
        exit();
    }

    echo $_SESSION["message"];
?>