<?php
    include("./includes/database.php");
    $db = new Database();
    
    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);
    
    $inv->updateBundleQuantity("all");
    $inv->updateQuantity("all");
    $inv->updateStockStatus();
    $inv->updateExpirationStatus();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <script>
        window.location.href = "./login-page.php";
    </script>
</body>

</html>