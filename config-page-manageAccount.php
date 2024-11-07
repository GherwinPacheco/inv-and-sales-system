<?php
    session_start();
    include("./includes/login-check.php");

    if($_SESSION["level"] == 1){
        header("Location: ./dashboard.php");
        exit();
    }

    $user = $_SESSION["user"];
    
    include("./includes/database.php");
    $db = new Database();

    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);

    $inv->updateQuantity("all");
    $inv->updateStockStatus();
    $inv->updateExpirationStatus();


    //stores the condition based on the POST request
    $search = "";
    $itemCount = "";
    $page = 0;
    $condition = "`user_id` != '$user'";
    $fromDate = "";
    $toDate = "";
    $disabledAcc = "";

    $loc = isset($_GET["loc"]) ? $_GET["loc"] : "config-page-manageAccount";

    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        //for searching of items
        if(isset($_GET["search"])){
            $search = $_GET["search"];
            $condition .= " AND `user_name` LIKE '%$search%' OR `first_name` LIKE '%$search%' OR `last_name` LIKE '%$search%' OR CONCAT(`first_name`, ' ', `last_name`) LIKE '%$search%'";
        }

        if(isset($_GET["dA"]) and $_GET["dA"] === '1'){
            $disabledAcc = $_GET["dA"];
        }
        else{
            $disabledAcc = 0;
            $condition .= " AND `account_status` = 1";
        }

        

        //get the page of table
        if(isset($_GET["page"])){
            $page = $_GET["page"];
        }
        else{
            $page = 1;
        }
    }
    else{
        $page = 1;
    }
    
    //set the query for retrieving the transactions table
    $query = "
        SELECT * FROM `accounts` WHERE $condition ORDER BY `account_status` DESC, `user_name` ASC
    ";

    $pageLocation = "./config-page-manageAccount.php";
    include("./includes/pagination.php");
    
    //execute the sql query for getting the table of products 
    $result = $db->sql($query." LIMIT $limit OFFSET $offset;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=`, initial-scale=1.0">


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

    <link rel="stylesheet" href="./css/config.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>System Settings</title>
</head>

<style>
    .settings-section {
        animation: transitionIn-Y-bottom 0.5s;
    }
    
    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
</style>

<body>
    <?php 
        //implement the sidebar
        $main = "Configurations";
        $sub = "manage-account";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div">
        
        <!--Row 1 (Heading)-->
        <?php include("./includes/header.php"); ?>


        <!--Settings Section Container Div-->
        <div class="container mb-5 settings-section">

            <!--Heading and Buttons-->
            <div class="row mb-5">
                <div class="col col-lg-6">
                    <h4>Accounts</h4>
                </div>

                <div class="col col col-lg-6 d-flex justify-content-end">
                    <form action="./config-page-manageAccount.php" method="get">
                        <button type="submit" name="dA" value="<?=$disabledAcc == 1 ? '0' : '1'?>" class="btn btn-white mx-3" data-toggle="tooltip" title="<?=$disabledAcc == 1 ? 'Hide' : 'Show'?> Disabled Accounts">
                            <?=$disabledAcc == 1 ? '<i class="fa-solid fa-eye-slash text-secondary"></i>' : '<i class="fa-solid fa-eye text-primary"></i>'?>
                        </button>
                    </form>
                    

                    <button type="button" class="btn btn-blue" data-toggle="modal" data-target="#addAccountModal">
                        <i class="fa-solid fa-person-circle-plus"></i>&emsp;Add Account
                    </button>
                </div>
            </div>

            
            <table class="accounts-table table rounded shadow-lg bg-white">
                <thead>
                    <th></th>
                    <th>USERNAME</th>
                    <th>FIRST NAME</th>
                    <th>LAST NAME</th>
                    <th>ROLE</th>
                    <th></th>
                </thead>
            
                <tbody>
                    <?php
                        if($result->num_rows > 0){
                            while($row = $result->fetch_assoc()){
                                $id = $row["user_id"];

                                $userLevelBadge = "";
                                $changeRoleButton = "";
                                $changeStatusButton = "";
                                $color = "";
                                
                                if($row["user_level"] == 1){
                                    $userLevelBadge = '<span class="badge badge-success">Staff</span>';
                                    $changeRoleButton = '<a class="dropdown-item" href="./config-form-changeRole.php?id='.$id.'"><i class="text-primary fa-solid fa-user-gear"></i>&emsp;Set as Admin</a>';
                                }
                                else{
                                    $userLevelBadge = '<span class="badge badge-primary">Admin</span>';
                                    $changeRoleButton = '<a class="dropdown-item" href="./config-form-changeRole.php?id='.$id.'"><i class="text-success fa-solid fa-user"></i>&emsp;Set as Staff</a>';
                                }

                                if($row["account_status"] == 0){
                                    $changeStatusButton = '<a class="dropdown-item" href="./config-form-changeStatus.php?id='.$id.'"><i class="fa-regular fa-circle-check"></i>&emsp;Enable Account</a>';
                                    $color = 'bg-gray';
                                }
                                else{
                                    $changeStatusButton = '<a class="dropdown-item" href="./config-form-changeStatus.php?id='.$id.'"><i class="fa-solid fa-ban"></i>&emsp;Disable Account</a>';
                                }
                                echo '
                                <tr class="'.$color.'">
                                    <td><img class="table-image rounded-circle bordered" src="./assets/profiles/user_'.$id.'.png?t='.time().'"></td>
                                    <td class="text-left">'.$row["user_name"].'</td>
                                    <td class="text-left">'.$row["first_name"].'</td>
                                    <td class="text-left">'.$row["last_name"].'</td>
                                    <td class="text-center">'.$userLevelBadge.'</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn text-secondary" type="button" id="dropdownMenuButton-'.$id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                '.$changeRoleButton.'
                                                '.$changeStatusButton.'
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                ';
                            }
                        }
                        else{
                            echo '
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <br>
                                        <img src="./assets/no_results.svg" style="width: 10%; height: 10%">
                                        <br>
                                        <br>
                                        No Results
                                    </td>
                                </tr>
                            ';
                        }
                    ?>
                </tbody>
            </table>
            <?=$pageCount > 1 ? '<p>Page '.$page.' out of '.$pageCount.' pages</p>' : ''?>
        
            
            <?=$pagination?>

        </div>
    </div>



    <!-- Modal -->
    <form action="./config-form-addAccount.php" method="post" enctype="multipart/form-data">
        <div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-labelledby="addAccountModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalTitle">Add Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group w-50">
                        <label for="role">Role</label>
                        <select class="form-control" name="role" id="role">
                            <option value="1">Staff</option>
                            <option value="2">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userImage">Profile Image</label>
                        <input type="file" class="form-control" id="userImage" name="userImage" accept="image/*">
                        <small class="text-muted">&emsp;Leave blank for default image</small>
                    </div>
                    <hr>
                    <div class="form-group w-50">
                        <label for="userImage">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="d-flex">
                        <div class="form-group w-50 mr-3">
                            <label for="userImage">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required>
                        </div>
                        <div class="form-group w-50">
                            <label for="userImage">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="form-group w-50 mr-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group w-50">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
                </div>
            </div>
        </div>
    </form>

<script src="./js/preventKeydown.js"></script>
<script>
    <?php
        if($pageCount == 1){
            echo '$("#pagination").css("display", "none");';
        }
    ?>

    $("input").keydown(function(e){
        // Enter was pressed without shift key
        if (e.keyCode == 220)
        {
            // prevent default behavior
            e.preventDefault();
        }
    });
</script>
</script>
</body>
</html>