<?php
    session_start();
    include("./includes/login-check.php");
    
    include("./includes/database.php");
    $db = new Database();

    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);

    $inv->updateQuantity("all");
    $inv->updateStockStatus();
    $inv->updateExpirationStatus();

    $user = $_SESSION["user"];
    $level = $_SESSION["level"];

    $username = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["user_name"];
    $firstname = $db->sql("SELECT `first_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["first_name"];
    $lastname = $db->sql("SELECT `last_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["last_name"];
    $securityQuestion = $db->sql("SELECT `security_question` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["security_question"];

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

    <link rel="stylesheet" href="./css/account.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>My Account</title>
</head>
<style>
    .account-section {
        animation: transitionIn-Y-bottom 0.5s;
    }
    
    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
</style>
<body>
    <?php 
        //implement the sidebar
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>

    <div class="main-div">

        <!--Heading-->
        

        <div class="container-lg p-5 mb-5 account-section">
            <h4 class="pb-3 mb-2">My Account</h4>
            <div class="row">


                <!--Account Details-->
                <div class="col col-lg-4 p-3">
                    <div class="account-details bordered rounded p-3">
                        <div class="row d-flex justify-content-center p-3">
                            <img class="p-1 bordered rounded-circle" 
                                src="./assets/profiles/user_<?=$user?>.png?t=<?=time()?>" 
                                alt="" 
                                style="width: 150px; height: 150px;"
                            >
                        </div>
                        <div class="row d-flex justify-content-center">
                            <h3 class="text-center"><?=$username?></h3>
                        </div>
                        <div class="row d-flex justify-content-center">
                            <h5 class="text-muted"><?=$firstname." ".$lastname?></h5>
                        </div>
                        <br>
                        <div class="row d-flex justify-content-center mb-5">
                            <?php
                                echo $level == 1 ? '<span class="badge badge-success">Staff</span>' :
                                '<span class="badge badge-primary">Admin</span>';
                            ?>
                        </div>
                    </div>
                    
                </div>
                

                <!--Edit Account-->
                <div class="col col-lg-8 p-3">
                    <div class="edit-account bordered rounded p-4">
                        <form id="editAccountForm" action="./account-form-editAccount.php" method="post" enctype="multipart/form-data">
                            <h5>Edit Account</h5>
                            <br>
                            <br>
                            <div class="form-group w-50">
                                <label for="userImage">Profile Image</label>
                                <input type="file" class="form-control" id="userImage" name="userImage" accept="image/*">
                            </div>
                            <br>
                            <div class="form-group w-50">
                                <label for="userImage">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?=$username?>" required>
                            </div>
                            <div class="d-flex">
                                <div class="form-group w-50 mr-3">
                                    <label for="userImage">First Name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?=$firstname?>" required>
                                </div>
                                <div class="form-group w-50">
                                    <label for="userImage">Last Name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?=$lastname?>" required>
                                </div>
                            </div>
                            <hr>
                            <small class="text-muted">Leave blank if you don't want to change password</small>
                            <br>
                            <br>
                            <div class="form-group w-50">
                                <label for="newPassword">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword">
                            </div>
                            <div class="form-group w-50">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                            </div>

                            <hr>
                            <small class="text-muted">Leave blank if you don't want to change security question.</small>
                            <br>
                            <br>
                            <div class="form-group w-75">
                                <label for="securityQuestion">Security Question</label>
                                <input type="text" class="form-control" id="securityQuestion" name="securityQuestion" value="<?=$securityQuestion?>" placeholder="Set a security question for Forgot Password">
                            </div>
                            <div class="form-group w-50">
                                <label for="securityAnswer">Answer</label>
                                <input type="text" class="form-control" id="securityAnswer" name="securityAnswer">
                            </div>
                            
                            <div class="d-flex justify-content-end" data-toggle="modal" data-target="#verifyPasswordModal">
                                <button type="button" class="btn btn-blue">Apply Changes</button>
                            </div>

                            <input type="hidden" id="password" name="password">
                            
                        </form>

                        
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="verifyPasswordModal" tabindex="-1" role="dialog" aria-labelledby="verifyPasswordModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyPasswordModalTitle">Verify Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>Enter your current password to apply changes</h6>
                <br>
                <div class="form-group w-50">
                    <label for="verifyPass">Current Password</label>
                    <input type="password" class="form-control" id="verifyPass" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitFormEdit">Submit</button>
            </div>
            </div>
        </div>
    </div>

    <script src="./js/preventKeydown.js"></script>
    <script>
        $("#submitFormEdit").click(function(){
            if($("#verifyPass").val() !== ""){
                var verifyPass = $("#verifyPass").val();
                $("#password").val(verifyPass);
                $("#editAccountForm").submit();
            }
            

        });
    </script>
</body>
</html>