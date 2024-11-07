<?php
    if(! isset($_SESSION["user"]) and ! isset($_SESSION["level"])){
        header("Location: ./login-page.php");
        exit();
    }
?>