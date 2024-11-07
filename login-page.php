<?php
    session_start();
    if(isset($_SESSION["user"]) and isset($_SESSION["level"])){
        header("Location: ./dashboard.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Bootstrap-->
    <link rel="stylesheet" href="./bootstrap/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="./bootstrap/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    
    <!--Jquery-->
    <script src="./js/jquery_3.6.4_jquery.min.js"></script>

    <script src="./bootstrap//popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="./bootstrap/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        })
    </script>

    <!--Fontawesome-->
    <link rel="stylesheet" href="./fontawesome/css/all.min.css">

    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/animations.css">

    <link rel="stylesheet" href="./css/login.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>Login</title>
</head>

<style>
    
    .login-section {
        animation: transitionIn-Y-bottom 0.5s;
    }

    
</style>

<body>
    <?php 
        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="container-fluid d-flex align-items-center justify-content-center" id="main-div">
        
        
            <div class="login-section bordered rounded shadow-lg w-25 p-4 bg-white">
                <h4 class="header-text text-center">Login</h4>
                <hr>
                <br>
                <form action="login-validate.php" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter Username" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                        <a href="" data-toggle="modal" data-target="#forgotPassModal" style="font-size: 14px; text-decoration: underline">Forgot Password?</a>
                    </div>
                    
                    <br>
                    <button type="submit" class="btn btn-success float-right">Login</button>
                </form>
            </div>
        
    </div>

<!--Forgot Password Modal -->
<div class="modal fade" id="forgotPassModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Forgot Password</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group" id="usernameDiv">
                <label for="username">Enter your Username</label>
                <input type="text" class="form-control" id="usernameVal">
                <br>
                <button type="button" id="submitUsername" class="btn btn-primary float-right">Proceed</button>
                
                <script>
                    $("#submitUsername").click(function(){
                        var name = $("#usernameVal").val();
                        if(name !== ""){
                            $.post("./ajax/forgotPass-checkUsername.php",
                            {
                                username: name
                            },
                            function(data, status){
                                $("#questionDiv").html(data);
                            });
                        }
                        
                    });
                </script>
            </div>
            
            <div class="form-group" id="questionDiv">
                
            </div>

            <div class="form-group" id="passwordDiv">
                
            </div>
        </div>
    </div>
</div>
</div>

<script src="./js/preventKeydown.js"></script>
</body>
</html>