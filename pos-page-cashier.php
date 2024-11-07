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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!--Bootstrap-->
    <link rel="stylesheet" href="./bootstrap/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="./bootstrap/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    
    <!--Jquery-->
    <script src="./js/jquery_3.6.4_jquery.min.js"></script>

    <script src="./bootstrap/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="./bootstrap/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

    <script>
        $(function () {
            $(document).tooltip({selector: '[data-toggle="tooltip"]'});
            $(document).popover({selector: '[data-toggle="popover"]', trigger: 'hover'});
        })
    </script>

    <!--Fontawesome-->
    <link rel="stylesheet" href="./fontawesome/css/all.min.css">

    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/animations.css">

    <link rel="stylesheet" href="./css/pos.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>Point of Sale</title>
</head>

<style>
    .pos-section {
        animation: transitionIn-Y-bottom 0.5s;
    }

    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
    
</style>

<body>
    
    <?php 
        //implement the sidebar
        $main = "Point of Sale";
        $sub = "cashier";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");

    ?>

        
    <div class="main-div" id="main-div">
        <div class="container-fluid">

            <!--Row 1 (Heading)-->
            <?php include("./includes/header.php"); ?>

            <form id="transactionForm" action="./pos-form-submitTransaction.php" method="post">
                <div class="row pos-section">
                    <div class="col col-lg-6 p-3 border-right">
                        <div class="d-flex mb-4">
                            <input type="text" class="form-control search-bar" id="productSearch" placeholder="Search Barcode Number or Product Name">
                            <button type="button" class="btn search-btn">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                        
                        <div class="productListDiv"><table class="table productListDiv rounded shadow-lg bg-white">
                            <thead>
                                <tr>
                                    <th style="width: 10%; text-align: center; vertical-align: middle">#</th>
                                    <th style="width: 35%; text-align: center; vertical-align: middle">NAME</th>
                                    <th style="width: 20%; text-align: center; vertical-align: middle">CATEGORY</th>
                                    <th style="width: 20%; text-align: center; vertical-align: middle">PRICE</th>
                                    <th style="width: 5%; text-align: center; vertical-align: middle"></th>
                                </tr>
                            </thead>
                            <tbody id="productList">
                                
                            </tbody>
                        </table></div>
                    </div>

                    
                    <div class="col col-lg-6 pt-3 selectedItemsDiv">
                        <!--Selected Item List-->
                        <h5 class="mb-3">Selected Items</h5>
                        
                        <div class="selectedTableDiv">
                            <table class="table rounded shadow-lg bg-white">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; vertical-align: middle; width: 25%">NAME</th>
                                        <th style="text-align: center; vertical-align: middle; width: 20%">QUANTITY</th>
                                        <th style="text-align: center; vertical-align: middle; width: 15%">PRICE</th>
                                        <th style="text-align: center; vertical-align: middle;  width: 15%">SUBTOTAL</th>
                                        <th style="text-align: center; vertical-align: middle;  width: 8%"></th>
                                    </tr>
                                </thead>
                                <tbody id="selectedItems">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>

                <input type="hidden" id="total" name="total">
                <input type="hidden" id="amountTendered" name="amountTendered">
                <input type="hidden" id="change" name="change">
                <input type="hidden" id="vatableSales" name="vatableSales">
                <input type="hidden" id="vatAmount" name="vatAmount">
                <input type="hidden" id="discount" name="discount">

                <hr>
                <!--Transaction Details Div-->
                <div class="row transactionDetails p-3">
                    <div class="col col-lg-3">
                        <div class="form-group">
                            <label for="amountTenderedInput">Amount Tendered&emsp;<span class="key-hint text-muted">[F1]</span></label>
                            <input type="number" class="form-control" id="amountTenderedInput" step="any" placeholder="0.00" required>
                        </div>
                        <div class="row">
                            
                            <div class="col col-7"><h6>Total:</h6></div>
                            <div class="col col-5"><p class="text-primary totalText"><b>₱ 0.00</b></p></div>
                        </div>
                        <div class="row">
                            <div class="col col-7"><h6>Change:</h6></div>
                            <div class="col col-5"><p class="changeText">₱ 0.00</p></div>
                        </div>
                    </div>

                    <div class="col col-lg-3">
                        <div class="form-group">
                            <label for="discount">Discount&emsp;<span class="key-hint text-muted">[F2]</span></label>
                            <select id="discountInput" class="form-control" name="discount">
                                <option value="0" selected>None</option>
                                <?php
                                    $discount = $db->sql("
                                        SELECT * FROM `discount` WHERE `discount_status` = 1
                                    ");
                                    while($row = $discount->fetch_assoc()){
                                        echo '<option value="'.$row["percent"].'">'.$row["discount_name"].'&emsp;'.$row["percent"].'%</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col col-7"><h6>Vatable Sales:</h6></div>
                            <div class="col col-5"><p class="vatableSalesText">₱ 0.00</p></div>
                        </div>
                        
                        <div class="row">
                            <div class="col col-7"><h6>Vat Amount:</h6></div>
                            <div class="col col-5"><p class="vatAmountText">₱ 0.00</p></div>
                        </div>
                    </div>

                    <div class="col col-lg-3">
                        <div class="form-group" class="discountForms" id="customerIdForm" style="display: none">
                            <label for="amountTenderedInput">Customer ID&emsp;&emsp;<span class="key-hint text-muted">[F3]</span>
                            <a type="button" class="row-tooltip" data-toggle="tooltip" data-placement="top" title="Leave blank if not applicable to the discount">
                                <i class="fa-solid fa-question"></i>
                            </a>
                            </label>
                            <input type="text" class="form-control" id="customerId" name="customerId" placeholder="Enter ID Number">
                        </div>
                    </div>

                    <div class="col col-lg-3">
                        <div class="form-group" class="discountForms" id="discountValidationForm" style="height: 100px">
                            <!---->
                        </div>

                        <div class="row  d-flex align-items-end justify-content-end">
                            <button type="submit" id="processTransaction" class="btn btn-primary m-3" disabled>Submit</button>
                        </div>
                        
                    </div>

                </div>
            </form>
                
            

        </div>
    </div>


<script src="./js/preventKeydown.js"></script>
<script type="text/javascript" src="./js/pos-cashier.js"></script>
</body>
</html>