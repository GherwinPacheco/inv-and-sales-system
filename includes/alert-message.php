<?php
    //function for showing alert box
    function alertMessage($color, $message){
        echo '
            <div 
                id="alert-box"
                class="alert alert-'.$color.' alert-dismissible fade show shadow-lg" role="alert" 
                style="
                    position: fixed;
                    z-index: 1000;
                    width: 500px;
                    top: 30%;
                    left: 50%;
                    margin-top: -100px; /* Negative half of height. */
                    margin-left: -250px; /* Negative half of width. */
                    text-align: center;
                    animation: transitionIn-Y-over 0.8s;
                ">
                '.$message.'
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!--Tank u user:2513523 from Stackoverflow for this maangas na codes na nag hihide ng alert box hahahahahahahahahahahha-->
            <script>
                $("#alert-box").fadeTo(2000, 500).slideUp(500, function(){
                    $("#alert-box").slideUp(1500);
                });
            </script>
        ';
    }

    $message = isset($_SESSION["message"]) ? $_SESSION["message"] : "";
    if($message == "error"){
        alertMessage("danger", "Something went wrong while performing the activity");
    }
    elseif($message == "product-added"){
        alertMessage("success", "Product has been added successfully");
    }
    elseif($message == "barcode-exist"){
        alertMessage("danger", "
            <b>Failed to add the product</b>
            <br>
            The barcode you have entered already exist.    
        ");
    }
    elseif($message == "batch-added"){
        alertMessage("success", "The new batch has been added successfully.");
    }
    elseif($message == "product-edited"){
        alertMessage("success", "Product has been edited successfully.");
    }
    elseif($message == "edit-barcode-exist"){
        alertMessage("danger", "
            <b>Failed to edit the product details</b>
            <br>
            The barcode you have entered already exist.
        ");
    }
    elseif($message == "product-archived"){
        alertMessage("success", "Product has been archived successfully.");
    }
    elseif($message == "batch-edited"){
        alertMessage("success", "Batch has been edited successfully.");
    }
    elseif($message == "batch-archived"){
        alertMessage("success", "Batch has been archived successfully.");
    }
    elseif($message == "batch-returned"){
        alertMessage("success", "Batch has been added to the <b>Return List</b> successfully.");
    }
    elseif($message == "batch-returned"){
        alertMessage("success", "Batch has been added to the <b>Return List</b> successfully.");
    }
    elseif($message == "return-completed-success"){
        alertMessage("success", "Batch return has been set as Completed.");
    }
    elseif($message == "cancel-return-success"){
        alertMessage("success", "Batch return has been moved back to the <b>Product List</b>.");
    }
    elseif($message == "category-added"){
        alertMessage("success", "Category has been added successfully.");
    }
    elseif($message == "category-exist"){
        alertMessage("danger", "The category name you enter is already in use.");
    }
    elseif($message == "category-edited"){
        alertMessage("success", "Category has been edited successfully.");
    }
    elseif($message == "category-disabled"){
        alertMessage("success", "Category has been disabled successfully.");
    }
    elseif($message == "category-in-use"){
        alertMessage("danger", "Category is in use and cannot be disabled.");
    }
    elseif($message == "unit-added"){
        alertMessage("success", "Unit of measurement has been added successfully.");
    }
    elseif($message == "unit-exist"){
        alertMessage("danger", "The unit name you enter is already in use.");
    }
    elseif($message == "unit-edited"){
        alertMessage("success", "Unit of measurement has been edited successfully.");
    }
    elseif($message == "unit-disabled"){
        alertMessage("success", "Unit of measurement has been disabled successfully.");
    }
    elseif($message == "unit-in-use"){
        alertMessage("danger", "Unit of measurement is in use and cannot be disabled.");
    }
    elseif($message == "supplier-added"){
        alertMessage("success", "Supplier has been added successfully.");
    }
    elseif($message == "supplier-exist"){
        alertMessage("danger", "The supplier name and contact you enter is already in use.");
    }
    elseif($message == "supplier-edited"){
        alertMessage("success", "Supplier has been edited successfully.");
    }
    elseif($message == "supplier-disabled"){
        alertMessage("success", "Supplier has been disabled successfully.");
    }
    elseif($message == "supplier-in-use"){
        alertMessage("danger", "Supplier is in use and cannot be disabled.");
    }
    elseif($message == "reason-added"){
        alertMessage("success", "Archive/Return reason has been added successfully.");
    }
    elseif($message == "reason-edited"){
        alertMessage("success", "Archive/Return reason has been edited successfully.");
    }
    elseif($message == "reason-disabled"){
        alertMessage("success", "Archive/Return reason has been disabled successfully.");
    }
    elseif($message == "transaction-success"){
        alertMessage("success", "Transaction has been processed successfully.");
    }
    elseif($message == "product-restored"){
        alertMessage("success", "The product has been returned to the inventory.");
    }
    elseif($message == "batch-restored"){
        alertMessage("success", "The batch has been returned to the inventory.");
    }
    elseif($message == "promo-added"){
        alertMessage("success", "The promo has been added successfully.");
    }
    elseif($message == "promo-updated"){
        alertMessage("success", "The promo has been edited successfully.");
    }
    elseif($message == "promo-disabled"){
        alertMessage("success", "The promo has been removed successfully.");
    }
    elseif($message == "product-promo-active"){
        alertMessage("danger", "
            <b>Failed to add the promo</b>
            <br>
            The product you have selected already has an active promo
        ");
    }
    elseif($message == "discount-added"){
        alertMessage("success", "The discount has been added successfully.");
    }
    elseif($message == "discount-updated"){
        alertMessage("success", "The discount has been edited successfully.");
    }
    elseif($message == "discount-disabled"){
        alertMessage("success", "The discount has been removed successfully.");
    }
    elseif($message == "discount-existing"){
        alertMessage("danger", "
            <b>Failed to add the discount</b>
            <br>
            The discount name you have entered is already in use.
        ");
    }
    elseif($message == "storeDetails-edited"){
        alertMessage("success", "Store details has been edited successfully.");
    }
    elseif($message == "edit-password-mismatch"){
        alertMessage("danger", "
            <b>Failed to edit account details.</b>
            <br>
            The new password and confirmation password you have entered does not match.
        ");
    }
    elseif($message == "edit-wrong-password"){
        alertMessage("danger", "
            <b>Failed to edit account details.</b>
            <br>
            The current password you have entered is incorrect.
        ");
    }
    elseif($message == "edit-username-exist"){
        alertMessage("danger", "
            <b>Failed to edit account details.</b>
            <br>
            The username you have entered is already in use.
        ");
    }
    elseif($message == "account-edited-successfully"){
        alertMessage("success", "The account details has been edited successfully.");
    }
    elseif($message == "add-password-mismatch"){
        alertMessage("danger", "
            <b>Failed to add account.</b>
            <br>
            The password and confirmation password you have entered does not match.
        ");
    }
    elseif($message == "add-username-exist"){
        alertMessage("danger", "
            <b>Failed to add account.</b>
            <br>
            The username you have entered is already in use.
        ");
    }
    elseif($message == "account-added-successfully"){
        alertMessage("success", "The account has been added successfully.");
    }
    elseif($message == "wrong-credentials"){
        alertMessage("danger", "The username or password you have entered is incorrect");
    }
    elseif($message == "password-forgotten"){
        alertMessage("success", "The password has been changed successfully");
    }
    elseif($message == "validation-wrong"){
        alertMessage("danger", "You have entered a wrong validation code");
    }
    elseif($message == "validation-changed"){
        alertMessage("success", "The validation has been changed successfully");
    }
    elseif($message == "what"){
        alertMessage("danger", "what");
    }
    

    

    $message = "";
    $_SESSION["message"] = "";

    if(isset($_SESSION["error"])){
        $_SESSION["error"] = "";
    }
    
    
?>

