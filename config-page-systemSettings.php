<?php
    session_start();
    include("./includes/login-check.php");

    if($_SESSION["level"] == 1){
        header("Location: ./dashboard.php");
        exit();
    }
    
    include("./includes/database.php");
    $db = new Database();

    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);

    $inv->updateQuantity("all");
    $inv->updateStockStatus();
    $inv->updateExpirationStatus();
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
        $sub = "system-settings";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div">
        
        <!--Row 1 (Heading)-->
        <?php include("./includes/header.php"); ?>


        <!--Settings Section Container Div-->
        <div class="container mb-5 settings-section">

            <div class="row">
                <div class="col d-flex flex-row-reverse">

                    <div class="btn-group dropleft">
                        <button type="button" class="btn btn-blue dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-lock"></i>&emsp;Change Validations
                        </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" data-toggle="modal" data-target="#changeInvValidationModal">Inventory</a>
                        <a class="dropdown-item" data-toggle="modal" data-target="#changeDiscountValidationModal">Discount</a>
                    </div>
                    </div>
                    
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col">
                    <table class="table shadow-lg bg-white rounded">
                        <thead>
                            <th colspan="2">STORE DETAILS</th>
                        </thead>
                        <tbody>
                            <?php
                                $storeDetails = $db->sql("SELECT * FROM store_details WHERE 1");
                                $store = $storeDetails->fetch_assoc();
                            ?>
                            <tr>
                                <th>STORE NAME</th>
                                <td><?=$store["store_name"]?></td>
                            </tr>
                            <tr>
                                <th>ADDRESS</th>
                                <td><?=$store["store_address"]?></td>
                            </tr>
                            <tr>
                                <th>TIN NUMBER</th>
                                <td><?=$store["tin_number"]?></td>
                            </tr>
                            <tr>
                                <th>CONTACT</th>
                                <td><?=$store["contact"]?></td>
                            </tr>
                            <tr>
                                <th></th>
                                <td><button class="btn btn-blue float-right" data-toggle="modal" data-target="#editStoreDetailsModal"><i class="fa-solid fa-pen-to-square"></i>&emsp;Edit Details</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col">
                    <table class="table shadow-lg bg-white rounded">
                        <thead>
                            <th colspan="3">DISPLAY SETTINGS</th>
                        </thead>
                        <tbody>
                            <tbody>
                                <tr>
                                    <th style="vertical-align: middle">LOGO</th>
                                    <td style="vertical-align: middle"><img src="./assets/logo.png?t=<?=time()?>" alt="" style="width: 142px"></td>
                                    <td style="vertical-align: middle"><button class="btn btn-blue" data-toggle="modal" data-target="#changeLogoModal"><i class="fa-solid fa-upload"></i> Change</button></td>
                                </tr>
                                <tr>
                                    <th style="vertical-align: middle">BACKGROUND</th>
                                    <td style="vertical-align: middle"><img src="./assets/login-background.png?t=<?=time()?>" alt="" style="width: 142px"></td>
                                    <td style="vertical-align: middle"><button class="btn btn-blue" data-toggle="modal" data-target="#changeBackgroundModal"><i class="fa-solid fa-upload"></i> Change</button></td>
                                </tr>
                            </tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>

            <br><hr>

            <!--Logs-->
            
            <?php
                $module = isset($_GET["module"]) ? $_GET["module"] : "1";
                $page = isset($_GET["page"]) ? $_GET["page"] : "1";

                $condition = "";

                if($module === "1"){
                    $condition = "`activity_type` = `activity_type`";
                }
                elseif($module === "login/logout"){
                    $condition = "`activity_type` = 'login' OR `activity_type` = 'logout'";
                }
                else{
                    $condition = "`activity_type` = '$module'";
                }

                $query = "
                    SELECT `user_logs`.`log_id`, `user_logs`.`user_id`, `accounts`.`user_name`, `user_logs`.`activity_type`, `user_logs`.`activity_description`, `user_logs`.`date_added`, `user_logs`.`time_added`
                    FROM `user_logs`
                        INNER JOIN `accounts` ON `user_logs`.`user_id` = `accounts`.`user_id`
                ";

                $pageLocation = "./config-page-systemSettings.php#moduleForm";
                include("./includes/pagination.php");

                $result = $db->sql("
                    SELECT *
                    FROM (
                        $query
                    ) as `tbl`
                    WHERE $condition
                    ORDER BY `log_id` DESC
                    LIMIT $limit OFFSET $offset;
                ");

            ?>
            <h5 class="mt-1">Logs</h5>
            <form id="moduleForm" action="./config-page-systemSettings.php#moduleForm" method="get" tabindex="1" style="outline: none">
                <select class="form-control w-25 mt-3" name="module" id="module" onchange="this.form.submit()">
                    <option value="1" <?=$module == "1" ? "selected" : ""?>>All</option>
                    <option value="login/logout" <?=$module == "login/logout" ? "selected" : ""?>>Login/Logout</option>
                    <option value="inventory" <?=$module == "inventory" ? "selected" : ""?>>Inventory</option>
                    <option value="pos" <?=$module == "pos" ? "selected" : ""?>>POS</option>
                    <option value="config" <?=$module == "config" ? "selected" : ""?>>Configurations</option>
                </select>
            </form>
            <br>
            <table class="table shadow-lg rounded" id="logsTable" >
                <thead>
                    <tr>
                        <th>#</th>
                        <th>LOG ID</th>
                        <th>ACTIVITY TYPE</th>
                        <th>DESCRIPTION</th>
                        <th>DATE</th>
                        <th>TIME</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($result->num_rows > 0){
                            $rowCount = $offset + 1;
                            while($row = $result->fetch_assoc()){
                                echo '
                                    <tr>
                                        <th style="text-align: center; vertical-align: middle">'.$rowCount.'</th>
                                        <td style="text-align: center; vertical-align: middle">LOG-'.$row["log_id"].'</td>
                                        <td style="text-align: center; vertical-align: middle">'.strtoupper($row["activity_type"]).'</td>
                                        <td style="text-align: left; vertical-align: middle">'.wordwrap($row["user_name"]." ".$row["activity_description"],40,"<br>\n").'</td>
                                        <td style="text-align: center; vertical-align: middle">'.date("Y-m-d", strtotime($row["date_added"])).'</td>
                                        <td style="text-align: center; vertical-align: middle">'.date("h:i a", strtotime($row["time_added"])).'</td>
                                    </tr>
                                ';
                                $rowCount++;
                            }
                        }
                    ?>
                </tbody>
            </table>
            <?=$pageCount > 1 ? '<p>Page '.$page.' out of '.$pageCount.' pages</p>' : ''?>

            <?=$pagination?>
        </div>

        
    </div>



<!-- Change Validations -->
<form action="./config-form-changeInvValidation.php" method="post">
<div class="modal fade" id="changeInvValidationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Change Inventory Validation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col">
                <label for="oldInvValidation">Current Validation</label>
                <input class="form-control" type="password" id="oldInvValidation" name="oldInvValidation" required>
            </div>
            <div class="col">
                <label for="newInvValidation">New Validation</label>
                <input class="form-control" type="password" id="newInvValidation" name="newInvValidation" required>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</form>

<!-- Change Validations -->
<form action="./config-form-changeDiscountValidation.php" method="post">
<div class="modal fade" id="changeDiscountValidationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Change Discount Validation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col">
                <label for="oldDiscountValidation">Current Validation</label>
                <input class="form-control" type="password" id="oldDiscountValidation" name="oldDiscountValidation" required>
            </div>
            <div class="col">
                <label for="newDiscountValidation">New Validation</label>
                <input class="form-control" type="password" id="newDiscountValidation" name="newDiscountValidation" required>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Edit Store Details Modal -->
<form action="./config-form-editStoreDetails.php" method="post" enctype="multipart/form-data">
    <div class="modal fade" id="editStoreDetailsModal" tabindex="-1" role="dialog" aria-labelledby="editStoreDetailsModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Edit Store Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

            <div class="form-group w-75">
                <label for="storeName">Store Name</label>
                <input type="text" class="form-control" id="storeName" name="storeName" value="<?=$store["store_name"]?>" required>
            </div>

            <div class="form-group w-100">
                <label for="storeAddress">Store Address</label>
                <input type="text" class="form-control" id="storeAddress" name="storeAddress" value="<?=$store["store_address"]?>" required>
            </div>

            <div class="form-group w-75">
                <label for="tinNumber">TIN Number</label>
                <input type="text" class="form-control" id="tinNumber" name="tinNumber" value="<?=$store["tin_number"]?>" required>
            </div>

            <div class="form-group w-75">
                <label for="contact">Contact</label>
                <input type="text" class="form-control" id="contact" name="contact" placeholder="Enter mobile number or email" value="<?=$store["contact"]?>" required>
            </div>
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
        </div>
    </div>
    </div>
</form>


<!-- Change Logo Modal -->                
<form action="./config-form-changeLogo.php" method="post" enctype="multipart/form-data">
    <div class="modal fade" id="changeLogoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Change Logo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="file" name="logo" accept="image/png, image/gif, image/jpeg" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
    </div>
</form>


<!-- Change Background Modal -->                
<form action="./config-form-changeBackground.php" method="post" enctype="multipart/form-data">
    <div class="modal fade" id="changeBackgroundModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Change Background</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="file" name="background" accept="image/png, image/gif, image/jpeg" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
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