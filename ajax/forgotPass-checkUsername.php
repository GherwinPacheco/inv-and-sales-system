<?php
    include("../includes/database.php");
    $db = new Database();

    $username = $_POST["username"];

    $result = $db->sql("
        SELECT `security_question` FROM `accounts` WHERE `user_name` = '$username' LIMIT 1
    ");

    
    $question = $result->num_rows > 0 ? $result->fetch_assoc()["security_question"] : "";
    if($result->num_rows > 0 and $question !== ""){
        $userId = $result = $db->sql("
            SELECT `user_id` FROM `accounts` WHERE `user_name` = '$username' LIMIT 1
        ")->fetch_assoc()["user_id"];
        
        echo '
            <p>
                <b>Security Question:</b>
                <br>
                &emsp;'.$question.'
            </p>
            <label for="answer">Answer</label>
            <input type="text" class="form-control" id="answer">
            <input type="hidden" id="userId" value="'.$userId.'">
            <br>
            <button type="button" id="submitAnswer" class="btn btn-primary float-right">Proceed</button>

            <script>
                $("#submitUsername").css("display", "none");
                $("#submitAnswer").click(function(){
                    var answer = $("#answer").val();
                    var id = $("#userId").val();
                    if(answer !== ""){
                        $.post("./ajax/forgotPass-checkAnswer.php",
                        {
                            securityAnswer: answer,
                            userId: id
                        },
                        function(data, status){
                            $("#passwordDiv").html(data);
                        });
                    }
                    
                });
            </script>
        ';
    }
    elseif($result->num_rows > 0 and $question == ""){
        echo '
            <br><br>
            <p class="text-danger">There are no security questions set to this account</p>
        ';
    }
    else{
        echo '
            <br><br>
            <p class="text-danger">There are no account with '.$username.' username</p>
        ';
    }
?>