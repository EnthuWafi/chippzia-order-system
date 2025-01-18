<?php

require("../../includes/functions.inc.php");

session_start();

member_login_required();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try {
        if (!empty($postedToken)) {
            if (isTokenValid($postedToken)) {
                // Process form
                if (isset($_POST["product_id"])) {
                    $productID = htmlspecialchars($_POST["product_id"]);
                    $quantity = htmlspecialchars($_POST["quantity"]);
                    $cart = $_SESSION["cart"] ?? [];
                    $product = retrieveProduct($productID);

                    if (!$product) {
                        throw new Exception("Invalid product");
                    }

                    //loop thru cart (see if duplicates exists)
                    $duplicateItem = null;
                    foreach ($cart as $itemKey => $itemValue) {
                        //match
                        if ($itemValue["product"]["PRODUCT_ID"] == $product["PRODUCT_ID"]) {
                            $duplicateItem = $itemValue;
                            unset($cart[$itemKey]);
                            break;
                        }
                    }

                    if (!is_numeric($quantity)) {
                        makeToast("error", "Quantity must be a number!", "Error");
                    } else if ($quantity <= 0) {
                        makeToast("error", "Quantity cannot be lower than zero!", "Error");
                    } else {
                        if (!empty($duplicateItem)) {
                            $quantity = $duplicateItem["quantity"] + $quantity;
                        }

                        if ($quantity > $product["INVENTORY_QUANTITY"]) {
                            throw new Exception( "Insufficient inventory! Only {$product['INVENTORY_QUANTITY']} items available.");
                        }

                        $array = ["product" => $product, "quantity" => $quantity];
                        array_push($cart, $array);

                        $_SESSION["cart"] = $cart;
                        makeToast("success", "Product added to Cart", "Success");
                    }
                }
            } else {
                makeToast("warning", "Please refrain from attempting to resubmit previous form", "Warning");
            }
        }
    }
    catch (Exception $e) {
        makeToast("error", $e->getMessage(), "Error");
    }

    header("Location: ".BASE_URL."account/shop.php");
    die();
}

$products = retrieveAllProduct();
$productCount = retrieveProductCount()["COUNT"];
$token = getToken();

displayToast();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Shop</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php header_bar("Shop") ?>

            <!-- todo DASHBOARD here  -->
            <div class="row">
                <div class="container">
                    <div class="row mt-4 gx-4 ms-3">
                        <div class="row">
                            <div class="float-end text-end mb-3">
                                <a type="button" href="<?= BASE_URL ?>order/cart.php" class="btn btn-primary bg-yellow-dark">
                                    <i class='bi bi-cart-fill text-end text-white'></i>
                                </a>
                            </div>

                        </div>
                        <div class="shadow p-3 mb-5 bg-body rounded row gx-3">
                            <div class="row mb-3">
                                <span class="h3"><?= $productCount ?> product(s) found</span>
                            </div>
                            <?php
                            $count = 0;
                            $base_url = BASE_URL;

                            if ($products != null){

                                foreach ($products as $product) {
                                    //price
                                    $productCost = number_format((float)$product["PRODUCT_PRICE"], 2, '.', '');

                                    $totalLeft = !empty($product["INVENTORY_QUANTITY"]) ? "Total left: {$product["INVENTORY_QUANTITY"]}" : "Sold out!" ;

                                    if ($count % 4 == 0) {
                                        echo "<div class='row'>";
                                    }
                                    $count++;
                                    echo "
                                <div class='col-md-3 p-1'>
                                    <div class='align-middle text-center'>
                                          <div class='card p-3'>
                                            <img src='{$product["PRODUCT_IMAGE"]}' class='card-img-top' alt='Product Image'>
                                              <div class='card-body h-100'>
                                                <h5 class='card-title'>{$product["PRODUCT_NAME"]}</h5>
                                                <p class='card-text'>Product Code: {$product["PRODUCT_ID"]}<br>
                                                {$totalLeft}</p>
                                                
                                                <p class='card-text'>Price: RM{$productCost}</p>
           
                                                <form action='{$base_url}account/shop.php' method='post'>
                                                    <div class='row gy-2'>
                                                        <input type='number' class='form-control' name='quantity' placeholder='Quantity' min='0' max=''>
                                                        <input type='hidden' name='product_id' value='{$product["PRODUCT_ID"]}'>
                                                        <input type='hidden' name='token' value='$token'>
                                                        <button type='submit' class='btn btn-dark text-white'><i class='bi bi-cart-plus'></i> Add to Cart</button>                                          
                                                    </div>
                                                    </form>
                                              </div>                                        
                                          </div>                              
                                    </div>
                                </div>";
                                    if ($count % 4 == 0) {
                                        echo "</div>";
                                    }
                                }
                                if ($count % 4 != 0){
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php footer(); ?>

        </main>
    </div>
</div>
<?php body_script_tag_content();?>
</body>

</html>