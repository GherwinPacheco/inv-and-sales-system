<!-- Add Product Modal -->
<form class="m-3" action="./inv-form-addProduct.php" method="post">
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Add New Product</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        <!--Barcode ID and Product Name-->
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="addProduct-barcodeId">Barcode Number</label>
                    <input type="text" class="form-control" id="addProduct-barcodeId" name="barcodeId" aria-describedby="barcodeId" placeholder="Scan the barcode" maxlength="100">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="addProduct-productName">Product Name</label>
                    <input type="text" class="form-control" id="addProduct-productName" name="productName" aria-describedby="productName" maxlength="100" required>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="addProduct-productProperties">Properties</label>
                    <input type="text" class="form-control" id="addProduct-productProperties" name="productProperties" aria-describedby="productProperties" placeholder="Size/Mg" maxlength="100" required>
                </div>
            </div>
        </div>

        <!--Category and Price-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="addProduct-category">Category</label>
                <select class="form-control" id="addProduct-category" name="category" required>
                    <option value="" selected disabled>Choose Category</option>
                    <?php
                        $category_select = $db->sql("
                            SELECT * 
                            FROM `category`
                            WHERE category_status = 1
                            ORDER BY `category_name` ASC
                        ");
                        while($row = $category_select->fetch_assoc()) {
                            echo '<option value="'.$row["category_id"].'">'.$row["category_name"].'</option>';
                        }
                    ?>
                </select>
            </div>
            </div>
            
            <div class="col">
            <div class="form-group">
                <label for="addProduct-price" id="addProduct-priceLabel">Price</label>
                <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text" id="price-addon">₱</span></div>
                <input type="number" class="form-control" id="addProduct-price" name="price" aria-describedby="price" step="0.01" min="1" placeholder="0.00" required>
                </div>
                <small class="form-text text-muted">
                    Price is based on <b>single quantities</b>.
                </small>
            </div>
            </div>
        </div>

        <!--Minimum Stock and Bundle Pieces-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="addProduct-minimumStock">Minimum Stock</label>
                <input type="number" class="form-control" id="addProduct-minimumStock" name="minimumStock" aria-describedby="minimumStock" min="1" required>
                <small class="form-text text-muted">
                    Minimum stock is based on <b>single quantities</b>.
                </small>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="addProduct-bundlePieces">Bundle Pieces</label>
                <input type="number" class="form-control bundle-form" id="addProduct-bundlePieces" name="bundlePieces" aria-describedby="bundlePieces" min="2" required>
                
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="addProduct-noBundle" name="noBundle" value="1" checked>
                    <label class="form-check-label text-muted checkbox-text" for="noBundle">Check if product is being sold by <b>singles only</b>.</label>
                </div>
            </div>
            </div>
        </div>

        <!--Quantity-->
        <div class="row">
            
            <div class="col">
            <div class="form-group">
            <label for="addProduct-quantitySingle">Quantity (Singles)</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="addProduct-quantitySingle" name="quantitySingle" aria-describedby="quantitySingle" min="1" required>
                    <div class="input-group-append">
                        <select class="form-control" id="addProduct-unitSingle" name="unitSingle" required>
                            <?php
                                $unitS_select = $db->sql("
                                    SELECT * 
                                    FROM `unit_measurement`
                                    WHERE `unit_status` = 1
                                    ORDER BY `unit_name` ASC
                                ");
                                while($row = $unitS_select->fetch_assoc()) {
                                    echo '<option value="'.$row["unit_id"].'" '.$selected.'>'.$row["unit_name"].'</option>';

                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
            <label for="addProduct-quantityBundle">Quantity (Bundles)</label>
                <div class="input-group">
                    <input type="number" class="form-control bundle-form" id="addProduct-quantityBundle" name="quantityBundle" aria-describedby="quantityBundle" min="0" required>
                    <div class="input-group-append">
                        <select class="form-control bundle-form" id="addProduct-unitBundle" name="unitBundle" required>
                            <?php
                                $unitB_select = $db->sql("
                                    SELECT * 
                                    FROM `unit_measurement`
                                    WHERE `unit_status` = 1
                                    ORDER BY `unit_name` ASC
                                ");
                                $sel = 0;
                                while($row = $unitB_select->fetch_assoc()) {
                                    $sel = $sel = 0 ? $row["unit_id"] : $row["unit_id"];
                                    echo '<option value="'.$row["unit_id"].'">'.$row["unit_name"].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            </div>
        </div>
        
        <!--Expiration Date and Supplier-->
        <div class="row">

            <div class="col">
            <div class="form-group">
                <label for="addProduct-expirationDate">Expiration Date</label>
                <input type="date" class="form-control" id="addProduct-expirationDate" name="expirationDate" aria-describedby="expirationDate" required>
                
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="addProduct-noExpiration" name="noExpiration" value="1">
                    <label class="form-check-label text-muted checkbox-text" for="noExpiration">No Expiration Date.</label>
                </div>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="addProduct-supplier">Supplier</label>
                <select class="form-control" id="addProduct-supplier" name="supplier" required>
                    <option value="" selected disabled>Choose Supplier</option>
                    <?php
                        $supplier_select = $db->sql("
                            SELECT * 
                            FROM `supplier`
                            WHERE `supplier_status` = 1
                            ORDER BY `supplier_name` ASC
                        ");
                        while($row = $supplier_select->fetch_assoc()) {
                            echo '<option value="'.$row["supplier_id"].'">'.$row["supplier_name"].'</option>';
                        }
                    ?>
                </select>
            </div>
            </div>
        </div>
        
        <!--Product Description-->
        <div class="row">
            <div class="col">
                <label for="addProduct-description">Product Description (Optional)</label>
                <textarea class="form-control" id="addProduct-description" name="description" maxlength="255"></textarea>
            </div>
        </div>


    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Add Product</button>
    </div>
</div>
</div>
</div>
</form>

<script>
    noBundle();
    //autofocus the barcode input
    $('#addProductModal').on('shown.bs.modal', function (event) {
        $("#addProduct-barcodeId").focus();
        
    });

    //set bundle pieces and quantity (bundles) to readonly when checkbox is checked
    $("#addProduct-noBundle").change(function() {
        noBundle();
    });

    function noBundle(){
        if($("#addProduct-noBundle").is(":checked")) {
            $(".bundle-form").attr("readonly", true);
            $("select.bundle-form option").prop("disabled", true);
            $(".bundle-form").val("");
        }
        else{
            $(".bundle-form").attr("readonly", false);
            $("select.bundle-form option").prop("disabled", false);
            $("select.bundle-form").val("<?=$sel?>");
        }
    }

    //set expiration date to readonly when checkbox is checked
    $("#addProduct-noExpiration").change(function() {
        if(this.checked) {
            $("#addProduct-expirationDate").attr("readonly", true);
            $("#addProduct-expirationDate").val("");
        }
        else{
            $("#addProduct-expirationDate").attr("readonly", false);
        }
    });

    //clear values of quantities when user change the value of bundle pieces
    $("#addProduct-bundlePieces").on("input", function(){
        $("#addProduct-quantitySingle").val("");
        $("#addProduct-quantityBundle").val("");
    });

    //auto input the quantity of bundles
    $("#addProduct-quantitySingle").on("input", function(){
        var bundlePieces = $("#addProduct-bundlePieces").val();
        var qtySingle = $("#addProduct-quantitySingle").val();
        var qtyBundle = $("#addProduct-quantityBundle").val();

        $("#addProduct-quantityBundle").val(parseInt(qtySingle / bundlePieces));
    });
    
    //auto input the quantity of singles
    $("#addProduct-quantityBundle").on("input", function(){
        var bundlePieces = $("#addProduct-bundlePieces").val();
        var qtySingle = $("#addProduct-quantitySingle").val();
        var qtyBundle = $("#addProduct-quantityBundle").val();

        $("#addProduct-quantitySingle").val(parseInt(qtyBundle * bundlePieces));
    });

</script>





<!-- Add Batch Modal -->
<form class="m-3" action="./inv-form-addBatch.php" method="post">
<div class="modal fade" id="addBatchModal" tabindex="-1" role="dialog" aria-labelledby="addBatchModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Add Batch</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        <input id="addBatch-productId" type="hidden" name="productId">
        <input id="addBatch-barcodeId" type="hidden" name="barcodeId">
        <input id="addBatch-productName" type="hidden" name="productName">
        <input id="addBatch-bundlePieces" type="hidden" name="bundlePieces">
        <input id="addBatch-expirable" type="hidden" name="expirable">

        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="addBatch-barcodeIdText"></small>
                <h5 id="addBatch-productNameText"></h5>
                <hr>
            </div>
        </div>

        <!--Price-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="addBatch-price">Price</label>
                <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text" id="price-addon">₱</span></div>
                <input type="number" class="form-control" id="addBatch-price" name="price" aria-describedby="price" step="0.01" min="1" placeholder="0.00" required>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="addBatch-applyPrice" name="applyPrice" value="1">
                    <label class="form-check-label text-muted checkbox-text" for="noExpiration">Apply as new price</label>
                </div>
            </div>
            </div>

            <div class="col d-flex align-items-center">
                <!--<small class="form-text text-muted">
                    Price is based on <b>single quantities</b>.
                </small>-->
            </div>
        </div>

        <!--Quantity-->
        <div class="row">   
            <div class="col">
            <div class="form-group">
                <label for="addBatch-quantitySingle">Quantity (Singles)</label>
                <input type="number" class="form-control" id="addBatch-quantitySingle" name="quantitySingle" aria-describedby="quantitySingle" min="1" required>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="addBatch-quantitySingle">Quantity (Bundles)</label>
                <input type="number" class="form-control" id="addBatch-quantityBundle" name="quantityBundle" aria-describedby="quantityBundle" min="0" required>
                <small class="form-text text-muted" id="addBatch-bundlePiecesText">
                    
                </small>
            </div>
            </div>
        </div>

        <!--Expiration Date and Supplier-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="addBatch-expirationDate">Expiration Date</label>
                <input type="date" class="form-control" id="addBatch-expirationDate" name="expirationDate" aria-describedby="expirationDate" required>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="addBatch-supplier">Supplier</label>
                <select class="form-control" id="addBatch-supplier" name="supplier" required>
                    <option value="" selected disabled>Choose Supplier</option>
                    <?php
                        $supplier_select = $db->sql("
                            SELECT * 
                            FROM `supplier`
                            WHERE `supplier_status` = 1
                            ORDER BY `supplier_name` ASC
                        ");
                        while($row = $supplier_select->fetch_assoc()) {
                            echo '<option value="'.$row["supplier_id"].'">'.$row["supplier_name"].'</option>';
                        }
                    ?>
                </select>
            </div>
            </div>
        </div>
        
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Add Batch</button>
    </div>
</div>
</div>
</div>
</form>

<script>
    //autofocus the barcode input
    $('#addBatchModal').on('shown.bs.modal', function (event) {
        $("#addBatch-price").focus();
    });

    //auto input the quantity of bundles
    $("#addBatch-quantitySingle").on("input", function(){
        var bundlePieces = $("#addBatch-bundlePieces").val();
        var qtySingle = $("#addBatch-quantitySingle").val();
        var qtyBundle = $("#addBatch-quantityBundle").val();

        $("#addBatch-quantityBundle").val(parseInt(qtySingle / bundlePieces));
    });
    
    //auto input the quantity of singles
    $("#addBatch-quantityBundle").on("input", function(){
        var bundlePieces = $("#addBatch-bundlePieces").val();
        var qtySingle = $("#addBatch-quantitySingle").val();
        var qtyBundle = $("#addBatch-quantityBundle").val();

        $("#addBatch-quantitySingle").val(parseInt(qtyBundle * bundlePieces));
    });
</script>



<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Product Details</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">

        <!--Barcode and Product Name-->
        <div class="row">
            <div class="col">
                <span class="badge filter-badge float-right bg-blue p-2" data-toggle="tooltip" data-placement="bottom" title="Category">
                    <i class="fa-solid fa-tag"></i>&emsp;<span id="viewProduct-category">...</span>
                </span>
                <small class="form-text text-muted" id="viewProduct-barcodeId">...</small>
                <h5 id="viewProduct-productName">...</h5>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Price</small>
                <p><span >₱</span> <span id="viewProduct-price">...</span></p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Pieces per Bundle</small>
                <p id="viewProduct-bundlePieces">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Quantity (Single)</small>
                <p id="viewProduct-quantitySingle">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Quantity (Bundle)</small>
                <p id="viewProduct-quantityBundle">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Minimum Stock</small>
                <p id="viewProduct-minimumStock">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Stock Status</small>
                <p id="viewProduct-stockStatus">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Product Description</small>
                <p id="viewProduct-productDescription">...</p>
            </div>

        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Added by</small>
                <p id="viewProduct-addedBy">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Modified by</small>
                <p id="viewProduct-modifiedBy">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Added During</small>
                <p id="viewProduct-addedDuring">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Modified During</small>
                <p id="viewProduct-modifiedDuring">...</p>
            </div>
        </div>

        </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>



<!-- Edit Product Modal -->
<form class="m-3" action="./inv-form-editProduct.php" method="post">
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Edit Product Details</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        <input type="hidden" id="editProduct-productId" name="productId">
        <input type="hidden" id="editProduct-oldBarcode" name="oldBarcode">
        <!--Barcode ID and Product Name-->
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="editProduct-barcodeId">Barcode Number</label>
                    <input type="text" class="form-control" id="editProduct-barcodeId" name="barcodeId" aria-describedby="barcodeId" placeholder="Scan the barcode" maxlength="100">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="editProduct-productName">Product Name</label>
                    <input type="text" class="form-control" id="editProduct-productName" name="productName" aria-describedby="productName" maxlength="100" required>
                </div>
            </div>
            <div class="form-group">
                <label for="editProduct-productProperties">Properties</label>
                <input type="text" class="form-control" id="editProduct-productProperties" name="productProperties" aria-describedby="productProperties" placeholder="Size/Mg" maxlength="100" required>
            </div>
        </div>

        <!--Category and Price-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="editProduct-category">Category</label>
                <select class="form-control" id="editProduct-category" name="category" required>
                    <option value="" selected disabled>Choose Category</option>
                    <?php
                        $category_select = $db->sql("
                            SELECT * 
                            FROM `category`
                            WHERE category_status = 1
                            ORDER BY `category_name` ASC
                        ");
                        while($row = $category_select->fetch_assoc()) {
                            echo '<option value="'.$row["category_id"].'">'.$row["category_name"].'</option>';
                        }
                    ?>
                </select>
            </div>
            </div>
            
            <div class="col">
            <div class="form-group">
            <label for="editProduct-price" id="editProduct-priceLabel">Price (Singles)</label>
                <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text" id="price-addon">₱</span></div>
                <input type="number" class="form-control" id="editProduct-price" name="price" aria-describedby="price" step="0.01" min="1" placeholder="0.00" required>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="editProduct-applyPrice" name="applyPrice" value="1">
                    <label class="form-check-label text-muted checkbox-text" for="noExpiration">Apply price to all of batches</label>
                </div>
            </div>
            </div>
        </div>

        <!--Minimum Stock and Bundle Pieces-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="editProduct-minimumStock">Minimum Stock</label>
                <input type="number" class="form-control" id="editProduct-minimumStock" name="minimumStock" aria-describedby="minimumStock" min="1" required>
                <small class="form-text text-muted">
                    Minimum stock is based on <b>single quantities</b>.
                </small>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="editProduct-bundlePieces">Bundle Pieces</label>
                <input type="hidden" id="editProduct-oldBundlePieces" value="">
                <input type="number" class="form-control editProduct-bundle-form" id="editProduct-bundlePieces" name="bundlePieces" aria-describedby="bundlePieces" min="2" required>
                
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="editProduct-noBundle" name="noBundle" value="1">
                    <label class="form-check-label text-muted checkbox-text" for="noBundle">Check product is being sold by <b>singles only</b>.</label>
                </div>
            </div>
            </div>
        </div>

        <!--Unit of measurements-->
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="editProduct-category">Unit of Measurement (Singles)</label>
                    <select class="form-control" id="editProduct-unitSingle" name="unitSingle" required>
                        <?php
                            $editProduct_unitS_select = $db->sql("
                                SELECT * 
                                FROM `unit_measurement`
                                WHERE `unit_status` = 1
                                ORDER BY `unit_name` ASC
                            ");
                            while($row = $editProduct_unitS_select->fetch_assoc()) {
                                echo '<option value="'.$row["unit_id"].'">Per '.$row["unit_name"].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col">
                <div class="form-group">
                    <label for="editProduct-category">Unit of Measurement (Bundles)</label>
                    <input type="hidden" id="editProduct-oldUnitBundle" value="">
                    <select class="form-control editProduct-bundle-form" id="editProduct-unitBundle" name="unitBundle" required>
                        <?php
                            $editProduct_unitS_select = $db->sql("
                                SELECT * 
                                FROM `unit_measurement`
                                WHERE `unit_status` = 1
                                ORDER BY `unit_name` ASC
                            ");
                            while($row = $editProduct_unitS_select->fetch_assoc()) {
                                echo '<option value="'.$row["unit_id"].'">Per '.$row["unit_name"].'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <!--Sold By and Expirable-->
        <div class="row">
            <div class="col">
                <label for="editProduct-expirable">Expiry Type</label>
                <select class="form-control" name="expirable" id="editProduct-expirable">
                    <option value="1">Expirable</option>
                    <option value="0">Not expirable</option>
                </select>
            </div>

            <div class="col"></div>
        </div>
        
        <!--Product Description-->
        <div class="row">
            <div class="col">
                <label for="editProduct-description">Product Description (Optional)</label>
                <textarea class="form-control" id="editProduct-description" name="description" maxlength="255" placeholder="Add product description (ex. Size/Mg)"></textarea>
            </div>
        </div>


    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Apply Changes</button>
    </div>
</div>
</div>
</div>
</form>

<script>
    //autofocus the barcode input
    $('#editProductModal').on('shown.bs.modal', function (event) {
        $("#editProduct-barcodeId").focus();
    });

    //set bundle pieces and quantity (bundles) to readonly when checkbox is checked
    $("#editProduct-noBundle").change(function() {
        if(this.checked) {
            $(".editProduct-bundle-form").attr("readonly", true);
            $("select.editProduct-bundle-form option").prop("disabled", true);
            $("#editProduct-bundlePieces").val("");
            $("#editProduct-unitBundle").val("");
        }
        else{
            $(".editProduct-bundle-form").attr("readonly", false);
            $("select.editProduct-bundle-form option").prop("disabled", false);
            $("#editProduct-unitBundle").val();
            if($("#editProduct-oldBundlePieces").val() !== "" && $("#editProduct-oldUnitBundle").val() !== ""){
                $("#editProduct-bundlePieces").val($("#editProduct-oldBundlePieces").val());
                $("#editProduct-unitBundle").val($("#editProduct-oldUnitBundle").val());
            }
            else{
                $("select.editProduct-bundle-form").val($("select.editProduct-bundle-form option:first").val());
            }
            
            
        }
        
    });

    $("#editProduct-bundlePieces").on("input", function(){
        $("#editProduct-oldBundlePieces").val($(this).val());
    });

    $("#editProduct-unitBundle").change(function(){
        $("#editProduct-oldUnitBundle").val($(this).val());
    });


</script>




<!-- Archive Product Modal -->
<form class="m-3" action="./inv-form-archiveProduct.php" method="post">
<div class="modal fade" id="archiveProductModal" tabindex="-1" role="dialog" aria-labelledby="archiveProductModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="archiveProductModalTitle">Archive Product</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
    <input type="hidden" id="archiveProduct-productId" name="productId">
        
        <div class="row">
            <div class="col">
                <center>
                    <p>Are you sure you want to archive
                    <br>
                    <b><span id="archiveProduct-productName">...</span>?</p></b>
                </center>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col">
                <!--Return Remarks-->
                <div class="row">
                    <div class="col">
                        <label for="archiveProduct-archiveRemarks">Archive Remarks (Optional)</label>
                        <select class="form-control w-75" name="archiveReason" id="archiveBatch-archiveReason">
                            <option value="" selected>Select a Reason</option>
                            <?php
                                $result = $db->sql("SELECT * FROM archive_reasons WHERE reason_status = 1");
                                while($row = $result->fetch_assoc()){
                                    echo '<option value="'.$row["reason"].'">'.$row["reason"].'</option>';
                                }
                            ?>
                        </select>
                        <br>
                        <textarea class="form-control" id="archiveProduct-archiveRemarks" name="archiveRemarks" maxlength="255" placeholder="Add reason for archiving of product"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <br>

        <!--Archive Validation-->
        <div class="row">
            <div class="col">
                <label for="archiveProduct-archiveValidation">Archive Validation</label>
                <input type="password" class="form-control w-75" id="archiveProduct-archiveValidation" name="archiveValidation" placeholder="Enter archive validation" required>
                <small class="text-success"></small>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" id="archiveProduct-submitBtn" class="btn btn-danger" disabled>Archive Product</button>
    </div>
</div>
</div>
</div>
</form>

<script>
    $("#archiveProduct-archiveValidation").on("input", function(){
        var val = $(this).val();
        var message = $(this).next();

        var archiveBtn = $("#archiveProduct-submitBtn");

        $.post("./ajax/invValidation.php", 
            {
                validation: val
            },
            function(data){
                if(data === "validation-correct"){
                    message.html("Validation Correct");
                    archiveBtn.attr("disabled", false);
                }
                else{
                    message.html("");
                    archiveBtn.attr("disabled", true);
                }
            }
        );
    });
</script>



<!-- View Batch Modal -->
<div class="modal fade" id="viewBatchModal" tabindex="-1" role="dialog" aria-labelledby="viewBatchModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="viewBatch-batchId">...</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">

        <!--Barcode and Product Name-->
        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="viewBatch-barcodeId">...</small>
                <h5 id="viewBatch-productName">...</h5>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Price</small>
                <p><span >₱</span> <span id="viewBatch-price">...</span></p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Supplier</small>
                <p id="viewBatch-supplier">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Quantity (Single)</small>
                <p id="viewBatch-quantitySingle">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Quantity (Bundle)</small>
                <p id="viewBatch-quantityBundle">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Expiration Date</small>
                <p id="viewBatch-expirationDate">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Expiration Status</small>
                <p id="viewBatch-expirationStatus">...</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Added by</small>
                <p id="viewBatch-addedBy">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Modified by</small>
                <p id="viewBatch-modifiedBy">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Added During</small>
                <p id="viewBatch-addedDuring">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Modified During</small>
                <p id="viewBatch-modifiedDuring">...</p>
            </div>
        </div>

        </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>




<!--Edit Batch Modal-->
<form class="m-3" action="./inv-form-editBatch.php" method="post">
<div class="modal fade" id="editBatchModal" tabindex="-1" role="dialog" aria-labelledby="editBatchModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Edit Batch Details</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        <input id="editBatch-batchId" type="hidden" name="batchId">
        <input id="editBatch-productId" type="hidden" name="productId">

        <input id="editBatch-bundlePieces" type="hidden" name="bundlePieces">
        <input id="editBatch-expirable" type="hidden" name="expirable">

        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="editBatch-barcodeIdText"></small>
                <h5 id="editBatch-productNameText" style="padding: 0; margin: 0"></h5>
                <small class="form-text text-muted" id="editBatch-batchIdText"></small>
                <hr>
            </div>
        </div>

        <!--Price-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="addBatch-price">Price</label>
                <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text" id="price-addon">₱</span></div>
                <input type="number" class="form-control" id="editBatch-price" name="price" aria-describedby="price" step="0.01" min="1" placeholder="0.00" required>
                </div>
            </div>
            </div>

            <div class="col d-flex align-items-center">
                <!--<small class="form-text text-muted">
                    Price is based on <b>single quantities</b>.
                </small>-->
            </div>
        </div>

        <!--Quantity-->
        <div class="row">   
            <div class="col">
            <div class="form-group">
                <label for="editBatch-quantitySingle">Quantity (Singles)</label>
                <input type="number" class="form-control" id="editBatch-quantitySingle" name="quantitySingle" aria-describedby="quantitySingle" min="1" required>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="editBatch-quantitySingle">Quantity (Bundles)</label>
                <input type="number" class="form-control" id="editBatch-quantityBundle" name="quantityBundle" aria-describedby="quantityBundle" min="0" required>
                <small class="form-text text-muted" id="editBatch-bundlePiecesText">
                    
                </small>
            </div>
            </div>
        </div>

        <!--Expiration Date and Supplier-->
        <div class="row">
            <div class="col">
            <div class="form-group">
                <label for="editBatch-expirationDate">Expiration Date</label>
                <input type="date" class="form-control" id="editBatch-expirationDate" name="expirationDate" aria-describedby="expirationDate" required>
            </div>
            </div>

            <div class="col">
            <div class="form-group">
                <label for="editBatch-supplier">Supplier</label>
                <select class="form-control" id="editBatch-supplier" name="supplier" required>
                    <option value="" selected disabled>Choose Supplier</option>
                    <?php
                        $supplier_select = $db->sql("
                            SELECT * 
                            FROM `supplier`
                            WHERE `supplier_status` = 1
                            ORDER BY `supplier_name` ASC
                        ");
                        while($row = $supplier_select->fetch_assoc()) {
                            echo '<option value="'.$row["supplier_id"].'">'.$row["supplier_name"].'</option>';
                        }
                    ?>
                </select>
            </div>
            </div>
        </div>
        
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Apply Changes</button>
    </div>
</div>
</div>
</div>
</form>

<script>
    //autofocus the barcode input
    $('#addBatchModal').on('shown.bs.modal', function (event) {
        $("#editBatch-price").focus();
    });

    //auto input the quantity of bundles
    $("#editBatch-quantitySingle").on("input", function(){
        var editBundlePieces = $("#editBatch-bundlePieces").val();
        var editQtySingle = $("#editBatch-quantitySingle").val();
        var editQtyBundle = $("#editBatch-quantityBundle").val();

        $("#editBatch-quantityBundle").val(parseInt(editQtySingle / editBundlePieces));
    });
    
    //auto input the quantity of singles
    $("#editBatch-quantityBundle").on("input", function(){
        var editBundlePieces = $("#editBatch-bundlePieces").val();
        var editQtySingle = $("#editBatch-quantitySingle").val();
        var editQtyBundle = $("#editBatch-quantityBundle").val();

        $("#editBatch-quantitySingle").val(parseInt(editQtyBundle * editBundlePieces));
    });
</script>




<!-- Archive Batch Modal -->
<form class="m-3" action="./inv-form-archiveBatch.php" method="post">
<div class="modal fade" id="archiveBatchModal" tabindex="-1" role="dialog" aria-labelledby="archiveBatchModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title">Archive Batch</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        <input type="hidden" id="archiveBatch-batchId" name="batchId">
        <input type="hidden" id="archiveBatch-productId" name="productId">
        <div class="row">
            <div class="col">
                <center>
                    <p>Are you sure you want to archive
                    <br>
                    <span id="archiveBatch-batchText">...</span>?</p>
                </center>
            </div>
        </div>
        <br>
        <!--Archive Remarks-->
        
        <div class="row">
            <div class="col">
                <label for="archiveBatch-archiveRemarks">Archive Remarks (Optional)</label>
                <select class="form-control w-75" name="archiveReason" id="archiveBatch-archiveReason">
                    <option value="" selected>Select a Reason</option>
                    <?php
                        $result = $db->sql("SELECT * FROM archive_reasons WHERE reason_status = 1");
                        while($row = $result->fetch_assoc()){
                            echo '<option value="'.$row["reason"].'">'.$row["reason"].'</option>';
                        }
                    ?>
                </select>
                <br>
                <textarea class="form-control" id="archiveBatch-archiveRemarks" name="archiveRemarks" maxlength="255" placeholder="Add reason for archiving of batch"></textarea>
            </div>
        </div>
        
        <hr>

        <!--Archive Validation-->
        <div class="row">
            <div class="col">
                <label for="archiveBatch-archiveValidation">Archive Validation</label>
                <input type="password" class="form-control w-75" id="archiveBatch-archiveValidation" name="archiveValidation" placeholder="Enter archive validation" required>
                <small class="text-success"></small>
            </div>
        </div>
            
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger" id="archiveBatch-submitBtn" disabled>Archive Batch</button>
    </div>
</div>
</div>
</div>
</form>
<script>
    $("#archiveBatch-archiveValidation").on("input", function(){
        var val = $(this).val();
        var message = $(this).next();

        var archiveBtn = $("#archiveBatch-submitBtn");

        $.post("./ajax/invValidation.php", 
            {
                validation: val
            },
            function(data){
                if(data === "validation-correct"){
                    message.html("Validation Correct");
                    archiveBtn.attr("disabled", false);
                }
                else{
                    message.html("");
                    archiveBtn.attr("disabled", true);
                }
            }
        );
    });
</script>



<!-- Return Batch Modal -->
<form class="m-3" action="./inv-form-returnBatch.php" method="post">
<div class="modal fade" id="returnBatchModal" tabindex="-1" role="dialog" aria-labelledby="returnBatchModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title">Return Batch</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
    <input type="hidden" id="returnBatch-batchId" name="batchId">
    <input type="hidden" id="returnBatch-productId" name="productId">
    <div class="row">
        <div class="col">
            <center>
                <p>Are you sure you want to return
                <br>
                <span id="returnBatch-batchText">...</span>?</p>
            </center>
        </div>
    </div>
    <br>
    <!--Return Remarks-->
    <div class="row">
        <div class="col">
            <label for="returnBatch-returnRemarks">Return Remarks</label>
            <select class="form-control w-75" name="returnReason" id="returnBatch-returnReason" required>
                <option value="" selected>Select a Reason</option>
                <?php
                    $result = $db->sql("SELECT * FROM archive_reasons WHERE reason_status = 1");
                    while($row = $result->fetch_assoc()){
                        echo '<option value="'.$row["reason"].'">'.$row["reason"].'</option>';
                    }
                ?>
            </select>
            <br>
            <textarea class="form-control" id="returnBatch-returnRemarks" name="returnRemarks" maxlength="255" placeholder="Add reason for returning of batch"></textarea>
        </div>
    </div>

    <hr>

    <!--Archive Validation-->
    <div class="row">
        <div class="col">
            <label for="returnBatch-returnValidation">Return Validation</label>
            <input type="password" class="form-control w-75" id="returnBatch-returnValidation" name="returnValidation" placeholder="Enter return validation" required>
            <small class="text-success"></small>
        </div>
    </div>
        
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success" id="returnBatch-submitBtn" disabled>Return Batch</button>
    </div>
</div>
</div>
</div>
</form>
<script>
    $("#returnBatch-returnValidation").on("input", function(){
        var val = $(this).val();
        var message = $(this).next();

        var archiveBtn = $("#returnBatch-submitBtn");

        $.post("./ajax/invValidation.php", 
            {
                validation: val
            },
            function(data){
                if(data === "validation-correct"){
                    message.html("Validation Correct");
                    archiveBtn.attr("disabled", false);
                }
                else{
                    message.html("");
                    archiveBtn.attr("disabled", true);
                }
            }
        );
    });
</script>


<!--Inventory Logs Modal -->
<div class="modal fade" id="inventoryLogsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Inventory Logs</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body" style="padding: 0; margin: 0">
            <ul class="list-group">
                <?php
                    $user = $_SESSION["user"];
                    $logs = $db->sql("
                        SELECT *, `accounts`.`user_name` 
                        FROM `user_logs`
                            INNER JOIN `accounts` ON `user_logs`.`user_id` = `accounts`.`user_id`
                        WHERE `user_logs`.`user_id` = '$user' AND `activity_type` = 'inventory' AND `product_id` =  0
                        ORDER BY `date_added` DESC,`time_added` DESC
                        LIMIT 50
                    ");

                    if($logs->num_rows > 0){
                        while($row = $logs->fetch_assoc()){
                            echo '
                                <li class="list-group-item">
                                    <small class="text-muted">LOG: '.$row["log_id"].'</small>
                                    <br>
                                    <div class="ml-5">'.$row["user_name"].' '.$row["activity_description"].'</div>
                                    <small class="text-muted float-right">'.date("F d, Y", strtotime($row["date_added"])).'&emsp;'.date("h:i a", strtotime($row["time_added"])).'</small>
                                </li>
                            ';
                        }
                    }
                    else{
                        echo '
                            <li class="list-group-item  p-5 text-center">
                                <small class="text-muted">No Activities to Show</small>
                            </li>
                        ';
                    }
                ?>
                
            </ul>
        </div>
        
    </div>
  </div>
</div>



<!--Inventory Logs Modal -->
<div class="modal fade" id="productLogsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Product Logs</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body" style="padding: 0; margin: 0">
            <ul class="list-group">
                <?php
                    
                ?>
                
            </ul>
        </div>
        
    </div>
  </div>
</div>