<!--Row 1 (Heading)-->
<div class="row mb-5 text-dark py-3 px-4 shadow-sm" style="background-color: #b0dc60">
    <div class="col d-flex align-items-center">
        <?php
            if(isset($_GET["search"]) or isset($_GET["category-filter"]) or isset($_GET["stock-filter"]) or isset($_GET["expiration-filter"]) or isset($_GET["fromDate"]) or isset($_GET["toDate"])){
                //show back button if there is a POST request
                echo '
                    <a href="./'.$loc.'.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left-long"></i>&emsp;<b>Back</b></a>
                ';
            }
            else{
                //show the title if there are no POST request
                echo '<h3>'.$main.'</h3>';
            }
        ?>
    </div>
    <div class=" col-7 d-flex align-items-center">
        <?php
            if($main === "Inventory" and $sub === "product-list"){
                echo '
                    <form class="search-form d-flex w-100" action="./inv-page-productList.php" method="get">
                        <input type="text" class="search-bar form-control" name="search" id="inventory-search" placeholder="Search Barcode Number or Product Name" value="'.$search.'" required>
                        <input type="hidden" name="loc" value="inv-page-productList">
                        <button type="submit" class="btn search-btn" data-toggle="tooltip" data-placement="bottom" title="Search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                    <div class="search-rec w-100 mt-5" id="search-rec" style="position: absolute">
                        
                    </div>
                ';
            }
            if($main === "Inventory" and $sub === "return-list"){
                echo '
                    <form class="search-form d-flex w-100" action="./inv-page-returnList.php" method="get">
                        <input type="text" class="search-bar form-control" name="search" id="inventory-search" placeholder="Search Batch Number or Product Name" value="'.$search.'" required>
                        <button type="submit" class=" btn search-btn" data-toggle="tooltip" data-placement="bottom" title="Search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button> 
                        <input type="hidden" name="returnStatus" value="'.$returnStatus.'">
                    </form>
                ';
            }
            elseif($main === "Point of Sale" and $sub === "transactions"){
                echo '
                    <form class="search-form d-flex w-100" action="./pos-page-transactions.php" method="get">
                        <input type="hidden" name="loc" value="pos-page-transactions">
                        <input type="text" class="search-bar form-control" name="search" id="pos-search" placeholder="Search Transaction ID" value="" required>
                        <button type="submit" class="btn search-btn" data-toggle="tooltip" data-placement="bottom" title="Search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                ';
            }
        ?>
    </div>
    <div class="col d-flex flex-row-reverse align-items-center">
        
        <?php include("./includes/account-tab.php") ?>
        &emsp;&emsp;
        <?php
            $expired = $db->sql("
                SELECT b.*, p.barcode_id , p.product_name, p.properties 
                FROM batch b
                    INNER JOIN products p ON b.product_id = p.product_id
                WHERE b.archive_status = 0 AND b.return = 0 AND b.expiration_status > 1 AND b.single_quantity > 0
                ORDER BY b.expiration_status DESC, p.product_name ASC
            ");

            $critical = $db->sql("
                SELECT * 
                FROM products
                WHERE archive_status = 0 AND stock_status > 1
                ORDER BY stock_status DESC, product_name ASC
            ");
        ?>
        <div class="btn-group dropleft">
            <button class="btn btn-white text-primary dropdown" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative; padding: 0px">
                <img src="./assets/bell.png" alt="" style="height: 30px">
                <span class="bg-danger text-white" style="position: absolute; top: -10px; right: -10px; width: 20px; height: 20px; border-radius: 30px; font-size: 13px; <?=($expired->num_rows + $critical->num_rows) == 0 ? "display: none" : ""?>"><?=$expired->num_rows + $critical->num_rows?></span>
            </button>
            <div class="dropdown-menu" style="max-height: 500px; width: 500px; overflow-y: auto">
                <!-- Dropdown menu links -->
                <?php
                    if($expired->num_rows > 0 or $critical->num_rows > 0){
                        while($row = $expired->fetch_assoc()){
                            $status = "";
                            if($row["expiration_status"] === "2"){
                                $status = '<img class="mt-3" src="./assets/nearly-expired.png" style="height: 50px">';
                            }
                            else{
                                $status = '<img class="mt-3" src="./assets/expired.png" style="height: 50px">';
                            }
                            echo '
                                <a class="dropdown-item border-bottom" href="./inv-page-productList.php?search='.$row["product_name"].'" style="height: 100px">
                                    
                                    <div class="row">
                                        <div class="col col-lg-9" style="vertical-align: middle">
                                        <small class="text-muted">'.$row["batch_id"].'</small>
                                        <br>
                                        '. wordwrap($row["product_name"].' '.$row["properties"],30,"<br>\n").'
                                        </div>
                                        <div class="col col-lg-3 d-flex justify-content-center">
                                            '.$status.'
                                        </div>
                                    </div>
                                    
                                </a>
                            ';
                        }


                        while($row = $critical->fetch_assoc()){
                            $status = "";
                            if($row["stock_status"] === "2"){
                                $status = '<img class="mt-3" src="./assets/low-stock.png" style="height: 50px">';
                            }
                            else{
                                $status = '<img class="mt-3" src="./assets/out-of-stock.png" style="height: 50px">';
                            }
                            echo '
                                <a class="dropdown-item border-bottom" href="./inv-page-productList.php?search='.$row["product_name"].'" style="height: 100px">
                                    
                                    <div class="row">
                                        <div class="col col-lg-9" style="vertical-align: middle">
                                        <small class="text-muted">'.$row["barcode_id"].'</small>
                                        <br>
                                        '. wordwrap($row["product_name"].' '.$row["properties"],30,"<br>\n").'
                                        </div>
                                        <div class="col col-lg-3 d-flex justify-content-center">
                                            '.$status.'
                                        </div>
                                    </div>
                                    
                                </a>
                            ';
                        }
                    }
                    else{
                        echo '
                            <div class="text-center p-5">
                                <b>You Have 0 Notifications</b>
                            </div>
                        ';
                    }
                ?>
            </div>
        </div>
        
    </div>
</div>