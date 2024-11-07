<?php
    include("../includes/database.php");
    $db = new Database();

    $validation = $_POST["validation"];
    $encValidation = md5($validation);

    $result = $db->sql("
        SELECT discount_validation FROM validations WHERE discount_validation = '$encValidation'
    ");

    if($result->num_rows > 0){
        echo "validation-correct";
    }
    
?>