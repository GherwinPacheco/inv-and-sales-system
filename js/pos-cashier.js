$(document).ready(function () {
    productSearchAjax("1");
    $("input").keydown(function (e) {
        // Enter was pressed without shift key
        if (e.keyCode == 220) {
            // prevent default behavior
            e.preventDefault();
        }
        else if (e.keyCode == 13) {
            // prevent default behavior
            e.preventDefault();
        }
    });
    $("#productSearch").focus();
});

var selectedTab = 1;
var productSelected = $('.product-row.selected');
var itemSelected = undefined;
$(window).keydown(function (e) {
    if (e.keyCode == 9) { //if tab is clicked, swap selected tab
        $("#productSearch").blur();
        $("#amountTenderedInput").blur();
        $("#discountInput").blur();
        $("#customerId").blur();
        switch (selectedTab) {
            case 1:
                if ($(".selected-row").length) {
                    selectedTab = 2;
                    $('#productList').find('.selected').removeClass("selected");
                }
                break;
            case 2:
                selectedTab = 1;
                $('#selectedItems').find('.selected').removeClass("selected");
                break;
            case 3:
                selectedTab = 1;
                break;
            default:
                selectedTab = 3;
                return;
        }
        e.preventDefault();
    }
    /*else if (e.keyCode == 112) {  //if f1 is clicked
        selectedTab = 1;
        $('#selectedItems').find('.selected').removeClass("selected");
        $("#productSearch").focus();
        e.preventDefault();
    }*/
    else if (e.keyCode == 112) {  //if f1 is clicked
        selectedTab = 3;
        $('#productList').find('.selected').removeClass("selected");
        $('#selectedItems').find('.selected').removeClass("selected");
        $("#amountTenderedInput").focus();
        e.preventDefault();
    }
    else if (e.keyCode == 113) {  //if f2 is clicked
        selectedTab = 3;
        $('#productList').find('.selected').removeClass("selected");
        $('#selectedItems').find('.selected').removeClass("selected");
        $("#discountInput").focus();
        e.preventDefault();
    } else if (e.keyCode == 114) {  //if f3 is clicked
        selectedTab = 3;
        $('#productList').find('.selected').removeClass("selected");
        $('#selectedItems').find('.selected').removeClass("selected");
        $("#customerId").focus();
        e.preventDefault();
    } else if (e.keyCode == 115) {  //if f3 is clicked
        selectedTab = 3;
        $('#productList').find('.selected').removeClass("selected");
        $('#selectedItems').find('.selected').removeClass("selected");
        $("#discountValidation").focus();
        e.preventDefault();
    }
    else if (e.keyCode == 13 && event.shiftKey) {  //if enter + shift is clicked
        selectedTab = 3;
        $('#productList').find('.selected').removeClass("selected");
        $('#selectedItems').find('.selected').removeClass("selected");
        $("#processTransaction").click();
        e.preventDefault();
    }
    //for productList tab
    if (selectedTab == 1 && !($(".quantity").is(":focus"))) {
        $("#productSearch").focus();

        if ($('#productList').find('.selected').length == 0) {
            $('.product-row').first().addClass('selected');
            productSelected = $('.product-row.selected');
        }
        else {
            productSelected = $('.product-row.selected');
            switch (e.which) {
                case 38: // up key
                    productSelected.removeClass('selected');
                    var bool = true;
                    while (bool) {
                        productSelected = productSelected.prev();
                        if (productSelected.prop("nodeName") == "TR") {
                            productSelected.addClass('selected');
                            break;
                        }
                        else {
                            $(".product-row").last().addClass('selected');
                            productSelected = $('.product-row.selected');
                            break;
                        }
                    }
                    break;
                case 40: // down key
                    productSelected.removeClass('selected');
                    var bool = true;
                    while (bool) {
                        productSelected = productSelected.next();
                        if (productSelected.prop("nodeName") == "TR") {
                            productSelected.addClass('selected');
                            break;
                        }
                        else {
                            $(".product-row").first().addClass('selected');
                            productSelected = $('.product-row.selected');
                            break;
                        }
                    }
                    break;
                case 13: //add product when Enter is clicked
                    productSelected.find(".add-button").click();
                    break;
                default:
                    return; // exit this handler for other keys
            }
            e.preventDefault();
            $("#productList").find(".selected").focus();
            $("#productSearch").focus();
        }
    }
    else if (selectedTab == 2) {  //for selected items tab
        if ($('#selectedItems').find('.selected').length == 0) {
            $(".selected-row").first().addClass("selected");
            itemSelected = $('.selected-row.selected');
        }
        else {
            switch (e.which) {
                case 38: // up key
                    itemSelected.removeClass('selected');
                    var bool = true;
                    while (bool) {
                        itemSelected = itemSelected.prev();
                        if (itemSelected.prop("nodeName") == "TR") {
                            itemSelected.addClass('selected');
                            break;
                        }
                        else {
                            $(".selected-row").last().addClass('selected');
                            itemSelected = $('.selected-row.selected');
                            break;
                        }
                    }
                    break;
                case 40: // down key
                    itemSelected.removeClass('selected');
                    var bool = true;
                    while (bool) {
                        itemSelected = itemSelected.next();
                        if (itemSelected.prop("nodeName") == "TR") {
                            itemSelected.addClass('selected');
                            break;
                        }
                        else {
                            $(".selected-row").first().addClass('selected');
                            itemSelected = $('.selected-row.selected');
                            break;
                        }
                    }
                    break;
                case 187:   //add quantity when add key is clicked
                    var quantityForm = itemSelected.find(".quantity");
                    if (parseInt(quantityForm.val()) < parseInt(quantityForm.attr("max"))) {
                        var qty = parseInt(quantityForm.val()) + 1;
                        quantityForm.val(qty).trigger("input");
                        var id = quantityForm.attr("id").split("-")[1];
                    }

                    break;
                case 189:   //decrement quantity when minus key is clicked
                    var quantityForm = itemSelected.find(".quantity");
                    var qty = parseInt(quantityForm.val()) - 1;
                    quantityForm.val(qty).trigger("input");
                    break;
                case 37:   //change to simgles when left arrow key is clicked
                    var quantityTypeForm = itemSelected.find(".quantityType");
                    var id = quantityTypeForm.attr("id").split("-")[1];
                    quantityTypeForm.val("1").change();
                    break;
                case 39:   //change to bundles when right arrow key is clicked
                    var quantityTypeForm = itemSelected.find(".quantityType");
                    var id = quantityTypeForm.attr("id").split("-")[1];
                    if ($(`#bundle-${id}`).val() > 0 && $(`#bundleQuantity-${id}`).val() > 0) {
                        quantityTypeForm.val("2").change();
                    }
                    break;
                case 46:   //click remove button when delete is clicked
                    var removeButton = itemSelected.find(".remove-item");
                    var id = removeButton.attr("id").split("-")[2];
                    removeButton.click();
                    updateTransactionDetails();
                    if ($('#selectedItems').find('.selected').length == 0) {
                        if ($(".selected-row").length) {
                            $(".selected-row").first().addClass("selected");
                            itemSelected = $('.selected-row.selected');
                        }
                        else {
                            selectedTab = 1;
                            $('.product-row').first().addClass('selected');
                            productSelected = $('.product-row.selected');
                            $("#productSearch").focus();
                        }

                    }
                    break;
                default:
                    return; // exit this handler for other keys
            }
        }

        e.preventDefault();
        $("#selectedItems").find(".selected").focus();
    }
});

$("#amountTenderedInput").focus(function () {
    selectedTab = 3;
    $("#productList").find(".selected").removeClass("selected");
    $("#selectedItems").find(".selected").removeClass("selected");
});

$("#discountInput").focus(function () {
    selectedTab = 3;
    $("#productList").find(".selected").removeClass("selected");
    $("#selectedItems").find(".selected").removeClass("selected");
});



$("#productSearch").on("input", function () {
    var value = ($(this).val()) ?
        "`barcode_id` = '"+$(this).val()+"' OR CONCAT_WS(' ', `product_name`, `properties`) LIKE '%"+$(this).val()+"%'" :
        "1";

    productSearchAjax(value);
    
});

function productSearchAjax(val) {
    $.post(
        "./ajax/pos-productSearch.php",
        { search: val },
        function (result) {
            $("#productList").html(result);
            if($("#productList tr").length == 1 && $("#productList").find(".barcodeId").val() == $("#productSearch").val()){
                $("#productList").find(".add-button").click();
            }
        });
}

function removeList(id, elem) {
    $(`#add-product-${id}`).html(`<i class="fa-solid fa-plus"></i>`);
    return (elem.parentNode).parentNode.remove();
}

/*function setUnit(id, single, bundle){
    var quantityType = $(`#quantityType-${id}`).val();
    if(quantityType == 1){
        $(`#unitId-${id}`).val(single);
    }
    else{
        $(`#unitId-${id}`).val(bundle);
    }
}*/

function updateItemSubtotal(id) {
    if ($(`#quantity-${id}`).val() == "" || $(`#quantity-${id}`).val() < 1) {
        $(`#quantity-${id}`).val("1");
    }


    var quantity = $(`#quantity-${id}`).val();
    var price = $(`#price-${id}`).val();


    var subtotal = price * quantity;
    if($(`#promoType-${id}`).val() === '2' && $(`#quantityType-${id}`).val() === '1'){
        var promoDiscout = parseInt($(`#promoPercentage-${id}`).val());
        subtotal = subtotal - ( (promoDiscout / 100) * subtotal );
    }
    else if($(`#promoType-${id}`).val() === '3' && $(`#quantityType-${id}`).val() === '1'){
        var promoDiscout = parseInt($(`#promoPercentage-${id}`).val());
        var minSpend = parseInt($(`#minSpend-${id}`).val());
        if(subtotal >= minSpend){
            subtotal = subtotal - ( (promoDiscout / 100) * subtotal );
        }
    }





    $(`#subtotal-${id}`).val(subtotal);
    $(`#subtotalText-${id}`).html("₱ " + roundNumText(subtotal));
    updateTransactionDetails();
}

function updateItemPrice(id) {
    var quantityType = $(`#quantityType-${id}`).val();
    var originalPrice = $(`#originalPrice-${id}`).val();
    var bundlePieces = $(`#bundle-${id}`).val();
    if (quantityType == 1) {
        $(`#price-${id}`).val(originalPrice);
        $(`#priceText-${id}`).html("₱ " + roundNumText(originalPrice));
        $(`#promoBadge-${id}`).css("display", "inline");
    }
    else {
        $(`#price-${id}`).val(originalPrice * bundlePieces);
        $(`#priceText-${id}`).html("₱ " + roundNumText(originalPrice * bundlePieces));
        $(`#promoBadge-${id}`).css("display", "none");
    }

    updateItemSubtotal(id);

    updateTransactionDetails();
}

/*function updatePromo(id, promo){
    var quantityType = $(`#quantityType-${id}`).val();
    if(quantityType == 1){
        $(`#productPromo-${id}`).val(promo);
        $(`#promoBadge-${id}`).css("display", "inline");
    }
    else{
        $(`#productPromo-${id}`).val("0");
        $(`#promoBadge-${id}`).css("display", "none");
    }
}*/

function setMaxQuantity(id) {
    var quantityType = $(`#quantityType-${id}`).val();
    var single = $(`#singleQuantity-${id}`).val();
    var bundle = $(`#bundleQuantity-${id}`).val();
    if (quantityType == 1) {
        $(`#quantity-${id}`).attr("max", single);
    }
    else {
        $(`#quantity-${id}`).attr("max", bundle);
    }
    updateTransactionDetails();
}

//round number to 2 decimal places (returns float)
function roundNum(num) {
    return (Math.round(parseFloat(num) * 100) / 100).toFixed(2);
}

//currency format (returns string)
function roundNumText(num){
    var val = roundNum(num);
    var parts = val.toString().split(".");
    var result = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
    return result;
}

function updateTransactionDetails() {
    var total = 0;
    $('.subtotal').each(function () {
        total += parseFloat($(this).val());
    });
    var discount = parseInt($("#discountInput").val());
    if (discount > 0) {
        total = parseFloat(total - (total * discount / 100));
    }

    total = roundNum(total);
    $("#amountTenderedInput").attr("min", total);

    var amountTendered = $("#amountTenderedInput").val() ? parseFloat($("#amountTenderedInput").val()) : 0;
    var change = roundNum(amountTendered - total);
    var vatableSales = roundNum(total / 1.12);
    var vatAmount = roundNum(total - vatableSales);

    $(".totalText").html("₱ " + roundNumText(total));
    $(".changeText").html("₱ " + roundNumText(change));
    $(".vatableSalesText").html("₱ " + roundNumText(vatableSales));
    $(".vatAmountText").html("₱ " + roundNumText(vatAmount));
    $(".discountText").html(discount + "%");

    $("#total").val(total);
    $("#amountTendered").val(amountTendered);
    $("#change").val(change);
    $("#discount").val(discount);
    $("#vatableSales").val(vatableSales);
    $("#vatAmount").val(vatAmount);

}


$("#amountTenderedInput").on("input", function () {
    updateTransactionDetails();
});





let discountValid = true;

function submitToggle() {
    var x = $(".selected-row").length;
    var y = $("#discountInput").val();

    if (x > 0 && y == 0  && discountValid == true) {
        $("#processTransaction").prop("disabled", false);
    }
    else if (x > 0 && y > 0  && discountValid == true) {
        $("#processTransaction").prop("disabled", false);
    }
    else {
        $("#processTransaction").prop("disabled", true);
    }
    
}

$("#discountInput").change(function () {
    if (parseInt($(this).val()) > 0) {
        discountValid = false;
        $("#customerIdForm").css("display", "block");
        $("#discountValidationForm").html(`
            <label for="amountTenderedInput">Discount Validation&emsp;&emsp;<span class="key-hint text-muted">[F4]</span>
            <a type="button" class="row-tooltip" data-toggle="tooltip" data-placement="top" title="Code for applying discount" required>
                <i class="fa-solid fa-question"></i>
            </a>
            </label>
            <input type="password" class="form-control" id="discountValidation" name="discountValidation" placeholder="Enter code">
            <small class="text-success"></small>

            <!--Disable the submit btn if transaction has a discount and the validation is wrong-->
            <script>
                $("#discountValidation").on("input", function(){
                    var val = $(this).val();
                    var message = $(this).next();
                
                    var submitBtn = $("#processTransaction");
                
                    $.post("./ajax/posValidation.php", 
                        {
                            validation: val
                        },
                        function(data){
                            if(data === "validation-correct"){
                                discountValid = true;
                                message.html("Validation Correct");
                                submitToggle();
                            }
                            else{
                                discountValid = false;
                                message.html("");
                                submitToggle();
                            }
                        }
                    );
                });
            </script>
        `);
    }
    else {
        //$("#customerIdForm").val("");
        discountValid = true;
        $("#customerIdForm").css("display", "none");
        $("#discountValidationForm").html("");
    }
    submitToggle();
    updateTransactionDetails();
});





$('#transactionDetailsModal').on('hide.bs.modal', function (e) {
    updateTransactionDetails();
});

$('#transactionDetailsModal').on('hide.bs.modal', function (e) {
    $("#amountTenderedInput").val("");
    $("#discountInput").val("0");
    updateTransactionDetails();
});



function preventSubmit(e) {
    e.preventDefault();
    someBug();
    return false;
}
