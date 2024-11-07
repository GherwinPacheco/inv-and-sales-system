<?php
    include("../includes/database.php");
    $db = new Database();

    $id = $_POST["userId"];
    $answer = md5(strtolower($_POST["securityAnswer"]));

    $result = $db->sql("SELECT `question_answer` FROM `accounts` WHERE `user_id` = '$id'")->fetch_assoc()["question_answer"];

    if($result == $answer){
        echo '
            <form id="passwordChangeForm" action="./login-form-forgotPass.php" method="post">
                <label for="passwordForm">Password</label>
                <input type="password" class="form-control" id="passwordForm" name="password">
                <br>
                <label for="confirmPasswordForm">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPasswordForm">
                <input type="hidden" id="userId" name="userId" value="'.$id.'">
                <br>
                <div id="warning-div"></div>
                <button type="button" id="submitPassword" class="btn btn-primary float-right">Submit</button>
                
                
                
            </form>
            <script>
                $("#questionDiv").css("display", "none");
                $("#submitPassword").click(function(){
                    var pass = $("#passwordForm").val();
                    var confirmPass = $("#confirmPasswordForm").val();

                    if(pass !== "" && confirmPass !== "" && pass === confirmPass){
                        $("#warning-div").html("");
                        $("#passwordChangeForm").submit();
                    }
                    else if(pass === "" || confirmPass === ""){
                        $("#warning-div").html(`
                            <p class="text-danger">Password fields are required</p>
                        `);
                    }
                    else{
                        $("#warning-div").html(`
                            <p class="text-danger">Password does not match</p>
                        `);
                    }
                });
            </script>
        ';
    }
    else{
        echo '
            <br><br>
            <p class="text-danger">Wrong Answer</p>
        ';
    }
?>