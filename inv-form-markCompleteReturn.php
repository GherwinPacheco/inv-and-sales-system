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

        $returnId = $_POST["returnId"];
    
        $db->sql("
            UPDATE `return_list` 
            SET `return_status`='2',`date_returned`='$date',`time_returned`='$time',`returned_by`='$user'
            WHERE `return_id` = '$returnId'
        ");


        //add logs
        $batchId = $db->sql("SELECT `batch_id` FROM `return_list` WHERE `return_id` = '$returnId'")->fetch_assoc()["batch_id"];
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','inventory','set the return of BATCH-$batchId as completed','$date','$time'
            )
        ");

        $_SESSION["message"] = "return-completed-success";
        header("Location: ./inv-page-returnList.php");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./inv-page-returnList.php");
    }
?>