<?php
try{
    session_start();
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

    include("./includes/database.php");
    $db = new Database();

    $id = $_POST["userId"];
    $password = md5($_POST["password"]);
    
    $db->sql("
        UPDATE `accounts` 
        SET `password`='$password'
        WHERE `user_id` = '$id'
    ");

    $_SESSION["message"] = "password-forgotten";
    header("Location: ./login-page.php");
}
catch(Exception $e){
    $_SESSION["message"] = "error";
    header("Location: ./pos-page-settings.php");
}
?>