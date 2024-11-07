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

    <link rel="stylesheet" href="./css/inventory.css">
    <link rel="stylesheet" href="./css/config.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>POS Settings</title>
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
        $sub = "pos-settings";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div">
        
        <!--Row 1 (Heading)-->
        <?php include("./includes/header.php"); ?>


        <!--Settings Section Container Div-->
        <div class="container mb-5 settings-section">

            <?php
                $promo = $db->sql("
                    SELECT `promo`.*, `products`.`product_id`, `products`.`product_name`, `products`.`properties`
                    FROM `promo`
                        INNER JOIN `products` ON `promo`.`product_id` = `products`.`product_id`
                    WHERE `promo_status` = 1
                    ORDER BY `products`.`product_name` ASC
                ");
                $discount = $db->sql("
                    SELECT *
                    FROM `discount` 
                    WHERE `discount_status` = 1
                    ORDER BY `discount_name` ASC
                ");
            ?>
            
            <!--Row 2 Promo Section-->
            <div class="row">
                <div class="col col-lg-6">
                    <button class="btn btn-primary my-2" data-toggle="modal" data-target="#addPromoModal"><i class="fa-solid fa-plus"></i>&emsp;Add</button>
                    <div class="upperTables shadow-lg bg-white rounded">
                        <table class="table">
                            <thead>
                                <th colspan="3">PROMOS</th>
                            </thead>
                            <tbody>
                                <?php
                                    while($row = $promo->fetch_assoc()){
                                        $promoType = "";
                                        $promoTooltip = "";
                                        if($row["promo_type"] == 1){
                                            $promoType .= "Buy ".$row["buy_quantity"]." Get ".$row["get_quantity"];

                                            $prdFree = $db->sql("SELECT product_name, properties FROM products WHERE product_id = ".$row["product_freebie"])->fetch_assoc();
                                            $promoTooltip = "Free ".$prdFree["product_name"]." ".$prdFree["properties"];
                                        }
                                        elseif($row["promo_type"] == 2){
                                            $promoType .= $row["percentage"]."% Discount";
                                        }
                                        elseif($row["promo_type"] == 3){
                                            $promoType .= "Save ".$row["percentage"]."%<br>Min Spend<br>₱ ".number_format($row["min_spend"], 2);
                                        }

                                        echo '
                                            <tr>
                                                <td style="vertical-align: middle">'.wordwrap($row["product_name"],25,"<br>\n").'</td>
                                                <td class="text-center" style="vertical-align: middle"><small class="text-muted" data-toggle="tooltip" title="'.$promoTooltip.'">'.$promoType.'</small></td>
                                                <td style="vertical-align: middle">
                                                    <a id="disable-promo-'.$row["promo_id"].'" class="float-right ml-3 text-primary" data-toggle="modal" data-target="#disablePromoModal"><i class="fa-solid fa-ban"></i></a>
                                                        
                                                    <a id="edit-promo-'.$row["promo_id"].'" class="float-right" data-toggle="modal" data-target="#editPromoModal" title="Edit Promo"><i class="fa-solid fa-pen"></i></a>
                                                </td>
                                            </tr>

                                            <script>
                                                $("#edit-promo-'.$row["promo_id"].'").click(function(){
                                                    $("#editPromo-promoId").val("'.$row["promo_id"].'");
                                                    $("#editPromo-oldProductId").val("'.$row["product_id"].'");
                                                    $("#editPromo-product").val("'.$row["product_id"].'");
                                                    $("#editPromo-promoType").val("'.$row["promo_type"].'");
                                                    changePromoTypeForms("editPromo");

                                                    $("#editPromo-buyQuantity").val("'.$row["buy_quantity"].'");
                                                    $("#editPromo-getQuantity").val("'.$row["get_quantity"].'");
                                                    $("#editPromo-freeProduct").val("'.$row["product_freebie"].'");
                                                    $("#editPromo-minimumSpend").val("'.$row["min_spend"].'");
                                                    $("#editPromo-percentage").val("'.$row["percentage"].'");
                                                    $("#editPromo-savePercentage").val("'.$row["percentage"].'");
                                                });

                                                $("#disable-promo-'.$row["promo_id"].'").click(function(){
                                                    $("#disablePromo-promoId").val("'.$row["promo_id"].'");
                                                    $("#disablePromo-productNameText").html("'.$row["product_name"].' '.$row["properties"].'");
                                                });
                                            </script>
                                        ';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col col-lg-6">
                    <button class="btn btn-primary my-2" data-toggle="modal" data-target="#addDiscountModal"><i class="fa-solid fa-plus"></i>&emsp;Add</button>
                    <div class="upperTables shadow-lg bg-white rounded">
                        <table class="table">
                            <thead>
                                <th colspan="3">DISCOUNT</th>
                            </thead>
                            <tbody>
                                <?php
                                    while($row = $discount->fetch_assoc()){
                                        echo '
                                            <tr>
                                                <td style="vertical-align: middle">'.$row["discount_name"].'</td>
                                                <td class="text-center" style="vertical-align: middle">'.$row["percent"].'%</td>
                                                <td style="vertical-align: middle">
                                                    <a id="disable-discount-'.$row["discount_id"].'" class="float-right ml-3 text-primary" data-toggle="modal" data-target="#disableDiscountModal"><i class="fa-solid fa-ban"></i></a>
                                                        
                                                    <a id="edit-discount-'.$row["discount_id"].'" class="float-right" data-toggle="modal" data-target="#editDiscountModal" title="Edit Discount"><i class="fa-solid fa-pen"></i></a>
                                                </td>
                                            </tr>

                                            <script>
                                                $("#edit-discount-'.$row["discount_id"].'").click(function(){
                                                    $("#editDiscount-discountId").val("'.$row["discount_id"].'");
                                                    $("#editDiscount-oldDiscountName").val("'.$row["discount_name"].'");
                                                    $("#editDiscount-discountName").val("'.$row["discount_name"].'");
                                                    $("#editDiscount-percentage").val("'.$row["percent"].'");
                                                });

                                                $("#disable-discount-'.$row["discount_id"].'").click(function(){
                                                    $("#disableDiscount-discountId").val("'.$row["discount_id"].'");
                                                    $("#disableDiscount-discountNameText").html("'.$row["discount_name"].'");
                                                });
                                            </script>
                                        ';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            


        </div>


    </div>


<!-- Add Product Modal -->
<form action="./pos-form-addPromo.php" method="post">
<div class="modal fade" id="addPromoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Promo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <label for="addPromo-product">Apply Promo to:</label>
        <select class="form-control" name="product" id="addPromo-product" required>
            <option value selected disabled hidden>Select Product</option>
            <?php
                $result = $db->sql("SELECT product_id, product_name, properties FROM products WHERE archive_status = 0 ORDER BY product_name ASC");
                while($row = $result->fetch_assoc()){
                    echo '
                        <option value="'.$row["product_id"].'">'.$row["product_name"].' '.$row["properties"].'</option>
                    ';
                }
            ?>
        </select>

        <br>

        <label for="addPromo-promoType">Promo Type</label>
        <select class="form-control w-50" name="promoType" id="addPromo-promoType">
            <option value="1">BOGO</option>
            <option value="2">Product Discount</option>
            <option value="3">Buy More Save More</option>
        </select>

        <hr>
        
        <div id="addPromo-promoTypeInputs">

        </div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </div>
  </div>
</div>
</form>
<script>
    $("#addPromo-promoType").change(function(){
        changePromoTypeForms("addPromo");
    });
</script>

<!-- Edit Product Modal -->
<form action="./pos-form-editPromo.php" method="post">
<div class="modal fade" id="editPromoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Promo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editPromo-promoId" name="promoId">
        <input type="hidden" id="editPromo-oldProductId" name="oldProductId">

        <label for="editPromo-product">Apply Promo to:</label>
        <select class="form-control" name="product" id="editPromo-product" required>
            <option value selected disabled hidden>Select Product</option>
            <?php
                $result = $db->sql("SELECT product_id, product_name, properties FROM products WHERE archive_status = 0 ORDER BY product_name ASC");
                while($row = $result->fetch_assoc()){
                    echo '
                        <option value="'.$row["product_id"].'">'.$row["product_name"].' '.$row["properties"].'</option>
                    ';
                }
            ?>
        </select>

        <br>

        <label for="editPromo-promoType">Promo Type</label>
        <select class="form-control w-50" name="promoType" id="editPromo-promoType">
            <option value="1">BOGO</option>
            <option value="2">Product Discount</option>
            <option value="3">Buy More Save More</option>
        </select>

        <hr>
        
        <div id="editPromo-promoTypeInputs">

        </div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>
</form>
<script>
    $("#editPromo-promoType").change(function(){
        changePromoTypeForms("editPromo");
    });
</script>

<!-- Disable Promo Modal -->
<form action="./pos-form-disablePromo.php" method="post">
<div class="modal fade" id="disablePromoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Disable Promo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="disablePromo-promoId" name="promoId">
        <center>
            <p>Are you sure you want to disable
            <br>
            <span id="disablePromo-productNameText">...</span>?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Disable</button>
      </div>
    </div>
  </div>
</div>
</form>






<!-- Add Discount Modal -->
<form action="./pos-form-addDiscount.php" method="post">
<div class="modal fade" id="addDiscountModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Discount</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col">
                <label for="addDiscount-discountName">Discount Name</label>
                <input type="text" class="form-control" id="addDiscount-discountName" name="discountName">
            </div>
            <div class="col">
                <label for="addDiscount-percentage">Percentage</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="addDiscount-percentage" name="percentage">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">%</span>
                    </div>
                </div>
                
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Edit Discount Modal -->
<form action="./pos-form-editDiscount.php" method="post">
<div class="modal fade" id="editDiscountModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Discount</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editDiscount-discountId" name="discountId">
        <input type="hidden" id="editDiscount-oldDiscountName" name="oldDiscountName">
        <div class="row">
            <div class="col">
                <label for="editDiscount-discountName">Discount Name</label>
                <input type="text" class="form-control" id="editDiscount-discountName" name="discountName">
            </div>
            <div class="col">
                <label for="editDiscount-percentage">Percentage</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="editDiscount-percentage" name="percentage">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">%</span>
                    </div>
                </div>
                
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Disable Discount Modal -->
<form action="./pos-form-disableDiscount.php" method="post">
<div class="modal fade" id="disableDiscountModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Disable Discount</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="disableDiscount-discountId" name="discountId">
        <center>
            <p>Are you sure you want to disable
            <br>
            <span id="disableDiscount-discountNameText">...</span>?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Disable</button>
      </div>
    </div>
  </div>
</div>
</form>





<script src="./js/preventKeydown.js"></script>

<script>
    $(document).ready(function(){
        changePromoTypeForms("addPromo");
    });

    $("input").keydown(function(e){
        // Enter was pressed without shift key
        if (e.keyCode == 220)
        {
            // prevent default behavior
            e.preventDefault();
        }
    });


    function changePromoTypeForms(elem){
        var promoInputDiv = $(`#${elem}-promoTypeInputs`);
        if($(`#${elem}-promoType`).val() == 1){
            promoInputDiv.html(`
                <div class="row">
                    <div class="col">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Buy</span>
                            </div>
                            <input type="number" class="form-control" id="${elem}-buyQuantity" name="buyQuantity" required>
                        </div>
                    </div>

                    <div class="col">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Get</span>
                            </div>
                            <input type="number" class="form-control" id="${elem}-getQuantity" name="getQuantity" required>
                        </div>
                    </div>
                </div>


                <label for="${elem}-product">Free Product</label>
                <select class="form-control" name="freeProduct" id="${elem}-freeProduct" required>
                    <option value selected disabled hidden>Select Product</option>
                    <?php
                        $result = $db->sql("SELECT product_id, product_name, properties FROM products WHERE archive_status = 0 ORDER BY product_name ASC");
                        while($row = $result->fetch_assoc()){
                            echo '
                                <option value="'.$row["product_id"].'">'.$row["product_name"].' '.$row["properties"].'</option>
                            ';
                        }
                    ?>
                </select>
            `);
        }
        else if($(`#${elem}-promoType`).val() == 2){
            promoInputDiv.html(`
                <label for="${elem}-percentage">Discount Percentage</label>
                <div class="input-group mb-3 w-50">
                    <input type="number" class="form-control" id="${elem}-percentage" name="percentage" required>
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">%</span>
                    </div>
                </div>
            `);
        }
        else if($(`#${elem}-promoType`).val() == 3){
            promoInputDiv.html(`
            <div class="row">
                <div class="col">
                    <label for="${elem}-savePercentage">Save Percentage</label>
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" id="${elem}-savePercentage" name="percentage" required>
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon2">%</span>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <label for="${elem}-minimumSpend">Minimum Spend</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">₱</span>
                        </div>
                        <input type="number" step="0.01" class="form-control" id="${elem}-minimumSpend" name="minimumSpend" required>
                    </div>
                </div>
            </div>
                
            `);
        }
    }
</script>
</script>
</body>
</html>